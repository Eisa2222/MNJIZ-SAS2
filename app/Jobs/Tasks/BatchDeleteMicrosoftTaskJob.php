<?php

namespace App\Jobs\Tasks;

use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Services\MicrosoftGraphBaseService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BatchDeleteMicrosoftTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    protected $task;
    protected $removedUsersData; // مصفوفة من بيانات المستخدمين مع pivot

    public $tries = 3;
    public $timeout = 120;

    public function __construct(Task $task, array $removedUsersData)
    {
        $this->task = $task;
        $this->removedUsersData = $removedUsersData;
        $this->captureTenant();
    }

    public function handle(TaskService $taskService, MicrosoftGraphBaseService $graphService)
    {
        // Log::info('بدء تنفيذ BatchDeleteMicrosoftTaskJob للمهمة #' . $this->task->id);

        $accessToken = $graphService->getAppAccessToken();
        if (!$accessToken) {
            // Log::error('فشل في الحصول على رمز الوصول.');
            return;
        }

        foreach ($this->removedUsersData as $userData) {
            try {

                // Log::info('معالجة المستخدم #' . $userData);

                if (!isset($userData['email'])) {
                    // Log::warning('لا يوجد بريد إلكتروني للمستخدم #' . $userData);
                    continue;
                }

                $userId = $graphService->getUserIdByEmail($accessToken, $userData['email']);
                if (!$userId) {
                    // Log::warning('لم يتم العثور على معرف المستخدم لـ: ' . $userData['email']);
                    continue;
                }

                // حذف المهمة من To-Do إذا كانت بيانات الـ pivot متوفرة
                if (!empty($userData['graph_task_id']) && !empty($userData['graph_list_id'])) {
                    // Log::info('محاولة حذف مهمة To-Do: ' . $userData['graph_task_id']);
                    $result = $taskService->deleteTaskForUser(
                        $userId,
                        $userData['graph_list_id'],
                        $userData['graph_task_id']
                    );
                    // Log::info('نتيجة حذف المهمة: ' . ($result ? 'نجاح' : 'فشل'));
                } else {
                    // Log::info('تخطي حذف مهمة To-Do لعدم توفر المعرفات المطلوبة');
                }

                // حذف الحدث من التقويم إذا كانت بيانات الـ pivot متوفرة
                if (!empty($userData['graph_event_id'])) {
                    // Log::info('محاولة حذف حدث التقويم: ' . $userData['graph_event_id']);
                    $result = $taskService->deleteCalendarEventInGraph(
                        $userId,
                        $userData['graph_event_id']
                    );
                    // Log::info('نتيجة حذف الحدث: ' . ($result ? 'نجاح' : 'فشل'));
                } else {
                    // Log::info('تخطي حذف حدث التقويم لعدم توفر المعرف المطلوب');
                }

            } catch (\Exception $e) {
                // Log::error('خطأ في معالجة المستخدم #'  . $e->getMessage());
                // Log::error('التفاصيل: ' . $e->getTraceAsString());
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('فشل تنفيذ BatchDeleteMicrosoftTaskJob: ' . $exception->getMessage());
    }
}
