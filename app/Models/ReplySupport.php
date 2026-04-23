<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplySupport extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',      // نص الرد
        'user_id',      // معرف المستخدم الذي أرسل الرد
        'support_id',   // معرف التذكرة المرتبطة بالرد
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function support()
    {
        return $this->belongsTo(Support::class);
    }
}
