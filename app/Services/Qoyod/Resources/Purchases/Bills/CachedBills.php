<?php

namespace App\Services\Qoyod\Resources\Purchases\Bills;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\BillResourceInterface;

class CachedBills extends CacheResourceDecorator implements BillResourceInterface
{
    public function __construct(Bills $real)
    {
        parent::__construct($real, 'bills');
    }
}
