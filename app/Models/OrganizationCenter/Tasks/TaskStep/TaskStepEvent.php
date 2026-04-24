<?php
namespace App\Models\OrganizationCenter\Tasks\TaskStep;



use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStepEvent extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'task_step_id',
        'user_id',
        'event_type',
        'message'
    ];


    public function taskStep()
    {
        return $this->belongsTo(TaskStep::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
