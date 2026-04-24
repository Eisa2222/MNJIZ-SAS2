<?php

namespace App\Notifications;

use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Task\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class TaskDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable, TenantAwareJob;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->captureTenant();
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'created_at' => now(),
            'task_id' => '',
            'message' => "قام {$this->task->createdBy->name} بحذف المهمة {$this->task->task_name}   .",
            'image' => $this->task->assignedTo->employee->profile_picture,
            'action_url' => '',
            'created_at' => now(),
        ]);
    }
}
