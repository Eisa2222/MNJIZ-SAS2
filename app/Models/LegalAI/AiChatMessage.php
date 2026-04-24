<?php

namespace App\Models\LegalAI;

use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    use HasFactory, Cachable, BelongsToTenant;

    protected $table = 'ai_chat_messages';

    protected $fillable = [
        'ai_chat_id',
        'sender',
        'message',
        'file_path',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(AiChat::class, 'ai_chat_id');
    }
}
