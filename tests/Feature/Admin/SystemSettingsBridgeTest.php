<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\CentralSetting;
use App\Models\SuperAdmin;
use App\Models\SystemSetting;
use App\Services\Settings\SystemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Phase C — System Settings Bridge regression suite.
 *
 *   1. SystemSetting::set + get round-trips a plain value
 *   2. encrypted values are not stored in plaintext at the DB column level
 *   3. setMany invalidates cache so the next get reads fresh data
 *   4. SystemSettingsService falls back to central_settings on miss
 *   5. /super-admin/settings requires super_admin auth (302 when guest)
 *   6. /admin/settings requires admin auth + super_admin role
 *   7. authorized super-admin can update settings via PUT
 *   8. blank secrets do NOT overwrite existing encrypted values
 *   9. ApplySystemSettings overrides Config::set('mail.*') at runtime
 *  10. testMail JSON does not echo the SMTP password
 *  11. testMoyasar JSON does not echo the secret key
 *  12. central_settings dual-write fires when a key already exists there
 */
final class SystemSettingsBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::forgetCache();
        Cache::flush();
        // Login limiter shared across phase tests
        RateLimiter::clear('ops@mnjiz.sa|127.0.0.1');
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_set_and_get_round_trip_plain_value(): void
    {
        SystemSetting::set('app_name', 'MNJIZ Saudi', ['group' => 'general']);

        $this->assertSame('MNJIZ Saudi', SystemSetting::get('app_name'));
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_encrypted_values_are_stored_as_ciphertext(): void
    {
        SystemSetting::set('moyasar_secret_key', 'sk_test_PLAINTEXT_99', [
            'group'        => 'moyasar',
            'is_encrypted' => true,
            'cast'         => 'string',
        ]);

        $raw = DB::table('system_settings')
            ->where('key', 'moyasar_secret_key')
            ->value('value');

        $this->assertNotNull($raw);
        $this->assertNotSame('sk_test_PLAINTEXT_99', $raw, 'cipher-text expected at the DB column');
        $this->assertSame('sk_test_PLAINTEXT_99', Crypt::decryptString($raw));
        $this->assertSame('sk_test_PLAINTEXT_99', SystemSetting::get('moyasar_secret_key'),
            'accessor must return decrypted value');
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_set_many_invalidates_cache(): void
    {
        SystemSetting::set('app_name', 'OLD');
        $this->assertSame('OLD', SystemSetting::get('app_name'));

        SystemSetting::setMany(['app_name' => 'NEW', 'app_url' => 'https://app.test'], ['group' => 'general']);

        $this->assertSame('NEW',                SystemSetting::get('app_name'));
        $this->assertSame('https://app.test',   SystemSetting::get('app_url'));
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_service_falls_back_to_central_settings_when_key_missing_in_system_settings(): void
    {
        // Key lives ONLY in the legacy central_settings table.
        CentralSetting::create([
            'key'          => 'legacy_only_flag',
            'value'        => 'on',
            'group'        => 'misc',
            'is_encrypted' => false,
            'cast'         => 'string',
        ]);

        $svc = app(SystemSettingsService::class);

        // No matching system_settings row → falls through to central_settings.
        $this->assertSame('on', $svc->get('legacy_only_flag'));
        // Truly unknown key returns the explicit default.
        $this->assertSame('default-value', $svc->get('unknown_key', 'default-value'));
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_settings_route_requires_authentication(): void
    {
        $r = $this->get('/super-admin/settings');

        $this->assertContains($r->getStatusCode(), [302, 401, 403],
            '/super-admin/settings must reject anonymous visitors');
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_admin_settings_requires_super_admin_role(): void
    {
        // A SUPPORT admin (active, but not super_admin) must be 403'd.
        $support = Admin::create([
            'name'     => 'Support',
            'email'    => 'support@mnjiz.sa',
            'password' => Hash::make('support-password-123'),
            'role'     => Admin::ROLE_SUPPORT,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        $this->actingAs($support, 'admin');

        $this->get('/admin/settings')->assertForbidden();
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_can_update_settings(): void
    {
        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        $r = $this->put('/super-admin/settings', [
            'group'    => 'general',
            'app_name' => 'MNJIZ Updated',
            'app_url'  => 'https://app.test',
        ]);

        $r->assertRedirect();
        $this->assertSame('MNJIZ Updated', SystemSetting::get('app_name'));
        $this->assertSame('https://app.test', SystemSetting::get('app_url'));
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_blank_secret_does_not_overwrite_existing_encrypted_value(): void
    {
        SystemSetting::set('moyasar_secret_key', 'sk_existing_VAL', [
            'group' => 'moyasar', 'is_encrypted' => true,
        ]);

        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        // Form re-submit with the masked placeholder — must NOT clear it.
        $this->put('/super-admin/settings', [
            'group'              => 'moyasar',
            'moyasar_secret_key' => '••••••••',
        ])->assertRedirect();

        $this->assertSame('sk_existing_VAL', SystemSetting::get('moyasar_secret_key'),
            'placeholder masked value must be ignored');

        // And empty string must also leave it untouched.
        $this->put('/super-admin/settings', [
            'group'              => 'moyasar',
            'moyasar_secret_key' => '',
        ])->assertRedirect();

        $this->assertSame('sk_existing_VAL', SystemSetting::get('moyasar_secret_key'),
            'empty value must NOT overwrite the existing secret');
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_apply_system_settings_overrides_runtime_mail_config(): void
    {
        SystemSetting::setMany([
            'mail_host'         => 'smtp.example.test',
            'mail_port'         => 587,
            'mail_username'     => 'sender@example.test',
            'mail_password'     => 'super-secret-pw',
            'mail_from_address' => 'noreply@example.test',
            'mail_from_name'    => 'MNJIZ',
        ], [
            'group' => 'mail',
        ]);
        // mail_password is one of the auto-encrypted keys via SystemSettingsService.
        SystemSetting::set('mail_password', 'super-secret-pw', [
            'group' => 'mail', 'is_encrypted' => true,
        ]);

        // Pre-condition: nothing is set on the runtime config yet (env defaults).
        $beforeHost = Config::get('mail.mailers.smtp.host');

        // Run the middleware against a synthetic request.
        $request = \Illuminate\Http\Request::create('/super-admin', 'GET');
        $mw = app(\App\Http\Middleware\ApplySystemSettings::class);
        $mw->handle($request, fn () => null);

        $this->assertSame('smtp.example.test', Config::get('mail.mailers.smtp.host'));
        $this->assertSame(587,                  Config::get('mail.mailers.smtp.port'));
        $this->assertSame('super-secret-pw',    Config::get('mail.mailers.smtp.password'));
        $this->assertSame('noreply@example.test', Config::get('mail.from.address'));
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_test_mail_does_not_expose_password_in_response(): void
    {
        Mail::fake();

        SystemSetting::set('mail_password', 'leaky-pw-DO-NOT-LEAK', [
            'group' => 'mail', 'is_encrypted' => true,
        ]);

        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        $r = $this->postJson('/super-admin/settings/test-mail', ['to' => 'qa@mnjiz.sa']);

        $r->assertOk();
        $body = json_encode($r->json());
        $this->assertStringNotContainsString('leaky-pw-DO-NOT-LEAK', $body);
    }

    // 11 ────────────────────────────────────────────────────────────────
    public function test_test_moyasar_does_not_expose_secret_in_response(): void
    {
        SystemSetting::set('moyasar_secret_key', 'sk_DO_NOT_LEAK_999', [
            'group' => 'moyasar', 'is_encrypted' => true,
        ]);

        $super = $this->makeSuperAdmin();
        $this->actingAs($super, 'super_admin');

        // The endpoint will hit Moyasar's API — likely fail with no creds in
        // test env. We only assert it never echoes the secret regardless.
        $r = $this->postJson('/super-admin/settings/test-moyasar');

        $body = json_encode($r->json());
        $this->assertStringNotContainsString('sk_DO_NOT_LEAK_999', $body);
    }

    // 12 ────────────────────────────────────────────────────────────────
    public function test_central_settings_compatibility_dual_write(): void
    {
        // Pre-existing central_settings row (Phase 3) — bridge MUST update it
        // when a system_settings write occurs for the same key.
        CentralSetting::create([
            'key'          => 'support_email',
            'value'        => 'old@mnjiz.sa',
            'group'        => 'general',
            'is_encrypted' => false,
            'cast'         => 'string',
        ]);

        $svc = app(SystemSettingsService::class);
        $svc->set('support_email', 'new@mnjiz.sa', ['group' => 'general']);

        // New value lives in BOTH tables now.
        $this->assertSame('new@mnjiz.sa', SystemSetting::get('support_email'));
        $this->assertSame('new@mnjiz.sa', CentralSetting::query()->where('key', 'support_email')->first()->value);
    }

    // ────────────────────────────────────────────────────────── helpers

    private function makeSuperAdmin(): SuperAdmin
    {
        $admin = Admin::create([
            'name'     => 'Ops Lead',
            'email'    => 'ops@mnjiz.sa',
            'password' => Hash::make('correct-horse-battery-staple'),
            'role'     => Admin::ROLE_SUPER_ADMIN,
            'status'   => Admin::STATUS_ACTIVE,
        ]);

        return SuperAdmin::find($admin->id);
    }
}
