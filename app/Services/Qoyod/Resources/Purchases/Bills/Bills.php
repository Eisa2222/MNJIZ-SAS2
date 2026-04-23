<?php

namespace App\Services\Qoyod\Resources\Purchases\Bills;

use App\Services\Qoyod\Contracts\Resources\BillResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Bills extends AbstractResource implements BillResourceInterface
{
    protected static function endpoint(): string
    {
        return '/bills';
    }

    protected static function wrapper(): string
    {
        return 'bill';
    }
}
