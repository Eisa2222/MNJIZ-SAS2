<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Enums\Billing\SubscriptionStatus;
use App\Events\Billing\SubscriptionCanceled;
use App\Exceptions\Billing\SubscriptionStateException;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Two modes:
 *   - immediate = false (default) → "cancel at period end" (industry standard)
 *     Subscription stays entitling until current_period_ends_at. Tenant keeps
 *     paid-for features. ExpireSubscriptionAction (scheduler) finalizes it.
 *
 *   - immediate = true → revoke now. ends_at = now. Next scheduler run
 *     transitions to Expired.
 */
final class CancelSubscriptionAction
{
    public function execute(Subscription $subscription, bool $immediate = false): Subscription
    {
        return DB::transaction(function () use ($subscription, $immediate) {
            if ($subscription->status === SubscriptionStatus::Canceled) {
                return $subscription;
            }

            if ($subscription->status === SubscriptionStatus::Expired) {
                throw new SubscriptionStateException(
                    current: $subscription->status,
                    attemptedTransition: 'cancel',
                );
            }

            $subscription->status      = SubscriptionStatus::Canceled;
            $subscription->canceled_at = now();
            $subscription->ends_at     = $immediate
                                            ? now()
                                            : ($subscription->current_period_ends_at ?? now());
            $subscription->save();

            event(new SubscriptionCanceled($subscription, $immediate));

            activity('billing')
                ->performedOn($subscription)
                ->withProperties([
                    'tenant_id'  => $subscription->tenant_id,
                    'immediate'  => $immediate,
                    'ends_at'    => $subscription->ends_at?->toIso8601String(),
                ])
                ->event('subscription.canceled')
                ->log("Subscription #{$subscription->id} canceled (immediate=".($immediate?'yes':'no').").");

            return $subscription;
        });
    }
}
