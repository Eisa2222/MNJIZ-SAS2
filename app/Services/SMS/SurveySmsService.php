<?php

namespace App\Services\SMS;

use App\Enums\Survey\SurveyStatus;
use App\Enums\Survey\SurveyType;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Survey\Survey;
use App\Models\Survey\SurveyResponse;
use App\Services\General\UrlShortenerService;
use Illuminate\Support\Facades\Log;

class SurveySmsService
{
    public function __construct(private SmsService $smsService, private UrlShortenerService $urlShortener) {}


    public function sendSurveyByType(SurveyType $type, array $customerIds, array $variables = [])
    {
        $survey = Survey::where('type', $type)->where('status', SurveyStatus::Active)->first();

        if (!$survey) {
            return [
                'success' => false,
                'message' => "لا يوجد استبيان نشط  ",
                'success_count' => 0,
                'failed_count' => count($customerIds)
            ];
        }

        return $this->sendSurvey($survey, $customerIds, $variables);
    }

    public function sendSurvey(Survey $survey, array $customerIds, array $variables = [])
    {
        if ($survey->status !== SurveyStatus::Active) {
            return [
                'success' => false,
                'message' => 'الاستبيان غير نشط',
                'success_count' => 0,
                'failed_count' => count($customerIds)
            ];
        }

        $successCount   = 0;
        $failedCount    = 0;
        $results        = [];

        foreach ($customerIds as $customerId) {
            try {
                $customer = Customers::find($customerId);

                if (!$customer) {
                    $failedCount++;
                    $results[] = [
                        'customer_id'   => $customerId,
                        'success'       => false,
                        'message'       => 'العميل غير موجود'
                    ];
                    continue;
                }

                $surveyResponse = $this->createSurveyResponse($survey, $customerId);

                $originalUrl    = $surveyResponse->getPublicUrl();
                $shortUrl       = $this->urlShortener->shorten($originalUrl);

                $messageVariables = array_merge([
                    'name'          => $customer->name ?? 'عميلنا الكريم',
                    'survey_title'  => $survey->title,
                    'survey_url'    => $shortUrl,
                ], $variables);

                $message = $this->formatMessage($survey->message_template, $messageVariables);

                $result = $this->smsService->sendSmsToNumber($message, $customer->contact_number);

                if ($result['success']) {
                    $successCount++;
                    $results[] = [
                        'customer_id' => $customerId,
                        'customer_name' => $customer->name,
                        'survey_token' => $surveyResponse->token,
                        'survey_url' => $shortUrl,
                        'success' => true,
                        'message' => 'تم الإرسال بنجاح'
                    ];

                    Log::info('تم إرسال استبيان SMS', [
                        'survey_id' => $survey->id,
                        'customer_id' => $customerId,
                        'token' => $surveyResponse->token
                    ]);
                } else {
                    $failedCount++;
                    $results[] = [
                        'customer_id' => $customerId,
                        'customer_name' => $customer->name,
                        'success' => false,
                        'message' => $result['message']
                    ];

                    $surveyResponse->delete();
                }
            } catch (\Exception $e) {
                $failedCount++;
                $results[] = [
                    'customer_id' => $customerId,
                    'success' => false,
                    'message' => 'خطأ في الإرسال: ' . $e->getMessage()
                ];

                Log::error('خطأ في إرسال استبيان SMS', [
                    'customer_id' => $customerId,
                    'survey_id' => $survey->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'success'       => $successCount > 0,
            'message'       => "تم إرسال {$successCount} من " . count($customerIds) . " استبيان بنجاح",
            'success_count' => $successCount,
            'failed_count'  => $failedCount,
            'results'       => $results
        ];
    }

    protected function createSurveyResponse(Survey $survey, int $customerId): SurveyResponse
    {
        return SurveyResponse::create([
            'survey_id'         => $survey->id,
            'customer_id'       => $customerId,
            'status'            => 'pending',
        ]);
    }

    protected function formatMessage(string $template, array $variables): string
    {
        $message = $template;

        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }
}