<?php

namespace App\Services\Qoyod\Contracts;

use Illuminate\Http\Client\PendingRequest;

interface QoyodClientInterface
{
    public function http(): PendingRequest;
    public function url(string $uri): string;
}
