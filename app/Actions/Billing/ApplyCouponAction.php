<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Exceptions\Billing\CouponRedemptionException;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

/**
 * Attaches a coupon to a subscription (so the next invoice applies the
 * discount), after validating eligibility.
 *
 * Throws CouponRedemptionException with a machine-readable reason so the UI
 * can show a specific message ("expired", "min not met", …).
 */
final class ApplyCouponAction
{
    public function execute(Subscription $subscription, Coupon|string $couponOrCode): Subscription
    {
        $coupon = $couponOrCode instanceof Coupon
            ? $couponOrCode
            : Coupon::query()->where('code', $couponOrCode)->with('plans')->first();

        if (! $coupon) {
            throw new CouponRedemptionException(
                couponCode: is_string($couponOrCode) ? $couponOrCode : '',
                reason:     CouponRedemptionException::REASON_NOT_FOUND,
            );
        }

        $this->validate($coupon, $subscription->plan);

        return DB::transaction(function () use ($subscription, $coupon) {
            $subscription->coupon_id = $coupon->id;
            $subscription->save();

            // Increment redemption counter with atomic SQL to avoid race.
            Coupon::query()->where('id', $coupon->id)->increment('redemptions_count');

            activity('billing')
                ->performedOn($subscription)
                ->withProperties([
                    'tenant_id'   => $subscription->tenant_id,
                    'coupon_code' => $coupon->code,
                ])
                ->event('coupon.applied')
                ->log("Coupon '{$coupon->code}' applied to subscription #{$subscription->id}.");

            return $subscription->fresh();
        });
    }

    private function validate(Coupon $coupon, ?Plan $plan): void
    {
        if (! $coupon->is_active) {
            throw new CouponRedemptionException($coupon->code, CouponRedemptionException::REASON_INACTIVE);
        }

        if ($coupon->redeem_by && $coupon->redeem_by->isPast()) {
            throw new CouponRedemptionException($coupon->code, CouponRedemptionException::REASON_EXPIRED);
        }

        if ($coupon->max_redemptions !== null && $coupon->redemptions_count >= $coupon->max_redemptions) {
            throw new CouponRedemptionException($coupon->code, CouponRedemptionException::REASON_EXHAUSTED);
        }

        if ($coupon->applies_to === 'specific_plans' && $plan && ! $coupon->plans->contains($plan->id)) {
            throw new CouponRedemptionException($coupon->code, CouponRedemptionException::REASON_PLAN_MISMATCH);
        }
    }
}
