<?php

declare(strict_types=1);

namespace App\Data\LegalAI;

use App\Http\Requests\LegalAI\SendChatMessageRequest;

/*
|--------------------------------------------------------------------------
| ChatMessageData (DTO)
|--------------------------------------------------------------------------
*/

final readonly class ChatMessageData
{
    public function __construct(
        public ?string $message,
    ) {}


    /*
    |--------------------------------------------------------------------------
    | مباشرة من طلب HTTP الذي تم التحقق من صحته.
    |--------------------------------------------------------------------------
    */
    public static function fromRequest(SendChatMessageRequest $request): self
    {
        return new self(
            message: $request->validated('message'),
        );
    }
}
