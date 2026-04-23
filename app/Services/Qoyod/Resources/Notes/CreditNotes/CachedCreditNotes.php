<?php

namespace App\Services\Qoyod\Resources\Notes\CreditNotes;

use App\Services\Qoyod\Resources\CacheResourceDecorator;
use App\Services\Qoyod\Contracts\Resources\CreditNoteResourceInterface;

class CachedCreditNotes extends CacheResourceDecorator implements CreditNoteResourceInterface
{
    public function __construct(CreditNotes $real)
    {
        parent::__construct($real, 'credit_notes');
    }
}
