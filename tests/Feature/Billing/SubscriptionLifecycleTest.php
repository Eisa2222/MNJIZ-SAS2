<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Billing\Subscription\ActivateSubscriptionAction;
use App\Actions\Billing\Subscription\CancelSubscriptionAction;
use App\Actions\Billing\Subscription\ChangePlanAction;
use App\Actions\Billing\Subscription\ExpireSubscriptionAction;
use App\Actions\Billing\Subscription\MarkPastDueAction;
use App\Actions\Billing\Subscription\ResumeSubscriptionAction;
use App\Actions\Billing\Subscription\StartTrialAction;
use App\Enums\Billing\SubscriptionStatus;
use App\Exceptions\Billing\SubscriptionStateException;
use App\Models\Plan;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_start_trial_creates_trialing_subscription_when_plan_has_trial_days(): void
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', 'starter')->firstOrFail(); // trial_days=14

        $sub = app(StartTrialAction::class)->execute($tenant, $plan);

        $this->assertSame(SubscriptionStatus::Trialing, $sub->status);
        $this->assertNotNull($sub->trial_ends_at);
        $this->assertTrue($sub->trial_ends_at->isFuture());
    }

    public function test_start_trial_creates_active_subscription_when_plan_has_no_trial(): void
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', 'free')->firstOrFail(); // trial_days=0

        $sub = app(StartTrialAction::class)->execute($tenant, $plan);

        $this->assertSame(SubscriptionStatus::Active, $sub->status);
    }

    public function test_cannot_start_second_subscription_while_one_is_active(): void
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', 'starter')->firstOrFail();

        app(StartTrialAction::class)->execute($tenant, $plan);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/already has an active subscription/i');

        app(StartTrialAction::class)->execute($tenant, $plan);
    }

    public function test_activate_transitions_trialing_to_active(): void
    {
        [$sub] = $this->subOn('starter');

        app(ActivateSubscriptionAction::class)->execute($sub);

        $this->assertSame(SubscriptionStatus::Active, $sub->fresh()->status);
    }

    public function test_cancel_default_mode_leaves_sub_entitling_until_period_end(): void
    {
        [$sub] = $this->subOn('starter');

        $canceled = app(CancelSubscriptionAction::class)->execute($sub);

        $this->assertSame(SubscriptionStatus::Canceled, $canceled->status);
        $this->assertNotNull($canceled->canceled_at);
        $this->assertTrue($canceled->ends_at->isFuture()); // period still running
        $this->assertTrue($canceled->status->isEntitling());
    }

    public function test_cancel_immediate_sets_ends_at_to_now(): void
    {
        [$sub] = $this->subOn('starter');

        $canceled = app(CancelSubscriptionAction::class)->execute($sub, immediate: true);

        $this->assertSame(SubscriptionStatus::Canceled, $canceled->status);
        $this->assertLessThanOrEqual(now()->addSecond(), $canceled->ends_at);
    }

    public function test_resume_restores_canceled_subscription(): void
    {
        [$sub] = $this->subOn('starter');
        app(CancelSubscriptionAction::class)->execute($sub);

        $resumed = app(ResumeSubscriptionAction::class)->execute($sub->fresh());

        $this->assertSame(SubscriptionStatus::Active, $resumed->status);
        $this->assertNull($resumed->canceled_at);
        $this->assertNull($resumed->ends_at);
    }

    public function test_resume_rejects_expired_subscription(): void
    {
        [$sub] = $this->subOn('starter');
        app(ExpireSubscriptionAction::class)->execute($sub);

        $this->expectException(SubscriptionStateException::class);

        app(ResumeSubscriptionAction::class)->execute($sub->fresh());
    }

    public function test_mark_past_due_starts_grace_window(): void
    {
        [$sub] = $this->subOn('starter');
        app(ActivateSubscriptionAction::class)->execute($sub);

        $pd = app(MarkPastDueAction::class)->execute($sub->fresh(), graceDays: 5);

        $this->assertSame(SubscriptionStatus::PastDue, $pd->status);
        $this->assertNotNull($pd->grace_ends_at);
        $this->assertTrue($pd->inGracePeriod());
    }

    public function test_expire_transitions_to_terminal_and_reverts_tenant_to_free_plan(): void
    {
        [$sub, $tenant] = $this->subOn('professional');
        app(ActivateSubscriptionAction::class)->execute($sub);

        // Simulate grace window ended.
        $sub->grace_ends_at = now()->subDay();
        $sub->save();

        app(ExpireSubscriptionAction::class)->execute($sub->fresh());

        $this->assertSame(SubscriptionStatus::Expired, $sub->fresh()->status);
        $this->assertSame('free', $tenant->fresh()->plan->slug);
    }

    public function test_change_plan_upgrades_subscription_and_tenant_plan(): void
    {
        [$sub, $tenant] = $this->subOn('starter');
        $proPlan = Plan::where('slug', 'professional')->firstOrFail();

        $updated = app(ChangePlanAction::class)->execute($sub, $proPlan);

        $this->assertSame($proPlan->id, $updated->plan_id);
        $this->assertSame('professional', $tenant->fresh()->plan->slug);
    }

    /** @return array{0: \App\Models\Subscription, 1: Tenant} */
    private function subOn(string $planSlug): array
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', $planSlug)->firstOrFail();

        $sub = TenantContext::runAs($tenant, fn () => app(StartTrialAction::class)->execute($tenant, $plan));

        return [$sub, $tenant->fresh()];
    }
}
