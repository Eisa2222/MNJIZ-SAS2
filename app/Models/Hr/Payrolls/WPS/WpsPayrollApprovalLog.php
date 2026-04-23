<?php

namespace App\Models\Hr\Payrolls\WPS;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpsPayrollApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'wps_payroll_id',
        'employee_id',
        'action',
        'reason',
    ];

    public function wpsPayroll()
    {
        return $this->belongsTo(WpsPayroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }
}
