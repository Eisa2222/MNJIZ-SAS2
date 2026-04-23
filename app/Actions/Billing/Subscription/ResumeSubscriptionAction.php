<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Enums\Billing\SubscriptionStatus;
use App\Events\Billing\SubscriptionActivated;
use App\Exceptions\Billing\SubscriptionStateException;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Re-enable a Canceled (not yet Expired) or Paused subscription.
 * Useful when a customer changes their mind before grace ends.
 */
final class ResumeSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            if ($subscription->status === SubscriptionStatus::Expired) {
                throw new SubscriptionStateException(
                    current: $subscription->status,
                    attemptedTransition: 'resume',
                    message: 'Cannot resume an expired subscription — start a new one instead.',
                );
            }

            $subscription->status      = SubscriptionStatus::Active;
            $subscription->canceled_at = null;
            $subscription->ends_at     = null;
            $subscription->save();

            event(new SubscriptionActivated($subscription));

            activity('billing')
                ->performedOn($subscription)
                ->event('subscription.resumed')
                ->log("Subscription #{$subscription->id} resumed.");

            return $subscription;
        });
    }
}
