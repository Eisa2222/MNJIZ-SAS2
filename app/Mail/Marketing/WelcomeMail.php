<?php

declare(strict_types=1);

namespace App\Mail\Marketing;

use App\Models\Subscription;
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
 * Phase 9 — welcome email sent at end of /register flow.
 *
 * Queued: ShouldQueue + TenantAwareJob means the dispatcher's tenant
 * context is captured into the payload and re-seated on the worker
 * (so any model attribute access inside the view template runs under
 * the correct tenant, not the fallback default).
 */
final class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, TenantAwareJob;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public Subscription $subscription,
    ) {
        $this->captureTenant();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مرحباً بك في MNJIZ — تجربتك المجانية بدأت',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing.welcome',
            with: [
                'tenant'       => $this->tenant,
                'user'         => $this->user,
                'subscription' => $this->subscription,
                'trial_ends'   => $this->subscription->trial_ends_at,
                'plan'         => $this->subscription->plan,
            ],
        );
    }
}
