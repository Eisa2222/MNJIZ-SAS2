<?php

namespace App\Services\Qoyod\Resources\Customers;

use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Customers extends AbstractResource implements CustomerResourceInterface
{
    protected static function endpoint(): string
    {
        return '/customers';
    }

    protected static function wrapper(): string
    {
        return 'contact';
    }
}
