<?php

namespace App\Services\OrganizationCenter\Tasks\Task;


use App\Data\OrganizationCenter\Tasks\Task\TaskData;
use App\Enums\OrganizationCenter\Tasks\Task\TaskField;
use App\Enums\OrganizationCenter\Tasks\Task\TaskRoutingAction;
use App\Enums\OrganizationCenter\Tasks\Task\TaskStatus;
use App\Enums\OrganizationCenter\Tasks\TaskStep\TaskStepStatus;
use App\Jobs\OrganizationCenter\Tasks\Task\Email\SendTaskCompletionEmailJob;
use App\Jobs\OrganizationCenter\Tasks\Task\Email\SendTaskReturnEmailJob;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\OrganizationCenter\Tasks\Task\TaskRouting;
use App\Notifications\TaskCompletedNotification;
use App\Services\Notifications\Tasks\TaskNotificationService;
use App\Services\OrganizationCenter\Tasks\Task\Helper\TaskCompleteFormatterService;
use App\Services\OrganizationCenter\Tasks\TaskEvent\TaskEventService;
use App\Services\OrganizationCenter\Tasks\TaskStep\TaskStepService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    public function __construct(
        protected TaskStepService                   $stepService,
        protected TaskEventService                  $eventService,
        protected TaskNotificationService           $notificationService,
        protected TaskCompleteFormatterService      $TaskCompleteFormatterService
    ) {}


    // Task without steps or attachments
    public function createApprovalTask($flowType, $model, $userId)
    {
        return DB::transaction(function () use ($flowType, $model, $userId) {

            $formatedData = $this->formatTaskData($flowType, $model, $userId);

            $data = new TaskData($formatedData);

            $task = Task::create($data->toArray() + [
                'status' => TaskStatus::InProgress
            ]);

            if (!empty($data->assignedUserIds ?? [])) {
                $task->assignedUsers()->sync($data->assignedUserIds);
            }

            $this->eventService->recordTaskEvent($task, 'create', 'تم تقديم الطلب بواسطة');

            $this->notificationService->taskCreated($task);

            return $task;
        });
    }



    public function create(TaskData $dto)
    {
        return DB::transaction(function () use ($dto) {

            $taskData = $dto->toArray();

            if (!empty($dto->steps)) {
                $taskData['status']             = 'pending';
                $taskData['task_start_date']    = null;
            } else {
                $taskData['status']             = 'in_progress';
                $taskData['task_start_date']    = now();
            }

            $taskData['created_by'] = auth()->id();

            // إنشاء المهمة
            $task = Task::create($taskData);


            if (!empty($dto->assignedUserIds ?? [])) {
                $task->assignedUsers()->sync($dto->assignedUserIds);
            }

            // معالجة المرفقات
            if (!empty($dto->attachments ?? [])) {
                $this->handleAttachments($task, $dto->attachments);
            }

            $this->eventService->recordTaskEvent($task, 'create', 'تم إضافة المهمة');

            // التعامل مع الخطوات ان وجدت
            if (!empty($dto->steps)) {
                $this->stepService->createSteps($task, $dto->steps);
            } else {
                $this->notificationService->taskCreated($task);
            }


            return $task;
        });
    }

    public function update(Task $task, TaskData $dto): Task
    {
        return DB::transaction(function () use ($task, $dto) {

            $oldAssignedUsers = $task->assignedUsers->pluck('id')->toArray();

            $taskData = $dto->toArray();

            if (!empty($dto->steps)) {
                // إذا كانت هناك خطوات، تحقق من الحالة الحالية
                $hasIncompleteSteps = $task->steps()
                    ->whereNotIn('status', [
                        TaskStepStatus::Completed,
                        TaskStepStatus::Approved
                    ])
                    ->exists();

                if ($hasIncompleteSteps) {
                    $taskData['status']          = 'pending';
                    $taskData['task_start_date'] = $task->task_start_date ?? null;
                }
            } else {
                $taskData['status']          = 'in_progress';
                $taskData['task_start_date'] = $task->task_start_date ?? now();
            }



            $taskData['updated_by'] = auth()->id();

            $task->update($taskData);

            if (!empty($dto->assignedUserIds ?? [])) {
                $task->assignedUsers()->sync($dto->assignedUserIds);

                // إرسال إشعارات للمستخدمين الجدد فقط
                // $newAssignedUsers = array_diff($dto->assignedUserIds, $oldAssignedUsers);

                // if (!empty($newAssignedUsers)) {
                //     $this->notificationService->taskAssignedToNewUsers($task, $newAssignedUsers);
                // }
            }

            if (!empty($dto->attachments ?? [])) {
                $this->handleAttachments($task, $dto->attachments);
            }

            // التعامل مع الخطوات ان وجدت
            if (!empty($dto->steps)) {
                $this->stepService->updateTaskSteps($task, $dto->steps);
            } else {
                $task->steps()->delete();
                $this->notificationService->taskCreated($task);
            }

            $this->eventService->recordTaskEvent($task, 'update', 'تم تحديث المهمة');


            return $task;
        });
    }

    public function destroy(Task $task): bool
    {
        return DB::transaction(function () use ($task) {

            $this->eventService->recordTaskEvent($task, 'delete', 'تم حذف المهمة');

            return $task->delete();
        });
    }

    public function returnTask(Task $task, string $returnReason): array
    {
        return DB::transaction(function () use ($task, $returnReason) {
            // التحقق من الصلاحيات
            if (!$task->canReturn()) {
                throw new \Exception('لا يمكنك إرجاع هذه المهمة.');
            }

            if ($task->status == TaskStatus::Completed) {
                throw new \Exception('عفواً هذه المهمة مكتملة لا يمكن إرجاعها.');
            }

            $routingDate = TaskRouting::create([
                'task_id'      => $task->id,
                'from_user_id' => auth()->id(),
                'action'       => TaskRoutingAction::Return,
                'reason'       => $returnReason,
            ]);

            $task->update([
                'status'            => 'returned',
            ]);

            // تسجيل الحدث
            $this->eventService->recordTaskEvent($task, 'return', 'تم إرجاع المهمة.');

            $this->sendTaskReturnNotifications($task, $returnReason, $routingDate->created_at);

            return [
                'success'   => true,
                'message'   => 'تم إرجاع المهمة بنجاح.',
                'task'      => $task
            ];
        });
    }

    public function reassignTask(Task $task, string $note): array
    {
        return DB::transaction(function () use ($task, $note) {
            // التحقق من أن المهمة مُرجعة
            if ($task->status !== TaskStatus::Returned) {
                throw new \Exception('المهمة ليست في حالة إرجاع.');
            }

            // التحقق من أن المستخدم يمكنه الرد على الإرجاع
            if (!$task->canRespondToReturn()) {
                throw new \Exception('لا يمكنك الرد على إرجاع هذه المهمة.');
            }


            // إنشاء سجل routing جديد
            TaskRouting::create([
                'task_id'      => $task->id,
                'from_user_id' => auth()->id(),
                'action'       => TaskRoutingAction::Assign,
                'reason'       => $note,
            ]);

            // تحديث حالة المهمة
            $task->update([
                'status' => TaskStatus::Pending,
                'return_reason' => null, // مسح سبب الإرجاع السابق
            ]);

            // تسجيل الحدث
            $this->eventService->recordTaskEvent($task, 'reassign', 'تم إعادة إسناد المهمة.');


            // إرسال إشعار للمكلفين (اختياري)
            $this->notificationService->taskReassigned($task, $note);


            return [
                'success' => true,
                'message' => 'تم إعادة إسناد المهمة بنجاح.',
                'task' => $task
            ];
        });
    }

    public function toggleTaskCompletion(Task $task, $status)
    {
        return DB::transaction(function () use ($task, $status) {

            if (is_string($status)) {
                $status = TaskStatus::from($status);
            }

            if ($status == TaskStatus::Completed) {
                // تحقق من عدم وجود خطوات غير مكتملة أو غير معتمدة في المهمة
                $incompleteSteps = $task->steps()
                    ->whereNotIn('status', [TaskStepStatus::Completed, TaskStepStatus::Approved])
                    ->count();

                if ($incompleteSteps > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن إكمال المهمة، توجد خطوات غير مكتملة.'
                    ], 400);
                }

                $task->task_end_date    = now();
                $task->completed_by     = auth()->id();
                $task->status           = TaskStatus::Completed;

                // إرسال إشعارات الإكمال
                $this->sendTaskCompletionNotifications($task);
            } else {
                $task->task_end_date    = null;
                $task->completed_by     = null;
                $task->status           = TaskStatus::CancelCompletion;
            }

            $task->save();

            $this->eventService->recordTaskEvent(
                $task,
                $task->status->value,
                $task->status === TaskStatus::Completed ? 'تم إكمال المهمة.' : 'تم إلغاء إكمال المهمة.'
            );

            return $task;
        });
    }


    /*
    |============================================================================
    |============================================================================
    |                               private
    |============================================================================
    |============================================================================
    */
    private function sendTaskCompletionNotifications(Task $task)
    {
        $taskData = $this->TaskCompleteFormatterService->formatForMicrosoftEmail($task, 'بإكمال المهمة');

        if ($task->createdBy && $task->createdBy->email) {
            SendTaskCompletionEmailJob::dispatch($taskData, $task->createdBy->email);
            $task->createdBy->notify(new TaskCompletedNotification($task));
        }
    }

    private function sendTaskReturnNotifications(Task $task, $reason, $createdAt)
    {
        $taskData = $this->TaskCompleteFormatterService->formatForMicrosoftEmail($task, 'بإرجاع المهمة', $reason, $createdAt);

        if ($task->createdBy && $task->createdBy->email) {
            SendTaskReturnEmailJob::dispatch($taskData, $task->createdBy->email);
            // $task->createdBy->notify(new TaskCompletedNotification($task));
        }
    }

    private function handleAttachments(Task $task, array $attachments)
    {
        foreach ($attachments as $attachmentFile) {
            if ($attachmentFile instanceof UploadedFile) {
                $attachmentPath = $attachmentFile->store('task_attachments', 'public');

                $task->attachments()->create([
                    'task_id'   => $task->id,
                    'file_path' => $attachmentPath,
                    'file_name' => $attachmentFile->getClientOriginalName(),
                    'file_type' => $attachmentFile->getClientMimeType(),
                    'file_size' => $attachmentFile->getSize(),
                ]);
            }
        }
    }


    private function formatTaskData($flowType, $model, $userId)
    {
        if (is_string($flowType)) {
            $flowType = TaskField::from($flowType);
        }

        $baseTaskData = [
            'task_name'         => $this->generateTaskTitle($flowType, $model),
            'description'       => $this->generateTaskDescription($flowType, $model, $this->generateTaskTitle($flowType, $model)),
            'task_field'        => $flowType->value,
            'assigned_user_ids' => Arr::wrap($userId),
            'task_start_date'   => now(),
            'due_date'          => now(),
        ];


        return match ($flowType) {
            TaskField::Offers               => $this->formatOffersTaskData($baseTaskData, $model),
            TaskField::Contracts            => $this->formatContractsTaskData($baseTaskData, $model),
            TaskField::Projects             => $this->formatProjectsTaskData($baseTaskData, $model),
            // TaskField::Lawsuits             => $this->formatLawsuitsTaskData($baseTaskData, $model),
            TaskField::Sessions             => $this->formatSessionsTaskData($baseTaskData, $model),
            // TaskField::PowerOfAttorney      => $this->formatPowerOfAttorneyTaskData($baseTaskData, $model),
            // TaskField::Marketing            => $this->formatMarketingTaskData($baseTaskData, $model),
            // TaskField::Renewals             => $this->formatRenewalsTaskData($baseTaskData, $model),
            // TaskField::Sales                => $this->formatSalesTaskData($baseTaskData, $model),
            TaskField::ClearanceCertificate => $this->formatClearanceCertificateTaskData($baseTaskData, $model),
            TaskField::Advance              => $this->formatAdvanceTaskData($baseTaskData, $model),
            TaskField::Reward               => $this->formatRewardTaskData($baseTaskData, $model),
            TaskField::Deduction            => $this->formatDeductionTaskData($baseTaskData, $model),
            TaskField::ContentManagement    => $this->formatContentManagementTaskData($baseTaskData, $model),
            TaskField::Custody              => $this->formatCustodyTaskData($baseTaskData, $model),
            TaskField::Leave                => $this->formatLeaveTaskData($baseTaskData, $model),
            TaskField::WPS                  => $this->formatWPSTaskData($baseTaskData, $model),

            // TaskField::Other                => $this->formatOtherTaskData($baseTaskData, $model),
            default => $baseTaskData,
        };
    }


    private function generateTaskTitle(TaskField $flowType, $model): string
    {
        $fieldLabel = $flowType->label();

        return match ($flowType) {
            TaskField::Lawsuits                 => "متابعة دعوى: " . ($model->case_number ?? $model->title ?? 'دعوى جديدة'),
            TaskField::PowerOfAttorney          => "معالجة وكالة: " . ($model->attorney_number ?? $model->title ?? 'وكالة جديدة'),
            TaskField::Marketing                => "حملة تسويقية: " . ($model->campaign_name ?? $model->title ?? 'حملة جديدة'),
            TaskField::Renewals                 => "تجديد: " . ($model->renewal_type ?? $model->title ?? 'تجديد جديد'),
            TaskField::Sales                    => "عملية بيع: " . ($model->sale_reference ?? $model->title ?? 'عملية بيع جديدة'),

            TaskField::Offers                   => " إعتماد عرض  " . $model->offer_name . "  رقم " . $model->offer_number,
            TaskField::Contracts                => " إعتماد عقد  " . $model->contract_name . "  رقم " . $model->contract_number,
            TaskField::Projects                 => " استكمال مشروع  (" . $model->project_name . ")  " .  "رقم  (" . $model->project_number . ")",
            TaskField::Sessions                 => "حضور  " . $model->session_name . " بتاريخ  " . $model->session_date->format('Y-m-d'),



            TaskField::ClearanceCertificate     => " إعتماد طلب شهادة إخلاء طرف  للموظف " . $model->user->name,
            TaskField::Advance                  => " إعتماد طلب سلفة للموظف " . $model->employee->raw_name,
            TaskField::Reward                   => " إعتماد طلب مكافأة  للموظف " . $model->employee->raw_name,
            TaskField::Deduction                => " إعتماد طلب خصم مالي من الموظف " . $model->employee->raw_name,
            TaskField::ContentManagement        => " إعتماد طلب نشر محتوى بواسطة الموظف " . $model->createdBy->raw_name,
            TaskField::Custody                  => $model->request_type->label() . "  للموظف " . $model->employee->raw_name,
            TaskField::Leave                    =>  " إعتماد طلب إجازة للموظف " . $model->employee->raw_name,
            TaskField::WPS                      =>  " إعتماد مسير الرواتب لشهر  " . $model->run_date->format("M"),

            default => "مهمة {$fieldLabel}: " . ($model->title ?? $model->name ?? 'عنصر جديد'),
        };
    }



    private function formatOffersTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'offer_id'   => $item->id,
        ]);
    }

    private function formatContractsTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'contract_id'   => $item->id,
        ]);
    }

    private function formatProjectsTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'project_id'   => $item->id,
        ]);
    }

    private function formatSessionsTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'session_id'   => $item->id,
        ]);
    }

    private function formatClearanceCertificateTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'clearance_certificate_id'   => $item->id,
        ]);
    }

    private function formatAdvanceTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'advance_id'   => $item->id,
        ]);
    }

    private function formatRewardTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'reward_id'   => $item->id,
        ]);
    }

    private function formatDeductionTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'deduction_id'   => $item->id,
        ]);
    }

    private function formatContentManagementTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'content_management_id'   => $item->id,
        ]);
    }

    private function formatCustodyTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'custody_id'   => $item->id,
        ]);
    }

    private function formatLeaveTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'leave_id'   => $item->id,
        ]);
    }

    private function formatWPSTaskData(array $baseData, $item): array
    {
        return array_merge($baseData, [
            'wps_id'   => $item->id,
        ]);
    }

    private function generateTaskDescription(TaskField $flowType, $item, $title): string
    {
        $baseDescription = $title;

        return match ($flowType) {
            TaskField::Lawsuits             => $baseDescription . "\n\nالمطلوب:\n• مراجعة ملف الدعوى\n• تحضير المرافعات\n• متابعة الإجراءات القانونية",
            TaskField::PowerOfAttorney      => $baseDescription . "\n\nالمطلوب:\n• مراجعة صحة الوكالة\n• التأكد من البيانات\n• إعداد الوثائق اللازمة",
            TaskField::Marketing            => $baseDescription . "\n\nالمطلوب:\n• تطوير استراتيجية التسويق\n• إعداد المحتوى المطلوب\n• تنفيذ الحملة",
            TaskField::Renewals             => $baseDescription . "\n\nالمطلوب:\n• مراجعة وثائق التجديد\n• التأكد من المتطلبات\n• إكمال إجراءات التجديد",
            TaskField::Sales                => $baseDescription . "\n\nالمطلوب:\n• مراجعة تفاصيل البيع\n• إعداد العقود اللازمة\n• إتمام الإجراءات",


            TaskField::Offers               => $baseDescription .  " - العميل :" . ($item->customer?->name ?? " غير محدد ")  .  " - مسؤول العلاقة :" . ($item->relationshipManager?->raw_name ?? " غير محدد ") . " العرض يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Contracts            => $baseDescription .  " - العميل :" . ($item->customer?->name ?? " غير محدد ")  .  " - مسؤول العقد :" . ($item->contractManager?->raw_name ?? " غير محدد ") . " العقد يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Projects             => $baseDescription .  " - تاريخ بدء المشروع  :" . ($item->start_date ?? " غير محدد ")  . "  يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Sessions             => $baseDescription .  " - وقت الجلسة: " . ($item->session_time->format('H:i')) . " الجلسة تحتاج لحضورك يرجى مراجعة التفاصيل والاستعداد للحضور في الموعد المحدد ",



            TaskField::ClearanceCertificate => $baseDescription . " - سبب إخلاء الطرف :" . $item->reason  . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Advance              => $baseDescription . " - قيمة السلفة :" . $item->amount  . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Reward               => $baseDescription . " - قيمة المكافأة :" . $item->amount . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Deduction            => $baseDescription . " - قيمة الخصم المالي :" . $item->amount . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::ContentManagement    => $baseDescription . " - نوع المحتوى :" . $item->contentType?->name . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Custody              => $baseDescription . " -  الاصل :" . $item->item->name . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::Leave                => $baseDescription . " -  نوع الإجازة :" . $item->leaveType->name . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",
            TaskField::WPS                  => $baseDescription . " -  اجمالي المسير  :" . $item->total_net . " الطلب يحتاج لإعتمادك يرجي مراجعة التفاصيل واتخاذ الاجراءات اللازمة ",

            default => $baseDescription . "\n\nيرجى مراجعة التفاصيل واتخاذ الإجراءات اللازمة.",
        };
    }
}
