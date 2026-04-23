<?php

namespace App\Models\Hr\Payrolls\WPS;

use App\Enums\Hr\Payrolls\WPS\WpsPayrollDetailStatus;
use App\Models\Hr\Advances\Advance;
use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WpsPayrollDetail extends Model
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
        'payroll_run_id',
        'employee_id',
        'status',
        'basic',
        'transport',
        'housing',
        'other',
        'insurance',
        'incentives',
        'deductions',
        'net',
    ];

    protected $casts = [
        'status'    => WpsPayrollDetailStatus::class,
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function wpsPayroll()
    {
        return $this->belongsTo(WpsPayroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function WpsPayrollRevision()
    {
        return $this->hasMany(WpsPayrollDetailRevision::class);
    }


    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    protected function basic(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    protected function transport(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    protected function housing(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    protected function other(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    protected function insurance(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    protected function deductions(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    protected function incentives(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    protected function net(): Attribute
    {
        return Attribute::make(
            get: fn($value) => number_format($value, 2, '.', ',')
        );
    }

    // لارجاع الخصومات بدون التامين
    protected function deductionsWithoutInsurance(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalDeductions = $this->getRawOriginal('deductions') ?? 0;
                $insurance = $this->getRawOriginal('insurance') ?? 0;

                $result = $totalDeductions - $insurance;
                return $result < 0 ? 0 : $result;
            }
        );
    }


    /*
    |============================================================================
    |============================================================================
    |                          custom functions
    |============================================================================
    |============================================================================
    */
    public function hasRevisions(): bool
    {
        return $this->WpsPayrollRevision()->exists();
    }
}
