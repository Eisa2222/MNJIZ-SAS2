<?php

namespace App\Services;

use Beta\Microsoft\Graph\Model\Group;
use Beta\Microsoft\Graph\Model\Message;
use Beta\Microsoft\Graph\Model\TodoTaskList;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\PlannerPlan;

class PlannerPlanService extends MicrosoftGraphBaseService
{
    public function __construct()
    {
        parent::__construct();
    }


    // function createGroup($accessToken,$displayName ="one", $mailNickname="erer", $description = 'ddd')
    // {
    //     if (!$accessToken) {
    //         // Log::error('Access token is required to fetch user files.');
    //         return null;
    //     }

    //     $this->graph->setAccessToken($accessToken);
    //     $client = new Client();

    //     $url = 'https://graph.microsoft.com/v1.0/groups';

    //     $body = [
    //         "displayName" => $displayName,
    //         "mailNickname" => $mailNickname,
    //         "mailEnabled" => false,
    //         "securityEnabled" => true,
    //         "groupTypes" => ["Unified"],
    //         "description" => $description
    //     ];

    //     try {
    //         $response = $client->post($url, [
    //             'headers' => [
    //                 'Authorization' => "Bearer $accessToken",
    //                 'Content-Type' => 'application/json',
    //             ],
    //             'json' => $body,
    //         ]);

    //         $group = json_decode($response->getBody(), true);
    //         return $group; // تحتوي على تفاصيل المجموعة المنشأة، بما في ذلك "id"
    //     } catch (\GuzzleHttp\Exception\RequestException $e) {
    //         // التعامل مع الأخطاء
    //         if ($e->hasResponse()) {
    //             $error = json_decode($e->getResponse()->getBody(), true);
    //             // يمكنك تسجيل الخطأ أو عرضه حسب الحاجة
    //             return $error;
    //         }
    //         return ['error' => $e->getMessage()];
    //     }
    // }



    // لجلب المجموعات
    // function getGroups($accessToken)
    // {
    //     if (!$accessToken) {
    //         // Log::error('Access token is required to fetch user files.');
    //         return null;
    //     }

    //     $this->graph->setAccessToken($accessToken);

    //     $groups = $this->graph->createRequest("GET", "/groups?\$filter=groupTypes/any(c:c eq 'Unified')")
    //         ->setReturnType(\Microsoft\Graph\Model\Group::class)
    //         ->execute();

    //     return $groups; // إرجاع المجموعات مباشرةً

    // }


    // public function getGroupIdByName($accessToken, $groupName)
    // {
    //     if (!$accessToken) {
    //         return null;
    //     }

    //     $this->graph->setAccessToken($accessToken);

    //     try {
    //         $response = $this->graph->createRequest("GET", "/groups?\$filter=displayName eq '$groupName'")
    //             ->setReturnType(\Microsoft\Graph\Model\Group::class)
    //             ->execute();

    //         if (count($response) > 0) {
    //             return $response[0]->getId();
    //         } else {
    //             return ['error' => 'لم يتم العثور على المجموعة.'];
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Error fetching group ID: ' . $e->getMessage());
    //         return ['error' => $e->getMessage()];
    //     }
    // }

    // public function getOrCreateDefaultGroup($accessToken)
    // {
    //     $groupName = 'All Company';
    //     $groupId = $this->getGroupIdByName($accessToken, $groupName);

    //     if (isset($groupId['error'])) {
    //         // محاولة إنشاء المجموعة إذا لم تكن موجودة
    //         $newGroupId = $this->createGroup($groupName, 'alburhanCompany', 'مجموعة افتراضية لجميع موظفي الشركة', 'Private');

    //         if (isset($newGroupId['error'])) {
    //             return ['error' => 'فشل في إنشاء مجموعة "All Company": ' . $newGroupId['error']];
    //         }

    //         // إخفاء المجموعة من قوائم العناوين العامة إذا لزم الأمر


    //         return $newGroupId;
    //     }

    //     return $groupId;
    // }



    // public function createGroup($displayName, $mailNickname, $description = 'No', $visibility = 'Private')
    // {
    //     try {
    //         // البحث عن المستخدم بواسطة البريد الإلكتروني
    //         $ownerEmail = 'm@e-tec.sa'; // يمكنك تعديل هذا بناءً على الحاجة
    //         $response = $this->graph->createRequest("GET", "/users?\$filter=userPrincipalName eq '$ownerEmail'")
    //             ->setReturnType(\Microsoft\Graph\Model\User::class)
    //             ->execute();

    //         if (count($response) == 0) {
    //             throw new \Exception("لم يتم العثور على المستخدم بالعنوان: $ownerEmail");
    //         }

    //         $user = $response[0];
    //         $userId = $user->getId();

    //         // بناء روابط المالكين
    //         $owners = ["https://graph.microsoft.com/v1.0/users/$userId"];



    //         $group = [
    //             "displayName" => $displayName,
    //             "mailNickname" => $mailNickname,
    //             "mailEnabled" => true,
    //             "securityEnabled" => false,
    //             "groupTypes" => ["Unified"],
    //             "description" => $description,
    //             "visibility" => $visibility,
    //             "owners@odata.bind" => $owners
    //         ];


    //         $createdGroup = $this->graph->createRequest("POST", "/groups")
    //             ->attachBody($group)
    //             ->setReturnType(Group::class)
    //             ->execute();
    //         return $createdGroup->getId();
    //     } catch (\Exception $e) {
    //         Log::error('Error creating group: ' . $e->getMessage());
    //         return ['error' => $e->getMessage()];
    //     }
    // }


    // الخطط

    //  عرض كل الخطط حسب المجموعة - المجموعة ثابته هنا وهي all company
    public function listPlans($accessToken)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        $groupId = $this->getOrCreateDefaultGroup($accessToken);

        try {
            $response = $this->graph->createRequest("GET", "/groups/$groupId/planner/plans")
                ->setReturnType(PlannerPlan::class)
                ->execute();

            if (count($response) > 0) {
                return $response;
            } else {
                return ['error' => 'لم يتم العثور على خطط.'];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching group ID: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    // انشاء خطة جديدة


    public function createPlan($accessToken, $groupId, $planTitle)
    {
        if (!$accessToken) {
            Log::error('Access Token غير موجود.');
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $planData = [
                'owner' => $groupId,
                'title' => $planTitle
            ];

            Log::info('إرسال طلب إنشاء خطة Planner', ['groupId' => $groupId, 'planTitle' => $planTitle]);

            $plan = $this->graph->createRequest('POST', '/planner/plans')
                ->addHeaders(['Content-Type' => 'application/json'])
                ->attachBody($planData)
                ->setReturnType(PlannerPlan::class)
                ->execute();

            Log::info('Planner Plan created successfully: ' . json_encode($plan));

            return $plan;
        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء خطة Planner: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // عرض تفاصيل الخطة
    public function getPlan($accessToken, $planId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $plan = $this->graph->createRequest("GET", "/planner/plans/$planId")
                ->setReturnType(PlannerPlan::class)
                ->execute();



            return $plan;
        } catch (\Exception $e) {
            Log::error('Error getting plan: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    // تعديل الخطة
    public function updatePlan($accessToken, $planId, $data)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // إعداد عميل Guzzle
            $client = new Client([
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            // الخطوة 1: جلب الخطة الحالية للحصول على eTag
            $response = $client->request('GET', "https://graph.microsoft.com/v1.0/planner/plans/$planId");
            $eTag = $response->getHeaderLine('ETag');

            if (!$eTag) {
                throw new \Exception('لم يتم العثور على eTag في رأس الاستجابة.');
            }

            // الخطوة 2: تحديث الخطة مع تضمين eTag في If-Match
            $updateResponse = $client->request('PATCH', "https://graph.microsoft.com/v1.0/planner/plans/$planId", [
                'headers' => [
                    'If-Match' => $eTag,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $updatedPlan = json_decode($updateResponse->getBody(), true);

            return $updatedPlan;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    // حذف الخطة
    public function deletePlan($accessToken, $planId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);
        try {
            // إعداد عميل Guzzle
            $client = new Client([
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
            ]);

            // الخطوة 1: جلب الخطة الحالية للحصول على eTag
            $response = $client->request('GET', "https://graph.microsoft.com/v1.0/planner/plans/$planId");
            $eTag = $response->getHeaderLine('ETag');

            if (!$eTag) {
                throw new \Exception('لم يتم العثور على eTag في رأس الاستجابة.');
            }

            // الخطوة 2: حذف الخطة مع تضمين eTag في If-Match
            $updateResponse = $client->request('DELETE', "https://graph.microsoft.com/v1.0/planner/plans/$planId", [
                'headers' => [
                    'If-Match' => $eTag,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $updatedPlan = json_decode($updateResponse->getBody(), true);

            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting group: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
