<?php

namespace App\Models\OrganizationCenter\Tasks\TaskStep;

use App\Enums\OrganizationCenter\Tasks\TaskStep\TaskStepStatus;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStep extends Model
{
    use HasFactory, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'task_id',
        'name',
        'step_start_date',
        'step_end_date',
        'reject_reason',
        'step_order',
        'needs_approval',
        'status',
        'created_by',
        'completed_by',
        'updated_by'
    ];

    protected $casts = [
        'status'            => TaskStepStatus::class,

        'step_start_date'   => 'datetime',
        'step_end_date'     => 'datetime',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];


    // حساب المدة بين تاريخ البداية والنهاية
    public function getDurationAttribute()
    {
        if (!$this->step_start_date || !$this->step_end_date) {
            return null;
        }

        $start = Carbon::parse($this->step_start_date);
        $end = Carbon::parse($this->step_end_date);
        $durationMinutes = $end->diffInMinutes($start);

        $hours = floor($durationMinutes / 60);
        $minutes = $durationMinutes % 60;

        if ($hours > 0) {
            return $hours . ' ساعة ' . ($minutes > 0 ? $minutes . ' دقيقة' : '');
        }
        return $minutes . ' دقيقة';
    }





    /*
    |--------------------------------------------------------------------------
    | task relation
    |--------------------------------------------------------------------------
    */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }



    /*
    |--------------------------------------------------------------------------
    | user relation
    |--------------------------------------------------------------------------
    */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_step_user', 'task_step_id', 'user_id')
            ->withPivot('assigned_at', 'user_attachment', 'graph_list_id', 'graph_task_id', 'graph_event_id')
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | العلاقة مع المستخدم الذي قام باكمال المهمة
    |--------------------------------------------------------------------------
    */
    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }





    /*
    |--------------------------------------------------------------------------
    | check is assig
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    public function isAssignedUser()
    {
        return $this->assignedUsers->contains('id', auth()->id());
    }
}
