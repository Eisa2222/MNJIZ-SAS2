<?php

namespace App\Notifications;

use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class StepCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $step;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Task\TaskStep $step
     * @return void
     */
    public function __construct(TaskStep $step)
    {
        $this->step = $step;
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
        // استخدام المستخدم الذي أكمل الخطوة بدلاً من المستخدم الحالي
        $completedByUser = $this->step->completed_by ? \App\Models\User::find($this->step->completed_by) : null;
        $completedByName = $completedByUser ? $completedByUser->name : 'مستخدم';

        $taskName = $this->step->task ? $this->step->task->task_name : 'المهمة';
        $stepName = $this->step->name ?? 'الخطوة';

        // الحصول على صورة الملف الشخصي بطريقة آمنة
        $profilePicture = null;
        if ($completedByUser && $completedByUser->employee && $completedByUser->employee->profile_picture) {
            $profilePicture = $completedByUser->employee->profile_picture;
        }

        return new DatabaseMessage([
            'task_id' => $this->step->task_id,
            'step_id' => $this->step->id,
            'message' => "قام {$completedByName} بإكمال الخطوة \"{$stepName}\" في المهمة \"{$taskName}\".",
            'image' => $profilePicture,
            'action_url' => route('organization-center.tasks.show', $this->step->task_id),
            'created_at' => now(),
        ]);
    }
}
