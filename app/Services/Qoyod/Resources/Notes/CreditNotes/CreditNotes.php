<?php

namespace App\Services\Qoyod\Resources\Notes\CreditNotes;

use App\Services\Qoyod\Contracts\Resources\CreditNoteResourceInterface;
use App\Services\Qoyod\Resources\AbstractResource;

class CreditNotes extends AbstractResource implements CreditNoteResourceInterface
{
    protected static function endpoint(): string
    {
        return '/credit_notes';
    }

    protected static function wrapper(): string
    {
        return 'credit_note';
    }
}
