<?php

declare(strict_types=1);

namespace App\Events\Billing;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentFailed
{
    use Dispatchable;

    public function __construct(
        public readonly Payment $payment,
        public readonly string $reason,
    ) {}
}
