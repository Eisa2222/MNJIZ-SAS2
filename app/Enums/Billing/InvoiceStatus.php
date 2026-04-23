<?php

declare(strict_types=1);

namespace App\Enums\Billing;

enum InvoiceStatus: string
{
    case Draft          = 'draft';           // not yet finalized
    case Open           = 'open';            // awaiting payment
    case Paid           = 'paid';            // fully settled
    case Uncollectible  = 'uncollectible';   // write-off by admin
    case Void           = 'void';            // canceled before payment
    case RefundedPartial = 'refunded_partial';
    case RefundedFull   = 'refunded_full';

    public function label(): string
    {
        return match ($this) {
            self::Draft            => 'Draft',
            self::Open             => 'Open',
            self::Paid             => 'Paid',
            self::Uncollectible    => 'Uncollectible',
            self::Void             => 'Void',
            self::RefundedPartial  => 'Partially Refunded',
            self::RefundedFull     => 'Fully Refunded',
        };
    }

    public function isSettled(): bool
    {
        return in_array($this, [self::Paid, self::Void, self::Uncollectible, self::RefundedFull], true);
    }
}
