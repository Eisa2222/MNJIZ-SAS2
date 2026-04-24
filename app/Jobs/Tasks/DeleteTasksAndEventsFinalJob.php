<?php

namespace App\Jobs\Tasks;

use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\User;
use App\Services\MicrosoftGraphBaseService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteTasksAndEventsFinalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    protected $task;
    protected $previousAssignedUser;


    public $tries = 3; // عدد محاولات إعادة التشغيل
    public $timeout = 120; // وقت الانتظار بالثواني

    /**
     * إنشاء الوظيفة مع البيانات المطلوبة.
     *
     * @param \App\Models\Task\Task $task
     * @param array $updatedData
     * @param string $office_name
     * @return void
     */
    public function __construct(Task $task,$previousAssignedUser)
    {
        Log::error(" 8888888");

        $this->task = $task;
        $this->previousAssignedUser = $previousAssignedUser;
        $this->captureTenant();
    }

    /**
     * تنفيذ الوظيفة الرئيسية لتحديث المهمة والأحداث.
     *
     * @param \App\Services\TaskService $taskService
     * @param \App\Services\MicrosoftGraphBaseService $graphService
     * @param \App\Services\EmailService $emailService
     * @return void
     */
    public function handle(TaskService $taskService, MicrosoftGraphBaseService $graphService)
    {
        // الحصول على رمز الوصول من خدمة Microsoft Graph
        $accessToken = $this->getAccessToken($graphService);
        if (!$accessToken) return;


        Log::info("بدء حذف المهمة والأحداث للمهمة", ['task_id' => $this->task->id]);

        // إذا كان هناك مستخدم سابق، قم بحذف المهمة والحدث منه
        if ($this->previousAssignedUser) {
            $previousUserEmail = $this->previousAssignedUser->email;
            $previousUserId = $this->getUserIdFromEmail($graphService, $accessToken, $previousUserEmail);

            $this->removeTaskFromPreviousUser($taskService, $graphService, $previousUserId);
        }
    }


    /**
     * إزالة المهمة والحدث من المستخدم السابق.
     *
     * @param \App\Services\TaskService $taskService
     * @param \App\Services\MicrosoftGraphBaseService $graphService
     * @param int $previousUserId
     * @return void
     */
    private function removeTaskFromPreviousUser(TaskService $taskService, MicrosoftGraphBaseService $graphService, $previousUserId)
    {
        Log::info(" حذف المهمة والأحداث ");

        // الحصول على معلومات المستخدم السابق
        if (!$previousUserId) {
            Log::error("لم يتم العثور على المستخدم السابق بمعرف: {$previousUserId}");
            return;
        }

        // حذف المهمة من To-Do
        if ($this->task->graph_task_id && $this->task->graph_list_id) {
            Log::info(" حذف 111 والأحداث ");

            $deleteTaskResponse = $taskService->deleteTaskForUser($previousUserId, $this->task->graph_list_id, $this->task->graph_task_id);

            if (isset($deleteTaskResponse['error'])) {
                Log::error("خطأ في حذف المهمة من To-Do للمستخدم السابق {$previousUserId}: " . $deleteTaskResponse['error']);
            } else {
                Log::info("تم حذف المهمة من To-Do للمستخدم السابق {$previousUserId} بنجاح.");
            }
        }

        // حذف الحدث من التقويم
        if ($this->task->graph_event_id) {
            Log::info(" حذف 888 والأحداث ");

            $deleteEventResponse = $taskService->deleteCalendarEventInGraph($previousUserId, $this->task->graph_event_id);

            if (isset($deleteEventResponse['error'])) {
                Log::error("خطأ في حذف الحدث من التقويم للمستخدم السابق {$previousUserId}: " . $deleteEventResponse['error']);
            } else {
                Log::info("تم حذف الحدث من التقويم للمستخدم السابق {$previousUserId} بنجاح.");
            }
        }

        // إزالة معرفات Graph من قاعدة البيانات
        // $this->task->graph_task_id = null;
        // $this->task->graph_event_id = null;
        // $this->task->graph_list_id = null;
        $this->task->forceDelete();
    }


    /**
     * استرداد رمز الوصول المطلوب من Microsoft Graph API.
     *
     * @param \App\Services\MicrosoftGraphBaseService $graphService
     * @return string|null
     */
    private function getAccessToken(MicrosoftGraphBaseService $graphService)
    {
        $accessToken = $graphService->getAppAccessToken();
        if (!$accessToken) {
            Log::error('فشل في الحصول على رمز الوصول.');
        }
        return $accessToken;
    }

    /**
     * تحويل عنوان البريد الإلكتروني إلى معرف مستخدم باستخدام Microsoft Graph.
     *
     * @param \App\Services\MicrosoftGraphBaseService $graphService
     * @param string $accessToken
     * @param string $email
     * @return string|null
     */
    private function getUserIdFromEmail(MicrosoftGraphBaseService $graphService, $accessToken, $email)
    {
        return $graphService->getUserIdByEmail($accessToken, $email);
    }



    /**
     * التعامل مع فشل الوظيفة.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('فشل تنفيذ الوظيفة UpdateTasksAndEventsJob: ' . $exception->getMessage());
    }
}
