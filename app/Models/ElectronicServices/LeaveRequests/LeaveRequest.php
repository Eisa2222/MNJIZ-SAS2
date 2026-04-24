<?php

namespace App\Models\ElectronicServices\LeaveRequests;

use App\Enums\ElectronicServices\LeaveRequests\LeaveRequestsStatus;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable, HasApprovalWorkflow, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'start_datetime',
        'end_datetime',
        'hours_count',
        'status',
        'created_by',
        'updated_by',
        'reason',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء طلب اجازة جديد',
            'updated'       => 'تم تحديث طلب اجازة موجود مسبقا',
            'deleted'       => 'تم حذف طلب اجازة ',
            'restored'      => 'تم استعادة طلب اجازة ',
            'forceDeleted'  => 'تم حذف طلب اجازة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} طلب اجازة";
            });
    }


    protected $casts = [
        'status'        => LeaveRequestsStatus::class,

        'start_date'    => 'date',
        'end_date'      => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];


    protected string $flowType = 'leave';

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(SettingsLeaveType::class, 'leave_type_id');
    }

    public function attachments()
    {
        return $this->hasMany(LeaveRequestAttachment::class, 'leave_request_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }


    /*
    |--------------------------------------------------------------------------
    |  الحصول على إعدادات عرض الحالة
    |--------------------------------------------------------------------------
    | ----- in progress -----
    */
    public function getStatusConfig(): array
    {
        $statusValue = $this->status->value ?? $this->status;

        return match ($statusValue) {
            'approved' => [
                'color' => 'success',
                'label' => 'الموافقة على الطلب',
                'message' => 'تمت الموافقة على طلب الإجازة بنجاح',
                'alert_class' => 'alert-success',
                'icon' => 'ti-check'
            ],
            'rejected' => [
                'color' => 'danger',
                'label' => 'رفض الطلب',
                'message' => 'تم رفض طلب الإجازة',
                'alert_class' => 'alert-danger',
                'icon' => 'ti-x'
            ],
            'closed' => [
                'color' => 'secondary',
                'label' => 'إغلاق الطلب',
                'message' => 'تم إغلاق طلب الإجازة',
                'alert_class' => 'alert-secondary',
                'icon' => 'ti-lock'
            ],
            default => [
                'color' => 'warning',
                'label' => 'تحديث الطلب',
                'message' => 'تم تحديث حالة الطلب',
                'alert_class' => 'alert-warning',
                'icon' => 'ti-info-circle'
            ]
        };
    }
}
