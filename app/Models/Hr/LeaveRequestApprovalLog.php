<?php

namespace App\Models\Hr;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'employee_id',
        'action',
        'reason',
    ];

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }
}