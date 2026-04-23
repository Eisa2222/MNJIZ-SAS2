<?php

namespace App\Models\Hr\Violations;

use App\Models\ElectronicServices\UserViolation;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationExecution extends Model
{
    use HasFactory;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'violation_id',
        'execution_type',
        'execution_date',
        'executed_by',

        'deduction_amount',
        'deduction_days',
        'deduction_percentage',

        'applied_to_salary_date',
        'wps_payrolls_id',
        'notes',
        'attachment'
    ];

    protected $dates = [
        'execution_date',
        'applied_to_salary_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'applied_to_salary'     => 'boolean',
        'deduction_amount'      => 'float',
        'deduction_percentage'  => 'float'
    ];

    /*
    |--------------------------------------------------------------------------
    | scopes
    |--------------------------------------------------------------------------
    */

    // get  deduction
    public function scopeDeduction($query)
    {
        return $query->where('execution_type', 'deduction');
    }

    // approved and where date
    public function scopeForApprovedViolationsOf($query, int $employeeId, Carbon $until)
    {
        return $query->whereHas('violation', function ($q) use ($employeeId, $until) {
            $q->where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->whereDate('violation_date', '<=', $until);
        });
    }

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violation_id');
    }

    public function payroll()
    {
        return $this->belongsTo(WpsPayroll::class, 'wps_payrolls_id');
    }

    public function executor()
    {
        return $this->belongsTo(Employees::class, 'executed_by');
    }

    // public function getExecutionTypeTextAttribute()
    // {
    //     $typeMap = [
    //         'warning'       => 'إنذار',
    //         'deduction'     => 'خصم',
    //         'suspension'    => 'إيقاف',
    //         'dismissal'     => 'فصل',
    //         'deprivation'   => 'حرمان',
    //         'other'         => 'أخرى'
    //     ];

    //     return $typeMap[$this->execution_type] ?? $this->execution_type;
    // }

    // public function getIsAppliedAttribute()
    // {
    //     return $this->applied_to_salary;
    // }

    // public function getFormattedDeductionAmountAttribute()
    // {
    //     return number_format($this->deduction_amount, 2) . ' ريال';
    // }
}