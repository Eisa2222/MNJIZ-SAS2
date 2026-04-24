<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\User as GraphUser;

/*
|--------------------------------------------------------------------------
| خدمة الأساس لـ Microsoft Graph
|--------------------------------------------------------------------------
| تحتوي هذه الخدمة على وظائف أساسية للتعامل مع Microsoft Graph API،
| مثل الحصول على رموز الوصول، تجديد الرموز، إدارة الإعدادات،
| وإرسال البريد الإلكتروني.
*/
class MicrosoftGraphBaseService
{
    protected $graph;
    protected $client;
    protected $settings;

    /*
    |--------------------------------------------------------------------------
    | تهيئة الخدمة الأساسية
    |--------------------------------------------------------------------------
    | يقوم هذا القسم بتهيئة إعدادات Microsoft، إنشاء عميل Guzzle،
    | وتكوين كائن Microsoft Graph.
    */
    public function __construct()
    {
        // Lazy — never query the `settings` table at construction. The DI
        // container resolves this service (bound as singleton in
        // AppServiceProvider) during artisan boot, BEFORE migrate:fresh
        // has a chance to create the table. Settings are loaded on first
        // actual use via the protected bootIfNeeded() helper below.
        $this->client = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
        ]);

        $this->graph = new Graph();
    }

    protected function bootIfNeeded(): bool
    {
        if (is_array($this->settings) && ! empty($this->settings)) {
            return true;
        }
        try {
            $this->settings = $this->getMicrosoftSettings();
        } catch (\Throwable $e) {
            Log::warning('MicrosoftGraphBaseService settings load failed: '.$e->getMessage());
            $this->settings = [];
        }
        if (empty($this->settings)) {
            Log::warning('إعدادات Microsoft غير مكونة بشكل صحيح.');
            return false;
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | إدارة رموز الوصول
    |--------------------------------------------------------------------------
    | يحتوي هذا القسم على وظائف للحصول على وتجديد رموز الوصول
    | للتطبيق والمستخدمين.
    */

    /**
     * تجديد رمز الوصول للمستخدم باستخدام رمز التحديث الخاص به
     *
     * @param User $user
     * @return string|null
     */
    public function refreshAccessToken(User $user)
    {
        if (!$user->microsoft_refresh_token) {
            Log::error('لا يوجد رمز تحديث متاح للمستخدم ID: ' . $user->id);
            return null;
        }

        try {
            $client = new Client();

            $response = $client->post('https://login.microsoftonline.com/' . $this->settings['tenant_id'] . '/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $this->settings['client_id'],
                    'client_secret' => $this->settings['client_secret'],
                    'refresh_token' => $user->microsoft_refresh_token,
                    'grant_type' => 'refresh_token',
                    'scope' => 'https://graph.microsoft.com/.default',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['access_token'])) {
                // تحديث بيانات المستخدم
                $user->update([
                    'microsoft_token' => $data['access_token'],
                    'microsoft_token_expires' => now('UTC')->addSeconds($data['expires_in']),
                    'microsoft_refresh_token' => $data['refresh_token'] ?? $user->microsoft_refresh_token, // أحياناً لا يتم إرجاع رمز التحديث
                ]);

                return $data['access_token'];
            }

            Log::error('فشل في تجديد رمز الوصول للمستخدم ID: ' . $user->id);
            return null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            Log::error('خطأ في تجديد رمز الوصول: ' . ($body['error_description'] ?? $e->getMessage()));
            return null;
        } catch (\Exception $e) {
            Log::error('استثناء في تجديد رمز الوصول: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على إعدادات Microsoft من قاعدة البيانات
     *
     * @return array
     */
    public function getMicrosoftSettings()
    {
        $setting = Settings::current();

        if (!$setting) {
            Log::error('لا توجد إعدادات في جدول الإعدادات.');
            return [];
        }

        try {
            // فك تشفير client_secret إذا كان مخزنًا مشفرًا
            // قم بإلغاء التعليق وضبط الكود إذا لزم الأمر
            // $clientSecret = $setting->microsoft_client_secret ? Crypt::decryptString($setting->microsoft_client_secret) : null;
        } catch (\Exception $e) {
            Log::error('خطأ في فك تشفير سر العميل لـ Microsoft: ' . $e->getMessage());
            return [];
        }

        // تعيين إعدادات Socialite ديناميكيًا
        config([
            'services.microsoft.client_id' => $setting->microsoft_client_id,
            'services.microsoft.client_secret' => $setting->microsoft_client_secret,
            'services.microsoft.redirect' => $setting->microsoft_redirect_uri,
            'services.microsoft.tenant_id' => $setting->microsoft_tenant_id ?? 'common',
        ]);

        // إزالة تسجيل الإعدادات الحساسة
        // Log::error($setting);

        return [
            'client_id' => $setting->microsoft_client_id,
            'client_secret' => $setting->microsoft_client_secret,
            'redirect_uri' => $setting->microsoft_redirect_uri,
            'tenant_id' => $setting->microsoft_tenant_id ?? 'common',
        ];
    }

    /**
     * التحقق من إعدادات OAuth لـ Microsoft بمحاولة الحصول على رمز وصول للتطبيق
     *
     * @return bool
     */
    public function verifySettings()
    {
        $settings = $this->settings;

        if (empty($settings['client_id']) || empty($settings['client_secret']) || empty($settings['redirect_uri'])) {
            Log::error('إعدادات OAuth لـ Microsoft غير مكتملة.');
            return false;
        }

        try {
            // محاولة الحصول على رمز الوصول باستخدام client_credentials
            $response = $this->client->post('https://login.microsoftonline.com/' . $settings['tenant_id'] . '/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $settings['client_id'],
                    'client_secret' => $settings['client_secret'],
                    'grant_type' => 'client_credentials',
                    'scope' => 'https://graph.microsoft.com/.default',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['access_token'])) {
                // الإعدادات صحيحة
                return true;
            }

            // الإعدادات غير صالحة
            Log::error('إعدادات OAuth لـ Microsoft غير صالحة. لم يتم تلقي رمز الوصول.');
            return false;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // خطأ من العميل مثل client_secret غير صالح
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            Log::error('ClientException في OAuth لـ Microsoft: ' . ($body['error_description'] ?? $e->getMessage()));
            return false;
        } catch (\Exception $e) {
            Log::error('فشل في التحقق من إعدادات OAuth لـ Microsoft: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على رمز الوصول للتطبيق باستخدام منح client_credentials
     *
     * @return string|null
     */
    public function getAppAccessToken()
    {
        // التحقق مما إذا كان رمز الوصول مخزنًا مؤقتًا
        if (Cache::has('microsoft_app_access_token')) {
            return Cache::get('microsoft_app_access_token');
        }

        try {
            $response = $this->client->post('https://login.microsoftonline.com/' . $this->settings['tenant_id'] . '/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $this->settings['client_id'],
                    'client_secret' => $this->settings['client_secret'],
                    'grant_type' => 'client_credentials',
                    'scope' => 'https://graph.microsoft.com/.default',
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            $accessToken = $body['access_token'] ?? null;

            if ($accessToken) {
                $expiresIn = $body['expires_in'] ?? 3600; // استخدام 3600 ثانية في حالة عدم وجود قيمة

                // تخزين رمز الوصول مؤقتًا لمدة حياته
                Cache::put('microsoft_app_access_token', $accessToken, now()->addSeconds($expiresIn));

                return $accessToken;
            }
            Log::error('لم يتم تلقي رمز الوصول من Microsoft Graph API.');
            return null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            // Log::error('خطأ في الحصول على رمز الوصول للتطبيق: ' . ($body['error_description'] ?? $e->getMessage()));
            return null;
        } catch (\Exception $e) {
            // Log::error('خطأ في الحصول على رمز الوصول للتطبيق: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على رمز الوصول صالح للمستخدم وتجديده إذا لزم الأمر
     *
     * @param User $user
     * @return string|null
     */
    public function getValidUserAccessToken(User $user)
    {
        if ($user->microsoft_token_expires && $user->microsoft_token_expires->lte(now('UTC')->addMinutes(5))) {
            return $this->refreshAccessToken($user);
        }

        return $user->microsoft_token;
    }

    /**
     * تعيين رمز الوصول في كائن Graph
     *
     * @param string $accessToken
     * @return void
     */
    public function setGraphAccessToken($accessToken)
    {
        $this->graph->setAccessToken($accessToken);
    }

    /*
    |--------------------------------------------------------------------------
    | إدارة المستخدمين
    |--------------------------------------------------------------------------
    | يحتوي هذا القسم على وظائف للحصول على معرفات المستخدمين بناءً على
    | عناوين بريدهم الإلكتروني، باستخدام طلبات مجمعة عند الحاجة.
    */

    /**
     * الحصول على معرف المستخدم بناءً على عنوان بريده الإلكتروني
     *
     * @param string $accessToken
     * @param string $email
     * @return string|null
     */
    public function getUserIdByEmail($accessToken, $email)
    {
        if (!$accessToken) {
            Log::error('رمز الوصول مطلوب لجلب معرف المستخدم بواسطة البريد الإلكتروني.');
            return null;
        }

        $this->setGraphAccessToken($accessToken);

        try {
            $user = $this->graph->createRequest("GET", "/users/{$email}")
                ->setReturnType(GraphUser::class)
                ->execute();

            return $user->getId() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error("GraphException في getUserIdByEmail: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("استثناء في getUserIdByEmail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على معرفات المستخدمين بناءً على عناوين بريدهم الإلكتروني باستخدام الطلبات المجمعة
     *
     * @param string $accessToken
     * @param array $emails
     * @return array [email => userId, ...]
     */
    public function getUserIdsByEmails($accessToken, array $emails)
    {
        if (!$accessToken) {
            Log::error('رمز الوصول مطلوب لجلب معرفات المستخدمين بواسطة عناوين البريد الإلكتروني.');
            return [];
        }

        $this->setGraphAccessToken($accessToken);

        $batchRequests = [];
        foreach ($emails as $index => $email) {
            $requestId = "req{$index}";
            $batchRequests[] = [
                'id' => $requestId,
                'method' => 'GET',
                'url' => "/users/{$email}",
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
            ];
        }

        $batchBody = [
            'requests' => $batchRequests
        ];

        try {
            $response = $this->client->post('https://graph.microsoft.com/v1.0/$batch', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $batchBody,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            $responses = $responseBody['responses'] ?? [];

            $emailToIdMap = [];
            foreach ($responses as $response) {
                $requestId = $response['id'] ?? null;
                if ($requestId) {
                    // استخراج البريد الإلكتروني من معرف الطلب
                    $emailIndex = array_search($requestId, array_column($batchRequests, 'id'));
                    if ($emailIndex !== false) {
                        $email = $emails[$emailIndex];
                    } else {
                        // في حال عدم العثور على البريد الإلكتروني بناءً على معرف الطلب
                        $email = str_replace('req', '', $requestId);
                    }

                    // استخراج المعرف من الرد
                    if (isset($response['status']) && $response['status'] === 200) {
                        // تحقق مما إذا كانت 'body' سلسلة نصية أم مصفوفة
                        if (is_string($response['body'])) {
                            $body = json_decode($response['body'], true);
                        } else {
                            $body = $response['body'];
                        }

                        $emailToIdMap[$email] = $body['id'] ?? null;
                    } else {
                        Log::error("خطأ في جلب معرف المستخدم للبريد الإلكتروني: {$email}", [
                            'status' => $response['status'],
                            'body' => $response['body']
                        ]);
                        $emailToIdMap[$email] = null;
                    }
                }
            }

            return $emailToIdMap;
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال الطلب المجمّع لجلب معرفات المستخدمين: ' . $e->getMessage());
            return [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | إرسال البريد الإلكتروني
    |--------------------------------------------------------------------------
    | يحتوي هذا القسم على وظائف لإرسال رسائل البريد الإلكتروني باستخدام
    | Microsoft Graph API.
    */

    /**
     * إرسال بريد إلكتروني باستخدام Microsoft Graph API
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $bodyType
     * @return bool
     */
    public function sendEmail($to, $subject, $body,$attachments = [], $bodyType = 'HTML')
    {
        $setting = Settings::current();
        $accessToken = $this->getAppAccessToken();

        if (!$accessToken) {
            Log::error('فشل في الحصول على رمز الوصول لإرسال البريد الإلكتروني.');
            return false;
        }

        $url = "https://graph.microsoft.com/v1.0/users/" . $setting->main_email . "/sendMail";

        $payload = [
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
                // if has attachments
                'attachments' => $attachments,
            ],
            'saveToSentItems' => 'false',
        ];

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return $response->getStatusCode() == 202;
        } catch (\Exception $e) {
            Log::error('فشل في إرسال البريد الإلكتروني عبر Microsoft Graph API: ' . $e->getMessage());
            return false;
        }
    }
}
