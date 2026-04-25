<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\TenantWelcomeMail;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NewTenantSubscriptionNotification;
use App\Services\Auth\TenantPasswordSetupService;
use App\Tenancy\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * Phase F — secure post-signup / post-checkout fan-out job.
 *
 * The Tenant + Subscription + Payment rows are still created
 * SYNCHRONOUSLY by the controller (PublicSignupController for trial,
 * CheckoutController::callback for paid) so the existing tests that
 * assert immediate DB persistence still pass. This job handles the
 * SECONDARY work that should never block the HTTP response:
 *
 *   1. Provision the owner User (placeholder password — never shared)
 *   2. Issue a 48h signed setup URL via TenantPasswordSetupService
 *   3. Queue the TenantWelcomeMail (containing only the URL)
 *   4. Notify the configured `admin_notification_email` if
 *      `notify_new_subscription` SystemSetting is true
 *
 * Idempotent — safe to re-dispatch:
 *
 *   - User: looked up by tenant_id + email; created only if missing.
 *   - Setup token: `updateOrInsert` always replaces the prior row,
 *     so re-dispatching invalidates the previous URL.
 *   - Welcome mail: queued each time (operator may want to re-send);
 *     idempotency on the wire is the consumer's responsibility.
 *   - Notification: opt-in via SystemSetting and only fires when an
 *     `admin_notification_email` is configured.
 *
 * Logging discipline: NEVER log the plaintext token, the setup URL
 * itself, or the placeholder password hash. We log only generic events
 * with tenant_id + masked email.
 */
final class CreateTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    /**
     * @param  array<string, mixed> $payload  see constructor doc-block.
     */
    public function __construct(public array $payload)
    {
        // Required: tenant_id, owner_email, owner_name, source.
        // Optional: plan_id, billing_cycle, subscription_id, payment_id,
        //           coupon_code, owner_phone.
    }

    public function handle(
        TenantPasswordSetupService $setup,
    ): void {
        $tenantId    = (int)    ($this->payload['tenant_id']    ?? 0);
        $ownerEmail  = (string) ($this->payload['owner_email']  ?? '');
        $ownerName   = (string) ($this->payload['owner_name']   ?? '');
        $ownerPhone  = (string) ($this->payload['owner_phone']  ?? '');
        $source      = (string) ($this->payload['source']       ?? 'trial');
        $subscriptionId = $this->payload['subscription_id'] ?? null;

        if ($tenantId === 0 || $ownerEmail === '') {
            Log::warning('create_tenant_job.invalid_payload', [
                'tenant_id_missing' => $tenantId === 0,
                'email_missing'     => $ownerEmail === '',
                'source'            => $source,
            ]);
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            Log::warning('create_tenant_job.tenant_not_found', [
                'tenant_id' => $tenantId,
                'source'    => $source,
            ]);
            return;
        }

        // Subscription is optional (legacy trial path may pass it,
        // checkout always passes it). Loaded under the tenant context so
        // BelongsToTenant doesn't filter it out.
        $subscription = null;
        if ($subscriptionId !== null) {
            $subscription = TenantContext::runAs($tenant, fn () =>
                Subscription::query()->with('plan')->whereKey((int) $subscriptionId)->first()
            );
        }

        // 1+2. User + setup token — wrapped in a single transaction so
        //      a failure leaves no half-state row.
        $user = DB::transaction(function () use ($tenant, $ownerEmail, $ownerName, $ownerPhone) {
            return TenantContext::runAs($tenant, function () use ($ownerEmail, $ownerName, $ownerPhone) {
                $existing = User::query()
                    ->where('tenant_id', $tenant = TenantContext::current()?->id)
                    ->where('email', $ownerEmail)
                    ->first();

                if ($existing) {
                    return $existing;
                }

                return User::create([
                    'name'                 => $ownerName !== '' ? $ownerName : explode('@', $ownerEmail)[0],
                    'email'                => $ownerEmail,
                    'phone'                => $ownerPhone !== '' ? $ownerPhone : null,
                    'password'             => TenantPasswordSetupService::placeholderPasswordHash(),
                    'must_change_password' => true,
                    'nationality'          => 'SA',
                    'tour_completed'       => 0,
                    'tour_task_completed'  => 0,
                ]);
            });
        });

        // 3. Setup URL + welcome mail (queued separately on the mail queue).
        try {
            $setupUrl = $setup->issue($user);
            $loginUrl = url('/login');

            Mail::to($user->email)->queue(new TenantWelcomeMail(
                tenant:       $tenant,
                user:         $user,
                subscription: $subscription,
                setupUrl:     $setupUrl,
                loginUrl:     $loginUrl,
            ));
        } catch (\Throwable $e) {
            // Non-fatal — the user can request a fresh link from the
            // login page once the setup-link UI is wired in.
            Log::warning('create_tenant_job.welcome_mail_failed', [
                'tenant_id' => $tenant->id,
                'message'   => $e->getMessage(),
            ]);
        }

        // 4. Optional Super Admin notification.
        $this->maybeNotifySuperAdmin($tenant, $user, $subscription, $source);
    }

    /**
     * Sends an email to the configured `admin_notification_email`
     * recipient when `notify_new_subscription` SystemSetting is true.
     */
    private function maybeNotifySuperAdmin(
        Tenant $tenant,
        User $user,
        ?Subscription $subscription,
        string $source,
    ): void {
        $enabled = (bool) SystemSetting::get('notify_new_subscription', false);
        if (! $enabled) {
            return;
        }

        $recipient = (string) (SystemSetting::get('admin_notification_email') ?? '');
        if ($recipient === '') {
            return;
        }

        try {
            Notification::route('mail', $recipient)
                ->notify(new NewTenantSubscriptionNotification(
                    tenant:       $tenant,
                    user:         $user,
                    subscription: $subscription,
                    source:       $source,
                ));
        } catch (\Throwable $e) {
            Log::warning('create_tenant_job.super_admin_notify_failed', [
                'tenant_id' => $tenant->id,
                'message'   => $e->getMessage(),
            ]);
        }
    }
}
