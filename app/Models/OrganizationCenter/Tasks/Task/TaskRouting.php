<?php

namespace App\Models\OrganizationCenter\Tasks\Task;

use App\Enums\OrganizationCenter\Tasks\Task\TaskRoutingAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRouting extends Model
{
    use HasFactory;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'task_id',
        'from_user_id',
        'action',
        'reason',
    ];

    protected $casts = [
        'action'   => TaskRoutingAction::class,
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
