<?php

namespace App\Models\Hr\Advances;

use App\Enums\Hr\Advance\AdvanceStatus;
use App\Enums\Hr\Advance\AdvanceType;
use App\Models\Hr\Employees\Employees;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use Carbon\Carbon;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Advance extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable, HasApprovalWorkflow;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'advance_number',
        'employee_id',
        'advance_type',
        'amount',
        'advance_date',
        'remaining_amount',
        'status',
        'due_date',
        'created_by',
        'updated_by',
        'notes',

        'applied_to_salary_date',
        'wps_payrolls_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء سلفية جديدة',
            'updated'       => 'تم تحديث سلفية موجودة مسبقا',
            'deleted'       => 'تم حذف سلفية ',
            'restored'      => 'تم استعادة سلفية ',
            'forceDeleted'  => 'تم حذف سلفية بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} سلفية";
            });
    }


    protected $casts = [
        'status'          => AdvanceStatus::class,
        'advance_type'    => AdvanceType::class,
        'advance_date'    => 'date:Y-m-d',
        'due_date'        => 'date:Y-m-d',
        'approval_date'   => 'datetime:Y-m-d H:i:s',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Flow Type
    |--------------------------------------------------------------------------
    */
    protected string $flowType = 'advance';

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
            ->where('status', AdvanceStatus::Approved)
            ->whereYear('due_date', $month->year)
            ->whereMonth('due_date', $month->month);
    }
}
