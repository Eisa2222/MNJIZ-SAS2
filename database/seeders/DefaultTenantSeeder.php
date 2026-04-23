<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

/**
 * Seeds the "MNJIZ Default" tenant (id=1, slug=default).
 *
 * IMPORTANT: The create_tenants_table migration INSERTs this row during
 * `migrate`, so by the time this seeder runs the row already exists.
 * `updateOrCreate` therefore fires `updated` — NOT `created` — which means
 * TenantObserver::created() never runs on Default Tenant. We explicitly
 * assign the default plan here to cover that gap.
 *
 * Every existing row in the pre-SaaS database will be backfilled with
 * tenant_id=1 in Phase 7. Running this seeder is IDEMPOTENT.
 */
class DefaultTenantSeeder extends Seeder
{
    public function run(): void
    {
        $slug = config('tenancy.default_tenant_slug', 'default');
        $id   = config('tenancy.default_tenant_id', 1);

        /** @var Tenant $tenant */
        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'id'     => $id,
                'name'   => 'MNJIZ Default',
                'domain' => null,
                'status' => Tenant::STATUS_ACTIVE,
                'meta'   => [
                    'is_default' => true,
                    'created_by' => 'DefaultTenantSeeder',
                    'notes'      => 'Legacy single-tenant data lives here until Phase 7 migration.',
                ],
            ]
        );

        // Default Tenant was inserted by the migration — Observer never fired.
        // Ensure it has a plan. No-op if the default plan hasn't been seeded yet
        // (AssignPlanToTenantAction::assignDefault handles that gracefully).
        if ($tenant->plan_id === null) {
            app(AssignPlanToTenantAction::class)->assignDefault($tenant);
        }
    }
}
