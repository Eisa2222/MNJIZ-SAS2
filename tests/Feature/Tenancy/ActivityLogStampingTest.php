<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Actions\Admin\StartImpersonationAction;
use App\Models\Admin;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Every activity_log row MUST carry:
 *   - tenant_id (stamped automatically by TenancyServiceProvider)
 *   - properties.impersonator_admin_id  (only when logged inside an active
 *     impersonation session)
 */
final class ActivityLogStampingTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_is_stamped_with_current_tenant_id(): void
    {
        $tenant = Tenant::create(['name' => 'Audit', 'slug' => 'audit', 'status' => 'active']);

        TenantContext::runAs($tenant, function () {
            activity('test')->log('A tenant-scoped event.');
        });

        $row = Activity::query()->latest('id')->first();
        $this->assertNotNull($row);
        $this->assertSame($tenant->id, (int) $row->tenant_id);
    }

    public function test_activity_logged_without_tenant_context_has_null_tenant_id(): void
    {
        config(['tenancy.fallback_enabled' => false]);
        TenantContext::forget();

        activity('admin')->log('A platform-level event.');

        $row = Activity::query()->latest('id')->first();
        $this->assertNull($row->tenant_id);
    }

    public function test_impersonation_session_stamps_impersonator_admin_id_on_activity(): void
    {
        $tenant = Tenant::create(['name' => 'Imp', 'slug' => 'imp-'.uniqid(), 'status' => 'active']);

        $admin = Admin::create([
            'name' => 'Support',
            'email' => 'support-'.uniqid().'@mnjiz.sa',
            'password' => Hash::make('x'),
            'role' => Admin::ROLE_SUPPORT,
            'status' => Admin::STATUS_ACTIVE,
        ]);

        $user = TenantContext::runAs($tenant, fn () => User::factory()->create());

        $request = Request::create('/x', 'POST');
        app(StartImpersonationAction::class)->execute(
            admin: $admin, tenant: $tenant, user: $user, request: $request,
        );

        // After impersonation starts, session('impersonation') holds the metadata.
        // Any activity logged now should be stamped with the impersonator info.
        activity('billing')->performedOn($user)->log('user-initiated action under impersonation');

        $row = Activity::query()
            ->where('log_name', 'billing')
            ->latest('id')
            ->first();

        $this->assertNotNull($row);
        $props = $row->properties instanceof \Illuminate\Support\Collection
            ? $row->properties->toArray()
            : (array) $row->properties;

        $this->assertArrayHasKey('impersonator_admin_id', $props);
        $this->assertSame($admin->id, (int) $props['impersonator_admin_id']);
        $this->assertArrayHasKey('impersonation_log_id', $props);
    }
}
