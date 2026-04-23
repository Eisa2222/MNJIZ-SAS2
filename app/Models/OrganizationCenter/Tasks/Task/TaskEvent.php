<?php
namespace App\Models\OrganizationCenter\Tasks\Task;


use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'event_type',
        'message',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}