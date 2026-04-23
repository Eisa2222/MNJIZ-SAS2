<?php

namespace App\Services\Qoyod\Resources\Receipts;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\ReceiptResourceInterface;

class CachedReceipts extends CacheResourceDecorator implements ReceiptResourceInterface
{
    public function __construct(Receipts $real)
    {
        parent::__construct($real, 'receipts');
    }
}
