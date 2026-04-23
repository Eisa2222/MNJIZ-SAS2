<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Events\Billing\SubscriptionExpired;
use App\Models\Tenant;

/**
 * Terminal state — tenant drops to the free plan.
 */
final class RevertToFreePlanOnSubscriptionExpired
{
    public function __construct(private AssignPlanToTenantAction $assignPlan) {}

    public function handle(SubscriptionExpired $event): void
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($event->subscription->tenant_id);

        if (! $tenant) {
            return;
        }

        $this->assignPlan->assignDefault($tenant);
    }
}
