<?php

namespace App\Services\Notifications\Tasks;

use App\Jobs\OrganizationCenter\Tasks\Task\Email\SendTaskCompletionEmailJob;
use App\Jobs\OrganizationCenter\Tasks\Task\SyncTaskWithMicrosoftJob;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskCreatedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Services\OrganizationCenter\Tasks\Task\Helper\TaskFormatterService;

class TaskNotificationService
{
    protected ?string $officeName = null;

    public function __construct(private TaskFormatterService $formatter)
    {
        // Lazy — do not query the `settings` table at construction (DI
        // resolves this service during artisan boot from CheckContractPayments
        // command BEFORE migrate:fresh has a chance to create the table).
    }

    protected function getOfficeName(): string
    {
        if ($this->officeName !== null) {
            return $this->officeName;
        }
        try {
            $this->officeName = Settings::find(1)?->office_name ?? '';
        } catch (\Throwable $e) {
            $this->officeName = '';
        }

        return $this->officeName;
    }

    public function taskCreated(Task $task): void
    {
        SyncTaskWithMicrosoftJob::dispatch(
            assignedUsers: $task->assignedUsers,
            taskData: $this->formatter->formatForMicrosoft($task, 'create'),
            officeName: $this->getOfficeName()
        );

        Notification::send($task->assignedUsers, new TaskCreatedNotification($task));
    }


    public function taskReassigned(Task $task, $note): void
    {
        SyncTaskWithMicrosoftJob::dispatch(
            assignedUsers: $task->assignedUsers,
            taskData: $this->formatter->formatForMicrosoft($task, 'assign', $note),
            officeName: $this->getOfficeName()
        );

        Notification::send($task->assignedUsers, new TaskCreatedNotification($task));
    }

    // /** إشعار عند تحديث المهمة */
    // public function taskUpdated(
    //     Task $task,
    //     $addedUsers,
    //     $existingUsers,
    //     $removedUsersData
    // ): void {
    //     // حذف من Microsoft للمستخدمين المحذوفين
    //     if (! empty($removedUsersData)) {
    //         BatchDeleteMicrosoftTaskJob::dispatch(
    //             task: $task,
    //             removedUsersData: $removedUsersData
    //         );
    //     }

    //     // إضافة للمستخدمين الجدد
    //     if ($addedUsers->isNotEmpty()) {
    //         SyncTaskWithMicrosoftJob::dispatch(
    //             assignedUsers: $addedUsers,
    //             taskData: $this->formatter->formatForMicrosoft($task, 'create'),
    //             officeName: $this->officeName
    //         );
    //         Notification::send($addedUsers, new TaskCreatedNotification($task));
    //     }

    //     // تحديث الباقين
    //     if ($existingUsers->isNotEmpty()) {
    //         BatchUpdateMicrosoftTaskJob::dispatch(
    //             task: $task,
    //             taskData: $this->formatter->formatForMicrosoft($task, 'update'),
    //             officeName: $this->officeName,
    //             existingUsers: $existingUsers
    //         );
    //         Notification::send($existingUsers, new TaskUpdatedNotification($task));
    //     }
    // }

}
