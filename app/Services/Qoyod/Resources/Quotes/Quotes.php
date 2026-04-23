<?php

namespace App\Services\Qoyod\Resources\Quotes;

use App\Services\Qoyod\Contracts\Resources\QuoteResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class Quotes extends AbstractResource implements QuoteResourceInterface
{
    protected static function endpoint(): string
    {
        return '/quotes';
    }

    protected static function wrapper(): string
    {
        return 'quote';
    }
}
