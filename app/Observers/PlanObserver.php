<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Plan;
use App\Services\Billing\FeatureResolver;

/**
 * Invalidates the per-tenant feature cache whenever a Plan is touched so no
 * tenant keeps using yesterday's feature map after a plan edit.
 */
final class PlanObserver
{
    public function __construct(private FeatureResolver $resolver) {}

    public function updated(Plan $plan): void
    {
        $this->resolver->flushAllTenantsOnPlan($plan->id);
    }

    public function deleted(Plan $plan): void
    {
        $this->resolver->flushAllTenantsOnPlan($plan->id);
    }
}
