<?php

namespace App\Models\LegalAffair\Lawsuit\Note;

use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity; // استيراد LogsActivity trait
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;

class LawsuitNoteReply extends Model
{
    use HasFactory, LogsActivity, BelongsToTenant;

    protected $fillable = [
        'lawsuit_note_id',
        'user_id',
        'reply_text',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // تسجيل جميع الحقول القابلة للملء
            ->logOnlyDirty() // تسجيل الحقول التي تم تغييرها فقط
            ->useLogName('LawsuitNoteReply') // تحديد اسم السجل
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => 'تم إضافة رد جديد على الملاحظة',
                    'updated' => 'تم تعديل الرد على الملاحظة',
                    'deleted' => 'تم حذف الرد على الملاحظة',
                    default => "تم تنفيذ {$eventName} على الرد",
                };
            });
    }



    // علاقة الرد بالملاحظة
    public function note()
    {
        return $this->belongsTo(LawsuitNote::class, 'lawsuit_note_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getHijriCreatedAttribute()
    {
        $createdAt = $this->created_at;
        $humanReadable = Carbon::parse($createdAt)->diffForHumans(); // مثل "قبل 3 أيام"

        return $humanReadable;
    }
}
