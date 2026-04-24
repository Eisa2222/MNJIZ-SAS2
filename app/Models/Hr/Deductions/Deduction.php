<?php

namespace App\Models\Hr\Deductions;

use App\Enums\Hr\Deduction\DeductionStatus;
use App\Enums\Hr\Deduction\DeductionType;
use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Deduction extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, SoftDeletes, LogsActivity, HasApprovalWorkflow, BelongsToTenant;
    // use Cachable;

    protected $fillable = [
        'deduction_number',
        'employee_id',
        'deduction_type',
        'amount',
        'deduction_date',
        'status',
        'created_by',
        'updated_by',
        'notes',

        'applied_to_salary_date',
        'wps_payrolls_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء خصم مالي جديدة',
            'updated'       => 'تم تحديث خصم مالي موجودة مسبقا',
            'deleted'       => 'تم حذف خصم مالي ',
            'restored'      => 'تم استعادة خصم مالي ',
            'forceDeleted'  => 'تم حذف خصم مالي بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} خصم مالي";
            });
    }


    protected $casts = [
        'status'             => DeductionStatus::class,
        'deduction_type'     => DeductionType::class,
        'deduction_date'     => 'date:Y-m-d',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Flow Type
    |--------------------------------------------------------------------------
    */
    protected string $flowType = 'deduction';


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

    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }


    /*
    |============================================================================
    |============================================================================
    |                            scopes
    |============================================================================
    |============================================================================
    */
    public function scopeApprovedInMonth($query, int $employeeId, Carbon $month)
    {
        return $query->where('employee_id', $employeeId)
            ->where('status', DeductionStatus::Approved)
            ->whereYear('deduction_date', $month->year)
            ->whereMonth('deduction_date', $month->month);
    }
}
