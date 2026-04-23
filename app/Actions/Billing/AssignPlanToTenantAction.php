<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Billing\FeatureResolver;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Swaps a tenant onto a different plan (or assigns one for the first time)
 * and invalidates their feature cache.
 *
 * Does NOT touch subscriptions / invoices — that's Phase 5. For now this is
 * a direct assignment used by:
 *   - TenantObserver::created() → default plan on new tenant
 *   - Super Admin manual override
 *   - Phase 5 subscription activation (will wrap this call)
 */
final class AssignPlanToTenantAction
{
    public function __construct(private FeatureResolver $resolver) {}

    public function execute(Tenant $tenant, Plan|string $plan): Tenant
    {
        $plan = is_string($plan)
            ? Plan::query()->where('slug', $plan)->firstOrFail()
            : $plan;

        if (! $plan->is_active) {
            throw new RuntimeException("Cannot assign inactive plan '{$plan->slug}'.");
        }

        return DB::transaction(function () use ($tenant, $plan) {
            $tenant->plan_id = $plan->id;
            $tenant->save();

            // Clear resolved feature cache for this tenant — the plan just changed.
            $this->resolver->forgetFor($tenant->id);

            activity('billing')
                ->performedOn($tenant)
                ->withProperties([
                    'plan_id'   => $plan->id,
                    'plan_slug' => $plan->slug,
                ])
                ->event('plan.assigned')
                ->log("Tenant #{$tenant->id} assigned plan '{$plan->slug}'.");

            return $tenant->fresh();
        });
    }

    public function assignDefault(Tenant $tenant): Tenant
    {
        $slug = config('billing.default_plan_slug', 'free');
        $plan = Plan::query()->where('slug', $slug)->first();

        if (! $plan) {
            // No default plan seeded yet — leave tenant without a plan rather
            // than hard-fail creation. Feature gates will return false.
            return $tenant;
        }

        return $this->execute($tenant, $plan);
    }
}
