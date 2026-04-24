<?php

namespace App\Services\Microsoft\GraphBase;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\User as GraphUser;
use Microsoft\Graph\Exception\GraphException;
use Illuminate\Support\Carbon;

class GraphBase
{
    protected Graph $graph;
    protected Client $client;
    protected array $settings;

    // Constants
    protected const GRAPH_BASE_URL          = 'https://graph.microsoft.com/v1.0/';
    protected const OAUTH_BASE_URL          = 'https://login.microsoftonline.com/';
    protected const GRAPH_SCOPE             = 'https://graph.microsoft.com/.default';
    protected const TOKEN_CACHE_KEY         = 'microsoft_app_access_token';
    protected const TOKEN_BUFFER_MINUTES    = 5;
    protected const DEFAULT_TOKEN_EXPIRY    = 3600;
    protected const MAX_BATCH_REQUESTS      = 20;

    /**
     * Constructor
     * 
     * @throws Exception
     */
    public function __construct()
    {
        $this->settings = $this->getMicrosoftSettings();

        if (empty($this->settings)) {
            throw new Exception('إعدادات Microsoft Graph غير متوفرة');
        }

        $this->initializeClients();
    }


    private function initializeClients(): void
    {
        $this->client = new Client([
            'base_uri'          => self::GRAPH_BASE_URL,
            'timeout'           => 30,
            'connect_timeout'   => 10,
        ]);

        $this->graph = new Graph();
    }


    // تجديد رمز الوصول للمستخدم
    public function refreshAccessToken(User $user): ?string
    {
        if (!$user->microsoft_refresh_token) {
            Log::error('لا يوجد رمز تحديث متاح للمستخدم', ['user_id' => $user->id]);
            return null;
        }

        try {
            $response = $this->client->post($this->getTokenEndpoint(), [
                'form_params' => [
                    'client_id'     => $this->settings['client_id'],
                    'client_secret' => $this->settings['client_secret'],
                    'refresh_token' => $user->microsoft_refresh_token,
                    'grant_type'    => 'refresh_token',
                    'scope'         => self::GRAPH_SCOPE,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['access_token'])) {
                Log::error('لم يتم تلقي رمز الوصول من استجابة تجديد الرمز');
                return null;
            }

            $this->updateUserTokens($user, $data);

            return $data['access_token'];
        } catch (ClientException $e) {
            $this->logClientException($e, 'تجديد رمز الوصول');
            return null;
        } catch (Exception $e) {
            Log::error('خطأ في تجديد رمز الوصول', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


    //تحديث رموز المستخدم
    private function updateUserTokens(User $user, array $tokenData): void
    {
        $user->update([
            'microsoft_token'         => $tokenData['access_token'],
            'microsoft_token_expires' => Carbon::now('UTC')->addSeconds($tokenData['expires_in']),
            'microsoft_refresh_token' => $tokenData['refresh_token'] ?? $user->microsoft_refresh_token,
        ]);
    }


    // الحصول على إعدادات Microsoft من قاعدة البيانات
    public function getMicrosoftSettings(): array
    {
        $setting = Settings::current();

        if (!$setting) {
            Log::error('لا توجد إعدادات في جدول الإعدادات');
            return [];
        }

        return [
            'client_id'     => $setting->microsoft_client_id,
            'client_secret' => $setting->microsoft_client_secret,
            'redirect_uri'  => $setting->microsoft_redirect_uri,
            'tenant_id'     => $setting->microsoft_tenant_id ?? 'common',
        ];
    }


    // التحقق من إعدادات OAuth لـ Microsoft
    public function verifySettings(): bool
    {
        if (!$this->areSettingsComplete()) {
            Log::error('إعدادات OAuth لـ Microsoft غير مكتملة');
            return false;
        }

        try {
            $response = $this->client->post($this->getTokenEndpoint(), [
                'form_params' => [
                    'client_id'     => $this->settings['client_id'],
                    'client_secret' => $this->settings['client_secret'],
                    'grant_type'    => 'client_credentials',
                    'scope'         => self::GRAPH_SCOPE,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            return isset($data['access_token']);
        } catch (ClientException $e) {
            $this->logClientException($e, 'التحقق من إعدادات OAuth');
            return false;
        } catch (Exception $e) {
            Log::error('فشل في التحقق من إعدادات OAuth لـ Microsoft', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    // التحقق من اكتمال الإعدادات
    private function areSettingsComplete(): bool
    {
        $required = ['client_id', 'client_secret', 'redirect_uri'];

        foreach ($required as $key) {
            if (empty($this->settings[$key])) {
                return false;
            }
        }

        return true;
    }


    // الحصول على رمز الوصول للتطبيق
    public function getAppAccessToken(): ?string
    {
        if (Cache::has(self::TOKEN_CACHE_KEY)) {
            return Cache::get(self::TOKEN_CACHE_KEY);
        }

        try {
            $response = $this->client->post($this->getTokenEndpoint(), [
                'form_params' => [
                    'client_id' => $this->settings['client_id'],
                    'client_secret' => $this->settings['client_secret'],
                    'grant_type' => 'client_credentials',
                    'scope' => self::GRAPH_SCOPE,
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            $accessToken = $body['access_token'] ?? null;

            if ($accessToken) {
                $expiresIn = $body['expires_in'] ?? self::DEFAULT_TOKEN_EXPIRY;
                Cache::put(self::TOKEN_CACHE_KEY, $accessToken, now()->addSeconds($expiresIn - 300));
                return $accessToken;
            }

            Log::error('لم يتم تلقي رمز الوصول من Microsoft Graph API');
            return null;
        } catch (ClientException $e) {
            $this->logClientException($e, 'الحصول على رمز الوصول للتطبيق');
            return null;
        } catch (Exception $e) {
            Log::error('خطأ في الحصول على رمز الوصول للتطبيق', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    // الحصول على رمز الوصول صالح للمستخدم
    public function getValidUserAccessToken(User $user): ?string
    {
        if (!$user->microsoft_token) {
            Log::warning('لا يوجد رمز وصول للمستخدم', ['user_id' => $user->id]);
            return null;
        }

        if ($this->isTokenExpired($user->microsoft_token_expires)) {
            return $this->refreshAccessToken($user);
        }

        return $user->microsoft_token;
    }

    //  التحقق من انتهاء صلاحية الرمز
    private function isTokenExpired(?Carbon $expiresAt): bool
    {
        if (!$expiresAt) {
            return true;
        }

        return $expiresAt->lte(Carbon::now('UTC')->addMinutes(self::TOKEN_BUFFER_MINUTES));
    }

    //  تعيين رمز الوصول في كائن Graph
    public function setGraphAccessToken(string $accessToken): void
    {
        $this->graph->setAccessToken($accessToken);
    }

    //  الحصول على معرف المستخدم بناءً على البريد الإلكتروني
    public function getUserIdByEmail(string $accessToken, string $email): ?string
    {
        if (!$accessToken || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::error('معطيات غير صالحة لجلب معرف المستخدم', [
                'has_token' => !empty($accessToken),
                'email' => $email
            ]);
            return null;
        }

        $this->setGraphAccessToken($accessToken);

        try {
            $user = $this->graph->createRequest("GET", "/users/{$email}")
                ->setReturnType(GraphUser::class)
                ->execute();

            return $user->getId();
        } catch (GraphException $e) {
            Log::error('GraphException في getUserIdByEmail', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        } catch (Exception $e) {
            Log::error('خطأ في getUserIdByEmail', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    //  الحصول على معرفات المستخدمين بالدفعات
    public function getUserIdsByEmails(string $accessToken, array $emails): array
    {
        if (!$accessToken || empty($emails)) {
            Log::error('معطيات غير صالحة لجلب معرفات المستخدمين');
            return [];
        }

        // تصفية البريد الإلكتروني الصالح
        $validEmails = array_filter($emails, fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        if (empty($validEmails)) {
            Log::error('لا توجد عناوين بريد إلكتروني صالحة');
            return [];
        }

        // تقسيم إلى دفعات
        $chunks = array_chunk($validEmails, self::MAX_BATCH_REQUESTS);
        $results = [];

        foreach ($chunks as $chunk) {
            $batchResult = $this->processBatchRequest($accessToken, $chunk);
            $results = array_merge($results, $batchResult);
        }

        return $results;
    }

    // معالجة طلب الدفعة
    private function processBatchRequest(string $accessToken, array $emails): array
    {
        $batchRequests = [];
        foreach ($emails as $index => $email) {
            $batchRequests[] = [
                'id' => "req{$index}",
                'method' => 'GET',
                'url' => "/users/{$email}",
                'headers' => ['Content-Type' => 'application/json'],
            ];
        }

        try {
            $response = $this->client->post('https://graph.microsoft.com/v1.0/$batch', [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => ['requests' => $batchRequests],
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            return $this->parseBatchResponse($responseBody, $emails);
        } catch (Exception $e) {
            Log::error('خطأ في إرسال الطلب المجمّع', [
                'error' => $e->getMessage(),
                'emails_count' => count($emails)
            ]);
            return [];
        }
    }

    //  تحليل استجابة الدفعة
    private function parseBatchResponse(array $responseBody, array $emails): array
    {
        $responses = $responseBody['responses'] ?? [];
        $emailToIdMap = [];

        foreach ($responses as $response) {
            $requestId = $response['id'] ?? null;
            if (!$requestId) continue;

            $emailIndex = (int) str_replace('req', '', $requestId);
            $email = $emails[$emailIndex] ?? null;

            if (!$email) continue;

            if (isset($response['status']) && $response['status'] === 200) {
                $body = is_string($response['body'])
                    ? json_decode($response['body'], true)
                    : $response['body'];

                $emailToIdMap[$email] = $body['id'] ?? null;
            } else {
                Log::error('فشل في جلب معرف المستخدم', [
                    'email' => $email,
                    'status' => $response['status'] ?? 'unknown',
                    'body' => $response['body'] ?? 'empty'
                ]);
                $emailToIdMap[$email] = null;
            }
        }

        return $emailToIdMap;
    }

    //  * إرسال بريد إلكتروني
    public function sendEmail(
        string $to,
        string $subject,
        string $body,
        array $attachments = [],
        string $bodyType = 'HTML'
    ): bool {
        $setting = Settings::current();

        if (!$setting || !$setting->main_email) {
            Log::error('البريد الإلكتروني الرئيسي غير محدد في الإعدادات');
            return false;
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Log::error('عنوان البريد الإلكتروني المستلم غير صالح', ['to' => $to]);
            return false;
        }

        $accessToken = $this->getAppAccessToken();
        if (!$accessToken) {
            Log::error('فشل في الحصول على رمز الوصول لإرسال البريد الإلكتروني');
            return false;
        }

        $payload = $this->buildEmailPayload($to, $subject, $body, $attachments, $bodyType);
        $url = "https://graph.microsoft.com/v1.0/users/{$setting->main_email}/sendMail";

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $success = $response->getStatusCode() === 202;

            if ($success) {
                Log::info('تم إرسال البريد الإلكتروني بنجاح', [
                    'to' => $to,
                    'subject' => $subject
                ]);
            }

            return $success;
        } catch (RequestException $e) {
            Log::error('فشل في إرسال البريد الإلكتروني', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('خطأ عام في إرسال البريد الإلكتروني', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    //  بناء حمولة البريد الإلكتروني
    private function buildEmailPayload(
        string $to,
        string $subject,
        string $body,
        array $attachments,
        string $bodyType
    ): array {
        return [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => $bodyType,
                    'content' => $body,
                ],
                'toRecipients' => [
                    [
                        'emailAddress' => [
                            'address' => $to,
                        ],
                    ],
                ],
                'attachments' => $attachments,
            ],
            'saveToSentItems' => false,
        ];
    }

    // الحصول على نقطة نهاية الرمز
    private function getTokenEndpoint(): string
    {
        return self::OAUTH_BASE_URL . $this->settings['tenant_id'] . '/oauth2/v2.0/token';
    }

    //  تسجيل استثناء العميل
    private function logClientException(ClientException $e, string $operation): void
    {
        $response = $e->getResponse();
        $body = $response ? json_decode($response->getBody(), true) : null;

        Log::error("ClientException في {$operation}", [
            'status_code' => $response ? $response->getStatusCode() : 'unknown',
            'error' => $body['error'] ?? 'unknown',
            'error_description' => $body['error_description'] ?? $e->getMessage()
        ]);
    }
}
