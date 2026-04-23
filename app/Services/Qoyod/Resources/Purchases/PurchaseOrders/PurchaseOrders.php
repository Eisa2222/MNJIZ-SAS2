<?php

namespace App\Services\Qoyod\Resources\Purchases\PurchaseOrders;

use App\Services\Qoyod\Contracts\Resources\PurchaseOrdersResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class PurchaseOrders extends AbstractResource implements PurchaseOrdersResourceInterface
{
    protected static function endpoint(): string
    {
        return '/orders';
    }

    protected static function wrapper(): string
    {
        return 'order';
    }
}
