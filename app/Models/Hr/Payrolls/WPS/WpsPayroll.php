<?php

namespace App\Models\Hr\Payrolls\WPS;

use App\Enums\Hr\Payrolls\WPS\WpsStatus;
use App\Models\Hr\Advances\Advance;
use App\Models\Hr\Deductions\Deduction;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Rewards\Reward;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WpsPayroll extends Model
{
    use HasFactory, SoftDeletes, HasApprovalWorkflow, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'reference',
        'run_date',
        'status',
        'total_gross',
        'total_net',
        'created_by',
    ];

    protected $casts = [
        'status'    => WpsStatus::class,
        'run_date'  => 'date'
    ];

    protected string $flowType = 'wps';

    // -- خاص بمسيرات الراوتب  لاضافة الحالة  الخاصة بها عند اضافة الطلب  -- //
    protected $attributes = [
        'status' => 'generated_automatically'
    ];
    // -- خاص بمسيرات الراوتب  لاضافة الحالة  الخاصة بها عند اضافة الطلب  -- //
    
    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    // wpsDetails
    public function wpsDetails()
    {
        return $this->hasMany(WpsPayrollDetail::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function wpsDetailRevisions()
    {
        return $this->hasManyThrough(
            WpsPayrollDetailRevision::class, // الهدف النهائي
            WpsPayrollDetail::class,         // الجدول الوسيط
        );
    }

    // في WpsPayroll Model
    public function advances($employee_id)
    {
        return Advance::where('wps_payrolls_id', $this->id)
            ->where('employee_id', $employee_id)
            ->get();
    }

    public function deductions($employee_id)
    {
        return Deduction::where('wps_payrolls_id', $this->id)
            ->where('employee_id', $employee_id)
            ->get();
    }

    public function rewards($employee_id)
    {
        return Reward::where('wps_payrolls_id', $this->id)
            ->where('employee_id', $employee_id)
            ->get();
    }

    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    public function getSumRevisionsAttribute(): string
    {
        return $this->wpsDetailRevisions()->count();
    }

    /*
    |============================================================================
    |============================================================================
    |                               Scopes
    |============================================================================
    |============================================================================
    */
    // لتحديد الشهر و السنة
    public function scopeProcessedFor($query, $year, $month)
    {
        return $query->whereYear('run_date', $year)
            ->whereMonth('run_date', $month)
            ->whereNotIn('status', [WpsStatus::Rejected]);
    }


    /*
    |============================================================================
    |============================================================================
    |                               Custom methods
    |============================================================================
    |============================================================================
    */
    public function canBeApproved(): bool
    {
        return in_array($this->status, [
            WpsStatus::GeneratedAutomatically,
            WpsStatus::GeneratedManually,
            WpsStatus::Modified,
        ]);
    }

    public function isSubmittedForApproval(): bool
    {
        return $this->status === WpsStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === WpsStatus::Pending;
    }

    public function isRejected(): bool
    {
        return $this->status === WpsStatus::Rejected;
    }
}
