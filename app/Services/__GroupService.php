<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Model\Group;

class GroupService extends MicrosoftGraphBaseService
{
    protected $client;

    public function __construct()
    {
        parent::__construct();
        // تهيئة Guzzle Client لاستخدامه في طلبات الدُفعة
        $this->client = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * قائمة المجموعات
     *
     * @param string $accessToken
     * @return array|\Microsoft\Graph\Model\Group[]
     */
    public function listGroups($accessToken)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $groups = $this->graph->createRequest('GET', '/groups')
                ->setReturnType(Group::class)
                ->execute();

            return $groups;
        } catch (\Exception $e) {
            Log::error('خطأ في جلب المجموعات: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * إنشاء مجموعة جديدة
     *
     * @param string $accessToken
     * @param array $groupData
     * @return \Microsoft\Graph\Model\Group|array
     */
    public function createGroup($accessToken, array $groupData)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // تحديد نوع المجموعة بناءً على groupType
            if (isset($groupData['groupType'])) {
                switch ($groupData['groupType']) {
                    case 'office365':
                        $mailEnabled = true;
                        $securityEnabled = false;
                        $groupTypes = ['Unified'];
                        break;
                    case 'security':
                        $mailEnabled = false;
                        $securityEnabled = true;
                        $groupTypes = [];
                        break;
                    case 'distribution':
                        $mailEnabled = true;
                        $securityEnabled = false;
                        $groupTypes = [];
                        break;
                    default:
                        throw new \Exception('نوع المجموعة غير معروف.');
                }
            } else {
                // إذا لم يتم تحديد نوع المجموعة، استخدم القيم الافتراضية لمجموعة Office 365
                $mailEnabled = $groupData['mailEnabled'] ?? true;
                $securityEnabled = $groupData['securityEnabled'] ?? false;
                $groupTypes = ['Unified'];
            }

            // إعداد بيانات المجموعة
            $group = [
                'displayName' => $groupData['displayName'] ?? 'مجموعة جديدة',
                'description' => $groupData['description'] ?? '',
                'mailEnabled' => $mailEnabled,
                'mailNickname' => $groupData['mailNickname'] ?? 'newgroup',
                'securityEnabled' => $securityEnabled,
                'groupTypes' => $groupTypes,
                // يمكن إضافة المزيد من الحقول حسب الحاجة
            ];

            // تحقق من groupTypes في حالة المجموعات غير Unified
            if (empty($groupTypes)) {
                unset($group['groupTypes']);
            }

            // إنشاء المجموعة
            $response = $this->graph->createRequest('POST', '/groups')
                ->addHeaders(['Content-Type' => 'application/json'])
                ->attachBody($group)
                ->setReturnType(Group::class)
                ->execute();

            // تسجيل الاستجابة للتحقق
            Log::info('Group created successfully: ' . json_encode($response));

            return $response;
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * عرض تفاصيل مجموعة معينة
     *
     * @param string $accessToken
     * @param string $groupId
     * @return \Microsoft\Graph\Model\Group|array
     */
    public function getGroup($accessToken, $groupId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $group = $this->graph->createRequest("GET", "/groups/{$groupId}")
                ->setReturnType(Group::class)
                ->execute();

            return $group;
        } catch (\Exception $e) {
            Log::error('خطأ في جلب تفاصيل المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * تعديل مجموعة معينة
     *
     * @param string $accessToken
     * @param string $groupId
     * @param array $groupData
     * @return \Microsoft\Graph\Model\Group|array
     */
    public function updateGroup($accessToken, $groupId, array $groupData)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // تحديث بيانات المجموعة
            $updatedGroupData = [
                'displayName' => $groupData['displayName'] ?? null,
                'description' => $groupData['description'] ?? null,
                // إضافة الحقول الأخرى حسب الحاجة
            ];

            $updatedGroupData = array_filter($updatedGroupData, function ($value) {
                return !is_null($value);
            });

            // تحديث المجموعة
            $updatedGroup = $this->graph->createRequest('PATCH', "/groups/{$groupId}")
                ->attachBody($updatedGroupData)
                ->setReturnType(Group::class)
                ->execute();

            return $updatedGroup;
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * حذف مجموعة معينة
     *
     * @param string $accessToken
     * @param string $groupId
     * @return bool|array
     */
    public function deleteGroup($accessToken, $groupId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $this->graph->createRequest('DELETE', "/groups/{$groupId}")
                ->execute();

            return true;
        } catch (\Exception $e) {
            Log::error('خطأ في حذف المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * إضافة عضو إلى المجموعة
     *
     * @param string $accessToken
     * @param string $groupId
     * @param string $userId
     * @return bool|array
     */
    public function addMemberToGroup($accessToken, $groupId, $userId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $memberData = [
                "@odata.id" => "https://graph.microsoft.com/v1.0/users/{$userId}"
            ];

            $this->graph->createRequest('POST', "/groups/{$groupId}/members/\$ref")
                ->attachBody($memberData)
                ->execute();

            return true;
        } catch (\Exception $e) {
            Log::error('خطأ في إضافة العضو إلى المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * إزالة عضو من المجموعة
     *
     * @param string $accessToken
     * @param string $groupId
     * @param string $userId
     * @return bool|array
     */
    public function removeMemberFromGroup($accessToken, $groupId, $userId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $this->graph->createRequest('DELETE', "/groups/{$groupId}/members/{$userId}/\$ref")
                ->execute();

            return true;
        } catch (\Exception $e) {
            Log::error('خطأ في إزالة العضو من المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * إضافة مجموعة من الأعضاء إلى المجموعة مرة واحدة باستخدام طلب دفعة
     *
     * @param string $accessToken
     * @param string $groupId
     * @param array $userIds Array of user IDs to add
     * @return array|string Returns true on success or array of errors
     */
    public function addMembersToGroup($accessToken, $groupId, array $userIds)
    {
        if (!$accessToken) {
            return null;
        }

        // إزالة التكرارات من معرفات المستخدمين
        $userIds = array_unique($userIds);

        // تهيئة Access Token في رأس الطلب
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        try {
            $batchRequests = [];
            $responses = [];

            // تقسيم معرفات المستخدمين إلى دفعات بحجم 20 طلبًا كحد أقصى
            $chunks = array_chunk($userIds, 20);
            $batchId = 1;

            foreach ($chunks as $chunk) {
                foreach ($chunk as $userId) {
                    $requestId = "req{$batchId}";
                    $batchRequests[] = [
                        'id' => $requestId,
                        'method' => 'POST',
                        'url' => "/groups/{$groupId}/members/\$ref",
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                        'body' => [
                            "@odata.id" => "https://graph.microsoft.com/v1.0/users/{$userId}"
                        ]
                    ];
                    $batchId++;
                }

                // إرسال الدفعة الحالية
                $responses = array_merge($responses, $this->sendBatchRequest($headers, $batchRequests));

                // إعادة تعيين مصفوفة الطلبات للدفعة التالية
                $batchRequests = [];
            }

            // التحقق من الأخطاء في الردود
            $errors = [];
            foreach ($responses as $response) {
                if (isset($response['status']) && $response['status'] >= 400) {
                    $errors[] = [
                        'request_id' => $response['id'] ?? null,
                        'status' => $response['status'],
                        'body' => $response['body'],
                    ];
                }
            }

            if (!empty($errors)) {
                Log::error('أخطاء أثناء إضافة الأعضاء إلى المجموعة: ', $errors);
                return $errors;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('خطأ في إضافة الأعضاء إلى المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * تحديث أعضاء المجموعة لتكون مطابقة لقائمة جديدة
     *
     * @param string $accessToken
     * @param string $groupId
     * @param array $newUserIds Array of user IDs to set as members
     * @return array|string Returns true on success or array of errors
     */
    public function updateGroupMembers($accessToken, $groupId, array $newUserIds)
    {
        if (!$accessToken) {
            return null;
        }

        // إزالة التكرارات من معرفات المستخدمين
        $newUserIds = array_unique($newUserIds);

        try {
            // جلب الأعضاء الحاليين للمجموعة
            $currentMembers = $this->getGroupMembers($accessToken, $groupId);
            if (isset($currentMembers['error'])) {
                return $currentMembers; // إرجاع الخطأ إذا حدث أثناء جلب الأعضاء
            }

            // استخراج معرفات الأعضاء الحاليين
            $currentUserIds = array_map(function ($member) {
                return $member->getId();
            }, $currentMembers);

            // تحديد الأعضاء الذين يجب إزالتهم (الذين ليسوا في القائمة الجديدة)
            $userIdsToRemove = array_diff($currentUserIds, $newUserIds);

            // تحديد الأعضاء الذين يجب إضافتهم (الذين ليسوا في الأعضاء الحاليين)
            $userIdsToAdd = array_diff($newUserIds, $currentUserIds);

            // إزالة الأعضاء الذين تم تحديدهم
            if (!empty($userIdsToRemove)) {
                $removeResult = $this->removeMembersFromGroup($accessToken, $groupId, $userIdsToRemove);
                if ($removeResult !== true) {
                    // تسجيل الخطأ وإرجاعه
                    return $removeResult;
                }
            }

            // إضافة الأعضاء الجدد
            if (!empty($userIdsToAdd)) {
                $addResult = $this->addMembersToGroup($accessToken, $groupId, $userIdsToAdd);
                if ($addResult !== true) {
                    // تسجيل الخطأ وإرجاعه
                    return $addResult;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('خطأ في تحديث أعضاء المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * جلب الأعضاء الحاليين للمجموعة
     *
     * @param string $accessToken
     * @param string $groupId
     * @return array|\Microsoft\Graph\Model\DirectoryObject[]
     */
    public function getGroupMembers($accessToken, $groupId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $members = $this->graph->createRequest('GET', "/groups/{$groupId}/members")
                ->setReturnType(\Microsoft\Graph\Model\DirectoryObject::class)
                ->execute();

            return $members;
        } catch (\Exception $e) {
            Log::error('خطأ في جلب أعضاء المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * إزالة مجموعة من الأعضاء من المجموعة مرة واحدة باستخدام طلب دفعة
     *
     * @param string $accessToken
     * @param string $groupId
     * @param array $userIds Array of user IDs to remove
     * @return array|string Returns true on success or array of errors
     */
    public function removeMembersFromGroup($accessToken, $groupId, array $userIds)
    {
        if (!$accessToken) {
            return null;
        }

        // إزالة التكرارات من معرفات المستخدمين
        $userIds = array_unique($userIds);

        // تهيئة Access Token في رأس الطلب
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        try {
            $batchRequests = [];
            $responses = [];

            // تقسيم معرفات المستخدمين إلى دفعات بحجم 20 طلبًا كحد أقصى
            $chunks = array_chunk($userIds, 20);
            $batchId = 1;

            foreach ($chunks as $chunk) {
                foreach ($chunk as $userId) {
                    $requestId = "req{$batchId}";
                    $batchRequests[] = [
                        'id' => $requestId,
                        'method' => 'DELETE',
                        'url' => "/groups/{$groupId}/members/{$userId}/\$ref",
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],
                    ];
                    $batchId++;
                }

                // إرسال الدفعة الحالية
                $responses = array_merge($responses, $this->sendBatchRequest($headers, $batchRequests));

                // إعادة تعيين مصفوفة الطلبات للدفعة التالية
                $batchRequests = [];
            }

            // التحقق من الأخطاء في الردود
            $errors = [];
            foreach ($responses as $response) {
                if (isset($response['status']) && $response['status'] >= 400) {
                    $errors[] = [
                        'request_id' => $response['id'] ?? null,
                        'status' => $response['status'],
                        'body' => $response['body'],
                    ];
                }
            }

            if (!empty($errors)) {
                Log::error('أخطاء أثناء إزالة الأعضاء من المجموعة: ', $errors);
                return $errors;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('خطأ في إزالة الأعضاء من المجموعة: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * إرسال طلب دفعة إلى Microsoft Graph API
     *
     * @param array $headers
     * @param array $batchRequests
     * @return array
     */
    protected function sendBatchRequest(array $headers, array $batchRequests)
    {
        // إعداد هيكل طلب الدُفعة
        $batchBody = [
            'requests' => $batchRequests
        ];

        try {
            $response = $this->client->post('$batch', [ // إزالة الشرطة المائلة من بداية المسار
                'headers' => $headers,
                'json'    => $batchBody,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            return $responseBody['responses'] ?? [];
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال طلب الدُفعة: ' . $e->getMessage());
            throw $e;
        }
    }
}
