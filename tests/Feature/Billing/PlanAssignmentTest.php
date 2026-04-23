<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Models\Plan;
use App\Models\Tenant;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlanAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_new_tenant_receives_default_plan_via_observer(): void
    {
        $tenant = Tenant::create(['name' => 'Auto', 'slug' => 'auto', 'status' => 'active']);

        $this->assertNotNull($tenant->fresh()->plan_id);
        $this->assertSame('free', $tenant->fresh()->plan->slug);
    }

    public function test_assign_plan_action_replaces_existing_plan(): void
    {
        $tenant = Tenant::create(['name' => 'X', 'slug' => 'x', 'status' => 'active']);

        app(AssignPlanToTenantAction::class)->execute($tenant, 'professional');

        $this->assertSame('professional', $tenant->fresh()->plan->slug);
    }

    public function test_assigning_inactive_plan_is_rejected(): void
    {
        $plan = Plan::where('slug', 'starter')->firstOrFail();
        $plan->update(['is_active' => false]);

        $tenant = Tenant::create(['name' => 'Y', 'slug' => 'y', 'status' => 'active']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/inactive plan/i');

        app(AssignPlanToTenantAction::class)->execute($tenant, $plan);
    }

    public function test_plan_slug_is_immutable(): void
    {
        $plan = Plan::where('slug', 'free')->firstOrFail();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/slug is immutable/i');

        $plan->update(['slug' => 'free-renamed']);
    }
}
