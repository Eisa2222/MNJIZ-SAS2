<?php

namespace App\Services\Qoyod\Resources\Accounts;

use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Accounts extends AbstractResource implements AccountResourceInterface
{
    protected static function endpoint(): string
    {
        return '/accounts';
    }

    protected static function wrapper(): string
    {
        return 'account';
    }
}
