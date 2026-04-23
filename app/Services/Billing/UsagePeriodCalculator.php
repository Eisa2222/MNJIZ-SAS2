<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Enums\Billing\UsageResetPeriod;
use Carbon\CarbonImmutable;

final class UsagePeriodCalculator
{
    public function nextResetAt(UsageResetPeriod $period, ?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $now ??= CarbonImmutable::now();

        return $period->nextResetFrom($now);
    }

    public function periodStart(UsageResetPeriod $period, ?CarbonImmutable $now = null): CarbonImmutable
    {
        $now ??= CarbonImmutable::now();

        return match ($period) {
            UsageResetPeriod::Daily   => $now->startOfDay(),
            UsageResetPeriod::Weekly  => $now->startOfWeek(),
            UsageResetPeriod::Monthly => $now->startOfMonth(),
            UsageResetPeriod::Yearly  => $now->startOfYear(),
            UsageResetPeriod::Never   => $now,
        };
    }
}
