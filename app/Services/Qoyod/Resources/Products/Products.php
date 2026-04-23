<?php

namespace App\Services\Qoyod\Resources\Products;

use App\Services\Qoyod\Contracts\Resources\ProductResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Products extends AbstractResource implements ProductResourceInterface
{
    protected static function endpoint(): string
    {
        return '/products';
    }

    protected static function wrapper(): string
    {
        return 'product';
    }
}
