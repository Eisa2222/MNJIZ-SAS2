<?php

declare(strict_types=1);

namespace App\DTOs\Billing;

use App\Models\Payment;

final class RefundRequest
{
    public function __construct(
        public readonly Payment $payment,
        public readonly float $amount,
        public readonly ?string $reason = null,
        public readonly array $metadata = [],
    ) {}
}
