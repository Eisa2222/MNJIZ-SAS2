<?php

namespace App\Models\Hr;

use App\Models\Hr\Employees\Employees;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalanceLog extends Model
{
    use HasFactory;


    protected $fillable = [
        'leave_balance_id',
        'employee_id',
        'year',
        'user_id',
        'action',
        'old_total_days',
        'new_total_days',
        'old_used_days',
        'new_used_days',
        'old_remaining_days',
        'new_remaining_days',
        'notes',
    ];

    protected $casts = [
        'old_total_days' => 'decimal:4',
        'new_total_days' => 'decimal:4',
        'old_used_days' => 'decimal:4',
        'new_used_days' => 'decimal:4',
        'old_remaining_days' => 'decimal:4',
        'new_remaining_days' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships Employee
    |--------------------------------------------------------------------------
    */
    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships User
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships LeaveBalance
    |--------------------------------------------------------------------------
    */
    public function leaveBalance()
    {
        return $this->belongsTo(LeaveBalance::class, 'leave_balance_id');
    }
}
