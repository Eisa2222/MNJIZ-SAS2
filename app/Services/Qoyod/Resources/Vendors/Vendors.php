<?php

namespace App\Services\Qoyod\Resources\Vendors;

use App\Services\Qoyod\Contracts\Resources\VendorResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Vendors extends AbstractResource implements VendorResourceInterface
{
    protected static function endpoint(): string
    {
        return '/vendors';
    }
    
    protected static function wrapper(): string
    {
        return 'contact';
    }
}
