<?php

namespace App\Services\Qoyod\Presenters\JournalEntries;

use App\Services\Qoyod\Contracts\Resources\JournalEntrieResourceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JournalEntriesPresenter
{
    protected int $ttlMinutes;

    public function __construct()
    {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }
}
