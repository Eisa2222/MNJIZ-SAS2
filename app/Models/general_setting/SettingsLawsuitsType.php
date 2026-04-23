<?php

namespace App\Models\general_setting;

use Alkoumi\LaravelHijriDate\Hijri;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingsLawsuitsType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'settings_lawsuits_types';
    protected $dates = ['deleted_at'];
    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء الإعداد',
            'updated' => 'تم تحديث الإعداد',
            'deleted' => 'تم حذف الإعداد',
            'restored' => 'تم استعادة الإعداد',
            'forceDeleted' => 'تم حذف الإعداد بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} الإعداد";
            });
    }

    protected $fillable = [
        'subcategory_id',
        'name',
        'status',
        'user_id',
        'position',

    ];

    /**
     * علاقة مع التصنيف الفرعي.
     */
    public function subcategory()
    {
        return $this->belongsTo(SettingsSubcategories::class, 'subcategory_id');
    }


    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }

}
