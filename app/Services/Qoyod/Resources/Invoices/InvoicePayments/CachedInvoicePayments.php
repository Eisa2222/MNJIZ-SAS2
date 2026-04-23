<?php

namespace App\Services\Qoyod\Resources\Invoices\InvoicePayments;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\InvoicePaymentResourceInterface;

class CachedInvoicePayments extends CacheResourceDecorator implements InvoicePaymentResourceInterface
{
    public function __construct(InvoicePayments $real)
    {
        parent::__construct($real, 'invoice_payments');
    }
}
