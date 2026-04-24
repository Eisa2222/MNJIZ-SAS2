<?php

namespace App\Models\Hr\Attendance;

use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'editor_id',
        'old_data',
        'new_data',
        'edit_reason',
        'edited_at',
    ];

    protected $casts = [
        'old_data'  => 'array',
        'new_data'  => 'array',
        'edited_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
