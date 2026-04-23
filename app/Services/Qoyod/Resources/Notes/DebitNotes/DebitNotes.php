<?php

namespace App\Services\Qoyod\Resources\Notes\DebitNotes;

use App\Services\Qoyod\Contracts\Resources\DebitNoteResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class DebitNotes extends AbstractResource implements DebitNoteResourceInterface
{
    protected static function endpoint(): string
    {
        return '/debit_notes';
    }

    protected static function wrapper(): string
    {
        return 'debit_note';
    }
}
