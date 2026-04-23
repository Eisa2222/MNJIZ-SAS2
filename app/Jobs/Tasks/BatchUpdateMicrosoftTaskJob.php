<?php

namespace App\Jobs\Tasks;

use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Services\MicrosoftGraphBaseService;
use App\Services\EmailService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BatchUpdateMicrosoftTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    protected $taskData;
    protected $officeName;
    protected $existingUsers;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(Task $task, array $taskData,$officeName, Collection $existingUsers)
    {
        $this->task = $task;
        $this->taskData = $taskData;
        $this->officeName = $officeName;
        $this->existingUsers = $existingUsers;
    }

    public function handle(TaskService $taskService, MicrosoftGraphBaseService $graphService)
    {
        $accessToken = $graphService->getAppAccessToken();
        if (!$accessToken) {
            // Log::error('فشل في الحصول على رمز الوصول.');
            return;
        }

        foreach ($this->existingUsers as $user) {
            $userId = $graphService->getUserIdByEmail($accessToken, $user->email);
            if (!$userId) {
                // Log::error("لم يتم العثور على معرف المستخدم للبريد: {$user->email}");
                continue;
            }

            $pivotRecord = $this->task->assignedUsers()
                ->where('user_id', $user->id)
                ->first();

            if (!$pivotRecord) {
                continue;
            }

            $pivot = $pivotRecord->pivot;

            $taskAssignee = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ];

            // تحديث المهمة في To-Do
            if ($pivot->graph_task_id && $pivot->graph_list_id) {
                $response =  $taskService->updateTaskForUser(
                    $userId,
                    $pivot->graph_list_id,
                    $pivot->graph_task_id,
                    $this->taskData,
                    $this->officeName
                );
                // Log::info($response);
                if (isset($response['exists']) && !$response['exists']) {
                    $this->createNewTaskForUser($taskService, $userId, $taskAssignee);
                }
            } else {
                // إنشاء مهمة جديدة إذا لم تكن موجودة من قبل
                $this->createNewTaskForUser($taskService, $userId, $taskAssignee);
            }

            // تحديث الحدث في التقويم
            if ($pivot->graph_event_id) {
                $eventResponse = $taskService->updateCalendarEventForUser(
                    $userId,
                    $pivot->graph_event_id,
                    $this->taskData
                );

                // إذا فشل التحديث أو لم يكن الحدث موجوداً، قم بإنشائه
                if (isset($eventResponse['exists']) && !$eventResponse['exists']) {
                    $this->createNewEventForUser($taskService, $userId, $taskAssignee);
                }
            } else {
                // إنشاء حدث جديد إذا لم يكن موجوداً من قبل
                $this->createNewEventForUser($taskService, $userId, $taskAssignee);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    |  create a new task for the user if it doesn't exist
    |--------------------------------------------------------------------------
    */
    private function createNewTaskForUser(TaskService $taskService, $userId, $taskAssignee)
    {
        $response = $taskService->addTaskToUser($userId, $this->taskData, $this->officeName);

        if (isset($response['error'])) {
            // Log::error("خطأ في إنشاء المهمة للمستخدم {$userId}: " . json_encode($response['error']));
            return;
        }

        if (isset($response['task']) && isset($response['list_id'])) {
            $graphTaskId = $response['task']->getId();
            $graphListId = $response['list_id'];

            // تحديث سجل العلاقة في جدول pivot
            $this->task->assignedUsers()->updateExistingPivot($taskAssignee['id'], [
                'graph_task_id' => $graphTaskId,
                'graph_list_id' => $graphListId,
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    |  create a new event for the user if it doesn't exist
    |--------------------------------------------------------------------------
    */
    private function createNewEventForUser(TaskService $taskService, $userId, $taskAssignee)
    {
        try {
            $eventResponse = $taskService->addCalendarEventToUser($userId, $this->taskData, $taskAssignee['name']);

            // Check if the response is an Event object
            if ($eventResponse instanceof \Microsoft\Graph\Model\Event) {
                if (method_exists($eventResponse, 'getId')) {
                    $graphEventId = $eventResponse->getId();

                    // تحديث سجل العلاقة في جدول pivot
                    $this->task->assignedUsers()->updateExistingPivot($taskAssignee['id'], [
                        'graph_event_id' => $graphEventId,
                    ]);

                    return true;
                }
            }
            // Check if the response is an array containing an error
            elseif (is_array($eventResponse) && isset($eventResponse['error'])) {
                Log::error("خطأ في إنشاء الحدث للمستخدم {$userId}: " .
                    (is_array($eventResponse['error']) ? json_encode($eventResponse['error']) : $eventResponse['error']));
                return false;
            }
        } catch (\Exception $e) {
            Log::error("استثناء عند إنشاء الحدث للمستخدم {$userId}: " . $e->getMessage());
            return false;
        }

        return false;
    }

    public function failed(\Throwable $exception)
    {
        Log::error('فشل تنفيذ BatchUpdateMicrosoftTaskJob: ' . $exception->getMessage());
    }
}
