<?php

namespace App\Services;

use App\Models\TaskCalendarEvent;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Model\Event;
use Microsoft\Graph\Model\PlannerTask;

class PlannerTaskService extends MicrosoftGraphBaseService
{
    protected $calendarService;
    public function __construct()
    {
        parent::__construct();
        $this->calendarService = new CalendarService();
    }

    /**
     * قائمة المهام داخل Bucket معين
     *
     * @param string $accessToken
     * @param string $bucketId
     * @return array|\Microsoft\Graph\Model\PlannerTask[]
     */
    public function listTasks($accessToken, $bucketId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $response = $this->graph->createRequest("GET", "/planner/buckets/{$bucketId}/tasks")
                ->setReturnType(PlannerTask::class)
                ->execute();

            if (count($response) > 0) {
                return $response;
            } else {
                return ['error' => 'لم يتم العثور على مهام في هذا الـ Bucket.'];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching tasks: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * إنشاء مهمة جديدة داخل Bucket
     *
     * @param string $accessToken
     * @param string $planId
     * @param string $bucketId
     * @param string $taskTitle
     * @return \Microsoft\Graph\Model\PlannerTask|array
     */
    public function createTask($accessToken, $planId, $bucketId, array $taskData)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        $newTask = null;

        try {
            $task = [
                'planId' => $planId,
                'bucketId' => $bucketId,
                'title' => $taskData['title'] ?? 'بدون عنوان',
                'startDateTime' => $taskData['startDateTime'] ?? null, // صيغة ISO 8601
                'dueDateTime' => $taskData['dueDateTime'] ?? null,     // صيغة ISO 8601
                'priority' => $taskData['priority'] ?? 0,              // من 0 إلى 4
                'percentComplete' => $taskData['percentComplete'] ?? 0,
                'description' => $taskData['description'] ?? '',
                'assignments' => $taskData['assignments'] ?? new \stdClass(),
                // يمكن إضافة المزيد من الحقول حسب الحاجة
            ];

            $task = array_filter($task, function ($value) {
                return !is_null($value);
            });

            $newTask = $this->graph->createRequest('POST', "/planner/tasks")
                ->addHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->attachBody($task)
                ->setReturnType(PlannerTask::class)
                ->execute();

            // بعد إنشاء المهمة، قم بإنشاء حدث تقويم
            $eventData = [
                'title' => $task['title'],
                'description' => $task['description'],
                'startDateTime' => $task['startDateTime'],
                'dueDateTime' => $task['dueDateTime'],
            ];
            $calendarEvent = $this->calendarService->createCalendarEvent($accessToken, $eventData);

            // تحقق من نجاح إنشاء الحدث
            if (is_array($calendarEvent) && isset($calendarEvent['error'])) {
                // سجل الخطأ
                Log::error('Failed to create calendar event: ' . $calendarEvent['error']);
                // حذف المهمة التي تم إنشاؤها مسبقًا
                $this->deleteTask($accessToken, $newTask->getId());
                return ['error' => 'فشل إنشاء الحدث التقويمي: ' . $calendarEvent['error']];
            }

            if ($calendarEvent instanceof Event) {
                TaskCalendarEvent::create([
                    'task_id' => $newTask->getId(),
                    'event_id' => $calendarEvent->getId(),
                ]);
            } else {
                Log::warning('Unknown response type from createCalendarEvent');
            }

            return $newTask;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException creating task: ' . $e->getMessage());
            // حذف المهمة إذا تم إنشاؤها
            if ($newTask) {
                $this->deleteTask($accessToken, $newTask->getId());
            }
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Exception creating task: ' . $e->getMessage());
            // حذف المهمة إذا تم إنشاؤها
            if ($newTask) {
                $this->deleteTask($accessToken, $newTask->getId());
            }
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * عرض تفاصيل مهمة معينة
     *
     * @param string $accessToken
     * @param string $taskId
     * @return \Microsoft\Graph\Model\PlannerTask|array
     */
    public function getTask($accessToken, $taskId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $task = $this->graph->createRequest("GET", "/planner/tasks/{$taskId}")
                ->setReturnType(PlannerTask::class)
                ->execute();

            return $task;
        } catch (\Exception $e) {
            Log::error('Error getting task: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * تعديل مهمة معينة
     *
     * @param string $accessToken
     * @param string $taskId
     * @param array $newTaskData
     * @return array|\Microsoft\Graph\Model\PlannerTask
     */
    public function updateTask($accessToken, $taskId, array $taskData)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // استخدام Guzzle للحصول على المهمة والحصول على eTag
            $client = new Client([
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            // الخطوة 1: جلب المهمة الحالية للحصول على eTag
            $response = $client->request('GET', "https://graph.microsoft.com/v1.0/planner/tasks/{$taskId}");
            $taskContent = json_decode($response->getBody()->getContents(), true);
            $eTag = $response->getHeaderLine('ETag');

            if (!$eTag) {
                throw new \Exception('لم يتم العثور على eTag في رأس الاستجابة.');
            }

            // الحصول على معرف الحدث المرتبط
            $taskCalendarEvent = TaskCalendarEvent::where('task_id', $taskId)->first();

            if ($taskCalendarEvent) {
                $eventId = $taskCalendarEvent->event_id;

                // تحديث الحدث أولاً
                $eventData = [
                    'title' => $taskData['title'] ?? $taskContent['title'],
                    'description' => $taskData['description'] ?? $taskContent['description'] ?? '',
                    // سنضيف 'start' و 'end' بشكل شرطي
                ];

                // التحقق من startDateTime
                $startDateTime = $taskData['startDateTime'] ?? $taskContent['startDateTime'] ?? null;
                if (!is_null($startDateTime)) {
                    $eventData['startDateTime'] = $startDateTime;
                }

                // التحقق من dueDateTime
                $dueDateTime = $taskData['dueDateTime'] ?? $taskContent['dueDateTime'] ?? null;
                if (!is_null($dueDateTime)) {
                    $eventData['dueDateTime'] = $dueDateTime;
                }

                // تحديث الحدث التقويمي
                $updatedEvent = $this->calendarService->updateCalendarEvent($accessToken, $eventId, $eventData);

                if (is_array($updatedEvent) && isset($updatedEvent['error'])) {
                    Log::error('Failed to update calendar event: ' . $updatedEvent['error']);
                    return ['error' => 'فشل تحديث الحدث التقويمي: ' . $updatedEvent['error']];
                }
            } else {
                Log::warning('No calendar event associated with task ID: ' . $taskId);
            }

            // تحديث بيانات المهمة
            $updatedTaskData = [
                'title' => $taskData['title'] ?? $taskContent['title'],
                'priority' => $taskData['priority'] ?? $taskContent['priority'],
                'percentComplete' => $taskData['percentComplete'] ?? $taskContent['percentComplete'],
                // 'description' ليس جزءًا من بيانات المهمة المباشرة، بل يتم تخزينها في تفاصيل المهمة
            ];

            // التحقق من startDateTime و dueDateTime قبل تضمينهما
            if (!is_null($taskData['startDateTime'])) {
                $updatedTaskData['startDateTime'] = $taskData['startDateTime'];
            }

            if (!is_null($taskData['dueDateTime'])) {
                $updatedTaskData['dueDateTime'] = $taskData['dueDateTime'];
            }

            $updatedTaskData = array_filter($updatedTaskData, function ($value) {
                return !is_null($value);
            });

            // تحديث المهمة
            $updatedTask = $this->graph->createRequest('PATCH', "/planner/tasks/{$taskId}")
                ->addHeaders([
                    'Content-Type' => 'application/json',
                    'If-Match' => $eTag
                ])
                ->attachBody($updatedTaskData)
                ->setReturnType(\Microsoft\Graph\Model\PlannerTask::class)
                ->execute();

            // تحديث تفاصيل المهمة إذا كان هناك وصف
            if (isset($taskData['description'])) {
                // الحصول على تفاصيل المهمة
                $responseDetails = $client->request('GET', "https://graph.microsoft.com/v1.0/planner/tasks/{$taskId}/details");
                $taskDetailsContent = json_decode($responseDetails->getBody()->getContents(), true);
                $detailsETag = $responseDetails->getHeaderLine('ETag');

                if (!$detailsETag) {
                    throw new \Exception('لم يتم العثور على eTag في رأس الاستجابة لتفاصيل المهمة.');
                }

                $updatedDetailsData = [
                    'description' => $taskData['description']
                ];

                // تحديث تفاصيل المهمة
                $this->graph->createRequest('PATCH', "/planner/tasks/{$taskId}/details")
                    ->addHeaders([
                        'Content-Type' => 'application/json',
                        'If-Match' => $detailsETag
                    ])
                    ->attachBody($updatedDetailsData)
                    ->execute();
            }

            return $updatedTask;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException updating task: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Exception updating task: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }



    /**
     * حذف مهمة معينة
     *
     * @param string $accessToken
     * @param string $taskId
     * @return bool|array
     */
    public function deleteTask($accessToken, $taskId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // الحصول على معرف الحدث المرتبط
            $taskCalendarEvent = TaskCalendarEvent::where('task_id', $taskId)->first();

            if ($taskCalendarEvent) {
                $eventId = $taskCalendarEvent->event_id;

                // حذف الحدث التقويمي أولاً
                $deleteEventResult = $this->calendarService->deleteCalendarEvent($accessToken, $eventId);

                if ($deleteEventResult !== true) {
                    Log::error('Failed to delete calendar event: ' . $deleteEventResult['error']);
                    return ['error' => 'فشل حذف الحدث التقويمي: ' . $deleteEventResult['error']];
                }

                // حذف السجل من قاعدة البيانات
                $taskCalendarEvent->delete();
            } else {
                Log::warning('No calendar event associated with task ID: ' . $taskId);
            }

            // استخدام Guzzle للحصول على المهمة والحصول على eTag
            $client = new Client([
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            // الخطوة 1: جلب المهمة الحالية للحصول على eTag
            $response = $client->request('GET', "https://graph.microsoft.com/v1.0/planner/tasks/{$taskId}");
            $eTag = $response->getHeaderLine('ETag');

            if (!$eTag) {
                throw new \Exception('لم يتم العثور على eTag في رأس الاستجابة.');
            }

            // حذف المهمة
            $this->graph->createRequest('DELETE', "/planner/tasks/{$taskId}")
                ->addHeaders([
                    'If-Match' => $eTag
                ])
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException deleting task: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Exception deleting task: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * تعيين مستخدم كمُكلف للمهمة
     *
     * @param string $accessToken
     * @param string $taskId
     * @param string $userId
     * @return bool|array
     */
    // public function assignTask($accessToken, $taskId, $userId)
    // {
    //     if (!$accessToken) {
    //         return null;
    //     }

    //     $this->graph->setAccessToken($accessToken);

    //     try {
    //         $assignment = [
    //             'assignments' => [
    //                 $userId => [
    //                     '@odata.type' => '#microsoft.graph.plannerAssignment',
    //                     'orderHint' => ' !'
    //                 ]
    //             ]
    //         ];

    //         $this->graph->createRequest('PATCH', "/planner/tasks/{$taskId}/assignments/{$userId}")
    //             ->attachBody($assignment)
    //             ->execute();

    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error('Error assigning task: ' . $e->getMessage());
    //         return ['error' => $e->getMessage()];
    //     }
    // }

    /**
     * إزالة مُكلف من المهمة
     *
     * @param string $accessToken
     * @param string $taskId
     * @param string $userId
     * @return bool|array
     */
    // public function unassignTask($accessToken, $taskId, $userId)
    // {
    //     if (!$accessToken) {
    //         return null;
    //     }

    //     $this->graph->setAccessToken($accessToken);

    //     try {
    //         $this->graph->createRequest('DELETE', "/planner/tasks/{$taskId}/assignments/{$userId}")
    //             ->execute();

    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error('Error unassigning task: ' . $e->getMessage());
    //         return ['error' => $e->getMessage()];
    //     }
    // }
}
