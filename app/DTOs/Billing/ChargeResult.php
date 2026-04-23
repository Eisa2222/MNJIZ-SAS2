<?php

declare(strict_types=1);

namespace App\DTOs\Billing;

use App\Enums\Billing\PaymentStatus;

/**
 * Gateway-agnostic reply shape. ChargeInvoiceAction uses this to decide
 * what Payment row state to persist.
 */
final class ChargeResult
{
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly string $gatewayPaymentId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly ?string $redirectUrl = null,    // 3DS
        public readonly ?string $cardLast4 = null,
        public readonly ?string $cardBrand = null,
        public readonly ?string $sourceType = null,
        public readonly ?string $failureReason = null,
        public readonly array $raw = [],                 // original gateway payload
    ) {}

    public function requires3DS(): bool
    {
        return $this->status === PaymentStatus::Pending && $this->redirectUrl !== null;
    }

    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }
}
