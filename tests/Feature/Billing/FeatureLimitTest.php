<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Billing\AssignPlanToTenantAction;
use App\Actions\Billing\CheckFeatureLimitAction;
use App\Exceptions\Billing\UsageLimitExceededException;
use App\Models\Plan;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FeatureLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_starter_plan_respects_max_users_limit(): void
    {
        $tenant = $this->tenantOn('starter');

        TenantContext::runAs($tenant, function () {
            $check = app(CheckFeatureLimitAction::class);

            // At 9/10 (adding 1 fits)
            $r1 = $check('max_users', amount: 1, usedOverride: 9);
            $this->assertTrue($r1->allowed);
            $this->assertSame(10, $r1->limit);
            $this->assertSame(9, $r1->used);

            // At 10/10 (adding 1 overflows)
            $r2 = $check('max_users', amount: 1, usedOverride: 10);
            $this->assertFalse($r2->allowed);
            $this->assertStringContainsStringIgnoringCase('limit reached', $r2->reason);
        });
    }

    public function test_unlimited_limit_always_allows(): void
    {
        $tenant = $this->tenantOn('professional');

        TenantContext::runAs($tenant, function () {
            $r = app(CheckFeatureLimitAction::class)('max_users', amount: 9999, usedOverride: 5000);
            $this->assertTrue($r->allowed);
            $this->assertTrue($r->unlimited);
            $this->assertNull($r->limit);
            $this->assertNull($r->remaining);
        });
    }

    public function test_missing_feature_denies(): void
    {
        // Free plan has no legal_ai.chat → evaluating it through limit action
        // should return denied because the feature is gated off.
        $tenant = $this->tenantOn('free');

        TenantContext::runAs($tenant, function () {
            $r = app(CheckFeatureLimitAction::class)('legal_ai.chat');
            $this->assertFalse($r->allowed);
        });
    }

    public function test_allow_throws_with_exception_headers(): void
    {
        $tenant = $this->tenantOn('starter');

        TenantContext::runAs($tenant, function () {
            try {
                app(CheckFeatureLimitAction::class)->allow('max_users', amount: 1, usedOverride: 10);
                $this->fail('Expected exception');
            } catch (UsageLimitExceededException $e) {
                $this->assertSame(429, $e->getStatusCode());
                $headers = $e->getHeaders();
                $this->assertSame('max_users', $headers['X-Feature-Gate']);
                $this->assertSame('10',        $headers['X-Feature-Limit']);
            }
        });
    }

    private function tenantOn(string $slug): Tenant
    {
        $tenant = Tenant::create([
            'name' => 'Firm '.$slug, 'slug' => 'firm-'.$slug, 'status' => 'active',
        ]);
        app(AssignPlanToTenantAction::class)->execute($tenant, Plan::where('slug', $slug)->firstOrFail());

        return $tenant->fresh();
    }
}
