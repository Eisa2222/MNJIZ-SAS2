<?php

namespace App\Services\Qoyod\Resources\Invoices;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;

class CachedInvoices extends CacheResourceDecorator implements InvoiceResourceInterface
{
    public function __construct(Invoices $real)
    {
        parent::__construct($real, 'invoices');
    }
}
