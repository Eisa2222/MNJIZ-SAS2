<?php

declare(strict_types=1);

namespace App\Events\Billing;

use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentRefunded
{
    use Dispatchable;

    public function __construct(
        public readonly Payment $payment,
        public readonly Refund $refund,
    ) {}
}
