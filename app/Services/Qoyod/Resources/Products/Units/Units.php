<?php

namespace App\Services\Qoyod\Resources\Products\Units;

use App\Services\Qoyod\Contracts\Resources\ProductUnitResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Units extends AbstractResource implements ProductUnitResourceInterface
{
    protected static function endpoint(): string
    {
        return '/product_unit_types';
    }

    protected static function wrapper(): string
    {
        return 'product_unit_type';
    }
}
