<?php

declare(strict_types=1);

namespace App\Exceptions\Billing;

use RuntimeException;

class CouponRedemptionException extends RuntimeException
{
    public const REASON_INACTIVE      = 'coupon_inactive';
    public const REASON_EXPIRED       = 'coupon_expired';
    public const REASON_EXHAUSTED     = 'coupon_exhausted';
    public const REASON_MIN_NOT_MET   = 'coupon_min_not_met';
    public const REASON_PLAN_MISMATCH = 'coupon_plan_mismatch';
    public const REASON_NOT_FOUND     = 'coupon_not_found';

    public function __construct(
        public readonly string $couponCode,
        public readonly string $reason,
        ?string $message = null,
    ) {
        parent::__construct($message ?? "Coupon '{$couponCode}' cannot be redeemed: {$reason}");
    }
}
