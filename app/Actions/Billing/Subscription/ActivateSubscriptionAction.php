<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Enums\Billing\SubscriptionStatus;
use App\Events\Billing\SubscriptionActivated;
use App\Exceptions\Billing\SubscriptionStateException;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Flips a Trialing / PastDue subscription to Active. Called after a
 * successful payment or admin manual activation.
 *
 * Illegal-from states throw SubscriptionStateException — caller should catch
 * and surface a user-facing message.
 */
final class ActivateSubscriptionAction
{
    public function execute(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            $allowed = [
                SubscriptionStatus::Trialing,
                SubscriptionStatus::PastDue,
                SubscriptionStatus::Paused,
            ];

            if ($subscription->status === SubscriptionStatus::Active) {
                return $subscription;
            }

            if (! in_array($subscription->status, $allowed, true)) {
                throw new SubscriptionStateException(
                    current: $subscription->status,
                    attemptedTransition: 'activate',
                );
            }

            $subscription->status        = SubscriptionStatus::Active;
            $subscription->grace_ends_at = null; // clear any past_due grace
            $subscription->save();

            event(new SubscriptionActivated($subscription));

            activity('billing')
                ->performedOn($subscription)
                ->withProperties(['tenant_id' => $subscription->tenant_id])
                ->event('subscription.activated')
                ->log("Subscription #{$subscription->id} activated.");

            return $subscription;
        });
    }
}
