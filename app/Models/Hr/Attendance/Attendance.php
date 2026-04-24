<?php

namespace App\Models\Hr\Attendance;

use App\Models\Fingerprint;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'latitude',
        'longitude',
        'check_out_latitude',
        'check_out_longitude',
        'scheduled_start_time',
        'scheduled_end_time',
        'late_minutes',
        'early_arrival_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'day_status',
        'leave_type_id',
    ];

    protected $casts = [
        'date'                  => 'date',
        'check_in_time'         => 'datetime:H:i:s',
        'check_out_time'        => 'datetime:H:i:s',
        'scheduled_start_time'  => 'datetime:H:i:s',
        'scheduled_end_time'    => 'datetime:H:i:s',
        'late_minutes'          => 'integer',
        'early_arrival_minutes' => 'integer',
        'early_leave_minutes'   => 'integer',
        'overtime_minutes'      => 'integer',
        'day_status'            => 'string',
        'leave_type_id'         => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fingerprint()
    {
        return $this->belongsTo(Fingerprint::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(SettingsLeaveType::class, 'leave_type_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
