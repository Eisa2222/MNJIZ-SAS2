<?php

declare(strict_types=1);

namespace App\Enums\Billing;

use Carbon\CarbonImmutable;

/**
 * How often a metered feature's counter rolls back to zero.
 */
enum UsageResetPeriod: string
{
    case Never   = 'never';
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';
    case Yearly  = 'yearly';

    /**
     * Returns the timestamp at which the CURRENT period ends (and the counter
     * should reset). Caller should persist this to tenant_usages.reset_at.
     */
    public function nextResetFrom(CarbonImmutable $now): ?CarbonImmutable
    {
        return match ($this) {
            self::Never   => null,
            self::Daily   => $now->endOfDay()->addMicrosecond(),     // start of next day
            self::Weekly  => $now->endOfWeek()->addMicrosecond(),
            self::Monthly => $now->endOfMonth()->addMicrosecond(),
            self::Yearly  => $now->endOfYear()->addMicrosecond(),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Never   => 'Never (lifetime)',
            self::Daily   => 'Daily',
            self::Weekly  => 'Weekly',
            self::Monthly => 'Monthly',
            self::Yearly  => 'Yearly',
        };
    }
}
