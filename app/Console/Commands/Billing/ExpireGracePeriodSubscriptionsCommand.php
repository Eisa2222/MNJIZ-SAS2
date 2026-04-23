<?php

declare(strict_types=1);

namespace App\Console\Commands\Billing;

use App\Actions\Billing\Subscription\ExpireSubscriptionAction;
use App\Enums\Billing\SubscriptionStatus;
use App\Models\Subscription;
use App\Tenancy\TenantContext;
use Illuminate\Console\Command;
use Throwable;

/**
 * Moves past_due subscriptions whose grace window closed AND canceled
 * subscriptions whose ends_at passed into the terminal Expired state.
 *
 * Schedule: daily at 00:20.
 */
final class ExpireGracePeriodSubscriptionsCommand extends Command
{
    protected $signature = 'billing:expire-grace-period';
    protected $description = 'Expire past-due subscriptions that exhausted their grace window, and canceled subscriptions past ends_at.';

    public function handle(ExpireSubscriptionAction $expire): int
    {
        $now = now();

        $query = Subscription::withoutTenancy()
            ->where(function ($q) use ($now) {
                $q->where(function ($q) use ($now) {
                    $q->where('status', SubscriptionStatus::PastDue->value)
                      ->whereNotNull('grace_ends_at')
                      ->where('grace_ends_at', '<=', $now);
                })->orWhere(function ($q) use ($now) {
                    $q->where('status', SubscriptionStatus::Canceled->value)
                      ->whereNotNull('ends_at')
                      ->where('ends_at', '<=', $now);
                });
            });

        $count = 0;

        $query->chunkById(100, function ($subs) use ($expire, &$count) {
            foreach ($subs as $sub) {
                TenantContext::runAs($sub->tenantRelation, function () use ($sub, $expire, &$count) {
                    try {
                        $expire->execute($sub);
                        $count++;
                    } catch (Throwable $e) {
                        $this->error("Expire failed for #{$sub->id}: {$e->getMessage()}");
                    }
                });
            }
        });

        $this->info("Expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}
