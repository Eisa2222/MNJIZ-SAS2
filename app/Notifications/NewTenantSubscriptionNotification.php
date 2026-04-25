<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Phase F — Super Admin "new subscription" notification.
 *
 * Dispatched from CreateTenantJob ONLY when the operator has flipped
 * `notify_new_subscription` to true in /super-admin/settings (Phase C
 * Notifications tab) AND set an `admin_notification_email`.
 *
 * Routes via `Notification::route('mail', $email)` so we don't need a
 * Notifiable on the receiving end — the recipient is just an address.
 *
 * Contains zero secrets: tenant name, source ("trial" or
 * "paid_checkout"), plan name, owner name + email. No payment id,
 * no card data, no setup URL.
 */
final class NewTenantSubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public ?Subscription $subscription,
        public string $source,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName  = (string) (SystemSetting::get('app_name') ?? 'MNJIZ');
        $sourceLabel = $this->source === 'paid_checkout'
            ? __('emails.new_subscription.source_paid')
            : __('emails.new_subscription.source_trial');

        $message = (new MailMessage())
            ->subject(__('emails.new_subscription.subject', ['app' => $appName]))
            ->greeting(__('emails.new_subscription.greeting'))
            ->line(__('emails.new_subscription.intro', ['firm' => $this->tenant->name]))
            ->line(__('emails.new_subscription.fields.source', ['source' => $sourceLabel]))
            ->line(__('emails.new_subscription.fields.owner', ['name' => $this->user->name, 'email' => $this->user->email]));

        if ($this->subscription?->plan?->name) {
            $message->line(__('emails.new_subscription.fields.plan', ['plan' => $this->subscription->plan->name]));
        }

        if ($this->subscription?->billing_cycle) {
            $message->line(__('emails.new_subscription.fields.cycle', ['cycle' => $this->subscription->billing_cycle->label()]));
        }

        return $message;
    }
}
