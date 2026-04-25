<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Actions\Billing\Subscription\StartTrialAction;
use App\Enums\Billing\CouponDuration;
use App\Enums\Billing\CouponType;
use App\Enums\Billing\SubscriptionStatus;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end HTTP coverage of the tenant billing portal — the exact flow a
 * signed-in firm staffer follows: view subscription, apply coupon, cancel,
 * resume. All requests go through /t/{tenant}/billing/* which exercises
 * tenant resolution + TenantScope + the new Blade view.
 */
final class TenantBillingPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_tenant_user_can_view_billing_portal(): void
    {
        [$tenant, $user, $sub] = $this->scaffold();

        $this->actingAs($user);

        $response = $this->get("/t/{$tenant->slug}/billing");

        $response->assertOk()
                 ->assertSee($sub->plan->name)
                 // Phase 9 renamed display label: starter slug now shows as "Basic".
                 ->assertSeeText('Basic');
    }

    public function test_tenant_user_can_cancel_subscription(): void
    {
        [$tenant, $user, $sub] = $this->scaffold();

        $this->actingAs($user);

        $response = $this->post("/t/{$tenant->slug}/billing/cancel");

        $response->assertRedirect();
        $this->assertSame(SubscriptionStatus::Canceled, $sub->fresh()->status);
    }

    public function test_tenant_user_can_resume_canceled_subscription(): void
    {
        [$tenant, $user, $sub] = $this->scaffold();
        $sub->update(['status' => SubscriptionStatus::Canceled, 'canceled_at' => now()]);

        $this->actingAs($user);

        $response = $this->post("/t/{$tenant->slug}/billing/resume");

        $response->assertRedirect();
        $this->assertSame(SubscriptionStatus::Active, $sub->fresh()->status);
    }

    public function test_tenant_user_can_apply_valid_coupon(): void
    {
        [$tenant, $user, $sub] = $this->scaffold();

        $coupon = Coupon::create([
            'code' => 'WELCOME10', 'name' => '10% off',
            'type' => CouponType::Percentage, 'value' => 10,
            'duration' => CouponDuration::Once, 'applies_to' => 'any',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post("/t/{$tenant->slug}/billing/coupon", [
            'code' => 'WELCOME10',
        ]);

        $response->assertRedirect();
        $this->assertSame($coupon->id, $sub->fresh()->coupon_id);
    }

    public function test_tenant_user_sees_error_for_invalid_coupon(): void
    {
        [$tenant, $user, $sub] = $this->scaffold();

        $this->actingAs($user);

        $response = $this->post("/t/{$tenant->slug}/billing/coupon", [
            'code' => 'NOSUCHCODE',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('code');
        $this->assertNull($sub->fresh()->coupon_id);
    }

    public function test_billing_portal_requires_authentication(): void
    {
        $tenant = Tenant::create(['name' => 'X', 'slug' => 'x-'.uniqid(), 'status' => 'active']);

        $response = $this->get("/t/{$tenant->slug}/billing");

        // Not authenticated → redirect to legacy tenant login.
        $response->assertRedirect();
    }

    public function test_suspended_tenant_returns_403_before_controller_runs(): void
    {
        [$tenant, $user] = $this->scaffold();
        $tenant->update(['status' => 'suspended']);

        $this->actingAs($user);

        $this->get("/t/{$tenant->slug}/billing")->assertStatus(403);
    }

    /** @return array{0: Tenant, 1: User, 2: Subscription} */
    private function scaffold(): array
    {
        $tenant = Tenant::create(['name' => 'Firm', 'slug' => 'firm-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', 'starter')->firstOrFail();

        return TenantContext::runAs($tenant, function () use ($tenant, $plan) {
            $user = User::factory()->create();
            $sub  = app(StartTrialAction::class)->execute($tenant, $plan);

            return [$tenant, $user, $sub];
        });
    }
}
