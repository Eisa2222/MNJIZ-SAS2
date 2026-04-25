<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Domain;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Phase A — host-aware tenant resolution regression suite.
 *
 *   1. Custom-domain host resolves to its tenant
 *   2. Subdomain `{slug}.{base}` resolves to tenant by slug
 *   3. Central host (mnjiz.sa) is NEVER resolved as a tenant
 *   4. Legacy `/t/{slug}/...` path still resolves (Phase 2-9 contract)
 *   5. Unknown host falls through to default-tenant fallback (when enabled)
 *   6. Unknown host returns null when fallback is disabled
 *   7. Bare base domain (`mnjiz.sa`) is never a tenant subdomain
 *   8. Nested subdomain (`www.acme.mnjiz.sa`) is rejected, not split
 *   9. Domain row created via Tenant relation participates in resolution
 *  10. Tenant::primaryDomain() prefers is_primary then falls back to first
 */
final class DomainResolutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pin the env-driven config so tests are deterministic regardless of
        // what's in the running container's .env file.
        config([
            'tenancy.identification.subdomain.enabled'         => true,
            'tenancy.identification.subdomain.app_base_domain' => 'mnjiz.sa',
            'tenancy.identification.custom_domain.enabled'     => true,
            'tenancy.central_domains' => ['mnjiz.sa', 'www.mnjiz.sa', 'app.mnjiz.sa'],
            'tenancy.fallback_enabled' => true,
        ]);
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_custom_domain_resolves_to_owning_tenant(): void
    {
        $tenant = Tenant::create(['name' => 'Acme Law', 'slug' => 'acme', 'status' => 'active']);
        Domain::create(['tenant_id' => $tenant->id, 'domain' => 'portal.acme.com', 'is_primary' => true, 'verified_at' => now()]);

        $resolved = $this->resolve('portal.acme.com');

        $this->assertNotNull($resolved);
        $this->assertSame($tenant->id, $resolved->id);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_subdomain_resolves_by_stripped_slug(): void
    {
        $tenant = Tenant::create(['name' => 'Beta', 'slug' => 'beta', 'status' => 'active']);

        $resolved = $this->resolve('beta.mnjiz.sa');

        $this->assertNotNull($resolved);
        $this->assertSame($tenant->id, $resolved->id);
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_central_host_does_not_resolve_to_any_tenant(): void
    {
        // Even with a tenant whose slug accidentally collides, the central
        // host MUST take precedence and refuse to resolve.
        Tenant::create(['name' => 'Mnjiz', 'slug' => 'mnjiz', 'status' => 'active']);

        foreach (['mnjiz.sa', 'www.mnjiz.sa', 'app.mnjiz.sa'] as $host) {
            $this->assertNull(
                $this->resolveByHostOnly($host),
                "Central host '{$host}' must NEVER resolve to a tenant."
            );
        }
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_legacy_path_based_resolution_still_works(): void
    {
        $tenant = Tenant::create(['name' => 'Legacy', 'slug' => 'legacy', 'status' => 'active']);

        // Path-based: GET on the central /t/{tenant}/ping route from the
        // existing tenant.php scaffold.
        $response = $this->get("/t/{$tenant->slug}/ping");

        $response->assertOk()->assertJsonPath('tenant_slug', 'legacy');
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_unknown_host_falls_back_to_default_tenant_when_enabled(): void
    {
        // Use firstOrCreate — a default tenant may already be auto-seeded
        // by the Tenant model observer in some test runs.
        Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default', 'status' => 'active']
        );

        $resolved = $this->resolve('completely-unknown.example.com');

        // Fallback to the default tenant (compat for legacy /employees/* URLs).
        $this->assertNotNull($resolved);
        $this->assertSame('default', $resolved->slug);
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_unknown_host_returns_null_when_fallback_disabled(): void
    {
        config(['tenancy.fallback_enabled' => false]);

        $resolved = $this->resolve('also-unknown.example.com');

        $this->assertNull($resolved);
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_bare_base_domain_does_not_resolve_as_subdomain(): void
    {
        // No tenant should be created off `mnjiz.sa` itself — there is no
        // subdomain segment to strip.
        $resolver = app(TenantResolver::class);
        $this->assertNull($resolver->extractSubdomainSlug('mnjiz.sa'));
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_nested_subdomain_is_rejected(): void
    {
        $resolver = app(TenantResolver::class);
        // www.acme.mnjiz.sa → would-be slug "www.acme" contains a dot →
        // the resolver returns null rather than mis-resolving.
        $this->assertNull($resolver->extractSubdomainSlug('www.acme.mnjiz.sa'));
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_tenant_domains_relation_round_trip(): void
    {
        $tenant = Tenant::create(['name' => 'Rel', 'slug' => 'rel', 'status' => 'active']);
        $tenant->domains()->createMany([
            ['domain' => 'rel.mnjiz.sa',  'is_primary' => true,  'verified_at' => now()],
            ['domain' => 'app.rel.com',   'is_primary' => false, 'verified_at' => now()],
        ]);

        $this->assertSame(2, $tenant->fresh()->domains()->count());

        // Resolution by the SECOND row works too.
        $resolved = $this->resolve('app.rel.com');
        $this->assertNotNull($resolved);
        $this->assertSame($tenant->id, $resolved->id);
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_primary_domain_helper_picks_flagged_row(): void
    {
        $tenant = Tenant::create(['name' => 'P', 'slug' => 'p', 'status' => 'active']);
        $tenant->domains()->create(['domain' => 'p.mnjiz.sa', 'is_primary' => false]);
        $primary = $tenant->domains()->create(['domain' => 'cname.p.com', 'is_primary' => true]);

        $this->assertSame($primary->id, $tenant->primaryDomain()?->id);

        // When no row is flagged, falls back to the first inserted.
        $other = Tenant::create(['name' => 'P2', 'slug' => 'p2', 'status' => 'active']);
        $first = $other->domains()->create(['domain' => 'p2.mnjiz.sa', 'is_primary' => false]);
        $other->domains()->create(['domain' => 'second.p2.com',         'is_primary' => false]);

        $this->assertSame($first->id, $other->primaryDomain()?->id);
    }

    // ───────────────────────────────────────────────────────── helpers

    /**
     * Run the FULL resolver chain against a synthetic Request — exercises
     * resolveByHost first then falls back through the slug chain.
     */
    private function resolve(string $host): ?Tenant
    {
        TenantContext::forget();
        $request = Request::create("https://{$host}/", 'GET');
        return app(TenantResolver::class)->resolveFromRequest($request);
    }

    /**
     * Same as resolve() but skips the slug-chain — useful for asserting
     * that the host *alone* would never produce a tenant. Used to prove
     * the central-domain block.
     */
    private function resolveByHostOnly(string $host): ?Tenant
    {
        TenantContext::forget();
        $request = Request::create("https://{$host}/", 'GET');
        return app(TenantResolver::class)->resolveByHost($request);
    }
}
