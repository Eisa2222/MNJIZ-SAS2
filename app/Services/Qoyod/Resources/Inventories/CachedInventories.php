<?php

namespace App\Services\Qoyod\Resources\Inventories;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;

class CachedInventories extends CacheResourceDecorator implements InventoryResourceInterface
{
    public function __construct(Inventories $real)
    {
        parent::__construct($real, 'inventories');
    }
}
