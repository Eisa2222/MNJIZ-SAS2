<?php

namespace App\Notifications;

use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class TaskCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable, TenantAwareJob;

    protected $task;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Task\Task $task
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->captureTenant();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\DatabaseMessage
     */
    public function toDatabase($notifiable)
    {
        return new DatabaseMessage([
            'task_id' => $this->task->id,
            'message'    => optional($this->task->createdBy)->name
                ? "قام " . optional($this->task->createdBy)->name . " بإضافة مهمة جديدة لك."
                : $this->task->task_name,
            'image'      => optional(optional($this->task->createdBy)->employee)->profile_picture,
            'action_url' => route('organization-center.tasks.show', $this->task->id),
            'created_at' => now(),
        ]);
    }
}
