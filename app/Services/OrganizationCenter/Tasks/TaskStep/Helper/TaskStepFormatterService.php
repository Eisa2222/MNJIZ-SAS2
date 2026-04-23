<?php

namespace App\Services\OrganizationCenter\Tasks\TaskStep\Helper;


use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskStepFormatterService
{
    public function formatForMicrosoft(TaskStep $taskStep, string $messageType = 'create'): array
    {
        return [
            // 'step_id'       => $taskStep->id,
            'task_id'       => $taskStep->task->id,
            'task_name'     => $this->getTaskStepName($taskStep, $messageType),
            'description'   => $taskStep->task->description,
            'task_priority' => $taskStep->task->priority->label(),
            'endDateTime'   => $this->formatDateTime($taskStep),
            'addDateTime'   => now()->toIso8601String(),
            'showEndDate'   => true,
        ];
    }

    private function getTaskStepName(TaskStep $taskStep, string $messageType): string
    {
        $messageTemplate = $this->getDefaultMessage($messageType);

        return strtr($messageTemplate, [
            ':step_name' => $taskStep->name,
            ':task_name' => $taskStep->task->task_name,
            ':user'      => Auth::user()->name,
        ]);
    }

    private function getDefaultMessage(string $messageType): string
    {
        return match ($messageType) {
            'create'   => ':step_name - (خطوة في مهمة :task_name)',
            'update'   => 'تم تحديث الخطوة ":step_name" بواسطة :user في المهمة ":task_name".',
            'complete' => 'تم إكمال الخطوة ":step_name" بواسطة :user في المهمة ":task_name".',
            'delete'   => 'تم حذف الخطوة ":step_name" بواسطة :user من المهمة ":task_name".',
            default    => 'تم إضافة خطوة جديدة بعنوان ":step_name" بواسطة :user في المهمة ":task_name" وتم تعيينها إليك.'
        };
    }


    private function formatDateTime(TaskStep $taskStep): string
    {
        return Carbon::parse($taskStep->due_date . ' ' . $taskStep->due_time)
            ->setTimezone('Asia/Riyadh')
            ->toIso8601String();
    }
}
