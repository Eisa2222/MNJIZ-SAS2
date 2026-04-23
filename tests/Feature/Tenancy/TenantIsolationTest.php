<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_created_under_tenant_a_are_invisible_to_tenant_b(): void
    {
        [$tenantA, $tenantB] = $this->makeTwoTenants();

        TenantContext::runAs($tenantA, function () {
            User::factory()->create(['email' => 'alice@firm-a.test']);
            User::factory()->create(['email' => 'bob@firm-a.test']);
        });

        TenantContext::runAs($tenantB, function () {
            User::factory()->create(['email' => 'carol@firm-b.test']);
        });

        TenantContext::runAs($tenantA, function () {
            $this->assertSame(2, User::query()->count());
            $this->assertTrue(User::where('email', 'alice@firm-a.test')->exists());
            $this->assertFalse(User::where('email', 'carol@firm-b.test')->exists());
        });

        TenantContext::runAs($tenantB, function () {
            $this->assertSame(1, User::query()->count());
            $this->assertTrue(User::where('email', 'carol@firm-b.test')->exists());
            $this->assertFalse(User::where('email', 'alice@firm-a.test')->exists());
        });
    }

    public function test_creating_a_tenant_aware_model_without_context_uses_the_default_tenant_fallback(): void
    {
        $this->app['config']->set('tenancy.fallback_enabled', true);

        TenantContext::forget();

        $user = User::factory()->create(['email' => 'legacy@firm.test']);

        $this->assertSame(
            config('tenancy.default_tenant_id', 1),
            $user->fresh()->tenant_id
        );
    }

    public function test_cross_tenant_tenant_id_mutation_is_forbidden(): void
    {
        [$tenantA, $tenantB] = $this->makeTwoTenants();

        $user = TenantContext::runAs($tenantA, fn () => User::factory()->create());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant reassignment/i');

        $user->tenant_id = $tenantB->id;
        $user->save();
    }

    public function test_without_tenancy_macro_bypasses_scope(): void
    {
        [$tenantA, $tenantB] = $this->makeTwoTenants();

        TenantContext::runAs($tenantA, fn () => User::factory()->create());
        TenantContext::runAs($tenantB, fn () => User::factory()->create());

        TenantContext::runAs($tenantA, function () {
            $this->assertSame(1, User::query()->count());
            $this->assertSame(2, User::withoutTenancy()->count());
        });
    }

    /** @return array{0: Tenant, 1: Tenant} */
    private function makeTwoTenants(): array
    {
        $a = Tenant::create([
            'name' => 'Firm A', 'slug' => 'firm-a', 'status' => Tenant::STATUS_ACTIVE,
        ]);
        $b = Tenant::create([
            'name' => 'Firm B', 'slug' => 'firm-b', 'status' => Tenant::STATUS_ACTIVE,
        ]);

        return [$a, $b];
    }
}
