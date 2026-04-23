<?php

namespace App\Services\Qoyod\Resources\Products\Categories;

use App\Services\Qoyod\Contracts\Resources\CategoryResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Categories extends AbstractResource implements CategoryResourceInterface
{
    protected static function endpoint(): string
    {
        return '/categories';
    }

    protected static function wrapper(): string
    {
        return 'category';
    }
}
