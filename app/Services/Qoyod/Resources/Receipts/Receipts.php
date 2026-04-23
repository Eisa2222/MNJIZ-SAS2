<?php

namespace App\Services\Qoyod\Resources\Receipts;

use App\Services\Qoyod\Contracts\Resources\ReceiptResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Receipts extends AbstractResource implements ReceiptResourceInterface
{
    protected static function endpoint(): string
    {
        return '/receipts';
    }

    protected static function wrapper(): string
    {
        return 'receipt';
    }
}
