<?php

namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Model\PlannerBucket;

class PlannerBucketService extends MicrosoftGraphBaseService
{
    public function __construct()
    {
        parent::__construct();
    }





    public function listBucket($accessToken, $planId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);


        try {
            $response = $this->graph->createRequest("GET", "/planner/plans/{$planId}/buckets")
                ->setReturnType(PlannerBucket::class)
                ->execute();

            if (count($response) > 0) {
                return $response;
            } else {
                return ['error' => 'لم يتم العثور على بيانات.'];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching group ID: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    // انشاء صندوق جديد
    public function createBucket($accessToken, $planId, $bucketTitle)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        // $groupId =  $this->getOrCreateDefaultGroup($accessToken);

        // if ($groupId) {

        try {
            $bucket = [
                'planId' => $planId,
                'name' => $bucketTitle
            ];

            $newbucket = $this->graph->createRequest('POST', "/planner/buckets")
                ->addHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->attachBody($bucket)
                ->setReturnType(PlannerBucket::class) // تعيين نوع الإرجاع
                ->execute();

            return $newbucket;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching user files: ' . $e->getMessage());
            // Log::error('GraphException response: ' . $e->getResponseBody());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching user files: ' . $e->getMessage());
            return null;
        }
        // } else {
        //     return redirect('/dashboard')->with('error', 'إعدادات Microsoft Planner غير مكتملة أو غير صحيحة. الرجاء التواصل مع الإدارة.');
        // }
    }

    // عرض تفاصيل الصندوق
    public function getBucket($accessToken, $bucketId)
    {
        if (!$accessToken) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $plan = $this->graph->createRequest("GET", "/planner/buckets/{$bucketId}")
                ->setReturnType(PlannerBucket::class)
                ->execute();



            return $plan;
        } catch (\Exception $e) {
            Log::error('Error getting plan: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    // تعديل الصندوق
    public function updateBucket($accessToken, $bucketId, $newBucketTitle)
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
            $response = $client->request('GET', "https://graph.microsoft.com/v1.0/planner/buckets/{$bucketId}");
            $eTag = $response->getHeaderLine('ETag');

            if (!$eTag) {
                throw new \Exception('لم يتم العثور على eTag في رأس الاستجابة.');
            }

            $data = [
                "name" => $newBucketTitle,
            ];
            // الخطوة 2: تحديث الخطة مع تضمين eTag في If-Match
            $updateResponse = $client->request('PATCH', "https://graph.microsoft.com/v1.0/planner/buckets/{$bucketId}", [
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


    // حذف الصندوق
    public function deleteBucket($accessToken, $bucketId)
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
            $response = $client->request('GET', "https://graph.microsoft.com/v1.0/planner/buckets/{$bucketId}");
            $eTag = $response->getHeaderLine('ETag');

            if (!$eTag) {
                throw new \Exception('لم يتم العثور على eTag في رأس الاستجابة.');
            }

            // الخطوة 2: حذف الخطة مع تضمين eTag في If-Match
            $client->request('DELETE', "https://graph.microsoft.com/v1.0/planner/buckets/{$bucketId}", [
                'headers' => [
                    'If-Match' => $eTag,
                    'Content-Type' => 'application/json',
                ],
            ]);


            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting group: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    //لانشاء صندوق باسم مهام المشروع وارجاع المعرف الخاص به -- اذا كان موجود يتم ارجاع المعرف الخاص به
    public function getOrCreateProjectTasksBucket($accessToken, $planId, $bucketTitle = 'مهام المشروع')
    {
        if (!$accessToken) {
            return ['error' => 'رمز الوصول غير موجود.'];
        }

        // جلب جميع الصناديق في الخطة المحددة
        $bucketsResponse = $this->listBucket($accessToken, $planId);

        // التحقق من وجود خطأ في استجابة listBucket
        if (is_array($bucketsResponse) && isset($bucketsResponse['error'])) {
            return ['error' => 'خطأ في جلب الصناديق: ' . $bucketsResponse['error']];
        }

        // التحقق من وجود صندوق بالعنوان المطلوب
        foreach ($bucketsResponse as $bucket) {
            if ($bucket->getName() === $bucketTitle) {
                return $bucket->getId();
            }
        }

        // إذا لم يتم العثور على الصندوق، قم بإنشائه
        $newBucket = $this->createBucket($accessToken, $planId, $bucketTitle);

        if ($newBucket && method_exists($newBucket, 'getId')) {
            return $newBucket->getId();
        }

        // إذا فشل الإنشاء، إرجاع رسالة خطأ
        return ['error' => 'فشل في إنشاء الصندوق الجديد.'];
    }
}
