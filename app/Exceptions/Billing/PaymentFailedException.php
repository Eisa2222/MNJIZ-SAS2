<?php

declare(strict_types=1);

namespace App\Exceptions\Billing;

use RuntimeException;

class PaymentFailedException extends RuntimeException
{
    public function __construct(
        public readonly string $gatewayPaymentId,
        public readonly string $reason,
        public readonly array $raw = [],
    ) {
        parent::__construct("Gateway charge failed [{$gatewayPaymentId}]: {$reason}");
    }
}
