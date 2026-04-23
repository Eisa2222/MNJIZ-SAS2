<?php

namespace App\Services\Qoyod\Resources\Invoices;

use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Invoices extends AbstractResource implements InvoiceResourceInterface
{
    protected static function endpoint(): string
    {
        return '/invoices';
    }

    protected static function wrapper(): string
    {
        return 'invoice';
    }
}
