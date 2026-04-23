<?php

namespace App\Services\Qoyod\Resources\Vendors;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\VendorResourceInterface;

class CachedVendors extends CacheResourceDecorator implements VendorResourceInterface
{
    public function __construct(Vendors $real)
    {
        parent::__construct($real, 'vendors');
    }
}
