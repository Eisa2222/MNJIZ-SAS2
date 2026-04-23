<?php

declare(strict_types=1);

namespace App\Enums\Billing;

enum CouponDuration: string
{
    case Once      = 'once';       // discount applies to ONE invoice only
    case Repeating = 'repeating';  // applies for N months (duration_in_months)
    case Forever   = 'forever';    // applies to every invoice until coupon removed

    public function label(): string
    {
        return match ($this) {
            self::Once      => 'Once',
            self::Repeating => 'Repeating',
            self::Forever   => 'Forever',
        };
    }
}
