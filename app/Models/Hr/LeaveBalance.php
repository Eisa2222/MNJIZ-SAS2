<?php

namespace App\Models\Hr;

use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\Employees\Employees;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_balances';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
        'last_accrued_at',
        'last_updated_by',
    ];

    protected $casts = [
        'last_accrued_at' => 'date',
        'total_days' => 'decimal:4',
        'used_days' => 'decimal:4',
        'remaining_days' => 'decimal:4',
    ];

    /*
    |--------------------------------------------------------------------------
    | علاقة مع الموظف.
    |--------------------------------------------------------------------------
    */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    /*
    |--------------------------------------------------------------------------
    | علاقة مع نوع الإجازة.
    |--------------------------------------------------------------------------
    */
    public function leaveType()
    {
        return $this->belongsTo(SettingsLeaveType::class);
    }

    /*
    |--------------------------------------------------------------------------
    | علاقة مع المستخدم الذي قام بآخر تحديث
    |--------------------------------------------------------------------------
    */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | تحديث الرصيد عند الموافقة على طلب الإجازة
    |--------------------------------------------------------------------------
    */
    public static function updateBalance($employee_id, $leave_type_id, $days, $year = null)
    {
        $year = $year ?? now()->year;

        $balance = self::where('employee_id', $employee_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('year', $year)
            ->first();

        if ($balance && $balance->remaining_days >= $days) {
            $balance->used_days += $days;
            $balance->remaining_days -= $days;
            $balance->last_updated_by = auth()->id();
            $balance->save();
            return true;
        }
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | استعادة الرصيد عند إلغاء الإجازة.
    |--------------------------------------------------------------------------
    */
    public static function restoreBalance($employee_id, $leave_type_id, $days, $year = null)
    {
        $year = $year ?? now()->year;

        $balance = self::where('employee_id', $employee_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('year', $year)
            ->first();

        if ($balance) {
            $balance->used_days -= $days;
            $balance->remaining_days += $days;
            $balance->last_updated_by = auth()->id();
            $balance->save();
            return true;
        }
        return false;
    }
}
