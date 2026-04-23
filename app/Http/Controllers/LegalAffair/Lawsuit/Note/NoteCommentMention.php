<?php

namespace App\Http\Controllers\LegalAffair\Lawsuit\Note;

use App\Models\LegalAffair\Lawsuit\Note\LawsuitNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NoteCommentMention extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['note_comment_id', 'mentioner_user_id', 'mentioned_user_id'];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إضافة رد',
            'updated' => 'تم تحديث رد',
            'deleted' => 'تم حذف رد',
        ];

        return LogOptions::defaults()
            ->logAll() // تسجيل جميع الخصائص
            ->logOnlyDirty() // تسجيل الخصائص التي تم تغييرها فقط
            ->useLogName('SessionComment') // اسم السجل
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم تنفيذ {$eventName} على الملاحظة";
            });
    }


    public function noteComment()
    {
        return $this->belongsTo(LawsuitNote::class, 'note_comment_id');
    }

    public function mentioner()
    {
        return $this->belongsTo(User::class, 'mentioner_user_id');
    }

    public function mentionedUser()
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }
}
