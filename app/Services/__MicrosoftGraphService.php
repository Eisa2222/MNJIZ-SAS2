<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use Beta\Microsoft\Graph\Model\Calendar;
// use Beta\Microsoft\Graph\ModelDriveItem;
use Beta\Microsoft\Graph\Model\DriveItem as ModelDriveItem;
use Beta\Microsoft\Graph\Model\Event;
use Beta\Microsoft\Graph\Model\ItemPreviewInfo;
use Beta\Microsoft\Graph\Model\Message;
use Beta\Microsoft\Graph\Model\Permission;
use Beta\Microsoft\Graph\Model\UploadSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\User as GraphUser;


class MicrosoftGraphService11
{
    protected $graph;

    protected $client;
    protected $settings;

    // public function __construct()
    // {
    //     $this->settings = $this->getMicrosoftSettings();

    //     if (empty($this->settings)) {
    //         Log::error('Microsoft settings are not configured properly.');
    //         return;
    //     }

    //     $accessToken = $this->getAccessToken();

    //     if ($accessToken) {
    //         $this->graph = new Graph();
    //         $this->graph->setAccessToken($accessToken);
    //     }

    //     $this->client = new Client([
    //         'base_uri' => 'https://graph.microsoft.com/v1.0/',
    //     ]);

    //     // الحصول على المستخدم الحالي وتعيين رمز الوصول إذا كان موجودًا

    // }

    public function __construct()
    {
        $this->settings = $this->getMicrosoftSettings();

        if (empty($this->settings)) {
            Log::error('Microsoft settings are not configured properly.');
            return;
        }

        $this->client = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
        ]);

        // تهيئة كائن Graph بدون تعيين رمز الوصول هنا
        $this->graph = new Graph();
    }


    public function refreshAccessToken(User $user)
    {
        if (!$user->microsoft_refresh_token) {
            Log::error('No refresh token available for user ID: ' . $user->id);
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
                    'scope' => 'openid profile Mail.Read Mail.Send User.Read',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['access_token'])) {
                // تحديث بيانات المستخدم
                $user->update([
                    'microsoft_token' => $data['access_token'],
                    'microsoft_token_expires' => now()->addSeconds($data['expires_in']),
                    'microsoft_refresh_token' => $data['refresh_token'] ?? $user->microsoft_refresh_token, // بعض الأحيان لا يعود refresh_token
                ]);

                return $data['access_token'];
            }

            Log::error('Failed to refresh access token for user ID: ' . $user->id);
            return null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            Log::error('Error refreshing access token: ' . ($body['error_description'] ?? $e->getMessage()));
            return null;
        } catch (\Exception $e) {
            Log::error('Exception refreshing access token: ' . $e->getMessage());
            return null;
        }
    }


    public function getMicrosoftSettings()
    {
        $setting = Settings::first();

        if (!$setting) {
            Log::error('No settings found in the settings table.');
            return [];
        }

        try {
            // فك تشفير client_secret إذا كانت موجودة
            // $clientSecret = $setting->microsoft_client_secret ? Crypt::decryptString($setting->microsoft_client_secret) : null;
        } catch (\Exception $e) {
            Log::error('Error decrypting Microsoft client secret: ' . $e->getMessage());
            return [];
        }

        // تعيين إعدادات Socialite ديناميكيًا فقط إذا كان client_secret موجودًا
        // if ($clientSecret) {
        config([
            'services.microsoft.client_id' => $setting->microsoft_client_id,
            'services.microsoft.client_secret' => $setting->microsoft_client_secret,
            'services.microsoft.redirect' => $setting->microsoft_redirect_uri,
            'services.microsoft.tenant_id' => $setting->microsoft_tenant_id ?? 'common',
        ]);
        // }

        // Log::error($setting);

        return [
            'client_id' => $setting->microsoft_client_id,
            'client_secret' => $setting->microsoft_client_secret,
            'redirect_uri' => $setting->microsoft_redirect_uri,
            'tenant_id' => $setting->microsoft_tenant_id ?? 'common',
        ];
    }


    public function verifySettings()
    {

        $settings = $this->settings;

        if (empty($settings['client_id']) || empty($settings['client_secret']) || empty($settings['redirect_uri'])) {
            Log::error('Microsoft OAuth settings are incomplete.1111');
            return false;
        }

        try {
            // محاولة الحصول على Access Token باستخدام grant_type client_credentials
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

            // الإعدادات غير صحيحة
            Log::error('Microsoft OAuth settings are invalid. Access token not received.');
            return false;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // خطأ من جانب العميل مثل client_secret غير صالح
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            Log::error('Microsoft OAuth ClientException: ' . ($body['error_description'] ?? $e->getMessage()));
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to verify Microsoft OAuth settings: ' . $e->getMessage());
            return false;
        }
    }

    public function getAccessToken()
    {
        // تحقق من وجود رمز وصول مخزن في التخزين المؤقت
        if (Cache::has('microsoft_access_token')) {
            return Cache::get('microsoft_access_token');
        }

        try {
            $client = new Client();

            $response = $client->post('https://login.microsoftonline.com/' . $this->settings['tenant_id'] . '/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $this->settings['client_id'],
                    'client_secret' => $this->settings['client_secret'],
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            $accessToken = $body['access_token'] ?? null;

            if ($accessToken) {
                // تخزين رمز الوصول في التخزين المؤقت لمدة حياته (3600 ثانية)
                Cache::put('microsoft_access_token', $accessToken, $body['expires_in'] ?? 3600);
                return $accessToken;
            }

            return null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            Log::error('Error obtaining access token: ' . ($body['error_description'] ?? $e->getMessage()));
            return null;
        } catch (\Exception $e) {
            Log::error('Error obtaining access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على User ID باستخدام البريد الإلكتروني.
     *
     * @param string $email
     * @return string|null
     */
    // public function getUserIdByEmail($email)
    // {
    //     try {
    //         $user = $this->graph->createRequest("GET", "/users/{$email}")
    //             ->setReturnType(GraphUser::class) // استخدام الفئة الصحيحة
    //             ->execute();

    //         return $user->getId() ?? null;
    //     } catch (\Microsoft\Graph\Exception\GraphException $e) {
    //         Log::error('GraphException fetching user ID: ' . $e->getMessage());
    //         return null;
    //     } catch (\Exception $e) {
    //         Log::error('Exception fetching user ID: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    public function getUserIdByEmail($accessToken, $email)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch user ID by email.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول


        try {
            $user = $this->graph->createRequest("GET", "/users/{$email}")
                ->setReturnType(GraphUser::class)
                ->execute();

            return $user->getId() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching user ID: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching user ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * جلب ملفات المستخدم من OneDrive.
     *
     * @param string $userId
     * @param string $folderId
     * @return array|null
     */
    // public function getUserFiles($userId, $folderId = 'root')
    // {
    //     try {
    //         $path = $folderId === 'root'
    //             ? "/users/{$userId}/drive/root/children"
    //             : "/users/{$userId}/drive/items/{$folderId}/children";

    //         $files = $this->graph->createRequest("GET", $path)
    //             ->setReturnType(ModelDriveItem::class)
    //             ->execute();

    //         $result = [];
    //         foreach ($files as $file) {
    //             $result[] = $this->driveItemToArray($file);
    //         }

    //         return $result;
    //     } catch (\Microsoft\Graph\Exception\GraphException $e) {
    //         Log::error('GraphException fetching user files: ' . $e->getMessage());
    //         return null;
    //     } catch (\Exception $e) {
    //         Log::error('Exception fetching user files: ' . $e->getMessage());
    //         return null;
    //     }
    // }


    public function getUserFiles($accessToken, $userId, $folderId = 'root')
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول


        try {
            $path = $folderId === 'root'
                ? "/users/{$userId}/drive/root/children"
                : "/users/{$userId}/drive/items/{$folderId}/children";

            $files = $this->graph->createRequest("GET", $path)
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            $result = [];
            foreach ($files as $file) {
                $result[] = $this->driveItemToArray($file);
            }

            return $result;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching user files: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching user files: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تحويل DriveItem إلى مصفوفة.
     *
     * @param ModelDriveItem $item
     * @return array
     */
    protected function driveItemToArray(ModelDriveItem $item)
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'folder' => $item->getFolder() ? true : false,
            // يمكنك إضافة خصائص أخرى حسب الحاجة
        ];
    }

    /**
     * الحصول على رمز الوصول الصالح للمستخدم.
     *
     * @param User $user
     * @return string|null
     */
    public function getValidAccessToken(User $user)
    {
        if ($user->microsoft_token_expires && $user->microsoft_token_expires->lt(now())) {
            // رمز الوصول منتهي، قم بتجديده
            $newAccessToken = $this->refreshAccessToken($user);
            if (!$newAccessToken) {
                return null;
            }
            return $newAccessToken;
        }


        return $user->microsoft_token;
    }



    public function downloadFile($userId, $fileId)
    {
        try {
            // طلب محتوى الملف مباشرة من endpoint /content
            $response = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}/content")
                ->execute();

            // الحصول على معلومات الملف (اسم ونوعه)
            $file = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}")
                ->setReturnType(DriveItem::class)
                ->execute();

            $fileName = $file->getName() ?? 'downloaded_file';
            $mimeType = $file->getFile() ? $file->getFile()->getMimeType() : 'application/octet-stream';

            // استخدام Guzzle لتحميل الملف من /content endpoint
            $client = new Client();
            $downloadResponse = $client->get("https://graph.microsoft.com/v1.0/users/{$userId}/drive/items/{$fileId}/content", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->getAccessToken(),
                    'Accept' => 'application/octet-stream',
                ],
                'stream' => true,
            ]);

            return [
                'stream' => $downloadResponse->getBody(),
                'name' => $fileName,
                'mimeType' => $mimeType,
            ];
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException downloading file: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception downloading file: ' . $e->getMessage());
            return null;
        }
    }


    public function uploadFile($accessToken, $userId, $parentId, $filePath, $fileName)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $response = $this->graph->createRequest("PUT", "/users/{$userId}/drive/items/{$parentId}:/{$fileName}:/content")
                ->attachBody(fopen($filePath, 'r'))
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            return $this->driveItemToArray($response);
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException uploading file: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception uploading file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إنشاء مجلد جديد باستخدام SDK.
     *
     * @param string $userId
     * @param string $parentId
     * @param string $folderName
     * @return array|null
     */
    public function createFolder($accessToken, $userId, $parentId, $folderName)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $response = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$parentId}/children")
                ->attachBody([
                    'name' => $folderName,
                    'folder' => new \stdClass(),
                    '@microsoft.graph.conflictBehavior' => 'rename',
                ])
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            return $this->driveItemToArray($response);
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException creating folder: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating folder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * حذف ملف أو مجلد باستخدام SDK.
     *
     * @param string $userId
     * @param string $itemId
     * @return bool
     */
    public function deleteItem($accessToken, $userId, $itemId)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $this->graph->createRequest("DELETE", "/users/{$userId}/drive/items/{$itemId}")
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException deleting item: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('Exception deleting item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إنشاء رابط تحرير للملف باستخدام SDK.
     *
     * @param string $userId
     * @param string $fileId
     * @return string|null
     */
    public function createEditLink($accessToken, $userId, $fileId)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $link = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/createLink")
                ->attachBody([
                    'type' => 'edit', // نوع الرابط: edit أو view
                    'scope' => 'organization', // نطاق الرابط: anonymous أو organization
                ])
                ->setReturnType(Permission::class)
                ->execute();

            return $link->getLink()->getWebUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException creating edit link: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating edit link: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إنشاء رابط عرض للملف باستخدام SDK.
     *
     * @param string $userId
     * @param string $fileId
     * @return string|null
     */
    public function createViewLink($accessToken, $userId, $fileId)

    {
        if (!$accessToken) {
            Log::error('Access token is required to create a view link.');
            return null;
        }


        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول
        try {
            $link = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/createLink")
                ->attachBody([
                    'type' => 'view', // نوع الرابط: view أو edit
                    'scope' => 'organization', // نطاق الرابط: anonymous أو organization
                ])
                ->setReturnType(Permission::class)
                ->execute();

            return $link->getLink()->getWebUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException creating view link: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating view link: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إنشاء جلسة تحميل متقطع للملف باستخدام SDK.
     *
     * @param string $userId
     * @param string $parentId
     * @param string $fileName
     * @param int $fileSize
     * @return string|null
     */
    public function createUploadSession($accessToken, $userId, $parentId, $fileName, $fileSize)
    {
        if (!$accessToken) {
            Log::error('Access token is required to create a view link.');
            return null;
        }


        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول
        try {
            $uploadSession = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$parentId}:/{$fileName}:/createUploadSession")
                ->attachBody([
                    'item' => [
                        '@microsoft.graph.conflictBehavior' => 'rename',
                        'name' => $fileName,
                    ],
                ])
                ->setReturnType(UploadSession::class)
                ->execute();

            return $uploadSession->getUploadUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException creating upload session: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating upload session: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تحميل جزء من الملف باستخدام Upload Session مع SDK.
     *
     * @param string $uploadUrl
     * @param string $chunkData
     * @param int $start
     * @param int $end
     * @param int $totalSize
     * @param int $retryCount
     * @return array|null
     */
    public function uploadChunk($uploadUrl, $chunkData, $start, $end, $totalSize, $retryCount = 3)
    {
        try {
            $client = new Client();

            $response = $client->put($uploadUrl, [
                'headers' => [
                    'Content-Range' => "bytes {$start}-{$end}/{$totalSize}",
                ],
                'body' => $chunkData,
            ]);

            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($retryCount > 0) {
                Log::warning("Retrying upload chunk: {$start}-{$end}. Retries left: " . ($retryCount - 1));
                sleep(1); // انتظار قبل إعادة المحاولة
                return $this->uploadChunk($uploadUrl, $chunkData, $start, $end, $totalSize, $retryCount - 1);
            } else {
                $response = $e->getResponse();
                $body = json_decode($response->getBody(), true);
                Log::error('Error uploading chunk after retries: ' . ($body['error']['message'] ?? $e->getMessage()));
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error uploading chunk: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * توليد محتوى افتراضي لأنواع الملفات المختلفة.
     *
     * @param string $fileType
     * @return string|null
     */
    public function getDefaultFileContent($fileType)
    {
        $templatePath = storage_path("app/templates/empty.{$fileType}");

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        return null;
    }

    public function getPreviewLink($userId, $fileId)
    {
        try {
            $response = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/preview")
                ->setReturnType(ItemPreviewInfo::class)
                ->execute();

            return $response->getGetUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException getting preview link: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting preview link: ' . $e->getMessage());
            return null;
        }
    }


    public function getEmails($accessToken, $top = 20, $folder = 'inbox')
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch emails.');
            return [];
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول


        try {
// <<<<<<< m7md
            // $url = "/me/messages?\$top={$top}";
            // $response = $this->graph->createRequest("GET", $url)
// =======
            // جلب الرسائل من المجلد المحدد (inbox، SentItems، Drafts، JunkEmail, deleteditems, archive)
            $url = "/me/mailFolders/{$folder}/messages?\$top={$top}";
            $response = $this->graph->createRequest("GET", $url)
// >>>>>>> main
                ->setReturnType(Message::class)
                ->execute();

            return $response;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching emails: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching emails: ' . $e->getMessage());
            return [];
        }
    }


    public function getEmailById($accessToken, $id)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch email by ID.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول


        try {
            $email = $this->graph->createRequest("GET", "/me/messages/{$id}")
                ->setReturnType(Message::class)
                ->execute();

            return $email;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching email by ID: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching email by ID: ' . $e->getMessage());
            return null;
        }
    }

    public function sendEmail($accessToken, $subject, $body, $toRecipients, $ccRecipients = [], $bccRecipients = [], $attachments = [])
    {
        if (!$accessToken) {
            Log::error('Access token is required to send email.');
            return false;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول


        try {
            $message = [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $body
                ],
                'toRecipients' => $toRecipients,
                'ccRecipients' => $ccRecipients,
                'bccRecipients' => $bccRecipients,
                'attachments' => $attachments
            ];

            $this->graph->createRequest("POST", "/me/sendMail")
                ->attachBody(['message' => $message, 'saveToSentItems' => true])
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException sending email: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return false;
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return false;
        }
    }

    public function replyToEmail($accessToken, $messageId, $comment)
    {
        if (!$accessToken) {
            Log::error('Access token is required to reply to email.');
            return false;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول


        try {
            $reply = [
                'comment' => $comment
            ];

            $this->graph->createRequest("POST", "/me/messages/{$messageId}/reply")
                ->attachBody($reply)
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException replying to email: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return false;
        } catch (\Exception $e) {
            Log::error('Error replying to email: ' . $e->getMessage());
            return false;
        }
    }

// <<<<<<< m7md

    public function getUserCalendars($accessToken)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch calendars.');
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $calendars = $this->graph->createRequest("GET", "/me/calendars")
                ->setReturnType(Calendar::class)
                ->execute();

            Log::info('Fetched user calendars successfully.', ['calendars_count' => count($calendars)]);
            return $calendars;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching calendars.', [
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching calendars.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function getUserEvents($accessToken, $calendarId)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch events.');
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $events = $this->graph->createRequest("GET", "/me/calendars/{$calendarId}/events")
                ->setReturnType(Event::class)
                ->execute();

            Log::info('Fetched events from calendar.', [
                'calendar_id' => $calendarId,
                'events_count' => count($events),
            ]);

            return $events;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching events.', [
                'calendar_id' => $calendarId,
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching events.', [
                'calendar_id' => $calendarId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }


    public function createEvent($accessToken, array $eventDetails)
    {
        if (!$accessToken) {
            Log::error('Access token is required to create an event.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $event = $this->graph->createRequest("POST", "/me/events")
                ->attachBody($eventDetails)
                ->setReturnType(Event::class)
                ->execute();

            Log::info('Event created via Microsoft Graph API.', ['event_id' => $event->getId()]);
            return $event;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException creating event.', [
                'event_details' => $eventDetails,
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating event.', [
                'event_details' => $eventDetails,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }



    public function getEventDetails($accessToken, $eventId)
    {
        try {
            $this->graph->setAccessToken($accessToken);
            $event = $this->graph->createRequest("GET", "/me/events/{$eventId}")
                ->setReturnType(Event::class)
                ->execute();

            return $event;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException fetching event details.', [
                'event_id' => $eventId,
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching event details.', [
                'event_id' => $eventId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }


    public function updateEvent($accessToken, $eventId, $eventData)
    {
        try {
            $this->graph->setAccessToken($accessToken);
            $updatedEvent = $this->graph->createRequest("PATCH", "/me/events/{$eventId}")
                ->attachBody($eventData)
                ->setReturnType(Event::class)
                ->execute();

            return $updatedEvent;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException updating event.', [
                'event_id' => $eventId,
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception updating event.', [
                'event_id' => $eventId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }


    public function deleteEvent($accessToken, $eventId)
    {
        try {
            $this->graph->setAccessToken($accessToken);
            $this->graph->createRequest("DELETE", "/me/events/{$eventId}")
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException deleting event.', [
                'event_id' => $eventId,
                'error_message' => $e->getMessage(),
                'response_body' => $e->getResponseBody(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception deleting event.', [
                'event_id' => $eventId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
// =======


// }
// >>>>>>> main
