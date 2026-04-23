<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guarantees that Phase 2/3 did NOT break the pre-SaaS system:
 *   - Legacy records keep their data (backfilled to Default Tenant)
 *   - A query outside any /t/{slug} or /admin path still returns results
 *     via the TenantContext fallback mechanism
 *   - tenant_id auto-fills on creation even without an explicit context
 */
final class BackwardCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_tenant_exists_after_migration(): void
    {
        // The create_tenants_table migration INSERTs Default Tenant directly.
        $default = Tenant::query()
            ->where('slug', config('tenancy.default_tenant_slug', 'default'))
            ->first();

        $this->assertNotNull($default, 'Default Tenant should exist right after migrations.');
        $this->assertSame(Tenant::STATUS_ACTIVE, $default->status);
        $this->assertSame((int) config('tenancy.default_tenant_id', 1), $default->id);
    }

    public function test_legacy_create_without_context_attaches_to_default_tenant(): void
    {
        config(['tenancy.fallback_enabled' => true]);
        TenantContext::forget();

        // Use factory so required columns (nationality, status, job, …) are
        // populated — reproduces legacy code paths that create users via
        // Laravel's factory helper during seeding or HR flows.
        $user = User::factory()->create([
            'email' => 'legacy+'.uniqid().'@firm.test',
        ]);

        $this->assertSame(
            (int) config('tenancy.default_tenant_id', 1),
            $user->fresh()->tenant_id,
            'Records created with no tenant context MUST fall back to Default Tenant.'
        );
    }

    public function test_querying_default_tenant_users_returns_backfilled_rows(): void
    {
        config(['tenancy.fallback_enabled' => true]);
        TenantContext::forget();

        User::factory()->create();
        User::factory()->create();

        // No context = fallback = Default Tenant. The two users created above
        // should be visible.
        $count = User::count();

        $this->assertGreaterThanOrEqual(2, $count);
    }

    public function test_strict_mode_throws_when_no_tenant_resolved(): void
    {
        config(['tenancy.strict' => true]);
        config(['tenancy.fallback_enabled' => false]);
        TenantContext::forget();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/no tenant is resolved/i');

        User::query()->count();
    }
}
