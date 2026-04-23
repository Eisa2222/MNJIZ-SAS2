<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Actions\Admin\StartImpersonationAction;
use App\Actions\Admin\StopImpersonationAction;
use App\Models\Admin;
use App\Models\ImpersonationLog;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_impersonate_tenant_user_and_log_is_opened(): void
    {
        [$admin, $tenant, $user] = $this->scaffold();

        $request = Request::create('/admin/tenants/firm-a/impersonate/'.$user->id, 'POST');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $log = app(StartImpersonationAction::class)->execute(
            admin: $admin, tenant: $tenant, user: $user, request: $request,
        );

        $this->assertInstanceOf(ImpersonationLog::class, $log);
        $this->assertNull($log->ended_at);
        $this->assertSame($admin->id,  $log->admin_id);
        $this->assertSame($tenant->id, $log->tenant_id);
        $this->assertSame($user->id,   $log->user_id);

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertSame($tenant->id, TenantContext::currentId());
    }

    public function test_cross_tenant_impersonation_is_blocked(): void
    {
        [$admin] = $this->scaffold();

        $tenantB = Tenant::create(['name' => 'Firm B', 'slug' => 'firm-b', 'status' => 'active']);
        $userOfA = TenantContext::runAs(Tenant::where('slug', 'firm-a')->first(), fn () => User::factory()->create());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/does not belong to tenant/i');

        app(StartImpersonationAction::class)->execute(
            admin: $admin,
            tenant: $tenantB,
            user:  $userOfA,
            request: Request::create('/x', 'POST'),
        );
    }

    public function test_suspended_admin_cannot_impersonate(): void
    {
        [$admin, $tenant, $user] = $this->scaffold();
        $admin->update(['status' => Admin::STATUS_SUSPENDED]);

        $this->expectException(\RuntimeException::class);

        app(StartImpersonationAction::class)->execute(
            admin: $admin->refresh(),
            tenant: $tenant,
            user: $user,
            request: Request::create('/x', 'POST'),
        );
    }

    public function test_stop_impersonation_closes_log_and_logs_out_web_guard(): void
    {
        [$admin, $tenant, $user] = $this->scaffold();

        $start = app(StartImpersonationAction::class)->execute(
            admin: $admin, tenant: $tenant, user: $user,
            request: Request::create('/x', 'POST'),
        );

        // Simulate the request lifecycle — StopAction reads session('impersonation').
        $request = Request::create('/admin/impersonation/stop', 'POST');
        $request->setLaravelSession(session());

        $closed = app(StopImpersonationAction::class)->execute($request);

        $this->assertNotNull($closed);
        $this->assertNotNull($closed->ended_at);
        $this->assertSame($start->id, $closed->id);
        $this->assertGuest('web');
    }

    /** @return array{0: Admin, 1: Tenant, 2: User} */
    private function scaffold(): array
    {
        $admin = Admin::create([
            'name'     => 'Support',
            'email'    => 'support@mnjiz.sa',
            'password' => Hash::make('pw'),
            'role'     => Admin::ROLE_SUPPORT,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        $tenant = Tenant::create(['name' => 'Firm A', 'slug' => 'firm-a', 'status' => 'active']);
        $user = TenantContext::runAs($tenant, fn () => User::factory()->create());

        return [$admin, $tenant, $user];
    }
}
