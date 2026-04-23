<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises the HTTP integration for tenant routing:
 *   - /t/{slug}/ping resolves to the right tenant
 *   - Suspended tenants are 403'd before their controllers run
 *   - Unknown tenants (+ fallback disabled) are 404'd
 *   - /admin/ping never tries to resolve a tenant
 */
final class TenantRoutingHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_path_resolves_to_correct_tenant(): void
    {
        $tenant = Tenant::create(['name' => 'Firm A', 'slug' => 'firm-a', 'status' => 'active']);

        $response = $this->getJson('/t/firm-a/ping');

        $response->assertOk();
        $response->assertJson([
            'pong'        => true,
            'layer'       => 'tenant',
            'tenant_id'   => $tenant->id,
            'tenant_slug' => 'firm-a',
        ]);
    }

    public function test_suspended_tenant_is_blocked_with_403(): void
    {
        Tenant::create(['name' => 'Frozen', 'slug' => 'frozen', 'status' => Tenant::STATUS_SUSPENDED]);

        $response = $this->get('/t/frozen/ping');

        $response->assertStatus(403);
    }

    public function test_unknown_tenant_with_fallback_disabled_returns_404(): void
    {
        config(['tenancy.fallback_enabled' => false]);

        $response = $this->get('/t/does-not-exist/ping');

        $response->assertStatus(404);
    }

    public function test_central_admin_path_does_not_try_to_resolve_tenant(): void
    {
        // /admin/login should respond regardless of tenant state — no tenant resolution.
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('Super Admin', false);
    }
}
