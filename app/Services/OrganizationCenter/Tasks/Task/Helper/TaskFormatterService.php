<?php

namespace App\Services\OrganizationCenter\Tasks\Task\Helper;


use App\Models\OrganizationCenter\Tasks\Task\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskFormatterService
{

    public function formatForMicrosoft(Task $task, string $messageType = 'create', $note = null): array
    {
        return [
            'task_id'       => $task->id,
            'task_name'     => $this->getTaskName($task, $messageType),
            'task_priority' => $task->priority->label(),
            'description'   => $task->description,
            'endDateTime'   => $this->formatDateTime($task),
            'showEndDate'   => true,
            'note'          => $note ?? '',
        ];
    }


    private function getTaskName(Task $task, string $messageType): string
    {
        if (! Auth::check()) {
            $template = $this->getSystemMessage($messageType);
            return strtr($template, [
                ':title' => $task->task_name,
            ]);
        }

        $template = $this->getUserMessage($messageType);
        return strtr($template, [
            ':title' => $task->task_name,
            ':user'  => Auth::user()->name,
        ]);
    }


    private function getUserMessage(string $messageType): string
    {
        return match ($messageType) {
            'create'   => ':title',
            'update'   => 'تم تحديث المهمة ":title" بواسطة :user.',
            'assign'   => 'تم اعادة اسناد المهمة ":title" بواسطة :user.',
            'complete' => 'تم إكمال المهمة ":title" بواسطة :user.',
            'delete'   => 'تم حذف المهمة ":title" بواسطة :user.',
            default    => 'تم إضافة مهمة جديدة بعنوان ":title" بواسطة :user وتم تعيينها إليك.',
        };
    }

    private function getSystemMessage(string $messageType): string
    {
        return match ($messageType) {
            'create'   => '":title"',
            'update'   => '":title"',
            'complete' => '":title"',
            'delete'   => '":title"',
            default    => '":title"',
        };
    }




    private function formatDateTime(Task $task): string
    {
        return Carbon::parse($task->due_date . ' ' . $task->due_time)->setTimezone('Asia/Riyadh')->toIso8601String();
    }
}
