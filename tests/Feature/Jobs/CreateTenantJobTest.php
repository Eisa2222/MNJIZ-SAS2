<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\CreateTenantJob;
use App\Mail\TenantWelcomeMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\NewTenantSubscriptionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Phase F — CreateTenantJob behaviour suite.
 *
 *   1. job creates tenant admin user
 *   2. job is idempotent (re-running does not duplicate)
 *   3. welcome mail is queued
 *   4. welcome mail does NOT contain plaintext password
 *   5. welcome mail contains the signed setup link
 *   6. trial signup dispatches the job (with flag on)
 *   7. checkout callback dispatches the job (with flag on)
 *   8. no auto-login when setup-link flow is enabled
 *   9. super admin notification fires when enabled
 *  10. notification does NOT fire when disabled
 */
final class CreateTenantJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::forgetCache();
        Cache::flush();
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_job_creates_tenant_admin_user(): void
    {
        Mail::fake();
        $tenant = $this->makeTenant();

        (new CreateTenantJob($this->payload($tenant, 'first@acme.test')))
            ->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));

        $user = User::query()->withoutGlobalScopes()
            ->where('email', 'first@acme.test')->first();

        $this->assertNotNull($user);
        $this->assertSame($tenant->id, (int) $user->tenant_id);
        $this->assertSame('first@acme.test', $user->email);
        $this->assertTrue((bool) $user->must_change_password);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_job_is_idempotent(): void
    {
        Mail::fake();
        $tenant = $this->makeTenant();

        $payload = $this->payload($tenant, 'idem@acme.test');

        (new CreateTenantJob($payload))->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));
        (new CreateTenantJob($payload))->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));
        (new CreateTenantJob($payload))->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));

        $count = User::query()->withoutGlobalScopes()
            ->where('email', 'idem@acme.test')->count();

        $this->assertSame(1, $count);
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_welcome_mail_is_queued(): void
    {
        Mail::fake();
        $tenant = $this->makeTenant();

        (new CreateTenantJob($this->payload($tenant, 'mail@acme.test')))
            ->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));

        Mail::assertQueued(TenantWelcomeMail::class, function (TenantWelcomeMail $mail) {
            return $mail->hasTo('mail@acme.test');
        });
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_welcome_mail_does_not_contain_plaintext_password(): void
    {
        Mail::fake();
        $tenant = $this->makeTenant();

        (new CreateTenantJob($this->payload($tenant, 'safe@acme.test')))
            ->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));

        Mail::assertQueued(TenantWelcomeMail::class, function (TenantWelcomeMail $mail) {
            $rendered = $mail->render();

            // Common patterns we MUST NOT leak.
            return ! str_contains(strtolower($rendered), 'temporary password')
                && ! str_contains(strtolower($rendered), 'your password is')
                && ! str_contains(strtolower($rendered), 'temp password');
        });
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_welcome_mail_contains_signed_setup_link(): void
    {
        Mail::fake();
        $tenant = $this->makeTenant();

        (new CreateTenantJob($this->payload($tenant, 'link@acme.test')))
            ->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));

        Mail::assertQueued(TenantWelcomeMail::class, function (TenantWelcomeMail $mail) {
            // setUpUrl property + visible signature query param.
            return str_contains($mail->setupUrl, '/password/setup/')
                && str_contains($mail->setupUrl, 'signature=')
                && str_contains($mail->setupUrl, 'expires=');
        });
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_trial_signup_dispatches_job_when_flag_on(): void
    {
        Bus::fake();
        Config::set('tenancy.signup.use_setup_link', true);
        $this->seedPlan('professional');

        $r = $this->post('/register', [
            'firm_name'             => 'Trial Firm',
            'name'                  => 'Trial User',
            'email'                 => 'trial@flag.test',
            'plan'                  => 'professional',
            'cycle'                 => 'monthly',
            'terms'                 => 'on',
        ]);

        $r->assertRedirect();
        Bus::assertDispatched(CreateTenantJob::class);
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_checkout_callback_dispatches_job_when_flag_on(): void
    {
        Bus::fake();
        Config::set('tenancy.signup.use_setup_link', true);

        $plan = $this->seedPlan('paid-checkout');

        \Illuminate\Support\Facades\Http::fake([
            'api.moyasar.com/*' => \Illuminate\Support\Facades\Http::response([
                'id'       => 'pay_FLAG_777',
                'status'   => 'paid',
                'amount'   => 10000,
                'currency' => 'SAR',
                'source'   => ['type' => 'creditcard'],
                'metadata' => [
                    'plan_id'       => (string) $plan->id,
                    'billing_cycle' => 'monthly',
                    'company_name'  => 'Flag Co',
                    'owner_name'    => 'Owner',
                    'owner_email'   => 'owner@flag.test',
                    'owner_phone'   => '+966500000000',
                    'coupon_code'   => '',
                ],
            ], 200),
        ]);

        $r = $this->get('/checkout/callback?id=pay_FLAG_777');

        $r->assertRedirect();
        Bus::assertDispatched(CreateTenantJob::class);
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_no_auto_login_when_setup_link_flow_enabled(): void
    {
        Bus::fake();
        Mail::fake();
        Config::set('tenancy.signup.use_setup_link', true);
        $this->seedPlan('professional');

        $this->post('/register', [
            'firm_name' => 'NoLogin Firm',
            'name'      => 'NoLogin User',
            'email'     => 'nologin@flag.test',
            'plan'      => 'professional',
            'cycle'     => 'monthly',
            'terms'     => 'on',
        ])->assertRedirect();

        $this->assertGuest();
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_notification_sent_when_enabled(): void
    {
        Notification::fake();
        Mail::fake();

        SystemSetting::set('notify_new_subscription', true);
        SystemSetting::set('admin_notification_email', 'ops@mnjiz.sa');

        $tenant = $this->makeTenant();

        (new CreateTenantJob($this->payload($tenant, 'notify@acme.test')))
            ->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));

        Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable(),
            NewTenantSubscriptionNotification::class,
            function ($notification, $channels, $notifiable) {
                return ($notifiable->routes['mail'] ?? null) === 'ops@mnjiz.sa';
            }
        );
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_disabled_notification_does_nothing(): void
    {
        Notification::fake();
        Mail::fake();

        SystemSetting::set('notify_new_subscription', false);
        SystemSetting::set('admin_notification_email', 'ops@mnjiz.sa');

        $tenant = $this->makeTenant();

        (new CreateTenantJob($this->payload($tenant, 'silent@acme.test')))
            ->handle(app(\App\Services\Auth\TenantPasswordSetupService::class));

        Notification::assertNothingSent();
    }

    // ────────────────────────────────────────────────────────── helpers

    private function makeTenant(): Tenant
    {
        return Tenant::create([
            'name'   => 'Acme Test '.uniqid(),
            'slug'   => 'acme-'.uniqid(),
            'status' => 'active',
        ]);
    }

    private function seedPlan(string $slug): Plan
    {
        return Plan::firstOrCreate(['slug' => $slug], [
            'name'          => ucfirst($slug),
            'description'   => '...',
            'price_monthly' => 100,
            'price_yearly'  => 1000,
            'currency'      => 'SAR',
            'trial_days'    => 14,
            'is_active'     => true,
            'is_featured'   => false,
            'is_free'       => false,
            'sort_order'    => 1,
        ]);
    }

    private function payload(Tenant $tenant, string $email): array
    {
        return [
            'tenant_id'       => $tenant->id,
            'plan_id'         => null,
            'billing_cycle'   => 'monthly',
            'owner_name'      => 'Test Owner',
            'owner_email'     => $email,
            'owner_phone'     => '+966500000000',
            'source'          => 'trial',
            'subscription_id' => null,
            'payment_id'      => null,
            'coupon_code'     => null,
        ];
    }
}
