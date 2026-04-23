<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Actions\Billing\CheckFeatureAccessAction;
use App\Enums\Billing\FeatureType;
use App\Enums\Billing\UsageResetPeriod;
use App\Exceptions\Billing\FeatureNotEnabledException;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_free_plan_does_not_grant_legal_ai(): void
    {
        $tenant = $this->makeTenantOnPlan('free');

        TenantContext::runAs($tenant, function () {
            $check = app(CheckFeatureAccessAction::class);
            $this->assertFalse($check('legal_ai.chat'));
        });
    }

    public function test_starter_plan_grants_legal_ai_chat_but_not_drafting(): void
    {
        $tenant = $this->makeTenantOnPlan('starter');

        TenantContext::runAs($tenant, function () {
            $check = app(CheckFeatureAccessAction::class);
            $this->assertTrue($check('legal_ai.chat'));
            $this->assertFalse($check('legal_ai.drafting'));
        });
    }

    public function test_allow_throws_when_feature_missing(): void
    {
        $tenant = $this->makeTenantOnPlan('free');

        $this->expectException(FeatureNotEnabledException::class);

        TenantContext::runAs($tenant, function () {
            app(CheckFeatureAccessAction::class)->allow('qoyod.sync');
        });
    }

    public function test_access_returns_false_when_no_tenant_in_context(): void
    {
        TenantContext::forget();

        // Set fallback off so nothing resolves
        config(['tenancy.fallback_enabled' => false]);

        $this->assertFalse(app(CheckFeatureAccessAction::class)('legal_ai.chat'));
    }

    public function test_plan_without_feature_returns_false(): void
    {
        // Create a plan that doesn't include api.access
        $plan = Plan::create([
            'name' => 'Tiny', 'slug' => 'tiny', 'is_active' => true,
            'price_monthly' => 0, 'price_yearly' => 0, 'currency' => 'SAR',
        ]);

        $tenant = Tenant::create(['name' => 'T', 'slug' => 't', 'status' => 'active']);
        app(AssignPlanToTenantAction::class)->execute($tenant, $plan);

        TenantContext::runAs($tenant, function () {
            $this->assertFalse(app(CheckFeatureAccessAction::class)('api.access'));
        });
    }

    private function makeTenantOnPlan(string $slug): Tenant
    {
        $tenant = Tenant::create([
            'name' => ucfirst($slug).' Firm',
            'slug' => $slug.'-firm',
            'status' => 'active',
        ]);

        $plan = Plan::where('slug', $slug)->firstOrFail();
        app(AssignPlanToTenantAction::class)->execute($tenant, $plan);

        return $tenant->fresh();
    }
}
