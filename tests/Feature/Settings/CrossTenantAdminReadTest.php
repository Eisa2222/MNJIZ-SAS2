<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Actions\Settings\GetTenantSettingAction;
use App\Actions\Settings\UpdateTenantSettingAction;
use App\Models\Tenant;
use App\Services\Settings\SettingsRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression for BUG #1 (Audit): SettingsRepository MUST bypass TenantScope
 * when the caller supplies an explicit tenantId (e.g., Super Admin viewing
 * any tenant's configured keys from /admin context).
 *
 * Before the fix: reading tenant B's settings from Default Tenant fallback
 * context returned [] because TenantScope combined `tenant_id = 1` with the
 * explicit `tenant_id = $b` → empty intersection.
 */
final class CrossTenantAdminReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_read_other_tenant_settings_while_in_default_context(): void
    {
        $b = Tenant::create(['name' => 'B', 'slug' => 'b-'.uniqid(), 'status' => 'active']);

        // 1. Seed a value inside tenant B's context.
        TenantContext::runAs($b, function () {
            app(UpdateTenantSettingAction::class)('qoyod.api_key', 'B-LIVE-KEY', [
                'group' => 'qoyod', 'is_encrypted' => true,
            ]);
        });

        // 2. Now pretend we're in the admin panel — context falls back to Default Tenant.
        TenantContext::forget();

        // 3. Reading tenant B's setting by explicit ID must succeed despite the
        //    Default-Tenant fallback context.
        $value = app(GetTenantSettingAction::class)('qoyod.api_key', null, $b->id);

        $this->assertSame('B-LIVE-KEY', $value);
    }

    public function test_admin_can_write_other_tenant_settings(): void
    {
        $b = Tenant::create(['name' => 'B', 'slug' => 'b-'.uniqid(), 'status' => 'active']);

        TenantContext::forget();
        config(['tenancy.fallback_enabled' => true]); // admin uses Default Tenant

        app(UpdateTenantSettingAction::class)(
            'microsoft.client_id',
            'admin-wrote-this',
            ['group' => 'microsoft'],
            $b->id,
        );

        // Verify from within B's own context that the write landed correctly.
        TenantContext::runAs($b, function () {
            $this->assertSame(
                'admin-wrote-this',
                app(GetTenantSettingAction::class)('microsoft.client_id')
            );
        });

        // Confirm Default Tenant's settings were NOT polluted.
        $this->assertNull(
            app(SettingsRepository::class)->get('microsoft.client_id', null, 1)
        );
    }

    public function test_cross_tenant_writes_do_not_leak_into_default(): void
    {
        $a = Tenant::create(['name' => 'A', 'slug' => 'a-'.uniqid(), 'status' => 'active']);
        $b = Tenant::create(['name' => 'B', 'slug' => 'b-'.uniqid(), 'status' => 'active']);

        TenantContext::forget();

        $repo = app(SettingsRepository::class);
        $repo->set('work_hours.start', '08:00', [], $a->id);
        $repo->set('work_hours.start', '10:00', [], $b->id);

        $this->assertSame('08:00', $repo->get('work_hours.start', null, $a->id));
        $this->assertSame('10:00', $repo->get('work_hours.start', null, $b->id));
    }
}
