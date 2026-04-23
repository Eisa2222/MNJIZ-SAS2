<?php

namespace App\Services\Qoyod\Resources\Customers;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;

class CachedCustomers extends CacheResourceDecorator implements CustomerResourceInterface
{
    public function __construct(Customers $real)
    {
        parent::__construct($real, 'customers');
    }
}
