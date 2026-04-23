<?php

declare(strict_types=1);

namespace App\Enums\Billing;

enum CouponType: string
{
    case Percentage = 'percentage';   // value 0–100
    case Fixed      = 'fixed';        // value in coupon.currency

    public function label(): string
    {
        return $this === self::Percentage ? 'Percentage' : 'Fixed Amount';
    }

    public function applyTo(float $subtotal, float $value, string $currency = 'SAR'): float
    {
        $discount = match ($this) {
            self::Percentage => round($subtotal * ($value / 100), 2),
            self::Fixed      => min($subtotal, $value),
        };

        return max(0, $discount);
    }
}
