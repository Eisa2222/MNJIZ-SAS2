<?php

namespace App\Services\Qoyod\Resources\Purchases\PurchaseOrders;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\PurchaseOrdersResourceInterface;

class CachedPurchaseOrders extends CacheResourceDecorator implements PurchaseOrdersResourceInterface
{
    public function __construct(PurchaseOrders $real)
    {
        parent::__construct($real, 'purchase_orders');
    }
}
