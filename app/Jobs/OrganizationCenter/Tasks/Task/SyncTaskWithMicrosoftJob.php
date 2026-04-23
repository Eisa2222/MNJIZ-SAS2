<?php

namespace App\Jobs\OrganizationCenter\Tasks\Task;


use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\EmailService;
use App\Services\Microsoft\ToDo\ToDoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SyncTaskWithMicrosoftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignedUsers;
    protected $taskData;
    protected $officeName;
    protected $toDoService;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(Collection $assignedUsers, array $taskData, $officeName)
    {
        $this->assignedUsers    = $assignedUsers;
        $this->taskData         = $taskData;
        $this->officeName       = $officeName;
    }

    public function handle(MicrosoftGraphBaseService $graphService, ToDoService $toDoService)
    {
        Log::error(' تنفيذ الوظيفة SyncTaskWithMicrosoftJob: ');

        $this->toDoService = $toDoService;

        $accessToken = $this->getAccessToken($graphService);
        if (!$accessToken) return;

        $graphService->setGraphAccessToken($accessToken);

        // للتعامل مع كل مستخدم بشكل منفصل
        foreach ($this->assignedUsers as $assignedUser) {
            $this->processIndividualUser($graphService, $accessToken, $assignedUser);
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                            Private methods
    |============================================================================
    |============================================================================
    */
    private function processIndividualUser(MicrosoftGraphBaseService $graphService, $accessToken, $assignedUser)
    {
        // تحويل عنوان البريد الإلكتروني إلى معرف المستخدم
        $userId = $this->getUserIdFromEmail($graphService, $accessToken, $assignedUser['email']);

        if (!$userId) {
            Log::error('لم يتم العثور على معرف مستخدم صالح للبريد الإلكتروني المقدم: ' . $assignedUser['email']);
            return;
        }

        // إعداد ربط البريد الإلكتروني بمعلومات المعينين للمهام
        $emailToTaskAssigneeIdMap = $this->mapEmailToTaskAssignee($assignedUser);

        // معالجة المستخدم
        $this->processUserTaskAndEvent(
            $assignedUser['email'],
            $userId,
            $emailToTaskAssigneeIdMap
        );
    }

    private function getAccessToken(MicrosoftGraphBaseService $graphService)
    {
        $accessToken = $graphService->getAppAccessToken();
        if (!$accessToken) {
            Log::error('فشل في الحصول على رمز الوصول.');
        }
        return $accessToken;
    }

    private function mapEmailToTaskAssignee($data)
    {
        $emailToTaskAssigneeIdMap = [];

        if (!empty($data['email'])) {
            $emailToTaskAssigneeIdMap[$data['email']] = [
                'id'    => $data['id'],
                'name'  => $data['name']
            ];
        }

        return $emailToTaskAssigneeIdMap;
    }

    private function getUserIdFromEmail(MicrosoftGraphBaseService $graphService, $accessToken, $email)
    {
        return $graphService->getUserIdByEmail($accessToken, $email);
    }

    private function processUserTaskAndEvent($email, $userId, $emailToTaskAssigneeIdMap)
    {
        $taskAssignee = $emailToTaskAssigneeIdMap[$email] ?? null;
        if (!$taskAssignee) {
            Log::error("لم يتم العثور على معرف المعين للمهمة للبريد الإلكتروني: {$email}");
            return;
        }

        // إعداد بيانات المهمة
        $taskData = $this->prepareTaskData();

        // إضافة المهمة للمستخدم
        $this->addTaskToUser($userId, $taskData, $taskAssignee);

        // إضافة الحدث إلى تقويم المستخدم
        $this->addEventToUser($userId, $taskData, $taskAssignee);

        // إرسال إشعار إلى المستخدم عبر البريد الإلكتروني
        $this->sendNotificationToUserByEmail($email, $taskData);
    }

    private function prepareTaskData()
    {
        return [
            'task_id'       => $this->taskData['task_id'],
            'task_name'     => $this->taskData['task_name'],
            'body'          => $this->taskData['description'] ?? null,
            'task_priority' => $this->taskData['task_priority'],
            'startDateTime' => $this->taskData['endDateTime'],  // هنا لانه سنستخدم نفس تاريخ النهاية للبداية
            'endDateTime'   => $this->taskData['endDateTime'],
            'addDateTime'   => $this->taskData['addDateTime'] ?? null,
            'showEndDate'   => $this->taskData['showEndDate'] ?? true,
            'note'          => $this->taskData['note'] ?? null,

        ];
    }

    private function addTaskToUser($userId, $taskData, $taskAssignee)
    {
        $response = $this->toDoService->addTaskToUser($userId, $taskData, $this->officeName);

        if (isset($response['error'])) {
            Log::error("خطأ في إضافة المهمة للمستخدم {$userId}: ", $response);
            return;
        }

        $graphTaskId = $response['task']->getId();
        $graphListId = $response['list_id'];

        if ($graphTaskId && $graphListId) {
            $task = Task::find($taskData['task_id']);
            // تحديث سجل العلاقة في جدول pivot "task_user" للمستخدم المعين الحالي
            $task->assignedUsers()->updateExistingPivot($taskAssignee['id'], [
                'graph_task_id' => $graphTaskId,
                'graph_list_id' => $graphListId,
            ]);
        }
    }


    private function addEventToUser($userId, $taskData, $taskAssignee)
    {
        $eventResponse = $this->toDoService->addCalendarEventToUser($userId, $taskData, $taskAssignee['name']);

        if (is_array($eventResponse) && isset($eventResponse['error'])) {
            Log::error("خطأ في إضافة الحدث للمستخدم {$userId}: ", $eventResponse);
            return;
        }

        Log::info("تم إضافة الحدث للمستخدم: {$taskAssignee['name']}");

        $graphEventId = $eventResponse->getId();
        if ($graphEventId) {
            $task = Task::find($taskData['task_id']);
            if ($task) {
                $task->assignedUsers()->updateExistingPivot($taskAssignee['id'], [
                    'graph_event_id' => $graphEventId,
                ]);
            }
        }
    }

    private function sendNotificationToUserByEmail($email, $taskData)
    {
        try {
            $user = User::where('email', $email)->first();
            if ($user) {

                $userName = $user->name;
                $userEmail = $user->email;

                $emailService = app(EmailService::class);
                // استدعاء الدالة مع جميع المعطيات المطلوبة
                $emailService->sendTaskAlert($taskData, $userEmail, $userName, $this->officeName);
            } else {
                Log::warning("لم يتم العثور على المستخدم بالبريد الإلكتروني: {$email} للإشعار.");
            }
        } catch (\Exception $e) {
            Log::error("فشل إرسال الإشعار للمستخدم {$email}: " . $e->getMessage());
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('فشل تنفيذ الوظيفة SyncTaskWithMicrosoftJob: ' . $exception->getMessage());
    }
}
