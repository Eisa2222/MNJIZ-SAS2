<?php

namespace App\Services\OrganizationCenter\Tasks\TaskStep;

use App\Enums\OrganizationCenter\Tasks\Task\TaskStatus;
use App\Enums\OrganizationCenter\Tasks\TaskStep\TaskStepStatus;
use App\Jobs\OrganizationCenter\Tasks\StepTask\Email\SendStepCompletionEmailJob;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use App\Notifications\StepCompletedNotification;
use App\Services\Notifications\Tasks\TaskNotificationService;
use App\Services\Notifications\Tasks\TaskStepNotificationService;
use App\Services\OrganizationCenter\Tasks\TaskEvent\TaskEventService;
use App\Services\OrganizationCenter\Tasks\TaskStep\Helper\TaskStepsCompleteFormatterService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskStepService
{
    public function __construct(
        protected TaskStepNotificationService           $notificationService,
        protected TaskNotificationService               $taskNotificationService,

        protected TaskEventService                      $eventService,
        protected TaskStepsCompleteFormatterService     $TaskStepsCompleteFormatterService

    ) {}

    public function createSteps(Task $task, array $steps)
    {
        return DB::transaction(function () use ($task, $steps) {
            $stepOrder = 1; // عداد منفصل للترقيم الصحيح
            $isFirstStep = true; // متتبع للخطوة الأولى الفعلية

            foreach ($steps as $stepData) {
                if (empty($stepData['name'])) {
                    continue; // تخطي الخطوات الفارغة دون زيادة العداد
                }

                // إنشاء الخطوة
                $step = $task->steps()->create([
                    'name'              => $stepData['name'],
                    'step_order'        => $stepOrder,
                    'needs_approval'    => $stepData['needs_approval'] ?? false,
                    'status'            => $isFirstStep ? 'in_progress' : 'pending',
                    'step_start_date'   => $isFirstStep ? now() : null,
                    'created_by'        => auth()->id(),
                ]);

                // ربط المستخدمين المكلفين بالخطوة
                if (!empty($stepData['assigned_user_ids'])) {
                    $step->assignedUsers()->sync($stepData['assigned_user_ids']);
                }

                // تسجيل حدث إنشاء الخطوة
                $this->eventService->recordStepEvent($step, 'create', 'تم إضافة الخطوة');

                // إرسال الإشعار للخطوة الأولى فقط
                if ($isFirstStep) {
                    $this->notificationService->stepCreated($step);
                    $isFirstStep = false; // بعد الخطوة الأولى، باقي الخطوات لن تكون "أولى"
                }

                $stepOrder++; // زيادة العداد فقط للخطوات المضافة فعلياً
            }
        });
    }


    public function updateTaskSteps(Task $task, array $steps)
    {
        $existingStepIds = [];
        $maxOrder = $task->steps()->max('step_order') ?? 0;

        foreach ($steps as $index => $stepData) {
            $stepIndex = $index;

            // البحث عن الخطوة الموجودة بنفس الترتيب
            $existingStep = $task->steps()->where('step_order', $stepIndex)->first();

            if ($existingStep) {
                Log::error('if');

                $this->updateExistingStep($existingStep, $stepData);
                $existingStepIds[] = $existingStep->id;
            } else {
                Log::error('else');

                // إنشاء خطوة جديدة
                $newStep = $this->createNewStep($task, $stepData, $stepIndex);
                $existingStepIds[] = $newStep->id;

                if ($stepIndex > $maxOrder) {
                    $maxOrder = $stepIndex;
                }
            }
        }

        // حذف الخطوات التي لم تعد موجودة
        $stepsToDelete = $task->steps()->whereNotIn('id', $existingStepIds)->get();

        foreach ($stepsToDelete as $stepToDelete) {
            // التحقق من إمكانية حذف الخطوة
            if ($this->canDeleteStep($stepToDelete)) {
                $stepToDelete->delete();
            }
        }

        // إعادة ترتيب الخطوات وتحديث حالاتها
        $this->reorderSteps($task);
        $this->updateStepsStatus($task);
        $this->updateTaskStatusBasedOnSteps($task);
    }


    /*
    |============================================================================
    |============================================================================
    |                           Praivate methods
    |============================================================================
    |============================================================================
    */
    private function updateExistingStep(TaskStep $step, array $stepData)
    {
        // تحديث البيانات الأساسية فقط إذا كانت الخطوة لم تكتمل بعد
        if ($step->status == TaskStepStatus::Pending) {
            $step->update([
                'name' => $stepData['name'],
                'needs_approval' => (bool) ($stepData['needs_approval'] ?? false),
            ]);

            // تحديث المستخدمين المكلفين
            if (!empty($stepData['assigned_user_ids'])) {
                $step->assignedUsers()->sync($stepData['assigned_user_ids']);
            }
        }
    }

    private function createNewStep(Task $task, array $stepData, int $stepOrder)
    {
        // تحديد حالة الخطوة الجديدة
        $prevStep = $task->steps()->where('step_order', $stepOrder - 1)->first();

        $status     = 'pending';
        $startDate  = null;

        // إذا لم تكن هناك خطوة سابقة أو كانت الخطوة السابقة مكتملة
        if (!$prevStep || in_array($prevStep->status, [TaskStepStatus::Completed, TaskStepStatus::Approved])) {
            $status     = 'in_progress';
            $startDate  = now();
        }

        $step = $task->steps()->create([
            'name'              => $stepData['name'],
            'needs_approval'    => (bool) ($stepData['needs_approval'] ?? false),
            'step_order'        => $stepOrder,
            'status'            => $status,
            'step_start_date'   => $startDate,
            'created_by'        => auth()->id(),
        ]);

        // ربط المستخدمين المكلفين
        if (!empty($stepData['assigned_user_ids'])) {
            $step->assignedUsers()->sync($stepData['assigned_user_ids']);
        }

        return $step;
    }

    private function canDeleteStep(TaskStep $step): bool
    {
        return !in_array($step->status, [TaskStepStatus::Completed, TaskStepStatus::Approved, TaskStepStatus::Rejected]);
    }

    private function reorderSteps(Task $task)
    {
        $steps = $task->steps()->orderBy('step_order')->get();

        foreach ($steps as $index => $step) {
            $newOrder = $index + 1;
            if ($step->step_order !== $newOrder) {
                $step->update(['step_order' => $newOrder]);
            }
        }
    }

    private function updateStepsStatus(Task $task)
    {
        $steps = $task->steps()->orderBy('step_order')->get();

        if ($steps->isEmpty()) {
            return;
        }

        foreach ($steps as $step) {
            // تخطي الخطوات المكتملة
            if (in_array($step->status, [
                TaskStepStatus::Completed,
                TaskStepStatus::Approved,
                TaskStepStatus::Rejected
            ])) {
                continue;
            }

            $prevStep = $steps->where('step_order', $step->step_order - 1)->first();

            $canStart = !$prevStep || in_array($prevStep->status, [
                TaskStepStatus::Completed,
                TaskStepStatus::Approved
            ]);

            if ($canStart && $step->status === TaskStepStatus::Pending) {
                $step->update([
                    'status' => TaskStepStatus::InProgress,
                    'step_start_date' => now()
                ]);
            } elseif (!$canStart && $step->status === TaskStepStatus::InProgress) {
                $step->update([
                    'status' => TaskStepStatus::Pending,
                    'step_start_date' => null
                ]);
            }
        }
    }

    private function updateTaskStatusBasedOnSteps(Task $task)
    {
        $totalSteps = $task->steps()->count();

        if ($totalSteps === 0) {
            $task->update([
                'status'            => 'in_progress',
                'task_start_date'   => $task->task_start_date ?? now()
            ]);
            return;
        }

        $completedSteps = $task->steps()
            ->whereIn('status', [
                TaskStepStatus::Completed,
                TaskStepStatus::Approved
            ])
            ->count();


        if ($completedSteps === $totalSteps) {
            // جميع الخطوات مكتملة
            $task->update([
                'status'                => TaskStatus::InProgress,
                'task_completion_date'  => now()
            ]);
        } else {
            $task->update([
                'status'            => TaskStatus::Pending,
                'task_start_date'   => null
            ]);
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                          Handel step task
    |============================================================================
    |============================================================================
    */
    public function toggleStepCompletion(TaskStep $step, bool $isChecked)
    {
        return DB::transaction(function () use ($step, $isChecked) {

            $taskId     = $step->task_id;
            $stepOrder  = $step->step_order;

            // جلب الخطوة السابقة والتالية
            $previousStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder - 1)
                ->first();

            $nextStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder + 1)
                ->first();

            // التحقق من القواعد
            if ($isChecked) {
                $validationResult = $this->validateStepCompletion($previousStep);
                if ($validationResult !== true) {
                    return $validationResult;
                }
            } else {
                $validationResult = $this->validateStepCancellation($nextStep);
                if ($validationResult !== true) {
                    return $validationResult; // إرجاع رسالة الخطأ
                }
            }

            // تحديث حالة الخطوة
            if ($isChecked) {
                $this->completeStep($step, $previousStep);
                $this->handleNextStep($nextStep, $step);
                $this->handleTaskStatusOnStepComplete($step->task);
            } else {
                $this->cancelStepCompletion($step);
                $this->handleNextStepOnCancel($nextStep);
                $this->handleTaskStatusOnStepCancel($step->task);
            }

            return [
                'success'   => true,
                'step'      => $step->fresh(),
                'message'   => $isChecked ? 'تم إكمال الخطوة بنجاح' : 'تم إلغاء إكمال الخطوة'
            ];
        });
    }

    // اعتماد أو رفض الخطوة
    public function toggleStepApproval(TaskStep $step, string $action, ?string $rejectReason = null)
    {
        return DB::transaction(function () use ($step, $action, $rejectReason) {

            $taskId         = $step->task_id;
            $stepOrder      = $step->step_order;

            // جلب الخطوة السابقة والتالية
            $previousStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder - 1)
                ->first();

            $nextStep = TaskStep::where('task_id', $taskId)
                ->where('step_order', $stepOrder + 1)
                ->first();

            // التحقق من القواعد
            $validationResult = $this->validateStepApproval($previousStep, $nextStep);
            if ($validationResult !== true) {
                return $validationResult;
            }

            if ($action === 'approve') {
                $this->approveStep($step, $previousStep);
                $this->handleNextStep($nextStep, $step);
                $this->handleTaskStatusOnStepComplete($step->task);
            } elseif ($action === 'reject') {
                $this->rejectStep($step, $rejectReason);
                $this->handleNextStepOnCancel($nextStep);
                $this->handleTaskStatusOnStepReject($step->task);
            }

            return [
                'success' => true,
                'step' => $step->fresh(),
                'message' => $action === 'approve' ? 'تم اعتماد الخطوة بنجاح' : 'تم رفض الخطوة'
            ];
        });
    }


    /*
    |============================================================================
    |============================================================================
    |                           Private
    |============================================================================
    |============================================================================
    */
    // التحقق من صحة إكمال الخطوة
    private function validateStepCompletion(?TaskStep $previousStep)
    {
        if ($previousStep) {
            if ($previousStep->status === TaskStepStatus::Rejected) {
                return [
                    'success' => false,
                    'message' => 'لا يمكن إكمال هذه الخطوة، لأن الخطوة السابقة قد تم رفضها.'
                ];
            }

            if (!in_array($previousStep->status, [TaskStepStatus::Completed, TaskStepStatus::Approved])) {
                return [
                    'success' => false,
                    'message' => 'لا يمكن إكمال هذه الخطوة، حيث أن الخطوة السابقة لم تكتمل بعد.'
                ];
            }
        }

        return true;
    }

    // التحقق من صحة إلغاء إكمال الخطوة
    private function validateStepCancellation(?TaskStep $nextStep)
    {
        if ($nextStep && in_array($nextStep->status, [TaskStepStatus::Completed, TaskStepStatus::Approved, TaskStepStatus::Rejected])) {
            return [
                'success' => false,
                'message' => 'لا يمكن إلغاء إكمال هذه الخطوة، لأن الخطوة التالية قد اكتملت أو تم اعتمادها أو رفضها.'
            ];
        }

        return true;
    }

    // التحقق من صحة اعتماد/رفض الخطوة
    private function validateStepApproval(?TaskStep $previousStep, ?TaskStep $nextStep)
    {
        if ($previousStep && $previousStep->status === TaskStepStatus::Rejected) {
            return [
                'success' => false,
                'message' => 'لا يمكن اعتماد او رفض هذه الخطوة، لأن الخطوة السابقة قد تم رفضها.'
            ];
        }

        if ($previousStep && !in_array($previousStep->status, [TaskStepStatus::Completed, TaskStepStatus::Approved])) {
            return [
                'success' => false,
                'message' => 'لا يمكن اعتماد أو رفض هذه الخطوة لأن الخطوة السابقة لم تكتمل بعد.'
            ];
        }

        if ($nextStep && in_array($nextStep->status, [TaskStepStatus::Approved, TaskStepStatus::Completed, TaskStepStatus::Rejected])) {
            return [
                'success' => false,
                'message' => 'لا يمكن تعديل هذه الخطوة لأن الخطوة التالية قد تم اعتمادها أو اكتمالها أو رفضها بالفعل.'
            ];
        }

        return true;
    }

    // إكمال الخطوة
    private function completeStep(TaskStep $step, ?TaskStep $previousStep)
    {
        if ($step->step_start_date === null) {
            $step->step_start_date = ($previousStep && $previousStep->step_end_date)
                ? $previousStep->step_end_date
                : now();
        }

        $step->step_end_date    = now();
        $step->status           = TaskStepStatus::Completed;
        $step->completed_by     = auth()->id();
        $step->save();

        // إرسال إشعارات
        $this->sendStepCompletionNotifications($step, 'اكمال');
    }

    // إلغاء إكمال الخطوة
    private function cancelStepCompletion(TaskStep $step)
    {
        $step->status           = TaskStepStatus::InProgress;
        $step->step_end_date    = null;
        $step->completed_by     = null;
        $step->save();
    }

    private function approveStep(TaskStep $step, ?TaskStep $previousStep)
    {
        if ($step->step_start_date === null) {
            $step->step_start_date = ($previousStep && $previousStep->step_end_date)
                ? $previousStep->step_end_date
                : now();
        }

        $step->status           = TaskStepStatus::Approved;
        $step->step_end_date    = now();
        $step->completed_by     = auth()->id();
        $step->reject_reason    = null;
        $step->save();

        // إرسال إشعارات
        $this->sendStepCompletionNotifications($step, 'اعتماد');
    }

    private function rejectStep(TaskStep $step, ?string $rejectReason)
    {
        $step->status           = TaskStepStatus::Rejected;
        $step->step_end_date    = now();
        $step->completed_by     = auth()->id();
        $step->reject_reason    = $rejectReason;
        $step->save();

        // إرسال إشعارات
        $this->sendStepCompletionNotifications($step, 'رفض');
    }

    // معالجة الخطوة التالية عند إكمال الخطوة الحالية
    private function handleNextStep(?TaskStep $nextStep, $step)
    {
        if ($nextStep) {
            $nextStep->step_start_date = now();
            $nextStep->status = TaskStepStatus::InProgress;
            $nextStep->save();

            // إرسال إشعارات للخطوة التالية
            $this->notificationService->stepCreated($nextStep);
        } else {
            // ارسال اشعار للمكلفين في المهمة الاساسية
            $this->taskNotificationService->taskCreated($step->task);
        }
    }

    // معالجة الخطوة التالية عند إلغاء إكمال الخطوة الحالية
    private function handleNextStepOnCancel(?TaskStep $nextStep)
    {
        if ($nextStep) {
            $nextStep->step_start_date = null;
            $nextStep->status = TaskStepStatus::Pending;
            $nextStep->save();
        }
    }

    // تحديث حالة المهمة عند إكمال خطوة
    private function handleTaskStatusOnStepComplete($task)
    {
        $hasNextStep = $task->steps()
            ->where('step_order', '>', 0)
            ->whereNotIn('status', [TaskStepStatus::Completed, TaskStepStatus::Approved])
            ->exists();

        if (!$hasNextStep) {
            // جميع الخطوات مكتملة - تفعيل المهمة
            $task->task_start_date  = now();
            $task->status           = TaskStatus::InProgress;
            $task->save();

            // إرسال إشعارات المهمة
            // $this->sendTaskNotifications($task);
        } else {
            // لا تزال هناك خطوات - تحديث تاريخ البداية فقط
            $task->task_start_date  = $task->task_start_date ?? now();
            $task->status           = TaskStatus::Pending;
            $task->save();
        }
    }

    // تحديث حالة المهمة عند إلغاء إكمال خطوة
    private function handleTaskStatusOnStepCancel($task)
    {
        $task->task_start_date  = null;
        $task->status           = TaskStatus::Pending;
        $task->save();
    }

    // تحديث حالة المهمة عند رفض خطوة
    private function handleTaskStatusOnStepReject($task)
    {
        // عند رفض أي خطوة، تعود المهمة للانتظار
        $task->task_start_date      = null;
        $task->status               = TaskStatus::Pending;
        $task->save();
    }


    // ارسال اشعار الاكمال
    private function sendStepCompletionNotifications(TaskStep $step, string $action = 'إكمال')
    {
        $stepData = $this->TaskStepsCompleteFormatterService->formatForMicrosoftEmail($step, $action);

        if ($step->task->createdBy && $step->task->createdBy->email) {
            SendStepCompletionEmailJob::dispatch($stepData, $step->task->createdBy->email);
            $step->task->createdBy->notify(new StepCompletedNotification($step));
        }
    }
}
