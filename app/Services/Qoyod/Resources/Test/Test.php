<?php

namespace App\Services\Qoyod\Resources\Test;

use App\Services\Qoyod\Contracts\Resources\TestResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Test extends AbstractResource implements TestResourceInterface
{
    protected static function endpoint(): string
    {
        return '/bill_payments';
    }

    protected static function wrapper(): string
    {
        return 'bill';
    }
}
