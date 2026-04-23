<?php

declare(strict_types=1);

namespace App\Enums\Billing;

enum PaymentStatus: string
{
    case Pending             = 'pending';      // dispatched, awaiting gateway reply / 3DS
    case Authorized          = 'authorized';   // auth-only (capture later)
    case Captured            = 'captured';     // settled — money moved
    case Failed              = 'failed';       // declined / timed out
    case Refunded            = 'refunded';
    case PartiallyRefunded   = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending              => 'Pending',
            self::Authorized           => 'Authorized',
            self::Captured             => 'Captured',
            self::Failed               => 'Failed',
            self::Refunded             => 'Refunded',
            self::PartiallyRefunded    => 'Partially Refunded',
        };
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [self::Captured, self::Authorized], true);
    }
}
