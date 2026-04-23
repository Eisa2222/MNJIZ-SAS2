<?php

namespace App\Models\general_setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingsLeaveType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'days',
        'status',
        'is_paid',
        'is_carry_forwardable',
        'is_deductible',
        'is_global',
        'count_weekends',
        'leave_unit_type',
        'gender_applicability',
        'min_service_years',
        'has_attachments',
        'attachment_description',
        'max_requests',
        'service_years_threshold',
        'days_after_threshold',
        'advance_notice_days',
        'user_id',
        'start_date',
        'end_date'
    ];


    protected $dates = [
        'deleted_at',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'supports_hourly_requests' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'max_requests' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء نوع إجازة',
            'updated' => 'تم تحديث نوع إجازة',
            'deleted' => 'تم حذف نوع إجازة',
            'restored' => 'تم استعادة نوع إجازة',
            'forceDeleted' => 'تم حذف نوع إجازة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings_leave_types')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} نوع الإجازة";
            });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
