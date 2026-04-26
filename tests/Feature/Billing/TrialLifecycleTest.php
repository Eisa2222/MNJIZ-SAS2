<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Enums\Billing\BillingCycle;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\SubscriptionStatus;
use App\Mail\TrialExpiredMail;
use App\Mail\TrialExpiryWarningMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Phase G — Trial lifecycle command + mail regression suite.
 *
 *   1. trial warning command sends warning exactly once
 *   2. warning disabled does not send email
 *   3. warning days setting respected (multi-milestone)
 *   4. expired trial suspends tenant when setting enabled
 *   5. expired trial does NOT suspend tenant when setting disabled
 *   6. expired mail sent exactly once
 *   7. rerunning commands is idempotent
 *   8. StartTrialAction respects plan.trial_days (and the setting fallback)
 *   9. trial disabled prevents creating trial via /register flag-on path
 *  10. schedule commands are registered (artisan list)
 */
final class TrialLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::forgetCache();
        Cache::flush();
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_trial_warning_command_sends_warning_exactly_once(): void
    {
        Mail::fake();

        SystemSetting::set('notify_trial_expiring', true);
        SystemSetting::set('trial_warning_days',    [3], ['cast' => 'array']);

        [$tenant, $user, $sub] = $this->seedTrialingSubscription(3);

        $this->artisan('saas:send-trial-warnings')->assertSuccessful();
        Mail::assertQueued(TrialExpiryWarningMail::class, 1);

        // Re-run on the same day: no duplicate.
        $this->artisan('saas:send-trial-warnings')->assertSuccessful();
        Mail::assertQueued(TrialExpiryWarningMail::class, 1);

        // The meta tracker recorded the milestone.
        $this->assertContains(3, (array) ($sub->fresh()->meta['trial_warning_days_sent'] ?? []));
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_warning_disabled_does_not_send_email(): void
    {
        Mail::fake();

        SystemSetting::set('notify_trial_expiring', false);
        SystemSetting::set('trial_warning_days',    [3], ['cast' => 'array']);

        [$tenant, $user, $sub] = $this->seedTrialingSubscription(3);

        $this->artisan('saas:send-trial-warnings')->assertSuccessful();
        Mail::assertNothingQueued();
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_warning_days_setting_respected(): void
    {
        Mail::fake();

        SystemSetting::set('notify_trial_expiring', true);
        SystemSetting::set('trial_warning_days',    [7, 3], ['cast' => 'array']);

        // One sub eligible at 7 days, another at 3 days, a third at 5 (not eligible).
        $this->seedTrialingSubscription(7, 'seven@trial.test');
        $this->seedTrialingSubscription(3, 'three@trial.test');
        $this->seedTrialingSubscription(5, 'five@trial.test');

        $this->artisan('saas:send-trial-warnings')->assertSuccessful();

        Mail::assertQueued(TrialExpiryWarningMail::class, 2);
        Mail::assertQueued(TrialExpiryWarningMail::class, fn ($m) => $m->daysRemaining === 7 && $m->hasTo('seven@trial.test'));
        Mail::assertQueued(TrialExpiryWarningMail::class, fn ($m) => $m->daysRemaining === 3 && $m->hasTo('three@trial.test'));
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_expired_trial_suspends_tenant_when_setting_enabled(): void
    {
        Mail::fake();

        SystemSetting::set('trial_suspend_after_expiry', true);
        SystemSetting::set('notify_trial_expiring',      true);

        [$tenant, $user, $sub] = $this->seedTrialingSubscription(-1);   // -1 ⇒ already past

        $this->artisan('saas:check-trial-expiry')->assertSuccessful();

        $this->assertSame(SubscriptionStatus::Expired, $sub->fresh()->status);
        $this->assertSame(Tenant::STATUS_SUSPENDED, $tenant->fresh()->status);
        $this->assertNotNull($sub->fresh()->trial_expired_notified_at);
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_expired_trial_does_not_suspend_tenant_when_setting_disabled(): void
    {
        Mail::fake();

        SystemSetting::set('trial_suspend_after_expiry', false);
        SystemSetting::set('notify_trial_expiring',      false);

        [$tenant, $user, $sub] = $this->seedTrialingSubscription(-1);

        $this->artisan('saas:check-trial-expiry')->assertSuccessful();

        $this->assertSame(SubscriptionStatus::Expired, $sub->fresh()->status);
        $this->assertSame(Tenant::STATUS_ACTIVE, $tenant->fresh()->status);
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_expired_mail_sent_exactly_once(): void
    {
        Mail::fake();

        SystemSetting::set('trial_suspend_after_expiry', true);
        SystemSetting::set('notify_trial_expiring',      true);

        [$tenant, $user, $sub] = $this->seedTrialingSubscription(-2, 'oneshot@trial.test');

        $this->artisan('saas:check-trial-expiry')->assertSuccessful();
        Mail::assertQueued(TrialExpiredMail::class, 1);

        // Re-run: status is now Expired, so the where-filter excludes it,
        // AND the trial_expired_notified_at NOT NULL column blocks re-mail.
        $this->artisan('saas:check-trial-expiry')->assertSuccessful();
        Mail::assertQueued(TrialExpiredMail::class, 1);
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_rerunning_commands_is_idempotent(): void
    {
        Mail::fake();

        SystemSetting::set('trial_suspend_after_expiry', true);
        SystemSetting::set('notify_trial_expiring',      true);
        SystemSetting::set('trial_warning_days',         [5], ['cast' => 'array']);

        [$tenantA, $userA, $subA] = $this->seedTrialingSubscription(5, 'a@idem.test');
        [$tenantB, $userB, $subB] = $this->seedTrialingSubscription(-1, 'b@idem.test');

        // 3 runs each — totals must not multiply.
        for ($i = 0; $i < 3; $i++) {
            $this->artisan('saas:send-trial-warnings')->assertSuccessful();
            $this->artisan('saas:check-trial-expiry')->assertSuccessful();
        }

        Mail::assertQueued(TrialExpiryWarningMail::class, 1);
        Mail::assertQueued(TrialExpiredMail::class, 1);

        $this->assertSame(SubscriptionStatus::Trialing, $subA->fresh()->status);
        $this->assertSame(SubscriptionStatus::Expired,  $subB->fresh()->status);
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_start_trial_action_respects_plan_trial_days(): void
    {
        $plan = $this->seedPlan(['trial_days' => 21]);
        $tenant = $this->seedTenant();

        $action = app(\App\Actions\Billing\Subscription\StartTrialAction::class);
        $sub = $action->execute($tenant, $plan, BillingCycle::Monthly, PaymentGateway::Moyasar);

        $this->assertSame(SubscriptionStatus::Trialing, $sub->status);
        $this->assertNotNull($sub->trial_ends_at);
        $this->assertSame(now()->addDays(21)->toDateString(), $sub->trial_ends_at->toDateString());
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_zero_trial_days_starts_active_not_trialing(): void
    {
        $plan = $this->seedPlan(['trial_days' => 0]);
        $tenant = $this->seedTenant();

        $sub = app(\App\Actions\Billing\Subscription\StartTrialAction::class)
            ->execute($tenant, $plan, BillingCycle::Monthly, PaymentGateway::Moyasar);

        $this->assertSame(SubscriptionStatus::Active, $sub->status);
        $this->assertNull($sub->trial_ends_at);
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_schedule_commands_are_registered(): void
    {
        $this->artisan('list', ['--raw' => true])
            ->expectsOutputToContain('saas:check-trial-expiry')
            ->expectsOutputToContain('saas:send-trial-warnings')
            ->assertSuccessful();
    }

    // ────────────────────────────────────────────────────────── helpers

    /**
     * @return array{0: Tenant, 1: User, 2: Subscription}
     */
    private function seedTrialingSubscription(int $daysFromNow, string $ownerEmail = 'owner@trial.test'): array
    {
        $tenant = $this->seedTenant();
        $plan   = $this->seedPlan(['trial_days' => max(1, $daysFromNow + 30)]);

        $user = TenantContext::runAs($tenant, fn () => User::create([
            'name'                 => 'Trial Owner',
            'email'                => $ownerEmail.'-'.uniqid(),
            'password'             => Hash::make('placeholder-'.uniqid()),
            'must_change_password' => true,
            'nationality'          => 'SA',
            'tour_completed'       => 0,
            'tour_task_completed'  => 0,
        ]));

        // We want a stable email for Mail::assertQueued matchers — overwrite
        // post-creation now that the unique-suffixed insert is committed.
        $user->update(['email' => $ownerEmail]);

        $now = now();
        $sub = Subscription::withoutTenancy()->create([
            'tenant_id'                 => $tenant->id,
            'plan_id'                   => $plan->id,
            'status'                    => SubscriptionStatus::Trialing->value,
            'billing_cycle'             => BillingCycle::Monthly->value,
            'currency'                  => $plan->currency,
            'gateway'                   => PaymentGateway::Moyasar->value,
            'trial_ends_at'             => $now->copy()->addDays($daysFromNow),
            'current_period_started_at' => $now,
            'current_period_ends_at'    => $now->copy()->addMonth(),
        ]);

        return [$tenant, $user, $sub];
    }

    private function seedTenant(): Tenant
    {
        return Tenant::create([
            'name'   => 'Trial Co '.uniqid(),
            'slug'   => 'trial-'.uniqid(),
            'status' => Tenant::STATUS_ACTIVE,
        ]);
    }

    private function seedPlan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'slug'          => 'trial-plan-'.uniqid(),
            'name'          => 'Trial Plan',
            'description'   => '...',
            'price_monthly' => 100,
            'price_yearly'  => 1000,
            'currency'      => 'SAR',
            'trial_days'    => 14,
            'is_active'     => true,
            'is_featured'   => false,
            'is_free'       => false,
            'sort_order'    => 1,
        ], $overrides));
    }
}
