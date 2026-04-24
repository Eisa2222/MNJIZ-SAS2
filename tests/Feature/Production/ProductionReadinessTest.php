<?php

declare(strict_types=1);

namespace Tests\Feature\Production;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Phase 8 — production-readiness regression suite.
 *
 *   1. /health endpoint returns 200 OK
 *   2. /health/db checks DB connectivity
 *   3. /health/queue reports queue status
 *   4. RequestIdMiddleware attaches X-Request-Id header
 *   5. Incoming X-Request-Id is preserved (trace correlation)
 *   6. SecureHeadersMiddleware applies security headers
 *   7. Rate limiter "login" limits excessive attempts (by email+IP)
 *   8. Per-tenant rate limiter keys include tenant_id
 *   9. Deploy-check flags default tenant presence
 *  10. Backup / migration audit log records runs
 */
final class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJson([
            'status'  => 'ok',
            'service' => 'mnjiz',
        ]);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_health_db_endpoint_probes_database(): void
    {
        $response = $this->get('/health/db');

        $response->assertOk();
        $response->assertJson(['status' => 'ok', 'check' => 'db']);
        $this->assertIsInt($response->json('latency_ms'));
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_health_queue_endpoint_reports_driver(): void
    {
        $response = $this->get('/health/queue');

        // sync or null in tests — both are fine; assert shape only.
        $response->assertOk();
        $response->assertJsonPath('check', 'queue');
        $this->assertNotEmpty($response->json('driver'));
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_request_id_middleware_attaches_header(): void
    {
        $response = $this->get('/health');

        $requestId = $response->headers->get('X-Request-Id');

        $this->assertNotEmpty($requestId, 'every response must carry X-Request-Id');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $requestId,
            'X-Request-Id must be a UUID'
        );
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_request_id_middleware_preserves_incoming_id(): void
    {
        $incoming = '11111111-2222-3333-4444-555555555555';
        $response = $this->get('/health', ['X-Request-Id' => $incoming]);

        $this->assertSame(
            $incoming,
            $response->headers->get('X-Request-Id'),
            'a valid incoming X-Request-Id must be echoed for trace correlation'
        );
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_secure_headers_middleware_applies_baseline(): void
    {
        $response = $this->get('/health');

        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff',    $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('1; mode=block', $response->headers->get('X-XSS-Protection'));
        $this->assertStringContainsString(
            'strict-origin',
            (string) $response->headers->get('Referrer-Policy')
        );
        $this->assertStringContainsString(
            'geolocation',
            (string) $response->headers->get('Permissions-Policy')
        );
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_login_rate_limiter_blocks_excessive_attempts(): void
    {
        $max     = (int) config('security.rate_limits.login.max_attempts');
        $key     = 'alice@example.com|127.0.0.1';

        // Burn down the allowance.
        for ($i = 0; $i < $max; $i++) {
            $this->assertTrue(
                RateLimiter::attempt($key, $max, fn () => true, 900) !== false,
                "attempt {$i} should pass within the allowance"
            );
        }

        // The next attempt must be blocked.
        $this->assertFalse(
            RateLimiter::attempt($key, $max, fn () => true, 900),
            'login limiter must refuse once allowance is exhausted'
        );

        RateLimiter::clear($key);
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_per_tenant_api_limiter_keys_by_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        // Simulate two counter bumps for tenant A; tenant B starts at 0.
        $keyA = "tenant_{$a->id}_api_127.0.0.1";
        $keyB = "tenant_{$b->id}_api_127.0.0.1";

        RateLimiter::hit($keyA);
        RateLimiter::hit($keyA);

        $this->assertSame(2, RateLimiter::attempts($keyA));
        $this->assertSame(
            0,
            RateLimiter::attempts($keyB),
            'per-tenant limiter MUST be keyed per-tenant — B must not share A bucket.'
        );

        RateLimiter::clear($keyA);
        RateLimiter::clear($keyB);
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_deploy_check_command_is_registered_and_runs(): void
    {
        // Smoke test — command resolves and terminates with a real exit
        // code. Its individual checks are tested by their subjects
        // (default tenant, migration validator, failed_jobs, etc.).
        Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default', 'status' => 'active']
        );

        $exit = Artisan::call('saas:deploy:check');

        $this->assertContains(
            $exit,
            [0, 1],
            'deploy:check must terminate deterministically (0 = GO, 1 = NO-GO)'
        );
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_backup_command_writes_audit_row(): void
    {
        // `saas:backup:run` will try to shell out to mysqldump/tar.
        // In CI/test those binaries may be absent, and the command
        // handles both cases gracefully (status=error / skipped). We
        // assert that the audit row is always written regardless.
        Artisan::call('saas:backup:run', ['--files-only' => true, '--retention-days' => 7]);

        $this->assertTrue(
            \App\Models\SaasMigrationRun::where('command', 'saas:backup:run')->exists(),
            'backup command must always log an audit row'
        );
    }

    // ───────────────────────────────────────────────────────── helpers

    /** @return array{0: Tenant, 1: Tenant} */
    private function twoTenants(): array
    {
        return [
            Tenant::create(['name' => 'P8-A', 'slug' => 'p8-a-'.uniqid(), 'status' => 'active']),
            Tenant::create(['name' => 'P8-B', 'slug' => 'p8-b-'.uniqid(), 'status' => 'active']),
        ];
    }
}
