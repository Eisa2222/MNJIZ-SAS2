<?php

declare(strict_types=1);

namespace App\DTOs\Billing;

use App\Enums\Billing\RefundStatus;

final class RefundResult
{
    public function __construct(
        public readonly RefundStatus $status,
        public readonly string $gatewayRefundId,
        public readonly float $amount,
        public readonly ?string $failureReason = null,
        public readonly array $raw = [],
    ) {}
}
