<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Actions\Billing\ApplyCouponAction;
use App\Actions\Billing\Invoice\CreateInvoiceAction;
use App\Actions\Billing\Subscription\StartTrialAction;
use App\Enums\Billing\CouponDuration;
use App\Enums\Billing\CouponType;
use App\Exceptions\Billing\CouponRedemptionException;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Database\Seeders\DefaultPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CouponRedemptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultPlansSeeder::class);
    }

    public function test_percentage_coupon_discounts_invoice(): void
    {
        [$sub, $tenant] = $this->tenantWithSub('starter');

        $coupon = Coupon::create([
            'code'       => 'HALFOFF',
            'name'       => '50% off',
            'type'       => CouponType::Percentage,
            'value'      => 50,
            'duration'   => CouponDuration::Once,
            'applies_to' => 'any',
            'is_active'  => true,
        ]);

        app(ApplyCouponAction::class)->execute($sub, $coupon);

        $invoice = TenantContext::runAs(
            $tenant,
            fn () => app(CreateInvoiceAction::class)->execute($sub->fresh(), $coupon)
        );

        $this->assertEqualsWithDelta(299.00, (float) $invoice->subtotal, 0.01);
        $this->assertEqualsWithDelta(149.50, (float) $invoice->discount_amount, 0.01);
        // Total = (299 - 149.50) = 149.50 (no tax seeded)
        $this->assertEqualsWithDelta(149.50, (float) $invoice->total, 0.01);
    }

    public function test_fixed_amount_coupon_capped_at_subtotal(): void
    {
        [$sub, $tenant] = $this->tenantWithSub('starter'); // 299 SAR

        $coupon = Coupon::create([
            'code'       => 'SAVE500',
            'name'       => 'SAR 500 off',
            'type'       => CouponType::Fixed,
            'value'      => 500,           // bigger than subtotal
            'currency'   => 'SAR',
            'duration'   => CouponDuration::Once,
            'applies_to' => 'any',
            'is_active'  => true,
        ]);

        $invoice = TenantContext::runAs(
            $tenant,
            fn () => app(CreateInvoiceAction::class)->execute($sub, $coupon)
        );

        // Discount capped at subtotal (299) — no negative totals.
        $this->assertEqualsWithDelta(299.00, (float) $invoice->discount_amount, 0.01);
        $this->assertEqualsWithDelta(0.00,  (float) $invoice->total, 0.01);
    }

    public function test_inactive_coupon_throws(): void
    {
        [$sub] = $this->tenantWithSub('starter');

        $coupon = Coupon::create([
            'code'       => 'DEAD',
            'name'       => 'dead',
            'type'       => CouponType::Percentage,
            'value'      => 10,
            'duration'   => CouponDuration::Once,
            'applies_to' => 'any',
            'is_active'  => false,
        ]);

        $this->expectException(CouponRedemptionException::class);

        app(ApplyCouponAction::class)->execute($sub, $coupon);
    }

    public function test_expired_coupon_throws(): void
    {
        [$sub] = $this->tenantWithSub('starter');

        $coupon = Coupon::create([
            'code'       => 'PASTDUE',
            'name'       => 'expired',
            'type'       => CouponType::Percentage,
            'value'      => 10,
            'duration'   => CouponDuration::Once,
            'applies_to' => 'any',
            'is_active'  => true,
            'redeem_by'  => now()->subDay(),
        ]);

        $this->expectException(CouponRedemptionException::class);

        app(ApplyCouponAction::class)->execute($sub, $coupon);
    }

    public function test_exhausted_coupon_throws(): void
    {
        [$sub] = $this->tenantWithSub('starter');

        $coupon = Coupon::create([
            'code'              => 'LIMITED',
            'name'              => 'limited',
            'type'              => CouponType::Percentage,
            'value'             => 10,
            'duration'          => CouponDuration::Once,
            'applies_to'        => 'any',
            'is_active'         => true,
            'max_redemptions'   => 1,
            'redemptions_count' => 1,
        ]);

        $this->expectException(CouponRedemptionException::class);

        app(ApplyCouponAction::class)->execute($sub, $coupon);
    }

    public function test_missing_coupon_throws(): void
    {
        [$sub] = $this->tenantWithSub('starter');

        $this->expectException(CouponRedemptionException::class);

        app(ApplyCouponAction::class)->execute($sub, 'NON_EXISTENT_CODE');
    }

    /** @return array{0: \App\Models\Subscription, 1: Tenant} */
    private function tenantWithSub(string $planSlug): array
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-'.uniqid(), 'status' => 'active']);
        $plan   = Plan::where('slug', $planSlug)->firstOrFail();
        $sub    = TenantContext::runAs($tenant, fn () => app(StartTrialAction::class)->execute($tenant, $plan));

        return [$sub, $tenant->fresh()];
    }
}
