<?php

declare(strict_types=1);

namespace Tests\Feature\Coupons;

use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\Plan;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase E — CouponService spec-compliance suite.
 *
 *   1. invalid coupon returns array, no exception
 *   2. expired coupon message
 *   3. max uses reached message
 *   4. plan restriction
 *   5. billing cycle restriction (optional via meta.billing_cycles)
 *   6. min order restriction
 *   7. percentage discount calculation
 *   8. fixed discount calculation
 *   9. remaining uses
 *  10. apply creates coupon_use
 *  11. apply increments redemptions_count atomically
 */
final class CouponServiceComplianceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CouponService::class);
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_invalid_coupon_returns_array_no_exception(): void
    {
        $plan = $this->makePlan();

        $result = $this->service->validate('NOPE', $plan->id, 'monthly', 100);

        $this->assertIsArray($result);
        $this->assertFalse($result['valid']);
        $this->assertNull($result['coupon']);
        $this->assertSame(0.0, $result['discount']);
        $this->assertSame(100.0, $result['final_amount']);
        $this->assertSame(__('checkout.coupon.not_found'), $result['message']);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_expired_coupon_message(): void
    {
        $plan   = $this->makePlan();
        $coupon = $this->makeCoupon(['code' => 'PAST', 'redeem_by' => now()->subDay()]);

        $result = $this->service->validate('PAST', $plan->id, 'monthly', 100);

        $this->assertFalse($result['valid']);
        $this->assertSame(__('checkout.coupon.expired'), $result['message']);
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_max_uses_reached_message(): void
    {
        $plan = $this->makePlan();
        $this->makeCoupon([
            'code'              => 'FULL',
            'max_redemptions'   => 1,
            'redemptions_count' => 1,
        ]);

        $result = $this->service->validate('FULL', $plan->id, 'monthly', 100);

        $this->assertFalse($result['valid']);
        $this->assertSame(__('checkout.coupon.exhausted'), $result['message']);
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_plan_restriction(): void
    {
        $planA = $this->makePlan(['slug' => 'plan-a']);
        $planB = $this->makePlan(['slug' => 'plan-b']);

        $coupon = $this->makeCoupon(['code' => 'AONLY', 'applies_to' => 'specific_plans']);
        $coupon->plans()->sync([$planA->id]);

        // Applies to A → ok
        $okA  = $this->service->validate('AONLY', $planA->id, 'monthly', 100);
        $this->assertTrue($okA['valid']);

        // B should be rejected
        $bad = $this->service->validate('AONLY', $planB->id, 'monthly', 100);
        $this->assertFalse($bad['valid']);
        $this->assertSame(__('checkout.coupon.plan_mismatch'), $bad['message']);
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_billing_cycle_restriction(): void
    {
        $plan = $this->makePlan();
        $this->makeCoupon([
            'code' => 'YEARLYONLY',
            'meta' => ['billing_cycles' => ['yearly']],
        ]);

        $monthly = $this->service->validate('YEARLYONLY', $plan->id, 'monthly', 100);
        $yearly  = $this->service->validate('YEARLYONLY', $plan->id, 'yearly', 100);

        $this->assertFalse($monthly['valid']);
        $this->assertTrue($yearly['valid']);
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_min_order_restriction(): void
    {
        $plan = $this->makePlan();
        $this->makeCoupon(['code' => 'BIG', 'min_amount' => 500]);

        $low  = $this->service->validate('BIG', $plan->id, 'monthly', 100);
        $high = $this->service->validate('BIG', $plan->id, 'monthly', 600);

        $this->assertFalse($low['valid']);
        $this->assertStringContainsString('500', $low['message']);
        $this->assertTrue($high['valid']);
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_percentage_discount_calculation(): void
    {
        $plan = $this->makePlan();
        $this->makeCoupon(['code' => 'TENPC', 'type' => 'percentage', 'value' => 10]);

        $r = $this->service->validate('TENPC', $plan->id, 'monthly', 200);

        $this->assertTrue($r['valid']);
        $this->assertSame(20.0, (float) $r['discount']);
        $this->assertSame(180.0, (float) $r['final_amount']);
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_fixed_discount_calculation(): void
    {
        $plan = $this->makePlan();
        $this->makeCoupon(['code' => 'FIFTY', 'type' => 'fixed', 'value' => 50]);

        $r = $this->service->validate('FIFTY', $plan->id, 'monthly', 200);

        $this->assertTrue($r['valid']);
        $this->assertSame(50.0, (float) $r['discount']);
        $this->assertSame(150.0, (float) $r['final_amount']);
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_remaining_uses_helper(): void
    {
        $unlimited = $this->makeCoupon(['code' => 'INF', 'max_redemptions' => null]);
        $capped    = $this->makeCoupon(['code' => 'CAP10', 'max_redemptions' => 10, 'redemptions_count' => 3]);

        $this->assertNull($unlimited->getRemainingUses());
        $this->assertSame(7, $capped->getRemainingUses());
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_apply_creates_coupon_use_row(): void
    {
        $coupon = $this->makeCoupon(['code' => 'AUDIT']);

        $this->assertSame(0, CouponUse::count());

        $use = $this->service->apply(
            coupon:         $coupon,
            tenantId:       null,
            subscriptionId: null,
            discountAmount: 25.50,
        );

        $this->assertInstanceOf(CouponUse::class, $use);
        $this->assertSame(1, CouponUse::count());
        $this->assertSame($coupon->id, $use->coupon_id);
        $this->assertSame('25.50', (string) $use->discount_amount);
    }

    // 11 ────────────────────────────────────────────────────────────────
    public function test_apply_increments_redemptions_count_atomically(): void
    {
        $coupon = $this->makeCoupon(['code' => 'ATOMIC']);
        $this->assertSame(0, $coupon->fresh()->redemptions_count);

        $this->service->apply($coupon, null, null, 10);
        $this->service->apply($coupon, null, null, 10);
        $this->service->apply($coupon, null, null, 10);

        $this->assertSame(3, $coupon->fresh()->redemptions_count);
        $this->assertSame(3, $coupon->fresh()->uses_count); // accessor proxy
        $this->assertSame(3, CouponUse::where('coupon_id', $coupon->id)->count());
    }

    // ────────────────────────────────────────────────────────── helpers

    private function makePlan(array $overrides = []): Plan
    {
        $base = [
            'slug'          => 'compl-plan-'.uniqid(),
            'name'          => 'Compliance Plan',
            'description'   => '...',
            'price_monthly' => 100,
            'price_yearly'  => 1000,
            'currency'      => 'SAR',
            'trial_days'    => 0,
            'is_active'     => true,
            'is_featured'   => false,
            'is_free'       => false,
            'sort_order'    => 1,
        ];

        return Plan::create(array_merge($base, $overrides));
    }

    private function makeCoupon(array $overrides = []): Coupon
    {
        $base = [
            'code'              => 'TEST'.strtoupper(uniqid()),
            'name'              => 'Test Coupon',
            'type'              => 'percentage',
            'value'             => 10,
            'currency'          => 'SAR',
            'duration'          => 'once',
            'applies_to'        => 'any',
            'is_active'         => true,
            'redemptions_count' => 0,
        ];

        return Coupon::create(array_merge($base, $overrides));
    }
}
