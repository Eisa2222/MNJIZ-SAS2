<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Models\Tenant;

/**
 * Single hook point for "new tenant" lifecycle events. Currently:
 *
 *   - created() → assign the default billing plan (idempotent; no-op if the
 *                 tenant already has one, e.g. because it was set at creation)
 *
 * Phase 5 will extend this to open a trial Subscription and raise a
 * TenantCreated domain event for onboarding emails.
 */
final class TenantObserver
{
    public function __construct(private AssignPlanToTenantAction $assignPlan) {}

    public function created(Tenant $tenant): void
    {
        if ($tenant->plan_id !== null) {
            return;
        }

        $this->assignPlan->assignDefault($tenant);
    }
}
