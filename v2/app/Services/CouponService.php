<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUse;
use Illuminate\Support\Facades\DB;

/**
 * V2 — CouponService.
 *
 * Spec lines 419-426:
 *   validate(): returns array (NEVER throws — UI consumes the message)
 *   apply(): persists CouponUse + atomically increments uses_count
 *
 * Spec line 548: ALWAYS use DB::increment, never $coupon->uses_count++
 * (race-condition free under concurrent checkouts).
 */
final class CouponService
{
    /**
     * @return array{valid:bool, coupon:?Coupon, discount:float, final_amount:float, message:string}
     */
    public function validate(string $code, int $planId, string $billingCycle, float $amount): array
    {
        $code = trim($code);
        if ($code === '') {
            return $this->fail($amount, 'الرجاء إدخال كود الكوبون.');
        }

        $coupon = Coupon::query()->where('code', $code)->first();
        if (! $coupon) {
            return $this->fail($amount, 'هذا الكود غير موجود.');
        }

        if (! $coupon->is_active) {
            return $this->fail($amount, 'هذا الكوبون غير مفعّل.');
        }

        if ($coupon->isExpired()) {
            return $this->fail($amount, 'انتهت صلاحية هذا الكوبون.');
        }

        if ($coupon->hasReachedMaxUses()) {
            return $this->fail($amount, 'تجاوز هذا الكوبون الحد الأقصى للاستخدامات.');
        }

        if (! $coupon->isApplicableToPlan($planId)) {
            return $this->fail($amount, 'هذا الكوبون لا ينطبق على الباقة المختارة.');
        }

        if (! $coupon->isApplicableToBillingCycle($billingCycle)) {
            $cycleAr = $billingCycle === 'yearly' ? 'السنوية' : 'الشهرية';
            return $this->fail($amount, "هذا الكوبون لا ينطبق على الدورة {$cycleAr}.");
        }

        if ($coupon->min_order_amount !== null && $amount < (float) $coupon->min_order_amount) {
            return $this->fail($amount,
                'الحد الأدنى للطلب هو '.number_format((float) $coupon->min_order_amount, 2).' ريال.');
        }

        $discount = $coupon->calculateDiscount($amount);
        $final    = max(0.0, round($amount - $discount, 2));

        return [
            'valid'        => true,
            'coupon'       => $coupon,
            'discount'     => $discount,
            'final_amount' => $final,
            'message'      => "تم تطبيق الخصم بنجاح! وفّرت ".number_format($discount, 2).' ريال',
        ];
    }

    public function apply(Coupon $coupon, string $tenantId, ?int $subscriptionId, float $discountAmount): CouponUse
    {
        return DB::transaction(function () use ($coupon, $tenantId, $subscriptionId, $discountAmount) {
            // Atomic counter — race-safe under concurrent checkouts.
            DB::table('coupons')->where('id', $coupon->id)->increment('uses_count');

            return CouponUse::create([
                'coupon_id'       => $coupon->id,
                'tenant_id'       => $tenantId,
                'subscription_id' => $subscriptionId,
                'discount_amount' => $discountAmount,
                'used_at'         => now(),
            ]);
        });
    }

    private function fail(float $amount, string $msg): array
    {
        return [
            'valid'        => false,
            'coupon'       => null,
            'discount'     => 0.0,
            'final_amount' => $amount,
            'message'      => $msg,
        ];
    }
}
