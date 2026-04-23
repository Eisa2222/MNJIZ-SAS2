<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'message_text',
        'platform',
        'recipients',
    ];

    protected $casts = [
        'recipients' => 'array', // تحويل حقل recipients إلى مصفوفة
    ];

    // علاقة المرسل بالمستخدم
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}