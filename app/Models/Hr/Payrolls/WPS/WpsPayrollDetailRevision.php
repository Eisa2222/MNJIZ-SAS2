<?php

namespace App\Models\Hr\Payrolls\WPS;

use App\Enums\Hr\Payrolls\WPS\WpsPayrollDetailRevisionStatus;
use App\Models\Hr\Employees\Employees;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WpsPayrollDetailRevision extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'wps_payroll_detail_id',
        'old_values',
        'new_values',
        'status',
        'notes',
        'reply',
        'edited_by',
        'approved_by',
        'approved_at',
    ];


    protected $casts = [
        'status'    => WpsPayrollDetailRevisionStatus::class,

        'old_values'   => 'array',
        'new_values'   => 'array',
        'approved_at'  => 'datetime',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function wpsPayrollDetail()
    {
        return $this->belongsTo(WpsPayrollDetail::class, 'wps_payroll_detail_id');
    }

    public function editor()
    {
        return $this->belongsTo(Employees::class, 'edited_by');
    }

    public function approver()
    {
        return $this->belongsTo(Employees::class, 'approved_by');
    }

    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->format('Y-m-d')
        );
    }

    /*
    |============================================================================
    |============================================================================
    |                          custom functions
    |============================================================================
    |============================================================================
    */
    public function isPending(): bool
    {
        return $this->status === WpsPayrollDetailRevisionStatus::Pending;
    }
}
