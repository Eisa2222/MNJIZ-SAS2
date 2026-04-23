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


    public string  $dueDate;
    public string  $dueTime;
    public ?string $taskStartDate;
    public ?string $taskEndDate;

    public array   $assignedUserIds   = [];   // مكلَّفو المهمة



    public function __construct(array $data)
    {
        $dateTime = Carbon::parse($data['due_date']);

        $this->taskName     = $data['task_name'];
        $this->priority     = $data['priority']        ?? 'high';
        $this->description  = $data['description']     ?? null;
        $this->taskField    = $data['task_field'];

        $this->setFieldsByTaskField($data);

        $this->offerId            = $data['offer_id']             ?? null;
        $this->contractId         = $data['contract_id']          ?? null;
        $this->projectId          = $data['project_id']           ?? null;
        $this->lawsuitId          = $data['lawsuit_id']           ?? null;
        $this->sessionId          = $data['session_id']           ?? null;
        $this->powerOfAttorneyId  = $data['power_of_attorney_id'] ?? null;

        // $this->marketingChannelId            = isset($data['marketing_id']) ? (int) $data['marketing_id'] : null;
        // $this->detailedMarketingChannelId    = isset($data['detailed_marketing_channel_id']) ? (int) $data['detailed_marketing_channel_id'] : null;
        // $this->customerId                    = isset($data['customer_id']) ? (int) $data['customer_id'] : null;
        // $this->socialMediaId                 = isset($data['social_media_id']) ? (int) $data['social_media_id'] : null;

        $this->dueDate       = $dateTime->toDateString();   // 2025-07-17
        $this->dueTime       = $dateTime->toTimeString();   // 16:33:00
        $this->taskStartDate = $data['task_start_date'] ?? null;
        $this->taskEndDate   = $data['task_end_date']   ?? null;

        $this->assignedUserIds = array_map('intval', $data['assigned_user_ids'] ?? []);

    }

    public function toArray(): array
    {
        return [
            'task_name'             => $this->taskName,
            'priority'              => $this->priority,
            'description'           => $this->description,
            'task_field'            => $this->taskField,

            'offer_id'              => $this->offerId,
            'contract_id'           => $this->contractId,
            'project_id'            => $this->projectId,
            'lawsuit_id'            => $this->lawsuitId,
            'session_id'            => $this->sessionId,
            'power_of_attorney_id'  => $this->powerOfAttorneyId,

            'marketing_id'                   => $this->marketingChannelId,
            'detailed_marketing_channel_id'  => $this->detailedMarketingChannelId,
            'customer_id'                    => $this->customerId,
            'social_media_id'                => $this->socialMediaId,

            'due_date'              => $this->dueDate,
            'due_time'              => $this->dueTime,
            'task_start_date'       => $this->taskStartDate,
            'task_end_date'         => $this->taskEndDate,

            'assigned_user_ids'     => $this->assignedUserIds,
        ];
    }


    private function setFieldsByTaskField(array $data): void
    {
        // تهيئة جميع الحقول بـ null أولاً
        $this->offerId            = null;
        $this->contractId         = null;
        $this->projectId          = null;
        $this->lawsuitId          = null;
        $this->sessionId          = null;
        $this->powerOfAttorneyId  = null;

        $this->marketingChannelId         = null;
        $this->detailedMarketingChannelId = null;
        $this->customerId                 = null;
        $this->socialMediaId              = null;

        // تحديد الحقول المسموح بها حسب مجال المهمة
        switch ($this->taskField) {
            case 'projects':
                $this->projectId = isset($data['project_id']) ? (int) $data['project_id'] : null;
                break;

            case 'lawsuits':
                $this->lawsuitId = isset($data['lawsuit_id']) ? (int) $data['lawsuit_id'] : null;
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

            // case 'offers':
            //     $this->offerId = isset($data['offer_id']) ? (int) $data['offer_id'] : null;
            //     break;

            // case 'contracts':
            //     $this->contractId = isset($data['contract_id']) ? (int) $data['contract_id'] : null;
            //     break;

            // case 'sessions':
            //     $this->sessionId = isset($data['session_id']) ? (int) $data['session_id'] : null;
            //     break;

            // case 'power_of_attorney':
            //     $this->powerOfAttorneyId = isset($data['power_of_attorney_id']) ? (int) $data['power_of_attorney_id'] : null;
            //     break;

            // في حالة مجالات أخرى، تبقى جميع الحقول null
            default:
                break;
        }
    }

    // public static function getAllowedFieldsForTaskField(string $taskField): array
    // {
    //     $allowedFields = [
    //         'projects' => ['project_id'],
    //         'lawsuits' => ['lawsuit_id'],
    //         'sales' => [
    //             'marketing_id',
    //             'detailed_marketing_channel_id',
    //             'customer_id',
    //             'social_media_id'
    //         ],
    //         'offers' => ['offer_id'],
    //         'contracts' => ['contract_id'],
    //         'sessions' => ['session_id'],
    //         'power_of_attorney' => ['power_of_attorney_id'],
    //     ];

    //     return $allowedFields[$taskField] ?? [];
    // }
}
