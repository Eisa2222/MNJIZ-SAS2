<?php

namespace App\Services\Notifications\Tasks;

use App\Jobs\OrganizationCenter\Tasks\StepTask\SyncStepsTaskWithMicrosoftJob;
use App\Jobs\OrganizationCenter\Tasks\Task\SyncTaskWithMicrosoftJob;
use Illuminate\Support\Facades\Notification;
use App\Jobs\Tasks\Email\SendStepCompletionEmailJob;
use App\Notifications\TaskCreatedNotification;
use App\Notifications\StepCompletedNotification;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use App\Services\OrganizationCenter\Tasks\TaskStep\Helper\TaskStepFormatterService;

class TaskStepNotificationService
{
    protected ?string $officeName = null;

    public function __construct(private TaskStepFormatterService $formatter)
    {
        // Lazy: never query the `settings` table at construction time. The
        // DI container resolves this service during artisan boot (via
        // CheckContractPayments command) BEFORE migrate:fresh has a chance
        // to create the table. Accessing $this->getOfficeName() defers the
        // query to the moment it's actually needed.
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

    public function stepCreated(TaskStep $step): void
    {
        SyncTaskWithMicrosoftJob::dispatch(
            assignedUsers: $step->assignedUsers,
            taskData: $this->formatter->formatForMicrosoft($step, 'create'),
            officeName: $this->getOfficeName()
        );

        Notification::send($step->assignedUsers, new TaskCreatedNotification($step->task));
    }



    /** إشعار عند إكمال أو اعتماد خطوة */
    public function stepCompleted(TaskStep $step, ?TaskStep $nextStep = null): void
    {
        $creator = $step->task->createdBy;
        if ($creator && $creator->email) {
            SendStepCompletionEmailJob::dispatch(
                [
                    'task_url'        => route('organization-center.tasks.show', $step->task->id),
                    'title'           => auth()->user()->name . ' أكمل الخطوة',
                    'user_name'       => $creator->name,
                    'task_name'       => $step->task->task_name,
                    'step_name'       => $step->name,
                    'assignee_name'   => auth()->user()->name,
                    'completion_date' => $step->step_end_date,
                    'duration'        => $step->duration,
                    'office_name'     => $this->getOfficeName(),
                ],
                $creator->email
            );

            $creator->notify(new StepCompletedNotification($step));
        }

        // إذا هناك خطوة تالية، أرسل لها إشعار إنشاء
        if ($nextStep) {
            SyncStepsTaskWithMicrosoftJob::dispatch(
                assignedUsers: $nextStep->assignedUsers,
                taskData: $this->formatter->formatForMicrosoft($nextStep, 'create'),
                officeName: $this->getOfficeName()
            );
            Notification::send(
                $nextStep->assignedUsers,
                new TaskCreatedNotification($nextStep->task)
            );
        }
    }

    /** إشعار عند رفض خطوة */
    public function stepRejected(TaskStep $step): void
    {
        $creator = $step->task->createdBy;
        if ($creator && $creator->email) {
            SendStepCompletionEmailJob::dispatch(
                [
                    'task_url'        => route('organization-center.tasks.show', $step->task->id),
                    'title'           => auth()->user()->name . ' رفض الخطوة',
                    'user_name'       => $creator->name,
                    'task_name'       => $step->task->task_name,
                    'step_name'       => $step->name,
                    'assignee_name'   => auth()->user()->name,
                    'completion_date' => $step->step_end_date,
                    'duration'        => $step->duration,
                    'office_name'     => $this->getOfficeName(),
                    'reject_reason'   => $step->reject_reason,
                ],
                $creator->email
            );
            $creator->notify(new StepCompletedNotification($step));
        }
    }
}
