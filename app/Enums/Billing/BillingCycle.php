<?php

declare(strict_types=1);

namespace App\Enums\Billing;

use Carbon\CarbonImmutable;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly  = 'yearly';

    public function addToNow(CarbonImmutable $start): CarbonImmutable
    {
        return match ($this) {
            self::Monthly => $start->addMonth(),
            self::Yearly  => $start->addYear(),
        };
    }

    public function priceOn(\App\Models\Plan $plan): float
    {
        return match ($this) {
            self::Monthly => (float) $plan->price_monthly,
            self::Yearly  => (float) $plan->price_yearly,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Yearly  => 'Yearly',
        };
    }
}
