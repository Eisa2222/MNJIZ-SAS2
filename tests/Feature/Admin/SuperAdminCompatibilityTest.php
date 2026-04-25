<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Phase B — Super Admin Compatibility Layer regression suite.
 *
 *   1. /super-admin/login renders the login form
 *   2. super_admin guard authenticates an admin row through SuperAdmin model
 *   3. tenant user (web guard) is rejected by /super-admin/login
 *   4. /super-admin (dashboard) is auth-protected
 *   5. legacy /admin still serves its own login form (zero regression)
 *   6. ADMIN_LEGACY_REDIRECT=false → /admin responds normally (no redirect)
 *   7. ADMIN_LEGACY_REDIRECT=true  → /admin/* 301-redirects to /super-admin/*
 *   8. session buckets stay isolated: web / admin / super_admin
 */
final class SuperAdminCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Login limiter is keyed per email+IP across runs — clear so tests
        // don't trip the throttle from each other.
        RateLimiter::clear('ops@mnjiz.sa|127.0.0.1');
        RateLimiter::clear('newop@mnjiz.sa|127.0.0.1');
        RateLimiter::clear('alice@tenant.test|127.0.0.1');
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_login_form_renders(): void
    {
        $r = $this->get('/super-admin/login');

        $r->assertOk();
        // The shared login view must POST to the new super-admin endpoint
        // (loginAction parameterized in PB).
        $r->assertSee(route('super-admin.login.attempt'), false);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_active_super_admin_can_log_in(): void
    {
        $admin = Admin::create([
            'name'     => 'Ops Lead',
            'email'    => 'ops@mnjiz.sa',
            'password' => Hash::make('correct-horse-battery-staple'),
            'role'     => Admin::ROLE_SUPER_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        $response = $this->post('/super-admin/login', [
            'email'    => 'ops@mnjiz.sa',
            'password' => 'correct-horse-battery-staple',
        ]);

        $response->assertRedirect(route('super-admin.dashboard'));

        // The session is on the NEW super_admin guard, NOT the legacy one.
        $this->assertAuthenticatedAs(SuperAdmin::find($admin->id), 'super_admin');
        $this->assertGuest('admin');   // legacy guard NOT touched
        $this->assertGuest('web');     // tenant guard NOT touched
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_tenant_user_cannot_log_in_via_super_admin_guard(): void
    {
        // A user that exists only in the `users` table (the tenant guard)
        // must NOT be recognized by the super_admin provider.
        User::create([
            'name'                => 'Alice Tenant',
            'email'               => 'alice@tenant.test',
            'password'            => Hash::make('tenant-password'),
            'nationality'         => 'SA',
            'tour_completed'      => 1,
            'tour_task_completed' => 1,
        ]);

        $response = $this->from('/super-admin/login')->post('/super-admin/login', [
            'email'    => 'alice@tenant.test',
            'password' => 'tenant-password',
        ]);

        $response->assertRedirect('/super-admin/login');
        $response->assertSessionHasErrors('email');

        $this->assertGuest('super_admin');
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_dashboard_requires_authentication(): void
    {
        $r = $this->get('/super-admin');

        // auth:super_admin → unauthenticated bounce. Laravel's default
        // redirect target is `/login` for the web guard; for our custom
        // guard we just assert it's a redirect (not 200).
        $this->assertContains($r->getStatusCode(), [302, 401, 403]);
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_legacy_admin_login_still_works(): void
    {
        // The whole point of compatibility — /admin must keep responding
        // even after the super-admin layer is in place.
        $r = $this->get('/admin/login');

        $r->assertOk();
        $r->assertSee(route('admin.login.attempt'), false);
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_legacy_admin_responds_directly_when_redirect_off(): void
    {
        config(['tenancy.admin_legacy_redirect' => false]);

        // Same admin login posted to the LEGACY endpoint → standard flow,
        // no 301 to /super-admin.
        Admin::create([
            'name'     => 'Op',
            'email'    => 'newop@mnjiz.sa',
            'password' => Hash::make('pw-legacy-12345'),
            'role'     => Admin::ROLE_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        $r = $this->post('/admin/login', [
            'email'    => 'newop@mnjiz.sa',
            'password' => 'pw-legacy-12345',
        ]);

        $r->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_legacy_admin_redirects_to_super_admin_when_flag_on(): void
    {
        config(['tenancy.admin_legacy_redirect' => true]);

        $r = $this->get('/admin/login');

        // The middleware fires BEFORE the controller, so the request is
        // 301-redirected to the spec-compliant path. The query string,
        // sub-path and verb are preserved at /super-admin/login.
        $r->assertStatus(301);
        $r->assertRedirect('/super-admin/login');
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_session_buckets_are_isolated_across_three_guards(): void
    {
        $admin = Admin::create([
            'name'     => 'Multi',
            'email'    => 'multi@mnjiz.sa',
            'password' => Hash::make('multi-password-12345'),
            'role'     => Admin::ROLE_SUPER_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        // Log in via the SUPER ADMIN endpoint.
        $this->post('/super-admin/login', [
            'email'    => 'multi@mnjiz.sa',
            'password' => 'multi-password-12345',
        ]);

        $this->assertTrue(auth('super_admin')->check(),  'super_admin guard should be authenticated');
        $this->assertFalse(auth('admin')->check(),       'admin guard should NOT inherit super_admin login');
        $this->assertFalse(auth('web')->check(),         'web (tenant) guard should NOT inherit super_admin login');

        // Logging out of super_admin must NOT log out admin (which wasn't
        // logged in to begin with) and must NOT touch the web guard.
        $this->post('/super-admin/logout');

        $this->assertGuest('super_admin');
        $this->assertGuest('admin');
        $this->assertGuest('web');
    }
}
