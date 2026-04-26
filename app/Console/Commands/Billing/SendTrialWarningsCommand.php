<?php

declare(strict_types=1);

namespace App\Console\Commands\Billing;

use App\Enums\Billing\SubscriptionStatus;
use App\Mail\TrialExpiryWarningMail;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Phase G — milestone-driven trial-expiry warnings.
 *
 * Reads `SystemSetting::trial_warning_days` (default `[7, 3, 1]`) and
 * for each milestone N:
 *
 *   1. Find subscriptions where status=trialing AND
 *      DATE(trial_ends_at) = today + N days.
 *   2. Per-milestone idempotency: skip subs whose
 *      `meta.trial_warning_days_sent` already includes N.
 *   3. If `notify_trial_expiring` is true, queue TrialExpiryWarningMail
 *      and append N to the meta tracker, plus stamp
 *      `trial_warning_sent_at = now`.
 *
 * Idempotent guarantees:
 *   - Two runs of this command on the same calendar day will NOT email
 *     the same operator twice (per-N tracker in meta).
 *   - The `notify_trial_expiring` setting OFF skips the entire mail
 *     dispatch path AND the meta update, so flipping it back ON the
 *     next day correctly resumes warnings from there.
 *
 * Schedule: registered in routes/console.php at 08:00 daily.
 */
final class SendTrialWarningsCommand extends Command
{
    protected $signature = 'saas:send-trial-warnings
                            {--dry-run : Print actions without executing}';

    protected $description = 'Email warnings to trialing subscriptions whose trial_ends_at is approaching one of the configured milestones.';

    public function handle(): int
    {
        $sendMail     = (bool) SystemSetting::get('notify_trial_expiring', true);
        $warningDays  = $this->normalizeWarningDays(SystemSetting::get('trial_warning_days', [7, 3, 1]));

        if (count($warningDays) === 0) {
            $this->info('No trial_warning_days configured — nothing to do.');
            return self::SUCCESS;
        }

        $totalQueued = 0;
        $totalSkipped = 0;

        foreach ($warningDays as $daysBefore) {
            $targetDate = now()->copy()->addDays($daysBefore)->toDateString();

            $query = Subscription::withoutTenancy()
                ->where('status', SubscriptionStatus::Trialing->value)
                ->whereNotNull('trial_ends_at')
                ->whereDate('trial_ends_at', $targetDate);

            $count = (clone $query)->count();
            $this->info("Milestone {$daysBefore} days → {$count} candidate subscriptions (target trial_ends_at date {$targetDate}).");

            $query->chunkById(100, function ($subs) use ($daysBefore, $sendMail, &$totalQueued, &$totalSkipped) {
                // Phase H+ collaborative-audit fix: batch-load tenants for
                // the chunk in ONE query (was N round-trips per chunk).
                $tenantIds = $subs->pluck('tenant_id')->unique()->all();
                $tenants   = Tenant::query()->whereIn('id', $tenantIds)->get()->keyBy('id');

                foreach ($subs as $sub) {
                    /** @var Subscription $sub */
                    $sentDays = (array) ($sub->meta['trial_warning_days_sent'] ?? []);

                    if (in_array($daysBefore, $sentDays, true)) {
                        $totalSkipped++;
                        continue;   // already sent this milestone — idempotency
                    }

                    if ($this->option('dry-run')) {
                        $this->line("[dry-run] would warn subscription #{$sub->id} ({$daysBefore}d before)");
                        continue;
                    }

                    $tenant = $tenants->get($sub->tenant_id);
                    if (! $tenant) {
                        Log::warning('saas.send_trial_warnings.tenant_missing', ['subscription_id' => $sub->id]);
                        continue;
                    }

                    if (! $sendMail) {
                        $totalSkipped++;
                        continue;   // notifications globally disabled
                    }

                    $owner = TenantContext::runAs($tenant, fn () =>
                        User::query()->where('tenant_id', $tenant->id)->first()
                    );

                    if (! $owner) {
                        Log::warning('saas.send_trial_warnings.owner_missing', [
                            'subscription_id' => $sub->id,
                            'tenant_id'       => $tenant->id,
                        ]);
                        continue;
                    }

                    try {
                        Mail::to($owner->email)->queue(
                            new TrialExpiryWarningMail($tenant, $owner, $sub->fresh(['plan']), $daysBefore)
                        );

                        $sentDays[] = $daysBefore;
                        $meta = (array) $sub->meta;
                        $meta['trial_warning_days_sent'] = array_values(array_unique($sentDays));

                        // Avoid touching the model directly (mass-assign
                        // protected `meta` is fine but withoutTenancy
                        // queries don't carry the BelongsToTenant
                        // creating-hook). update() is safe here.
                        $sub->update([
                            'meta'                  => $meta,
                            'trial_warning_sent_at' => now(),
                        ]);

                        $totalQueued++;
                        $this->line("Warned #{$sub->id} ({$daysBefore}d) → {$owner->email}");
                    } catch (Throwable $e) {
                        Log::warning('saas.send_trial_warnings.mail_failed', [
                            'subscription_id' => $sub->id,
                            'days_before'     => $daysBefore,
                            'message'         => $e->getMessage(),
                        ]);
                    }
                }
            });
        }

        $this->info("Queued: {$totalQueued}  Skipped: {$totalSkipped}");

        return self::SUCCESS;
    }

    /**
     * Coerce the SystemSetting value into an int[] of distinct positive
     * milestone days, sorted descending so we email the longest-out
     * milestone first (cosmetic — avoids out-of-order log output).
     *
     * @param  mixed $raw
     * @return array<int, int>
     */
    private function normalizeWarningDays(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [(int) $raw];
        }

        $days = array_filter(
            array_map(static fn ($v) => (int) $v, $raw),
            static fn (int $v) => $v > 0,
        );

        $days = array_values(array_unique($days));
        rsort($days);

        return $days;
    }
}
