<?php

namespace App\Models\general_setting;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingsDepartmentContractCase extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $fillable = ['name', 'user_id', 'status'];
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
    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
