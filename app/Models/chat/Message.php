<?php

namespace App\Models\chat;

use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'attachment_path',
        'attachment_name',
        'attachment_size',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot Method
    |--------------------------------------------------------------------------
    | Model events and default values.
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->message) && empty($model->attachment_path)) {
                throw new \InvalidArgumentException('يجب إما كتابة رسالة أو إرفاق ملف');
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    | Define model relationships.
    */

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    | Define attribute accessors and mutators.
    */

    public function getAttachmentUrlAttribute()
    {
        return $this->attachment_path ? asset('storage/' . $this->attachment_path) : null;
    }

    public function getHasAttachmentAttribute()
    {
        return !empty($this->attachment_path);
    }

    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    | Define query scopes.
    */
    public function scopeBetweenUsers($query, $user1, $user2)
    {
        return $query->where(function ($mainQuery) use ($user1, $user2) {
            $mainQuery->where(function ($q) use ($user1, $user2) {
                $q->where('sender_id', $user1)->where('receiver_id', $user2);
            })->orWhere(function ($q) use ($user1, $user2) {
                $q->where('sender_id', $user2)->where('receiver_id', $user1);
            });
        });
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
