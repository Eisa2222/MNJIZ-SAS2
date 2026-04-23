<?php

namespace App\Services\Qoyod\Resources\Invoices\InvoicePayments;

use App\Services\Qoyod\Contracts\Resources\InvoicePaymentResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class InvoicePayments extends AbstractResource implements InvoicePaymentResourceInterface
{
    protected static function endpoint(): string
    {
        return '/invoice_payments';
    }

    protected static function wrapper(): string
    {
        return 'invoice_payment';
    }
}
