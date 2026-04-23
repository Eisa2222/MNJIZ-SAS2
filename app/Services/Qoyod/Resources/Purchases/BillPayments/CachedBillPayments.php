<?php

namespace App\Services\Qoyod\Resources\Purchases\BillPayments;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\BillPaymentResourceInterface;

class CachedBillPayments extends CacheResourceDecorator implements BillPaymentResourceInterface
{
    public function __construct(BillPayments $real)
    {
        parent::__construct($real, 'bill_payments');
    }
}
