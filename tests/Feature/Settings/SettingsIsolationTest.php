<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\CentralSetting;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Services\Settings\SettingsRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 6 Module 5 — Settings isolation / security regression suite.
 *
 * The settings layer holds secrets (API keys, SMTP credentials, OAuth
 * client secrets, signatures). A cross-tenant leak means one firm sees
 * another firm's credentials — worst-case security breach.
 *
 *   1. tenant setting overrides central on getWithFallback
 *   2. fallback to central when tenant has no override
 *   3. tenant cannot read another tenant's settings
 *   4. encrypted setting stored as ciphertext (not plaintext)
 *   5. encrypted setting decrypts correctly on read
 *   6. cache does not leak between tenants
 *   7. updating a setting invalidates cache
 *   8. external API keys isolated per tenant (critical test)
 *   9. super admin withoutTenancy() can read all
 *  10. cross-tenant reassignment on TenantSetting blocked
 */
final class SettingsIsolationTest extends TestCase
{
    use RefreshDatabase;

    private SettingsRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(SettingsRepository::class);
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_tenant_setting_overrides_central(): void
    {
        [$a] = $this->twoTenants();

        // Central default
        $this->repo->setCentral('sms_sender_id', '4jawaly');

        // Tenant A overrides
        TenantContext::runAs($a, fn () => $this->repo->set('sms_sender_id', 'FirmA'));

        TenantContext::runAs($a, function () {
            $this->assertSame('FirmA', $this->repo->getWithFallback('sms_sender_id'));
        });
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_fallback_to_central_when_tenant_has_no_override(): void
    {
        [$a, $b] = $this->twoTenants();

        $this->repo->setCentral('signature_footer', 'Default © MNJIZ');

        TenantContext::runAs($a, function () {
            $this->assertSame(
                'Default © MNJIZ',
                $this->repo->getWithFallback('signature_footer'),
                'Tenant with no override must fall back to central.'
            );
        });

        // Tenant B also has no override — falls back to central too.
        TenantContext::runAs($b, function () {
            $this->assertSame('Default © MNJIZ', $this->repo->getWithFallback('signature_footer'));
        });
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_tenant_cannot_read_another_tenant_settings(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, fn () => $this->repo->set('secret_flag', 'A-only'));

        // From tenant B's context the key does NOT resolve — scope filters out.
        TenantContext::runAs($b, function () {
            $this->assertNull(
                $this->repo->get('secret_flag'),
                'Tenant B must not see tenant A settings via the tenant-scoped accessor.'
            );
        });
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_encrypted_setting_is_stored_as_ciphertext(): void
    {
        [$a] = $this->twoTenants();

        TenantContext::runAs($a, fn () => $this->repo->set(
            'moyasar_api_key',
            'sk_test_TENANT_A_SECRET',
            ['is_encrypted' => true]
        ));

        // Raw column value — not via the attribute accessor — must be ciphertext.
        $raw = DB::table('tenant_settings')
            ->where('tenant_id', $a->id)
            ->where('key', 'moyasar_api_key')
            ->value('value');

        $this->assertNotNull($raw);
        $this->assertNotSame(
            'sk_test_TENANT_A_SECRET',
            $raw,
            'Encrypted settings MUST NOT be stored in plaintext.'
        );

        // And decrypting the raw value yields the original.
        $this->assertSame('sk_test_TENANT_A_SECRET', Crypt::decryptString($raw));
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_encrypted_setting_decrypts_correctly_on_read(): void
    {
        [$a] = $this->twoTenants();

        TenantContext::runAs($a, function () {
            $this->repo->set(
                'smtp_password',
                'super-secret-smtp-pw',
                ['is_encrypted' => true, 'cast' => 'string']
            );

            // Clear cache so the read goes through the accessor freshly.
            $this->repo->flushTenantCache(TenantContext::currentId());

            $this->assertSame(
                'super-secret-smtp-pw',
                $this->repo->get('smtp_password'),
                'Encrypted setting must decrypt transparently on read.'
            );
        });
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_cache_does_not_leak_between_tenants(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, fn () => $this->repo->set('branding_color', '#A00000'));
        TenantContext::runAs($b, fn () => $this->repo->set('branding_color', '#0000B0'));

        // Warm both caches.
        $valA = TenantContext::runAs($a, fn () => $this->repo->get('branding_color'));
        $valB = TenantContext::runAs($b, fn () => $this->repo->get('branding_color'));

        $this->assertSame('#A00000', $valA);
        $this->assertSame('#0000B0', $valB);

        // Inspect the cache keys directly — they MUST be distinct.
        $this->assertNotNull(Cache::get("tenant_{$a->id}_settings"));
        $this->assertNotNull(Cache::get("tenant_{$b->id}_settings"));
        $this->assertNotSame(
            Cache::get("tenant_{$a->id}_settings"),
            Cache::get("tenant_{$b->id}_settings"),
            'Per-tenant cache keys must hold distinct payloads.'
        );
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_updating_setting_invalidates_cache(): void
    {
        [$a] = $this->twoTenants();

        TenantContext::runAs($a, function () use ($a) {
            $this->repo->set('theme', 'light');
            $this->assertSame('light', $this->repo->get('theme'));

            // Confirm the cache was populated.
            $this->assertNotNull(Cache::get("tenant_{$a->id}_settings"));

            // Write again — this must flush.
            $this->repo->set('theme', 'dark');

            // Without flush, the old 'light' value would stick.
            $this->assertSame('dark', $this->repo->get('theme'));
        });
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_external_api_keys_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, fn () => $this->repo->set(
            'moyasar_api_key',
            'sk_test_A_firm',
            ['is_encrypted' => true]
        ));

        TenantContext::runAs($b, fn () => $this->repo->set(
            'moyasar_api_key',
            'sk_test_B_firm',
            ['is_encrypted' => true]
        ));

        $aKey = TenantContext::runAs($a, fn () => $this->repo->get('moyasar_api_key'));
        $bKey = TenantContext::runAs($b, fn () => $this->repo->get('moyasar_api_key'));

        $this->assertSame('sk_test_A_firm', $aKey);
        $this->assertSame('sk_test_B_firm', $bKey);
        $this->assertNotSame(
            $aKey,
            $bKey,
            'CRITICAL: each tenant must have its own API key. A shared key = a shared merchant account = financial disaster.'
        );
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_without_tenancy_can_see_all(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, fn () => $this->repo->set('x', 'A'));
        TenantContext::runAs($b, fn () => $this->repo->set('x', 'B'));

        TenantContext::forget();

        $this->assertSame(
            2,
            TenantSetting::withoutTenancy()->where('key', 'x')->count(),
            'Super admin cross-tenant view must work via withoutTenancy().'
        );
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_cross_tenant_reassignment_blocked_on_tenant_setting(): void
    {
        [$a, $b] = $this->twoTenants();

        $setting = TenantContext::runAs($a, fn () => $this->repo->set('k', 'v'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant reassignment/i');

        $setting->tenant_id = $b->id;
        $setting->save();
    }

    // Bonus: legacy Settings::current() works on legacy `settings` table ───
    public function test_legacy_settings_current_returns_tenant_scoped_row(): void
    {
        [$a, $b] = $this->twoTenants();

        // Each tenant writes its own office_name to the legacy singleton row.
        TenantContext::runAs($a, function () {
            $s = Settings::current();
            // Fill required NOT NULL defaults so save succeeds.
            $this->seedLegacyRequiredFields($s);
            $s->office_name = 'Firm A Legal';
            $s->save();
        });

        TenantContext::runAs($b, function () {
            $s = Settings::current();
            $this->seedLegacyRequiredFields($s);
            $s->office_name = 'Firm B Legal';
            $s->save();
        });

        $this->assertSame(
            'Firm A Legal',
            TenantContext::runAs($a, fn () => Settings::current()->office_name)
        );
        $this->assertSame(
            'Firm B Legal',
            TenantContext::runAs($b, fn () => Settings::current()->office_name)
        );
    }

    // ────────────────────────────────────────────────────────── helpers

    /** @return array{0: Tenant, 1: Tenant} */
    private function twoTenants(): array
    {
        return [
            Tenant::create(['name' => 'ST-A', 'slug' => 'st-a-'.uniqid(), 'status' => 'active']),
            Tenant::create(['name' => 'ST-B', 'slug' => 'st-b-'.uniqid(), 'status' => 'active']),
        ];
    }

    /**
     * The legacy `settings` table has many NOT NULL columns without defaults
     * (archive_delete_duration, main_email, etc). Seed them so save() succeeds
     * in tests without needing to enumerate the whole schema each time.
     */
    private function seedLegacyRequiredFields(Settings $s): void
    {
        $notNullColumns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE
              FROM information_schema.COLUMNS
             WHERE TABLE_NAME = 'settings'
               AND IS_NULLABLE = 'NO'
               AND COLUMN_DEFAULT IS NULL
               AND COLUMN_NAME NOT IN ('id', 'tenant_id', 'created_at', 'updated_at')
        ");

        foreach ($notNullColumns as $col) {
            if (! isset($s->{$col->COLUMN_NAME})) {
                $s->{$col->COLUMN_NAME} = in_array($col->DATA_TYPE, ['int','bigint','tinyint','decimal','float','double'])
                    ? 0
                    : '';
            }
        }
    }
}
