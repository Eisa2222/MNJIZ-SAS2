<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Enums\Billing\SubscriptionStatus;
use App\Events\Billing\SubscriptionPastDue;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Renewal payment failed. Move the subscription to past_due and start the
 * grace window. Tenant still keeps access during grace — only after
 * ExpireSubscriptionAction (grace ends) do they lose the plan.
 *
 * Grace default: 3 days. Customizable via config('billing.grace_days').
 */
final class MarkPastDueAction
{
    public function execute(Subscription $subscription, ?int $graceDays = null): Subscription
    {
        $graceDays ??= (int) config('billing.grace_days', 3);

        return DB::transaction(function () use ($subscription, $graceDays) {
            if ($subscription->status === SubscriptionStatus::PastDue) {
                return $subscription; // already past due, nothing to do
            }

            $subscription->status        = SubscriptionStatus::PastDue;
            $subscription->grace_ends_at = now()->addDays($graceDays);
            $subscription->save();

            event(new SubscriptionPastDue($subscription));

            activity('billing')
                ->performedOn($subscription)
                ->withProperties([
                    'tenant_id'      => $subscription->tenant_id,
                    'grace_ends_at'  => $subscription->grace_ends_at->toIso8601String(),
                ])
                ->event('subscription.past_due')
                ->log("Subscription #{$subscription->id} moved to past_due (grace {$graceDays}d).");

            return $subscription;
        });
    }
}
