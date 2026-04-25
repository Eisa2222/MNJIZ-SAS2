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
 * Phase F — replaces the Phase 9 `Marketing\WelcomeMail` for the new
 * secure-onboarding flow. This mail:
 *
 *   ✅ embeds a 48h signed setup URL (no plaintext password)
 *   ✅ shows plan name, billing cycle, trial / period end
 *   ✅ pulls support email + app branding from SystemSetting
 *   ❌ never contains a password / token / payment secret
 *
 * The legacy `Marketing\WelcomeMail` is kept untouched so the
 * pre-Phase-F signup flow (still exercised by RegistrationTest /
 * default config) keeps working.
 *
 * `TenantAwareJob` captures the dispatcher's tenant id into the
 * payload so any model lookup inside the view template happens under
 * the right tenant context on the worker side.
 */
final class TenantWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, TenantAwareJob;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public ?Subscription $subscription,
        public string $setupUrl,
        public string $loginUrl,
    ) {
        $this->captureTenant();
    }

    public function envelope(): Envelope
    {
        $appName = (string) (SystemSetting::get('app_name') ?? 'MNJIZ');

        return new Envelope(
            subject: __('emails.tenant_welcome.subject', ['app' => $appName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.welcome',
            with: [
                'tenant'         => $this->tenant,
                'user'           => $this->user,
                'subscription'   => $this->subscription,
                'plan_name'      => $this->subscription?->plan?->name,
                'billing_cycle'  => $this->subscription?->billing_cycle?->label(),
                'trial_ends_at'  => $this->subscription?->trial_ends_at,
                'period_ends_at' => $this->subscription?->current_period_ends_at,
                'setup_url'      => $this->setupUrl,
                'login_url'      => $this->loginUrl,
                'app_name'       => (string) (SystemSetting::get('app_name') ?? 'MNJIZ'),
                'support_email'  => (string) (SystemSetting::get('support_email') ?? 'support@mnjiz.sa'),
                'valid_hours'    => \App\Services\Auth\TenantPasswordSetupService::VALID_HOURS,
            ],
        );
    }
}
