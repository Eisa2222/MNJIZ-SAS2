<?php

namespace App\Services\Qoyod\Resources\Products;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\ProductResourceInterface;

class CachedProducts extends CacheResourceDecorator implements ProductResourceInterface
{
    public function __construct(Products $real)
    {
        parent::__construct($real, 'products');
    }
}
