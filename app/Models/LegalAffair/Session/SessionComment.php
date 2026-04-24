<?php

namespace App\Models\LegalAffair\Session;


use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SessionComment extends Model
{
    use HasFactory, LogsActivity, BelongsToTenant;
    // protected $dates = ['deleted_at'];
    protected $fillable = [
        'session_id',
        'user_id',
        'content',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إضافة تعليق',
            'updated' => 'تم تحديث التعليق',
            'deleted' => 'تم حذف تعليق',
        ];

        return LogOptions::defaults()
            ->logAll() // تسجيل جميع الخصائص
            ->logOnlyDirty() // تسجيل الخصائص التي تم تغييرها فقط
            ->useLogName('SessionComment') // اسم السجل
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم تنفيذ {$eventName} على الملاحظة";
            });
    }


    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mentionedUsers()
    {
        return $this->belongsToMany(User::class, 'session_comment_mentions', 'session_comment_id', 'mentioned_user_id')
            ->withPivot('mentioner_user_id')
            ->withTimestamps();
    }

    // العلاقة مع المستخدمين الذين قاموا بالمنشن
    public function mentioners()
    {
        return $this->belongsToMany(User::class, 'session_comment_mentions', 'session_comment_id', 'mentioner_user_id')
            ->withPivot('mentioned_user_id')
            ->withTimestamps();
    }

    public function mentions()
    {
        return $this->hasMany(SessionCommentMention::class, 'session_comment_id');
    }
}
