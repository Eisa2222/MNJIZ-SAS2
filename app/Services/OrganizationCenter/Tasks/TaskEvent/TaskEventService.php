<?php
namespace App\Services\OrganizationCenter\Tasks\TaskEvent;


use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\OrganizationCenter\Tasks\Task\TaskEvent;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStepEvent;

class TaskEventService
{
    public function recordTaskEvent(Task $task, string $eventType, string $message): void
    {
        TaskEvent::create([
            'task_id'    => $task->id,
            'user_id'    => auth()->id(),
            'event_type' => $eventType,
            'message'    => $message,
        ]);
    }

    public function recordStepEvent(TaskStep $step, string $eventType, string $message, ?string $rejectReason = null): void
    {
        TaskStepEvent::create([
            'task_step_id' => $step->id,
            'user_id'      => auth()->id(),
            'event_type'   => $eventType,
            'message'      => $message,
            'reject_reason' => $rejectReason,
        ]);
    }
}
