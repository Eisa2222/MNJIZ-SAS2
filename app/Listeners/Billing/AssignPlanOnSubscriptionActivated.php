<?php

declare(strict_types=1);

namespace App\Listeners\Billing;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Events\Billing\SubscriptionActivated;
use App\Models\Tenant;

/**
 * When a subscription becomes active (trial started OR first payment
 * captured), the tenant's effective plan switches. We route through
 * AssignPlanToTenantAction (Phase 4) so feature cache invalidation and
 * activity logging happen consistently.
 */
final class AssignPlanOnSubscriptionActivated
{
    public function __construct(private AssignPlanToTenantAction $assignPlan) {}

    public function handle(SubscriptionActivated $event): void
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($event->subscription->tenant_id);

        if (! $tenant) {
            return;
        }

        if ($tenant->plan_id === $event->subscription->plan_id) {
            return; // already on the right plan — avoid a redundant write
        }

        $this->assignPlan->execute($tenant, $event->subscription->plan);
    }
}
