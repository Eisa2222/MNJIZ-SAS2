<?php

namespace App\Notifications;

use App\Models\OrganizationCenter\Tasks\Task\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class TaskCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $user;
    protected $isCompleted; // هل تم الإكمال أم الإلغاء؟

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Task\Task $task
     * @param \App\Models\User|int|null $user
     * @param bool $isCompleted
     * @return void
     */
    public function __construct(Task $task, $user = null, bool $isCompleted = true)
    {
        $this->task = $task;

        // إذا كان المستخدم null، فاستخدم المستخدم الحالي
        if ($user === null) {
            $this->user = auth()->user();
        }
        // إذا كان عدد صحيح، فابحث عن المستخدم بناءً على المعرف
        elseif (is_numeric($user)) {
            $this->user = User::find($user);
        }
        // خلاف ذلك، افترض أنه كائن مستخدم
        else {
            $this->user = $user;
        }

        $this->isCompleted = $isCompleted;
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
        // تأكد من وجود المستخدم
        $userName = $this->user ? $this->user->name : 'مستخدم';
        $taskName = $this->task->task_name ?? 'المهمة';

        // تحديد الرسالة بناءً على حالة الإكمال
        $message = $this->isCompleted
            ? "قام {$userName} بإكمال المهمة: {$taskName}"
            : "قام {$userName} بإلغاء إكمال المهمة: {$taskName}";

        // الحصول على صورة الملف الشخصي بطريقة آمنة
        $profilePicture = null;
        if ($this->user && isset($this->user->employee) && isset($this->user->employee->profile_picture)) {
            $profilePicture = $this->user->employee->profile_picture;
        }

        return new DatabaseMessage([
            'task_id' => $this->task->id,
            'message' => $message,
            'image' => $profilePicture,
            'action_url' => route('organization-center.tasks.show', $this->task->id),
            'created_at' => now(),
        ]);
    }
}
