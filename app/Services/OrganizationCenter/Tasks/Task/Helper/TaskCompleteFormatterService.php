<?php

namespace App\Services\OrganizationCenter\Tasks\Task\Helper;

use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\OrganizationCenter\Tasks\Task\Task;

class TaskCompleteFormatterService
{

    public function formatForMicrosoftEmail(Task $task, $action, $reason = null, $createdAt = null): array
    {
        return [
            'task_url'          => route('organization-center.tasks.show', $task->id),
            'title'             => 'قام ' . auth()->user()->name . ' ' . $action,
            'user_name'         => $task->createdBy->name ?? '',
            'task_name'         => $task->task_name ?? '',
            'completion_date'   => $task->task_end_date ?? '',
            'duration'          => $task->duration ?? '',

            'reason'             => $reason ?? '',
            'createdAt'          => $createdAt ?? '',

            'office_name'       => Settings::find(1)->office_name ?? '',
        ];
    }
}
