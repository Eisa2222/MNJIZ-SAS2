<?php

namespace App\Services\Qoyod\Resources\Products\Units;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\ProductUnitResourceInterface;

class CachedUnits extends CacheResourceDecorator implements ProductUnitResourceInterface
{
    public function __construct(Units $real)
    {
        parent::__construct($real, 'units');
    }
}
