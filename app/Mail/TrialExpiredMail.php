<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Phase G — sent the moment `saas:check-trial-expiry` flips a
 * trialing subscription to Expired. Body wording branches on whether
 * the operator has `trial_suspend_after_expiry` on (account suspended)
 * or off (account still reachable, partially limited).
 *
 * One-shot: gated by `subscriptions.trial_expired_notified_at` so a
 * re-run of the command never re-sends to the same operator.
 */
final class TrialExpiredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, TenantAwareJob;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public Subscription $subscription,
        public bool $tenantSuspended,
    ) {
        $this->captureTenant();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.trial_expired.subject', [
                'app' => (string) (SystemSetting::get('app_name') ?? 'MNJIZ'),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial.expired',
            with: [
                'tenant'           => $this->tenant,
                'user'             => $this->user,
                'subscription'     => $this->subscription,
                'plan_name'        => $this->subscription->plan?->name ?? '—',
                'tenant_suspended' => $this->tenantSuspended,
                'app_name'         => (string) (SystemSetting::get('app_name') ?? 'MNJIZ'),
                'support_email'    => (string) (SystemSetting::get('support_email') ?? 'support@mnjiz.sa'),
                'pricing_url'      => url('/pricing'),
            ],
        );
    }
}
