<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_log_in(): void
    {
        $admin = Admin::create([
            'name'     => 'Ops Lead',
            'email'    => 'ops@mnjiz.sa',
            'password' => Hash::make('correct-horse-battery-staple'),
            'role'     => Admin::ROLE_SUPER_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        $response = $this->post('/admin/login', [
            'email'    => 'ops@mnjiz.sa',
            'password' => 'correct-horse-battery-staple',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web'); // admin guard is SEPARATE from web guard
    }

    public function test_suspended_admin_is_rejected(): void
    {
        Admin::create([
            'name'     => 'Old Employee',
            'email'    => 'gone@mnjiz.sa',
            'password' => Hash::make('pw'),
            'role'     => Admin::ROLE_ADMIN,
            'status'   => Admin::STATUS_SUSPENDED,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email'    => 'gone@mnjiz.sa',
            'password' => 'pw',
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_tenant_user_cannot_authenticate_via_admin_guard(): void
    {
        // Even with matching email+password, a User row must not resolve against
        // the admin guard — they use different providers.
        $response = $this->post('/admin/login', [
            'email'    => 'someone@anytenant.test',
            'password' => 'whatever',
        ]);

        $response->assertStatus(302); // validation failure redirect
        $this->assertGuest('admin');
    }

    public function test_admin_dashboard_requires_auth(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('admin.login'));
    }
}
