<?php

declare(strict_types=1);

namespace App\Data\LegalAI;

use Illuminate\Http\UploadedFile;

final class PrecedentData
{

    /*
    |--------------------------------------------------------------------------
    | Constrct
    |--------------------------------------------------------------------------
    */
    public function __construct(
        public readonly ?string $queryText,
        public readonly ?UploadedFile $document,
    ) {}
}
