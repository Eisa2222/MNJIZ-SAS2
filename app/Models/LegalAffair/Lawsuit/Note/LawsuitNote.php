<?php

namespace App\Models\LegalAffair\Lawsuit\Note;


use Alkoumi\LaravelHijriDate\Hijri;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes; // استيراد السمة SoftDeletes
use Illuminate\Database\Eloquent\Model;

class LawsuitNote extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'lawsuit_id',
        'title',
        'text',
        'user_id',
        'type',
    ];
    protected $dates = ['deleted_at'];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إضافة الملاحظة',
            'updated' => 'تم تحديث الملاحظة ',
            'deleted' => 'تم حذف الملاحظة',
        ];

        return LogOptions::defaults()
            ->logAll() // تسجيل جميع الخصائص
            ->logOnlyDirty() // تسجيل الخصائص التي تم تغييرها فقط
            ->useLogName('LawsuitNote') // اسم السجل
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم تنفيذ {$eventName} على ";
            });
    }


    // علاقة الملاحقة بالدعوى
    public function lawsuit()
    {
        return $this->belongsTo(Lawsuit::class);
    }

    // علاقة الردود بالملاحظة
    public function replies()
    {
        return $this->hasMany(LawsuitNoteReply::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getHijriCreatedAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
