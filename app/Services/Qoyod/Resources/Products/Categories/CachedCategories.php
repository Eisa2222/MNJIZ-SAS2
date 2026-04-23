<?php

namespace App\Services\Qoyod\Resources\Products\Categories;

use App\Services\Qoyod\Contracts\Resources\CategoryResourceInterface;
use App\Services\Qoyod\Resources\CacheResourceDecorator;

class CachedCategories extends CacheResourceDecorator implements CategoryResourceInterface
{
    public function __construct(Categories $real)
    {
        parent::__construct($real, 'categories');
    }
}
