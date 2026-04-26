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
 * Phase G — sent N days before trial_ends_at to remind the operator to
 * subscribe. N comes from `SystemSetting::trial_warning_days` (e.g.
 * `[7, 3, 1]`); each milestone fires this mail at most once per
 * subscription thanks to the per-day idempotency tracker in
 * `subscriptions.meta.trial_warning_days_sent`.
 *
 * Carries no payment data and no setup URLs — strictly an upgrade CTA
 * + the days-remaining countdown.
 */
final class TrialExpiryWarningMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, TenantAwareJob;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public Subscription $subscription,
        public int $daysRemaining,
    ) {
        $this->captureTenant();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('emails.trial_warning.subject', [
                'app'  => (string) (SystemSetting::get('app_name') ?? 'MNJIZ'),
                'days' => $this->daysRemaining,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial.warning',
            with: [
                'tenant'         => $this->tenant,
                'user'           => $this->user,
                'subscription'   => $this->subscription,
                'plan_name'      => $this->subscription->plan?->name ?? '—',
                'days_remaining' => $this->daysRemaining,
                'trial_ends_at'  => $this->subscription->trial_ends_at,
                'app_name'       => (string) (SystemSetting::get('app_name') ?? 'MNJIZ'),
                'support_email'  => (string) (SystemSetting::get('support_email') ?? 'support@mnjiz.sa'),
                'pricing_url'    => url('/pricing'),
            ],
        );
    }
}
