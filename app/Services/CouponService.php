<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

/**
 * Phase E — spec-shaped coupon validation + redemption service.
 *
 * Contract differences vs Phase 5's `ApplyCouponAction`:
 *
 *   • `validate()` ALWAYS returns an array — never throws on a bad
 *     coupon. The UI (checkout AJAX) consumes the array directly.
 *   • `apply()` writes BOTH the per-redemption `coupon_uses` audit row
 *     AND atomically increments `coupons.redemptions_count` (the Phase 5
 *     counter that ApplyCouponAction also touches). Single transaction —
 *     the two stores can never drift.
 *
 * Both Phase E checkout AND Phase 5 BillingController coupon flows can
 * coexist: one goes through ApplyCouponAction (subscription-attached
 * coupon), the other through CouponService (one-time checkout discount).
 * The atomic counter remains the single source of truth for the
 * "remaining uses" gate, so neither can over-redeem.
 */
final class CouponService
{
    /**
     * Validate a coupon code against a plan + billing cycle + amount.
     *
     * Always returns an array of the shape:
     *
     *   [
     *     'valid'        => bool,
     *     'coupon'       => Coupon|null,
     *     'discount'     => float,
     *     'final_amount' => float,
     *     'message'      => string,   // user-facing localised message
     *   ]
     *
     * The caller (CheckoutController::applyCoupon) JSON-encodes this
     * verbatim, so localised strings live here, not in the controller.
     */
    public function validate(string $code, int $planId, string $billingCycle, float $amount): array
    {
        $code = trim($code);

        if ($code === '') {
            return $this->failure(0.0, $amount, __('checkout.coupon.code_required'));
        }

        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon) {
            return $this->failure(0.0, $amount, __('checkout.coupon.not_found'));
        }

        if (! $coupon->is_active) {
            return $this->failure(0.0, $amount, __('checkout.coupon.inactive'));
        }

        if ($coupon->isExpired()) {
            return $this->failure(0.0, $amount, __('checkout.coupon.expired'));
        }

        if ($coupon->hasReachedMaxUses()) {
            return $this->failure(0.0, $amount, __('checkout.coupon.exhausted'));
        }

        if (! $coupon->isApplicableToPlan($planId)) {
            return $this->failure(0.0, $amount, __('checkout.coupon.plan_mismatch'));
        }

        if (! $coupon->isApplicableToBillingCycle($billingCycle)) {
            return $this->failure(0.0, $amount, __('checkout.coupon.cycle_mismatch', [
                'cycle' => $billingCycle,
            ]));
        }

        if ($coupon->min_amount !== null && $amount < (float) $coupon->min_amount) {
            return $this->failure(0.0, $amount, __('checkout.coupon.min_amount', [
                'amount'   => number_format((float) $coupon->min_amount, 2),
                'currency' => (string) ($coupon->currency ?? 'SAR'),
            ]));
        }

        $discount = $coupon->calculateDiscount($amount);
        $final    = max(0.0, round($amount - $discount, 2));

        return [
            'valid'        => true,
            'coupon'       => $coupon,
            'discount'     => $discount,
            'final_amount' => $final,
            'message'      => __('checkout.coupon.applied', [
                'discount' => number_format($discount, 2),
                'currency' => (string) ($coupon->currency ?? 'SAR'),
            ]),
        ];
    }

    /**
     * Persist a redemption: writes a `coupon_uses` row AND atomically
     * increments `coupons.redemptions_count` in the same transaction.
     *
     * The atomic SQL increment is essential — `$coupon->redemptions_count++`
     * would race under concurrent checkouts and let a "max 1 use" coupon
     * be claimed twice. We use `DB::table()->increment()` which compiles
     * to a single `UPDATE coupons SET redemptions_count = redemptions_count + 1 WHERE id = ?`.
     */
    public function apply(
        Coupon $coupon,
        int|string|null $tenantId,
        ?int $subscriptionId,
        float $discountAmount,
    ): CouponUse {
        return DB::transaction(function () use ($coupon, $tenantId, $subscriptionId, $discountAmount) {
            // 1. Atomic counter (Phase 5 source of truth).
            DB::table('coupons')
                ->where('id', $coupon->id)
                ->increment('redemptions_count');

            // 2. Per-redemption audit trail (Phase E addition).
            return CouponUse::create([
                'coupon_id'       => $coupon->id,
                'tenant_id'       => $tenantId !== null ? (int) $tenantId : null,
                'subscription_id' => $subscriptionId,
                'discount_amount' => $discountAmount,
                'currency'        => (string) ($coupon->currency ?? 'SAR'),
                'used_at'         => now(),
            ]);
        });
    }

    /**
     * Helper: build the failure-shaped array. Keeps `validate()` readable
     * and ensures every failure path has the same key set.
     */
    private function failure(float $discount, float $finalAmount, string $message): array
    {
        return [
            'valid'        => false,
            'coupon'       => null,
            'discount'     => $discount,
            'final_amount' => $finalAmount,
            'message'      => $message,
        ];
    }
}
