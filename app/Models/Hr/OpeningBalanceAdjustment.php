<?php

namespace App\Models\Hr;

use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\Employees\Employees;
use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class OpeningBalanceAdjustment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'opening_balance',
        'effective_date',
        'note',
        'created_by',
    ];

    /* علاقات */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(SettingsLeaveType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
