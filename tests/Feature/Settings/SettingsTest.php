<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Actions\Settings\GetCentralSettingAction;
use App\Actions\Settings\GetTenantSettingAction;
use App\Actions\Settings\UpdateCentralSettingAction;
use App\Actions\Settings\UpdateTenantSettingAction;
use App\Models\Tenant;
use App\Services\Settings\SettingsRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_central_settings_roundtrip_and_encryption(): void
    {
        app(UpdateCentralSettingAction::class)(
            'moyasar.secret_key',
            'sk_live_xxxxxx',
            ['group' => 'billing', 'is_encrypted' => true, 'cast' => 'string']
        );

        // Ciphertext on disk — verify it's NOT plaintext.
        $row = \App\Models\CentralSetting::where('key', 'moyasar.secret_key')->firstOrFail();
        $this->assertNotSame('sk_live_xxxxxx', $row->getRawValue());

        // Accessor decrypts transparently.
        $this->assertSame('sk_live_xxxxxx', app(GetCentralSettingAction::class)('moyasar.secret_key'));
    }

    public function test_tenant_settings_are_isolated_per_tenant(): void
    {
        $a = Tenant::create(['name' => 'A', 'slug' => 'a', 'status' => 'active']);
        $b = Tenant::create(['name' => 'B', 'slug' => 'b', 'status' => 'active']);

        TenantContext::runAs($a, fn () => app(UpdateTenantSettingAction::class)('qoyod.api_key', 'KEY-A', ['is_encrypted' => true]));
        TenantContext::runAs($b, fn () => app(UpdateTenantSettingAction::class)('qoyod.api_key', 'KEY-B', ['is_encrypted' => true]));

        TenantContext::runAs($a, function () {
            $this->assertSame('KEY-A', app(GetTenantSettingAction::class)('qoyod.api_key'));
        });
        TenantContext::runAs($b, function () {
            $this->assertSame('KEY-B', app(GetTenantSettingAction::class)('qoyod.api_key'));
        });
    }

    public function test_cache_invalidates_on_write(): void
    {
        $t = Tenant::create(['name' => 'C', 'slug' => 'c', 'status' => 'active']);
        $repo = app(SettingsRepository::class);

        TenantContext::runAs($t, function () use ($repo, $t) {
            $repo->set('work_hours.start', '09:00');
            $this->assertSame('09:00', $repo->get('work_hours.start'));

            // Confirm cache is populated...
            $this->assertTrue(Cache::has("tenant_{$t->id}_settings"));

            $repo->set('work_hours.start', '08:30');
            // ...and invalidated on update.
            $this->assertSame('08:30', $repo->get('work_hours.start'));
        });
    }
}
