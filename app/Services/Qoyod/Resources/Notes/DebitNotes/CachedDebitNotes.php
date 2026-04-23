<?php

namespace App\Services\Qoyod\Resources\Notes\DebitNotes;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\DebitNoteResourceInterface;

class CachedDebitNotes extends CacheResourceDecorator implements DebitNoteResourceInterface
{
    public function __construct(DebitNotes $real)
    {
        parent::__construct($real, 'debit_notes');
    }
}
