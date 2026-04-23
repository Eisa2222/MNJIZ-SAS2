<?php

namespace App\Models\Hr\Violations;

use App\Enums\Hr\ViolationsPenalties\ViolationStatus;
use App\Models\general_setting\SettingsViolation;
use App\Models\Hr\Attendance\Attendance;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Violations\ViolationAppeal;
use App\Models\Hr\Violations\ViolationExecution;
use App\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Violation extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, SoftDeletes, LogsActivity;
    // use Cachable;

    protected $fillable = [
        'employee_id',
        'settings_violation_id',
        'violation_date',
        'occurrence',
        'reference_number',
        'penalty_text',
        'status',
        'is_appealable',
        'appeal_days',
        'notes',
        'reviewed_by',
        'reviewed_at',

        'related_attendance_id',

        'created_by',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء مخالفة جديدة',
            'updated'       => 'تم تحديث مخالفة موجودة مسبقا',
            'deleted'       => 'تم حذف مخالفة ',
            'restored'      => 'تم استعادة مخالفة ',
            'forceDeleted'  => 'تم حذف مخالفة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} مخالفة";
            });
    }


    protected $casts = [
        'status'           => ViolationStatus::class,

        'violation_date' => 'datetime',
        'reviewed_at'    => 'datetime',
        'is_appealable'  => 'boolean',

        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function violationType()
    {
        return $this->belongsTo(SettingsViolation::class, 'settings_violation_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(Employees::class, 'reviewed_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }

    public function appeal()
    {
        return $this->hasOne(ViolationAppeal::class, 'violation_id');
    }

    public function execution()
    {
        return $this->hasOne(ViolationExecution::class, 'violation_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'related_attendance_id');
    }


    /*
    |============================================================================
    |============================================================================
    |                               Scope
    |============================================================================
    |============================================================================
    */
    public function scopePendingOrUnderAppeal($query)
    {
        return $query->whereIn('status', [
            ViolationStatus::Pending,
            ViolationStatus::UnderAppeal,
        ]);
    }

    public function scopeNotCancelledOrRejected($query)
    {
        return $query->whereNotIn('status', [
            ViolationStatus::Cancelled,
            ViolationStatus::Rejected,
        ]);
    }

    public function scopeOfEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeOfType($query, int $settingsViolationId)
    {
        return $query->where('settings_violation_id', $settingsViolationId);
    }



    /*
    |--------------------------------------------------------------------------
    | للوصول الى نص العقوبة
    |--------------------------------------------------------------------------
    */
    public function formatPenalty($penalty)
    {
        return SettingsViolation::formatPenalty($penalty);
    }


    /*
    |--------------------------------------------------------------------------
    | حساب عدد الأيام المنقضية منذ إنشاء الانتهاك
    |--------------------------------------------------------------------------
    */
    protected function getDaysPassedSinceCreation()
    {
        $dateCreated = new DateTime($this->violation_date);
        $currentDate = new DateTime();
        return $dateCreated->diff($currentDate)->days;
    }

    /*
    |--------------------------------------------------------------------------
    | حساب عدد الأيام المتبقية للتظلم
    |--------------------------------------------------------------------------
    */
    public function getRemainingAppealDays()
    {
        if (!$this->is_appealable) {
            return 0;
        }

        return max(0, $this->appeal_days - $this->getDaysPassedSinceCreation());
    }

    /*
    |--------------------------------------------------------------------------
    |  حساب النسبة المئوية لتقدم مدة التظلم
    |--------------------------------------------------------------------------
    */
    public function getAppealProgressPercentage()
    {
        if (!$this->is_appealable || $this->appeal_days <= 0) {
            return 100;
        }

        $daysPassed = $this->getDaysPassedSinceCreation();
        return min(100, ($daysPassed / $this->appeal_days) * 100);
    }

    /*
    |--------------------------------------------------------------------------
    |  سجل الحضور المرتبط بالمخالفة
    |--------------------------------------------------------------------------
    */
}
