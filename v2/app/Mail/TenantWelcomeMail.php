<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Plan;
use App\Models\SystemSetting;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * V2 — Welcome mail with 48h password setup link. Spec lines 322-342.
 * NEVER contains a plaintext password.
 */
final class TenantWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public Plan $plan,
        public string $setupUrl,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        $appName = (string) (SystemSetting::get('app_name', 'MNJIZ'));
        return new Envelope(subject: "مرحباً بك في {$appName}");
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.welcome',
            with: [
                'tenant'        => $this->tenant,
                'plan'          => $this->plan,
                'setupUrl'      => $this->setupUrl,
                'loginUrl'      => $this->loginUrl,
                'appName'       => SystemSetting::get('app_name', 'MNJIZ'),
                'supportEmail'  => SystemSetting::get('support_email', 'support@mnjiz.sa'),
                'supportPhone'  => SystemSetting::get('support_phone'),
            ],
        );
    }
}
