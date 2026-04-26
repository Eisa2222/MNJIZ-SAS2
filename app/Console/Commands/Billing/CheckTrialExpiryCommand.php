<?php

declare(strict_types=1);

namespace App\Console\Commands\Billing;

use App\Enums\Billing\SubscriptionStatus;
use App\Events\Billing\SubscriptionExpired;
use App\Mail\TrialExpiredMail;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Phase G — terminal-state command for trialing subscriptions whose
 * `trial_ends_at` has passed.
 *
 *   1. Find subscriptions: status = trialing AND trial_ends_at <= now
 *   2. For each, INSIDE a per-tenant DB::transaction:
 *      - Flip status → Expired, set ends_at = now
 *      - If `trial_suspend_after_expiry` is true: tenant.status = suspended
 *      - Set trial_expired_notified_at = now (one-shot guard)
 *   3. Dispatch SubscriptionExpired event (so Phase 5 listeners that
 *      revert to free plan still fire — we never reach into their logic)
 *   4. If `notify_trial_expiring` is true, queue TrialExpiredMail
 *
 * Idempotency:
 *   - The `where status = trialing` filter naturally skips any sub
 *     that was already expired by a prior run.
 *   - The `whereNull('trial_expired_notified_at')` extra guard prevents
 *     a re-run from re-mailing if the prior run somehow committed the
 *     status flip but failed at mail dispatch.
 *
 * Schedule: registered in routes/console.php at 00:00 daily.
 *
 * Pre-existing Phase 5 commands (`billing:expire-grace-period` at 00:20)
 * stay untouched — that command targets PastDue subscriptions only,
 * never Trialing.
 */
final class CheckTrialExpiryCommand extends Command
{
    protected $signature = 'saas:check-trial-expiry
                            {--dry-run : Print actions without executing}';

    protected $description = 'Expire trialing subscriptions whose trial_ends_at has passed; optionally suspend the tenant.';

    public function handle(): int
    {
        $suspendTenants = (bool) SystemSetting::get('trial_suspend_after_expiry', true);
        $sendMail       = (bool) SystemSetting::get('notify_trial_expiring',     true);

        $query = Subscription::withoutTenancy()
            ->where('status', SubscriptionStatus::Trialing->value)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->whereNull('trial_expired_notified_at');

        $total      = (clone $query)->count();
        $expired    = 0;
        $suspended  = 0;
        $mailed     = 0;

        $this->info("Found {$total} trial subscriptions to expire (suspend={$this->bool($suspendTenants)}, notify={$this->bool($sendMail)}).");

        $query->chunkById(100, function ($subs) use ($suspendTenants, $sendMail, &$expired, &$suspended, &$mailed) {
            // Phase H+ collaborative-audit fix: batch-load tenants for the
            // chunk in ONE query (was N round-trips per chunk previously).
            $tenantIds = $subs->pluck('tenant_id')->unique()->all();
            $tenants   = Tenant::query()->whereIn('id', $tenantIds)->get()->keyBy('id');

            foreach ($subs as $sub) {
                /** @var Subscription $sub */
                if ($this->option('dry-run')) {
                    $this->line("[dry-run] would expire subscription #{$sub->id} tenant={$sub->tenant_id}");
                    continue;
                }

                $tenant = $tenants->get($sub->tenant_id);
                if (! $tenant) {
                    Log::warning('saas.check_trial_expiry.tenant_missing', ['subscription_id' => $sub->id]);
                    continue;
                }

                try {
                    $tenantWasSuspended = false;

                    DB::transaction(function () use ($sub, $tenant, $suspendTenants, &$tenantWasSuspended): void {
                        $sub->status                    = SubscriptionStatus::Expired;
                        $sub->ends_at                   = $sub->ends_at ?? now();
                        $sub->trial_expired_notified_at = now();
                        $sub->save();

                        if ($suspendTenants && $tenant->status === Tenant::STATUS_ACTIVE) {
                            $tenant->status = Tenant::STATUS_SUSPENDED;
                            $tenant->save();
                            $tenantWasSuspended = true;
                        }
                    });

                    $expired++;
                    if ($tenantWasSuspended) {
                        $suspended++;
                    }

                    // Dispatch event AFTER the commit — listeners (e.g.
                    // Phase 5 RevertToFreePlanOnSubscriptionExpired) get
                    // a fully-persisted row.
                    event(new SubscriptionExpired($sub->fresh()));

                    if ($sendMail) {
                        $owner = TenantContext::runAs($tenant, fn () =>
                            User::query()->where('tenant_id', $tenant->id)->first()
                        );

                        if ($owner) {
                            try {
                                Mail::to($owner->email)->queue(
                                    new TrialExpiredMail($tenant, $owner, $sub->fresh(), $tenantWasSuspended)
                                );
                                $mailed++;
                            } catch (Throwable $e) {
                                Log::warning('saas.check_trial_expiry.mail_failed', [
                                    'subscription_id' => $sub->id,
                                    'message'         => $e->getMessage(),
                                ]);
                            }
                        }
                    }

                    $this->line("Expired #{$sub->id} tenant={$tenant->id}".($tenantWasSuspended ? ' (tenant suspended)' : ''));
                } catch (Throwable $e) {
                    Log::error('saas.check_trial_expiry.failed', [
                        'subscription_id' => $sub->id,
                        'message'         => $e->getMessage(),
                    ]);
                    $this->error("Failed to expire #{$sub->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Expired: {$expired}  Suspended-tenants: {$suspended}  Mailed: {$mailed}");

        return self::SUCCESS;
    }

    private function bool(bool $v): string
    {
        return $v ? 'true' : 'false';
    }
}
