<?php

namespace App\Services\Qoyod\Resources\Accounts;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;

class CachedAccounts extends CacheResourceDecorator implements AccountResourceInterface
{
    public function __construct(Accounts $real)
    {
        parent::__construct($real,'accounts');
    }
}
