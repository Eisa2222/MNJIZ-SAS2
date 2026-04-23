<?php

namespace App\Services\Qoyod\Resources\JournalEntries;

use App\Services\Qoyod\Contracts\Resources\JournalEntrieResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class JournalEntries extends AbstractResource implements JournalEntrieResourceInterface
{
    protected static function endpoint(): string
    {
        return '/journal_entries';
    }

    protected static function wrapper(): string
    {
        return 'journal_entries';
    }
}
