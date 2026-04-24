<?php

namespace App\Jobs\OrganizationCenter\Tasks\StepTask;


use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Services\EmailService;
use App\Services\Microsoft\ToDo\ToDoService;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SyncStepsTaskWithMicrosoftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

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
        $this->captureTenant();
    }

    public function handle(MicrosoftGraphBaseService $graphService, ToDoService $toDoService)
    {
        Log::error(' تنفيذ الوظيفة SyncStepsTaskWithMicrosoftJob: ');

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
    |--------------------------------------------------------------------------
    | process Individual User
    |--------------------------------------------------------------------------
    | للتعامل مع كل مستخدم بشكل منفصل
    */
    private function processIndividualUser(
        TaskService $taskService,
        MicrosoftGraphBaseService $graphService,
        $accessToken,
        $assignedUser
    ) {
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
            $taskService,
            $assignedUser['email'],
            $userId,
            $emailToTaskAssigneeIdMap
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Access Token
    |--------------------------------------------------------------------------
    | استرداد رمز الوصول المطلوب من Microsoft Graph API.
    */
    private function getAccessToken(MicrosoftGraphBaseService $graphService)
    {
        $accessToken = $graphService->getAppAccessToken();
        if (!$accessToken) {
            Log::error('فشل في الحصول على رمز الوصول.');
        }
        return $accessToken;
    }

    /*
    |--------------------------------------------------------------------------
    | Map Email to Task Assignee
    |--------------------------------------------------------------------------
    | ربط عنوان البريد الإلكتروني بمعرف وأسم المعين للمهمة.
    */
    private function mapEmailToTaskAssignee($data)
    {
        $emailToTaskAssigneeIdMap = [];


        // تحقق من أن البيانات ليست فارغة لتجنب الأخطاء
        if (!empty($data['email'])) {
            $emailToTaskAssigneeIdMap[$data['email']] = [
                'id' => $data['id'],
                'name' => $data['name']
            ];
        }

        return $emailToTaskAssigneeIdMap;
    }

    /*
    |--------------------------------------------------------------------------
    | Get User ID from Email
    |--------------------------------------------------------------------------
    | تحويل عنوان البريد الإلكتروني إلى معرف مستخدم باستخدام Microsoft Graph.
    */
    private function getUserIdFromEmail(MicrosoftGraphBaseService $graphService, $accessToken, $email)
    {
        return $graphService->getUserIdByEmail($accessToken, $email);
    }

    /*
    |--------------------------------------------------------------------------
    | Process User Task and Event
    |--------------------------------------------------------------------------
    | معالجة مهمة وإضافة الحدث للمستخدم.
    */
    private function processUserTaskAndEvent(TaskService $taskService, $email, $userId, $emailToTaskAssigneeIdMap)
    {
        $taskAssignee = $emailToTaskAssigneeIdMap[$email] ?? null;
        if (!$taskAssignee) {
            Log::error("لم يتم العثور على معرف المعين للمهمة للبريد الإلكتروني: {$email}");
            return;
        }

        // إعداد بيانات المهمة
        $taskData = $this->prepareTaskData();

        // إضافة المهمة للمستخدم
        $this->addTaskToUser($taskService, $userId, $taskData, $taskAssignee);

        // إضافة الحدث إلى تقويم المستخدم
        $this->addEventToUser($taskService, $userId, $taskData, $taskAssignee);

        // إرسال إشعار إلى المستخدم عبر البريد الإلكتروني
        $this->sendNotificationToUserByEmail($email, $taskData);
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare Task Data
    |--------------------------------------------------------------------------
    | تجهيز بيانات المهمة المطلوبة.
    */
    private function prepareTaskData()
    {
        return [
            'step_id'       => $this->taskData['step_id'],
            'task_id'       => $this->taskData['task_id'],
            'task_name'     => $this->taskData['step_name'],
            'body'          => $this->taskData['description'] ?? null,
            'task_priority' => $this->taskData['task_priority'],
            'startDateTime' => $this->taskData['endDateTime'],  // هنا لانه سنستخدم نفس تاريخ النهاية للبداية
            'endDateTime'   => $this->taskData['endDateTime'],
            'addDateTime'   => $this->taskData['addDateTime'] ?? null,
            'showEndDate'   => $this->taskData['showEndDate'] ?? true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Add Task to User
    |--------------------------------------------------------------------------
    | إضافة المهمة إلى قائمة To-Do الخاصة بالمستخدم.
    */
    private function addTaskToUser(TaskService $taskService, $userId, $taskData, $taskAssignee)
    {
        $response = $taskService->addTaskToUser($userId, $taskData, $this->officeName);

        if (isset($response['error'])) {
            // Log::error("خطأ في إضافة المهمة للمستخدم {$userId}: ", $response);
            return;
        }

        $graphTaskId = $response['task']->getId();
        $graphListId = $response['list_id'];

        if ($graphTaskId && $graphListId) {
            $task = TaskStep::find($taskData['step_id']);
            // تحديث سجل العلاقة في جدول pivot "task_user" للمستخدم المعين الحالي
            $task->assignedUsers()->updateExistingPivot($taskAssignee['id'], [
                'graph_task_id' => $graphTaskId,
                'graph_list_id' => $graphListId,
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Add Event to User
    |--------------------------------------------------------------------------
    | إضافة الحدث إلى تقويم المستخدم.
    */
    private function addEventToUser(TaskService $taskService, $userId, $taskData, $taskAssignee)
    {
        $eventResponse = $taskService->addCalendarEventToUser($userId, $taskData, $taskAssignee['name']);

        if (is_array($eventResponse) && isset($eventResponse['error'])) {
            // Log::error("خطأ في إضافة الحدث للمستخدم {$userId}: ", $eventResponse);
            return;
        }

        // Log::info("تم إضافة الحدث للمستخدم: {$taskAssignee['name']}");

        $graphEventId = $eventResponse->getId();
        if ($graphEventId) {
            $task = Task::find($taskData['step_id']);
            if ($task) {
                $task->assignedUsers()->updateExistingPivot($taskAssignee['id'], [
                    'graph_event_id' => $graphEventId,
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Send Notification to User
    |--------------------------------------------------------------------------
    | إرسال إشعار للمستخدم بالبريد الإلكتروني عند إضافة المهمة والحدث.
    */
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
                // Log::warning("لم يتم العثور على المستخدم بالبريد الإلكتروني: {$email} للإشعار.");
            }
        } catch (\Exception $e) {
            Log::error("فشل إرسال الإشعار للمستخدم {$email}: " . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Job Failure
    |--------------------------------------------------------------------------
    | التعامل مع الأخطاء عند فشل الوظيفة.
    */
    public function failed(\Throwable $exception)
    {
        Log::error('فشل تنفيذ الوظيفة SyncTaskWithMicrosoftJob: ' . $exception->getMessage());
    }
}
