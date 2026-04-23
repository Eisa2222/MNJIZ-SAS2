<?php

namespace App\Services\Qoyod\Resources\Quotes;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\QuoteResourceInterface;

class CachedQuotes extends CacheResourceDecorator implements QuoteResourceInterface
{
    public function __construct(Quotes $real)
    {
        parent::__construct($real, 'quotes');
    }
}
