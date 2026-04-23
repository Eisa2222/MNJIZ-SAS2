<?php

namespace App\Data\OrganizationCenter\Tasks\Task;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TaskData
{
    public string  $taskName;
    public string  $priority;
    public ?string $description;
    public string  $taskField;

    public ?int $offerId;
    public ?int $contractId;
    public ?int $projectId;
    public ?int $lawsuitId;
    public ?int $sessionId;
    public ?int $powerOfAttorneyId;

    // المجالات
    public ?int $clearanceCertificateId;
    public ?int $advanceId;
    public ?int $rewardId;
    public ?int $deductionId;
    public ?int $contentManagementId;
    public ?int $custodyId;
    public ?int $leaveId;

    // تتعلق بالمبيعات
    public ?int    $marketingChannelId;
    public ?int    $detailedMarketingChannelId;
    public ?int    $customerId;
    public ?int    $socialMediaId;

    public string  $dueDate;
    public string  $dueTime;
    public ?string $taskStartDate;
    public ?string $taskEndDate;

    public array   $assignedUserIds   = [];   // مكلَّفو المهمة
    public array   $steps             = [];   // الخطوات الديناميكيّة
    public array   $attachments       = [];   // أسماء الملفات بعد الرفع

    public ?string $typeTask;



    public function __construct(array $data)
    {
        $dateTime = Carbon::parse($data['due_date']);

        $this->taskName     = $data['task_name'];
        $this->priority     = $data['priority']        ?? 'high';
        $this->description  = $data['description']     ?? null;
        $this->taskField    = $data['task_field'];

        $this->setFieldsByTaskField($data);

        // $this->offerId            = $data['offer_id']             ?? null;
        // $this->contractId         = $data['contract_id']          ?? null;
        // $this->projectId          = $data['project_id']           ?? null;
        // $this->lawsuitId          = $data['lawsuit_id']           ?? null;
        // $this->sessionId          = $data['session_id']           ?? null;
        // $this->powerOfAttorneyId  = $data['power_of_attorney_id'] ?? null;

        // $this->marketingChannelId            = isset($data['marketing_id']) ? (int) $data['marketing_id'] : null;
        // $this->detailedMarketingChannelId    = isset($data['detailed_marketing_channel_id']) ? (int) $data['detailed_marketing_channel_id'] : null;
        // $this->customerId                    = isset($data['customer_id']) ? (int) $data['customer_id'] : null;
        // $this->socialMediaId                 = isset($data['social_media_id']) ? (int) $data['social_media_id'] : null;

        $this->dueDate       = $dateTime->toDateString();   // 2025-07-17
        $this->dueTime       = $dateTime->toTimeString();   // 16:33:00
        $this->taskStartDate = $data['task_start_date'] ?? null;
        $this->taskEndDate   = $data['task_end_date']   ?? null;

        $this->assignedUserIds = array_map('intval', $data['assigned_user_ids'] ?? []);

        /* الخطوات */
        $this->steps = collect($data['steps'] ?? [])
            ->map(function ($step) {
                return [
                    'name'               => $step['name'],
                    'needs_approval'     => isset($step['needs_approval']) ? (bool)$step['needs_approval'] : false,
                    'assigned_user_ids'  => array_map('intval', $step['assigned_user_ids'] ?? []),
                ];
            })
            ->toArray();

        $this->attachments = $data['attachments'] ?? [];

        $this->typeTask = $data['type_task'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'task_name'             => $this->taskName,
            'priority'              => $this->priority,
            'description'           => $this->description,
            'task_field'            => $this->taskField,

            'offer_id'                  => $this->offerId,
            'contract_id'               => $this->contractId,
            'project_id'                => $this->projectId,
            'lawsuit_id'                => $this->lawsuitId,
            'session_id'                => $this->sessionId,
            'power_of_attorney_id'      => $this->powerOfAttorneyId,

            'clearance_certificate_id'  => $this->clearanceCertificateId,
            'advance_id'                => $this->advanceId,
            'reward_id'                 => $this->rewardId,
            'deduction_id'              => $this->deductionId,
            'content_management_id'     => $this->contentManagementId,
            'custody_id'                => $this->custodyId,
            'leave_id'                  => $this->leaveId,

            'marketing_id'                   => $this->marketingChannelId,
            'detailed_marketing_channel_id'  => $this->detailedMarketingChannelId,
            'customer_id'                    => $this->customerId,
            'social_media_id'                => $this->socialMediaId,

            'due_date'              => $this->dueDate,
            'due_time'              => $this->dueTime,
            'task_start_date'       => $this->taskStartDate,
            'task_end_date'         => $this->taskEndDate,

            'type_task'             => $this->typeTask,


            'assigned_user_ids'     => $this->assignedUserIds,
            'steps'                 => $this->steps,
            'attachments'           => $this->attachments,
        ];
    }


    private function setFieldsByTaskField(array $data): void
    {
        $this->offerId                      = null;
        $this->contractId                   = null;
        $this->projectId                    = null;
        $this->lawsuitId                    = null;
        $this->sessionId                    = null;
        $this->powerOfAttorneyId            = null;
        $this->marketingChannelId           = null;
        $this->detailedMarketingChannelId   = null;
        $this->customerId                   = null;
        $this->socialMediaId                = null;

        $this->clearanceCertificateId       = null;
        $this->advanceId                    = null;
        $this->rewardId                     = null;
        $this->deductionId                  = null;
        $this->contentManagementId          = null;
        $this->custodyId                    = null;
        $this->leaveId                      = null;

        // تحديد الحقول المسموح بها حسب مجال المهمة
        switch ($this->taskField) {
            case 'offer':
                $this->offerId = isset($data['offer_id']) ? (int) $data['offer_id'] : null;
                break;


            case 'contract':
                $this->contractId = isset($data['contract_id']) ? (int) $data['contract_id'] : null;
                break;

            case 'projects':
                $this->projectId = isset($data['project_id']) ? (int) $data['project_id'] : null;
                break;

            case 'sessions':
                $this->sessionId = isset($data['session_id']) ? (int) $data['session_id'] : null;
                break;

            case 'clearance_certificate':
                $this->clearanceCertificateId = isset($data['clearance_certificate_id']) ? (int) $data['clearance_certificate_id'] : null;
                break;

            case 'advance':
                $this->advanceId = isset($data['advance_id']) ? (int) $data['advance_id'] : null;
                break;

            case 'reward':
                $this->rewardId = isset($data['reward_id']) ? (int) $data['reward_id'] : null;
                break;

            case 'deduction':
                $this->deductionId = isset($data['deduction_id']) ? (int) $data['deduction_id'] : null;
                break;

            case 'content':
                $this->contentManagementId = isset($data['content_management_id']) ? (int) $data['content_management_id'] : null;
                break;

            case 'custody':
                $this->custodyId = isset($data['custody_id']) ? (int) $data['custody_id'] : null;
                break;

            case 'leave':
                $this->leaveId = isset($data['leave_id']) ? (int) $data['leave_id'] : null;
                break;


            case 'sales':
                $this->marketingChannelId = isset($data['marketing_id']) ? (int) $data['marketing_id'] : null;

                // تحديد الحقول الفرعية حسب قناة التسويق المختارة
                if ($this->marketingChannelId) {
                    switch ($this->marketingChannelId) {
                        case 2: // الموارد البشرية
                            $this->detailedMarketingChannelId = isset($data['detailed_marketing_channel_id'])
                                ? (int) $data['detailed_marketing_channel_id'] : null;
                            break;

                        case 3: // العملاء
                            $this->customerId = isset($data['customer_id']) ? (int) $data['customer_id'] : null;
                            break;

                        case 6: // مواقع التواصل الاجتماعي
                            $this->socialMediaId = isset($data['social_media_id']) ? (int) $data['social_media_id'] : null;
                            break;
                    }
                }
                break;


            default:
                break;
        }
    }
}
