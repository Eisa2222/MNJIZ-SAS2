<?php

namespace App\Models\LegalAI;

use App\Enums\LegalAI\AiChatType;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AiChat extends Model
{
    use HasFactory, HasUuids, Cachable;


    /*
    |--------------------------------------------------------------------------
    | The table associated with the model.
    |--------------------------------------------------------------------------
    */
    protected $table = 'ai_chats';

    /*
    |--------------------------------------------------------------------------
    | The attributes that are mass assignable.
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'title',
        'type',
    ];


    /*
    |--------------------------------------------------------------------------
    | The attributes that should be cast.
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'type' => AiChatType::class,
    ];


    /*
    |--------------------------------------------------------------------------
    | Get the user that owns the chat.
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Get the messages for the chat.
    |--------------------------------------------------------------------------
    */
    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'ai_chat_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeOfType(Builder $query, AiChatType $type): Builder
    {
        return $query->where('type', $type->value);
    }


    /*
    |--------------------------------------------------------------------------
    | Route Model Binding
    |--------------------------------------------------------------------------
    */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
