<?php

namespace App\Services\Microsoft\ToDo;

use App\Services\MicrosoftGraphBaseService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Model\Event;
use Microsoft\Graph\Model\TodoTask;
use Microsoft\Graph\Model\TodoTaskList;

/*
|--------------------------------------------------------------------------
| خدمة المهام والتقويم
|--------------------------------------------------------------------------
| تحتوي هذه الخدمة على وظائف لإدارة قوائم المهام، المهام الفردية،
| الأحداث في التقويم، والإشعارات عبر البريد الإلكتروني باستخدام
| Microsoft Graph API.
*/

class ToDoService extends MicrosoftGraphBaseService
{
    protected $graphClient;

    protected $accessToken;

    /*
    |--------------------------------------------------------------------------
    | تهيئة الخدمة
    |--------------------------------------------------------------------------
    | يقوم هذا القسم بتهيئة خدمة Microsoft Graph، الحصول على رمز الوصول،
    | وإعداد عميل Guzzle لإجراء الطلبات.
    */
    public function __construct(MicrosoftGraphBaseService $graphService)
    {
        parent::__construct();

        // الحصول على رمز الوصول للتطبيق
        $this->accessToken = $graphService->getAppAccessToken();

        if (! $this->accessToken) {
            // Log::error('فشل في الحصول على رمز الوصول للتطبيق.');
            // يمكنك رمي استثناء أو التعامل مع الحالة بناءً على متطلباتك
        }

        // تعيين رمز الوصول في Graph
        $this->setGraphAccessToken($this->accessToken);

        // تهيئة عميل Guzzle للطلبات المجمعة
        $this->graphClient = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | إدارة قوائم المهام
    |--------------------------------------------------------------------------
    | يحتوي هذا القسم على وظائف للحصول على أو إنشاء قوائم المهام
    | للمستخدمين.
    */
    public function getOrCreateTodoListId($userId, $listName)
    {
        if (! $this->accessToken) {
            return ['error' => 'رمز الوصول مفقود.'];
        }

        try {
            // جلب قوائم Todo الخاصة بالمستخدم
            $lists = $this->graph->createRequest('GET', "/users/{$userId}/todo/lists")
                ->setReturnType(TodoTaskList::class)
                ->execute();

            // البحث عن القائمة بالاسم المحدد
            foreach ($lists as $list) {
                if ($list->getDisplayName() === $listName) {
                    return $list->getId();
                }
            }

            // إذا لم يتم العثور على القائمة، قم بإنشائها
            $newList = $this->createTodoList($userId, $listName);
            if (is_array($newList) && isset($newList['error'])) {
                return $newList; // إرجاع الخطأ إذا حدث أثناء الإنشاء
            }

            return $newList->getId();
        } catch (\Exception $e) {
            // Log::error("خطأ في الحصول أو إنشاء قائمة Todo '{$listName}' للمستخدم {$userId}: " . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | لاضافة قائمة في  ToDo
    |--------------------------------------------------------------------------
    */
    public function createTodoList($userId, $listName)
    {
        if (! $this->accessToken) {
            return ['error' => 'رمز الوصول مفقود.'];
        }

        try {
            $list = [
                'displayName' => $listName,
            ];

            $response = $this->graph->createRequest('POST', "/users/{$userId}/todo/lists")
                ->attachBody($list)
                ->setReturnType(TodoTaskList::class)
                ->execute();

            // Log::info("تم إنشاء قائمة Todo '{$listName}' بنجاح للمستخدم {$userId}: " . json_encode($response));

            return $response;
        } catch (\Exception $e) {
            // Log::error("خطأ في إنشاء قائمة Todo '{$listName}' للمستخدم {$userId}: " . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }



    /*
    |--------------------------------------------------------------------------
    |  لاضافة مهمة في  ToDo
    |--------------------------------------------------------------------------
    */
    public function addTaskToUser($userId, array $taskData, $listName)
    {
        if (! $this->accessToken) {
            return ['error' => 'رمز الوصول مفقود.'];
        }

        try {
            // الحصول على أو إنشاء معرف قائمة Todo
            $todoListId = $this->getOrCreateTodoListId($userId, $listName);
            if (is_array($todoListId) && isset($todoListId['error'])) {
                return $todoListId; // إرجاع الخطأ إذا حدث

            }

            // تجهيز بيانات المهمة
            $task = [
                'title' => $taskData['task_name'] ?? 'مهمة جديدة',
                'dueDateTime' => [
                    'dateTime' => $taskData['endDateTime'] ?? null,
                    'timeZone' => 'Asia/Riyadh',
                ],
                'body' => [
                    'content' => $taskData['body'] ?? '',
                    'contentType' => 'text',
                ],
                // حقول إضافية حسب الحاجة
            ];

            // إنشاء المهمة
            $response = $this->graph->createRequest('POST', "/users/{$userId}/todo/lists/{$todoListId}/tasks")
                ->attachBody($task)
                ->setReturnType(TodoTask::class)
                ->execute();

            // تسجيل الاستجابة
            // Log::info("تمت إضافة المهمة بنجاح إلى القائمة '{$listName}' للمستخدم {$userId}: " . json_encode($response));

            return [
                'list_id' => $todoListId,
                'task' => $response,
            ];
        } catch (\Exception $e) {
            Log::error("خطأ في إضافة مهمة للمستخدم {$userId}: " . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | لتعديل المهمة في  ToDo
    |--------------------------------------------------------------------------
    */
    public function updateTaskForUser($userId, $graphListId, $graphTaskId, array $taskData, $listName)
    {
        if (!$this->accessToken) {
            return ['error' => 'رمز الوصول مفقود.'];
        }

        // تجهيز بيانات المهمة المراد تحديثها
        $taskPayload = [
            'title' => $taskData['task_name'] ?? 'مهمة جديدة',
            'dueDateTime' => [
                'dateTime' => $taskData['endDateTime'] ?? null,
                'timeZone' => 'Asia/Riyadh',
            ],
            'body' => [
                'content' => $taskData['body'] ?? '',
                'contentType' => 'text',
            ],
        ];

        try {
            // التحقق من وجود المهمة والقائمة
            $taskExists = false;
            $listExists = false;

            try {
                // أولاً نتحقق من وجود القائمة
                $listResponse = $this->graph->createRequest('GET', "/users/{$userId}/todo/lists/{$graphListId}")
                    ->execute();
                $listExists = true;

                // إذا وجدت القائمة، نتحقق من وجود المهمة
                $taskResponse = $this->graph->createRequest('GET', "/users/{$userId}/todo/lists/{$graphListId}/tasks/{$graphTaskId}")
                    ->setReturnType(TodoTask::class)
                    ->execute();

                if ($taskResponse && method_exists($taskResponse, 'getId') && !empty($taskResponse->getId())) {
                    $taskExists = true;
                }
            } catch (\Microsoft\Graph\Exception\GraphException $e) {
                // تحقق من نوع الخطأ (404 للقائمة غير الموجودة)
                if (strpos($e->getMessage(), 'FolderNotFound') !== false || strpos($e->getMessage(), 'itemNotFound') !== false) {
                    $listExists = false;
                    $taskExists = false;
                }
            } catch (\Exception $e) {
                $listExists = false;
                $taskExists = false;
            }

            // إذا كانت القائمة والمهمة موجودتين، نقوم بالتحديث
            if ($listExists && $taskExists) {
                $response = $this->graph->createRequest('PATCH', "/users/{$userId}/todo/lists/{$graphListId}/tasks/{$graphTaskId}")
                    ->attachBody($taskPayload)
                    ->setReturnType(TodoTask::class)
                    ->execute();

                return [
                    'task' => $response,
                    'status' => 'updated',
                    'message' => 'تم تحديث المهمة بنجاح',
                    'exists' => true
                ];
            } else {
                // إذا كانت القائمة غير موجودة أو المهمة غير موجودة، نقوم بإنشاء مهمة جديدة

                // Log::info('القائمة أو المهمة غير موجودة، سيتم إنشاء مهمة جديدة.');
                // أولاً نحصل على أو ننشئ قائمة جديدة
                $newListId = $this->getOrCreateTodoListId($userId, $listName);

                if (is_array($newListId) && isset($newListId['error'])) {
                    return [
                        'error' => 'فشل في إنشاء القائمة: ' . $newListId['error'],
                        'status' => 'error',
                        'exists' => false
                    ];
                }

                return [
                    'exists' => false,
                    'new_list_id' => $newListId
                ];
            }
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            return [
                'error' => 'خطأ في Microsoft Graph: ' . $e->getMessage(),
                'status' => 'error'
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }





    /*
    |--------------------------------------------------------------------------
    | لحذف المهمة من ToDo
    |--------------------------------------------------------------------------
    */
    public function deleteTaskForUser($userId, $listId, $graphTaskId)
    {
        // Log::info('محاولة حذف مهمة في Microsoft Graph', [
        //     'user_id' => $userId,
        //     'list_id' => $listId,
        //     'task_id' => $graphTaskId,
        // ]);

        if (! $this->accessToken) {
            Log::error('رمز الوصول مفقود.');

            return false;
        }

        // تأكد من تعيين رمز الوصول في كائن graph
        $this->graph->setAccessToken($this->accessToken);

        try {
            $response = $this->graph->createRequest('DELETE', "/users/{$userId}/todo/lists/{$listId}/tasks/{$graphTaskId}")
                ->execute();

            // Log::info("تم حذف المهمة بنجاح في Microsoft Graph للمستخدم {$userId}.", [
            //     'task_id' => $graphTaskId,
            //     'response_status' => $response->getStatus(),
            // ]);

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error("خطأ في Graph API عند حذف المهمة للمستخدم {$userId}.", [
            //     'task_id' => $graphTaskId,
            //     'error_code' => $e->getCode(),
            //     'error_message' => $e->getMessage(),
            // ]);
        } catch (\Exception $e) {
            // Log::error("خطأ عام في حذف المهمة للمستخدم {$userId}.", [
            //     'task_id' => $graphTaskId,
            //     'error_code' => $e->getCode(),
            //     'error_message' => $e->getMessage(),
            // ]);
        }

        return false;
    }



    /*
    |--------------------------------------------------------------------------
    | لاضافة المهمة في التقويم
    |--------------------------------------------------------------------------
    */
    public function addCalendarEventToUser($userId, array $eventData, $userName = "")
    {

        if (! $this->accessToken) {
            // Log::error('رمز الوصول مفقود.');
            return ['error' => 'رمز الوصول مفقود.'];
        }

        try {
            // إعداد بيانات الحدث
            $event = [
                'subject' => $eventData['task_name'] ?? 'مهمة جديدة',
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $eventData['body'] ?? '',
                ],
                'start' => [
                    'dateTime' => $eventData['endDateTime'],
                    'timeZone' => 'Asia/Riyadh',
                ],
                'end' => [
                    'dateTime' => $eventData['endDateTime'],
                    'timeZone' => 'Asia/Riyadh',
                ],
                'location' => [
                    'displayName' => 'غير محدد',
                ],
                'isReminderOn' => true, // إضافة خاصية التنبيه
                'reminderMinutesBeforeStart' =>  15,
                // يمكنك إضافة المدعوين إذا لزم الأمر
            ];

            // إنشاء الحدث
            $response = $this->graph->createRequest('POST', "/users/{$userId}/events")
                ->attachBody($event)
                ->setReturnType(Event::class)
                ->execute();

            // تسجيل الاستجابة
            // Log::info("تم إنشاء الحدث بنجاح للمستخدم {$userId}: " . json_encode($response));

            return $response;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error("GraphException عند إنشاء الحدث للمستخدم {$userId}: " . $e->getMessage());

            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error("خطأ عند إنشاء الحدث للمستخدم {$userId}: " . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | تعديل المهمة في التقويم
    |--------------------------------------------------------------------------
    */
    public function updateCalendarEventForUser($userId, $graphEventId, array $eventData)
    {
        if (!$this->accessToken) {
            return ['error' => 'رمز الوصول مفقود.'];
        }

        // تجهيز بيانات الحدث
        $eventPayload = [
            'subject' => $eventData['task_name'] ?? 'مهمة جديدة',
            'body' => [
                'contentType' => 'HTML',
                'content' => $eventData['body'] ?? '',
            ],
            'start' => [
                'dateTime' => $eventData['endDateTime'],
                'timeZone' => 'Asia/Riyadh',
            ],
            'end' => [
                'dateTime' => $eventData['endDateTime'],
                'timeZone' => 'Asia/Riyadh',
            ],
            'location' => [
                'displayName' => $eventData['location'] ?? 'غير محدد',
            ],
        ];

        try {
            // التحقق من وجود الحدث
            $eventExists = false;
            try {
                $this->graph->createRequest('GET', "/users/{$userId}/events/{$graphEventId}")
                    ->setReturnType(Event::class)
                    ->execute();
                $eventExists = true;
            } catch (\Exception $e) {
                $eventExists = false;
            }

            // إذا كان الحدث موجود، نقوم بالتحديث
            if ($eventExists) {
                $response = $this->graph->createRequest('PATCH', "/users/{$userId}/events/{$graphEventId}")
                    ->attachBody($eventPayload)
                    ->setReturnType(Event::class)
                    ->execute();

                return [
                    'event' => $response,
                    'status' => 'updated',
                    'message' => 'تم تحديث الحدث بنجاح',
                    'exists' => true
                ];
            }
            // إذا لم يكن الحدث موجود، نقوم بإنشاء حدث جديد
            else {
                return [
                    'exists' => false
                ];
            }
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            return [
                'error' => 'خطأ في Microsoft Graph: ' . $e->getMessage(),
                'status' => 'error'
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'خطأ غير متوقع: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }



    /*
    |--------------------------------------------------------------------------
    | حذف المهمة من التقويم
    |--------------------------------------------------------------------------
    */
    public function deleteCalendarEventInGraph($userId, $graphEventId)
    {
        // Log::info('محاولة حذف حدث تقويمي في Microsoft Graph', [
        //     'user_id' => $userId,
        //     'event_id' => $graphEventId,
        // ]);

        if (! $this->accessToken) {
            // Log::error('رمز الوصول مفقود.');

            return false;
        }

        try {
            $response = $this->graph->createRequest('DELETE', "/users/{$userId}/events/{$graphEventId}")
                ->execute();

            // Log::info("تم حذف الحدث بنجاح في Microsoft Graph للمستخدم {$userId}.", [
            //     'event_id' => $graphEventId,
            //     'response_status' => $response->getStatus(),
            // ]);

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error("خطأ في Graph API عند حذف الحدث للمستخدم {$userId}.", [
            //     'event_id' => $graphEventId,
            //     'error_code' => $e->getCode(),
            //     'error_message' => $e->getMessage(),
            // ]);
        } catch (\Exception $e) {
            // Log::error("خطأ عام في حذف الحدث للمستخدم {$userId}.", [
            //     'event_id' => $graphEventId,
            //     'error_code' => $e->getCode(),
            //     'error_message' => $e->getMessage(),
            // ]);
        }

        return false;
    }



    /*
    |--------------------------------------------------------------------------
    | الطلبات المجمعة
    |--------------------------------------------------------------------------
    | يحتوي هذا القسم على وظائف لإرسال طلبات مجمعة إلى Microsoft Graph API.
    */
    protected function sendBatchRequest(array $headers, array $batchRequests)
    {
        // Log::info('بدء إرسال طلب مجمع');

        // تجهيز جسم الطلب المجمّع
        $batchBody = [
            'requests' => $batchRequests,
        ];

        try {
            $response = $this->graphClient->post('$batch', [
                'headers' => $headers,
                'json' => $batchBody,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            return $responseBody['responses'] ?? [];
        } catch (\Exception $e) {
            // Log::error('خطأ في إرسال الطلب المجمّع: ' . $e->getMessage());
            throw $e;
        }
    }
}
