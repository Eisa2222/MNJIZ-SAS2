<?php

declare(strict_types=1);

namespace App\Console\Commands\Billing;

use App\Enums\Billing\SubscriptionStatus;
use App\Models\Subscription;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Heads-up email 7 days before renewal. Phase 5 logs only; Phase 8 wires
 * the actual RenewalReminderNotification mail.
 *
 * Schedule: daily at 09:00.
 */
final class SendRenewalRemindersCommand extends Command
{
    protected $signature = 'billing:send-renewal-reminders
                            {--days=7 : Days before renewal to notify}';
    protected $description = 'Notify tenants whose subscription renews in N days.';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $target = now()->addDays($days)->startOfDay();
        $window = [$target, $target->copy()->endOfDay()];

        $query = Subscription::withoutTenancy()
            ->whereIn('status', [
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Trialing->value,
            ])
            ->whereBetween('current_period_ends_at', $window);

        $count = 0;

        $query->chunkById(100, function ($subs) use (&$count) {
            foreach ($subs as $sub) {
                TenantContext::runAs($sub->tenantRelation, function () use ($sub, &$count) {
                    Log::info('billing.renewal_reminder_due', [
                        'tenant_id'       => $sub->tenant_id,
                        'subscription_id' => $sub->id,
                        'renews_at'       => $sub->current_period_ends_at?->toIso8601String(),
                    ]);
                    // TODO (Phase 8): dispatch RenewalReminderNotification.
                    $count++;
                });
            }
        });

        $this->info("Renewal reminders queued for {$count} subscription(s).");

        return self::SUCCESS;
    }
}
