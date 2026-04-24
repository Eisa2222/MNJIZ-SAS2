<?php

namespace App\Services\OrganizationCenter\Tasks\TaskStep\Helper;


use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;

class TaskStepsCompleteFormatterService
{

    public function formatForMicrosoftEmail(TaskStep $step, $action): array
    {

        return [
            'task_url'              => route('organization-center.tasks.show', $step->task->id),
            'title'                 => 'قام ' . auth()->user()->name . ' ب' . $action . ' الخطوة ',
            'user_name'             => $step->task->createdBy->name ?? '',
            'task_name'             => $step->task->task_name ?? '',
            'step_name'             => $step->name ?? '',
            'completion_date'       => $step->step_end_date ?? '',
            'duration'              => $step->duration ?? '',
            'office_name'           => Settings::current()->office_name ?? '',
            'reject_reason'         => $step->reject_reason ?? '',
        ];
    }
}
