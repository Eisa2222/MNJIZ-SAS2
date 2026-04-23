<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Enums\Billing\SubscriptionStatus;
use App\Events\Billing\SubscriptionExpired;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Terminal transition. Called by the scheduler when:
 *   - grace_ends_at passed without payment, OR
 *   - canceled subscription's ends_at passed
 *
 * Triggers RevertToFreePlanOnSubscriptionExpired listener → tenant drops
 * to the free plan automatically.
 */
final class ExpireSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            if ($subscription->status === SubscriptionStatus::Expired) {
                return $subscription;
            }

            $subscription->status  = SubscriptionStatus::Expired;
            $subscription->ends_at = $subscription->ends_at ?? now();
            $subscription->save();

            event(new SubscriptionExpired($subscription));

            activity('billing')
                ->performedOn($subscription)
                ->withProperties(['tenant_id' => $subscription->tenant_id])
                ->event('subscription.expired')
                ->log("Subscription #{$subscription->id} expired.");

            return $subscription;
        });
    }
}
