<?php

declare(strict_types=1);

namespace Tests\Feature\TenantAuth;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Auth\TenantPasswordSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Phase F — TenantPasswordSetup flow regression suite.
 *
 *   1. setup link valid for 48 hours
 *   2. expired (49h+) setup link rejected
 *   3. tampered URL signature rejected
 *   4. password setup updates password
 *   5. token row is deleted after successful setup
 *   6. wrong token returns 403
 */
final class TenantPasswordSetupTest extends TestCase
{
    use RefreshDatabase;

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_setup_link_valid_for_48_hours(): void
    {
        [$tenant, $user] = $this->seedTenantAndUser();
        $service = app(TenantPasswordSetupService::class);

        $url = $service->issue($user);

        $this->get($url)->assertOk();
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_expired_setup_link_rejected(): void
    {
        [$tenant, $user] = $this->seedTenantAndUser();
        $service = app(TenantPasswordSetupService::class);

        Carbon::setTestNow(now()->subHours(50));
        $url = $service->issue($user);
        Carbon::setTestNow(null);

        // 50h ago + 48h validity = 2h past expiry → signed middleware 403s
        $this->get($url)->assertForbidden();
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_tampered_signature_rejected(): void
    {
        [$tenant, $user] = $this->seedTenantAndUser();
        $service = app(TenantPasswordSetupService::class);

        $url = $service->issue($user);
        $tampered = preg_replace('/email=([^&]+)/', 'email=evil@attacker.test', $url);

        $this->get($tampered)->assertForbidden();
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_password_setup_updates_password(): void
    {
        [$tenant, $user] = $this->seedTenantAndUser('owner@setup.test');
        $service = app(TenantPasswordSetupService::class);
        $token   = $service->forceIssueForTesting($user->email);

        $r = $this->post('/password/setup', [
            'email'                 => $user->email,
            'token'                 => $token,
            'password'              => 'new-strong-password-123',
            'password_confirmation' => 'new-strong-password-123',
        ]);

        $r->assertRedirect('/login');

        $fresh = User::query()->withoutGlobalScopes()->find($user->id);
        $this->assertTrue(Hash::check('new-strong-password-123', $fresh->password));
        $this->assertFalse((bool) $fresh->must_change_password);
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_token_is_deleted_after_successful_setup(): void
    {
        [$tenant, $user] = $this->seedTenantAndUser('once@setup.test');
        $service = app(TenantPasswordSetupService::class);
        $token   = $service->forceIssueForTesting($user->email);

        $this->assertSame(1, DB::table('tenant_password_setup_tokens')
            ->where('email', $user->email)->count());

        $this->post('/password/setup', [
            'email'                 => $user->email,
            'token'                 => $token,
            'password'              => 'new-strong-password-123',
            'password_confirmation' => 'new-strong-password-123',
        ])->assertRedirect();

        $this->assertSame(0, DB::table('tenant_password_setup_tokens')
            ->where('email', $user->email)->count());

        // Re-submitting the same token must now fail.
        $this->post('/password/setup', [
            'email'                 => $user->email,
            'token'                 => $token,
            'password'              => 'another-password-456',
            'password_confirmation' => 'another-password-456',
        ])->assertSessionHasErrors(['email']);
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_wrong_token_returns_403(): void
    {
        [$tenant, $user] = $this->seedTenantAndUser('badtok@setup.test');
        $service = app(TenantPasswordSetupService::class);
        $service->forceIssueForTesting($user->email);

        // 64-hex-char string that is NOT the issued one.
        $fakeToken = str_repeat('a', 64);

        // Sign a valid URL signature but with a wrong token.
        $url = URL::temporarySignedRoute(
            TenantPasswordSetupService::ROUTE_NAME,
            now()->addHours(48),
            ['token' => $fakeToken, 'email' => $user->email],
        );

        $this->get($url)->assertForbidden();
    }

    // ────────────────────────────────────────────────────────── helpers

    private function seedTenantAndUser(string $email = 'setup@acme.test'): array
    {
        $tenant = Tenant::create([
            'name'   => 'Setup Co '.uniqid(),
            'slug'   => 'setup-'.uniqid(),
            'status' => 'active',
        ]);

        $user = \App\Tenancy\TenantContext::runAs($tenant, fn () => User::create([
            'name'                 => 'Setup Owner',
            'email'                => $email,
            'password'             => Hash::make('placeholder-'.uniqid()),
            'must_change_password' => true,
            'nationality'          => 'SA',
            'tour_completed'       => 0,
            'tour_task_completed'  => 0,
        ]));

        return [$tenant, $user];
    }
}
