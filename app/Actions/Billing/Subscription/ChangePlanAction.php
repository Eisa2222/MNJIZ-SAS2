<?php

declare(strict_types=1);

namespace App\Actions\Billing\Subscription;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Events\Billing\SubscriptionActivated;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Upgrade / downgrade the active plan on a subscription.
 *
 * Phase 5 semantics: plan changes are EFFECTIVE IMMEDIATELY (no proration
 * math — the existing invoice stays, and the next renewal uses the new
 * plan's price). Full proration is deferred to Phase 8.
 *
 * The `Tenant.plan_id` is updated in lock-step via AssignPlanToTenantAction
 * so feature cache stays coherent.
 */
final class ChangePlanAction
{
    public function __construct(private AssignPlanToTenantAction $assignPlan) {}

    public function execute(Subscription $subscription, Plan $newPlan): Subscription
    {
        if (! $newPlan->is_active) {
            throw new RuntimeException("Cannot change to inactive plan '{$newPlan->slug}'.");
        }

        if ($subscription->plan_id === $newPlan->id) {
            return $subscription;
        }

        return DB::transaction(function () use ($subscription, $newPlan) {
            $oldPlan = $subscription->plan;

            $subscription->plan_id  = $newPlan->id;
            $subscription->currency = $newPlan->currency;
            $subscription->save();

            // Propagate to tenant.plan_id + flush feature cache.
            $tenant = $subscription->tenantRelation;
            if ($tenant) {
                $this->assignPlan->execute($tenant, $newPlan);
            }

            event(new SubscriptionActivated($subscription));

            activity('billing')
                ->performedOn($subscription)
                ->withProperties([
                    'tenant_id'    => $subscription->tenant_id,
                    'old_plan'     => $oldPlan?->slug,
                    'new_plan'     => $newPlan->slug,
                ])
                ->event('subscription.plan_changed')
                ->log("Subscription #{$subscription->id}: '{$oldPlan?->slug}' → '{$newPlan->slug}'.");

            return $subscription->fresh(['plan']);
        });
    }
}
