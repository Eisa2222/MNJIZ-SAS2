<?php

namespace App\Services\Qoyod\Resources\Purchases\BillPayments;

use App\Services\Qoyod\Contracts\Resources\BillPaymentResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class BillPayments extends AbstractResource implements BillPaymentResourceInterface
{
    protected static function endpoint(): string
    {
        return '/bill_payments';
    }

    protected static function wrapper(): string
    {
        return 'bill_payment';
    }
}
