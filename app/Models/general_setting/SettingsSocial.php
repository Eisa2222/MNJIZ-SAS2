<?php

namespace App\Models\general_setting;

use Alkoumi\LaravelHijriDate\Hijri;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingsSocial extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $fillable = [
        // 'name',
        // 'icon',
        // 'user_id',
        'status',
        'api_key',
        'api_secret',
        'access_token',
        'access_token_secret',
        'is_active',

    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_active'             => 'boolean',
        'api_key'               => 'encrypted',
        'api_secret'            => 'encrypted',
        'access_token'          => 'encrypted',
        'access_token_secret'   => 'encrypted',
    ];

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
