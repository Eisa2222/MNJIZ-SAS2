<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Enums\Billing\BillingCycle;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\SubscriptionStatus;
use App\Events\Billing\SubscriptionActivated;
use App\Events\Billing\SubscriptionStarted;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creates the initial subscription for a tenant.
 *
 *   plan.trial_days > 0  → status = trialing, trial_ends_at = now + trial_days
 *   plan.trial_days = 0  → status = active    (caller should also invoke
 *                          ChargeInvoiceAction to collect first payment)
 *
 * Guards:
 *   - tenant must not already have a non-terminal subscription
 *   - plan must be active
 */
final class StartTrialAction
{
    public function execute(
        Tenant $tenant,
        Plan $plan,
        BillingCycle $cycle = BillingCycle::Monthly,
        PaymentGateway $gateway = PaymentGateway::Moyasar,
    ): Subscription {
        if (! $plan->is_active) {
            throw new RuntimeException("Cannot start a subscription on inactive plan '{$plan->slug}'.");
        }

        return DB::transaction(function () use ($tenant, $plan, $cycle, $gateway) {
            $existing = Subscription::withoutTenancy()
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', [
                    SubscriptionStatus::Trialing->value,
                    SubscriptionStatus::Active->value,
                    SubscriptionStatus::PastDue->value,
                    SubscriptionStatus::Paused->value,
                ])
                ->exists();

            if ($existing) {
                throw new RuntimeException(
                    "Tenant #{$tenant->id} already has an active subscription. "
                    ."Use ChangePlanAction to switch plans."
                );
            }

            $now = now();
            $hasTrial = $plan->trial_days > 0;

            $subscription = new Subscription();
            $subscription->tenant_id                 = $tenant->id;
            $subscription->plan_id                   = $plan->id;
            $subscription->billing_cycle             = $cycle;
            $subscription->currency                  = $plan->currency;
            $subscription->gateway                   = $gateway;
            $subscription->status                    = $hasTrial
                                                        ? SubscriptionStatus::Trialing
                                                        : SubscriptionStatus::Active;
            $subscription->trial_ends_at             = $hasTrial ? $now->copy()->addDays($plan->trial_days) : null;
            $subscription->current_period_started_at = $now;
            $subscription->current_period_ends_at    = $cycle->addToNow($now->toImmutable());
            $subscription->save();

            event(new SubscriptionStarted($subscription));

            if ($subscription->status === SubscriptionStatus::Active) {
                event(new SubscriptionActivated($subscription));
            }

            activity('billing')
                ->performedOn($subscription)
                ->withProperties([
                    'tenant_id' => $tenant->id,
                    'plan_slug' => $plan->slug,
                    'trial'     => $hasTrial,
                ])
                ->event('subscription.started')
                ->log("Subscription started for tenant #{$tenant->id} on plan '{$plan->slug}'.");

            return $subscription;
        });
    }
}
