<?php

declare(strict_types=1);

namespace App\Data\LegalAI;

use Illuminate\Http\UploadedFile;


final class DraftingData
{

    /*
    |--------------------------------------------------------------------------
    | Constract
    |--------------------------------------------------------------------------
    */
    public function __construct(
        public readonly string $draftingType,
        public readonly ?string $rawText,
        public readonly ?UploadedFile $document,
    ) {}
}
