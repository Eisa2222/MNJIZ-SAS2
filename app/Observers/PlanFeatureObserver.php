<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PlanFeature;
use App\Services\Billing\FeatureResolver;

/**
 * Invalidates the per-tenant feature cache when a plan's feature mapping or
 * its value changes (e.g., "Pro" gets `max_users` bumped from 50 to 100).
 */
final class PlanFeatureObserver
{
    public function __construct(private FeatureResolver $resolver) {}

    public function saved(PlanFeature $pivot): void
    {
        $this->resolver->flushAllTenantsOnPlan((int) $pivot->plan_id);
    }

    public function deleted(PlanFeature $pivot): void
    {
        $this->resolver->flushAllTenantsOnPlan((int) $pivot->plan_id);
    }
}
