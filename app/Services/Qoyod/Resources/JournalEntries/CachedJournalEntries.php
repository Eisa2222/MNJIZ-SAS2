<?php

namespace App\Services\Qoyod\Resources\JournalEntries;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\JournalEntrieResourceInterface;

class CachedJournalEntries extends CacheResourceDecorator implements JournalEntrieResourceInterface
{
    public function __construct(JournalEntries $real)
    {
        parent::__construct($real, 'journal_entries');
    }
}
