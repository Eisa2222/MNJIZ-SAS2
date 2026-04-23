<?php

namespace App\Services\Social\LinkedIn;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use App\Services\Social\LinkedIn\Exceptions\{
    LinkedInValidationException,
};
use Exception;

class LinkedInService
{

    private const PROFILE_URL           = 'https://api.linkedin.com/v2/userinfo';
    private const SHARE_URL             = 'https://api.linkedin.com/v2/ugcPosts';
    private const REGISTER_UPLOAD_URL   = 'https://api.linkedin.com/v2/assets?action=registerUpload';

    private const CONTENT_TYPE       = 'application/json';
    private const API_VERSION_HEADER = '2.0.0';

    private const CACHE_USER_KEY     = 'linkedin_core';
    private const CACHE_USER_TTL_MIN = 30;
    private const CACHE_TOKEN_KEY    = 'linkedin_token';
    private const TOKEN_TTL_HOURS    = 24;

    public const VISIBILITY_PUBLIC   = 'PUBLIC';
    public const MEDIA_NONE          = 'NONE';
    public const MEDIA_IMAGE         = 'IMAGE';
    public const MEDIA_VIDEO         = 'VIDEO';

    // Media constraints
    private const MAX_IMAGE_SIZE_MB = 100;
    private const MAX_VIDEO_SIZE_MB = 200;
    private const SUPPORTED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const SUPPORTED_VIDEO_TYPES = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'];


    public function __construct(private Client $client) {}


    public function testConnection(string $accessToken): array
    {
        return $this->getUserInfo($accessToken);
    }


    public function getUserInfo(string $accessToken): array
    {
        if ($cached = Cache::get(self::CACHE_USER_KEY)) {
            return $this->successResponse('ok', ['user_info' => $cached]);
        }

        $token = $this->cacheToken($accessToken);

        try {
            $resp = $this->client()->get(self::PROFILE_URL, [
                'headers' => $this->headers($token),
            ]);
            if ($resp->getStatusCode() !== 200) {
                return $this->errorResponse('فشل في جلب بيانات المستخدم');
            }
            $data = json_decode((string) $resp->getBody(), true);
            Cache::put(self::CACHE_USER_KEY, $data, now()->addMinutes(self::CACHE_USER_TTL_MIN));
            return $this->successResponse('ok', ['user_info' => $data]);
        } catch (ClientException $e) {
            return $this->errorResponse('بيانات الاعتماد غير صحيحة');
        } catch (ConnectException $e) {
            return $this->errorResponse('فشل الاتصال بالشبكة');
        } catch (Exception $e) {
            return $this->errorResponse('خطأ غير متوقع');
        }
    }

    /** Publish plain-text post. */
    public function publishPost(string $content, string $accessToken): array
    {
        try {
            $this->validatePostContent($content);
        } catch (LinkedInValidationException $e) {
            return $this->errorResponse($e->getMessage());
        }

        $userRes = $this->getUserInfo($accessToken);
        if (!$userRes['success']) {
            return $userRes;
        }
        $user = $userRes['user_info'];

        $payload = [
            'author' => 'urn:li:person:' . $user['sub'],
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $content],
                    'shareMediaCategory' => self::MEDIA_NONE,
                ],
            ],
            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => self::VISIBILITY_PUBLIC],
        ];

        return $this->postContent($payload, $accessToken);
    }

    /** Publish post with text and media (image or video). */
    public function publishPostWithMedia(string $content, UploadedFile $media, string $accessToken): array
    {
        try {
            $this->validatePostContent($content);
            $this->validateMediaFile($media);
        } catch (LinkedInValidationException $e) {
            return $this->errorResponse($e->getMessage());
        }

        $userRes = $this->getUserInfo($accessToken);
        if (!$userRes['success']) {
            return $userRes;
        }
        $user = $userRes['user_info'];

        // Upload media first
        $mediaUpload = $this->uploadMedia($media, $user['sub'], $accessToken);
        if (!$mediaUpload['success']) {
            return $mediaUpload;
        }

        $mediaType = $this->getMediaType($media);
        $mediaCategory = $mediaType === 'image' ? self::MEDIA_IMAGE : self::MEDIA_VIDEO;

        $payload = [
            'author' => 'urn:li:person:' . $user['sub'],
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $content],
                    'shareMediaCategory' => $mediaCategory,
                    'media' => [
                        [
                            'status' => 'READY',
                            'media' => $mediaUpload['asset_urn'],
                        ]
                    ],
                ],
            ],
            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => self::VISIBILITY_PUBLIC],
        ];

        return $this->postContent($payload, $accessToken);
    }

    /** Publish media only (image or video without text). */
    public function publishMediaOnly(UploadedFile $media, string $accessToken): array
    {
        try {
            $this->validateMediaFile($media);
        } catch (LinkedInValidationException $e) {
            return $this->errorResponse($e->getMessage());
        }

        $userRes = $this->getUserInfo($accessToken);
        if (!$userRes['success']) {
            return $userRes;
        }
        $user = $userRes['user_info'];

        // Upload media first
        $mediaUpload = $this->uploadMedia($media, $user['sub'], $accessToken);
        if (!$mediaUpload['success']) {
            return $mediaUpload;
        }

        $mediaType = $this->getMediaType($media);
        $mediaCategory = $mediaType === 'image' ? self::MEDIA_IMAGE : self::MEDIA_VIDEO;

        $payload = [
            'author' => 'urn:li:person:' . $user['sub'],
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => " "],
                    'shareMediaCategory' => $mediaCategory,
                    'media' => [
                        [
                            'status' => 'READY',
                            'media' => $mediaUpload['asset_urn'],
                        ]
                    ],
                ],
            ],
            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => self::VISIBILITY_PUBLIC],
        ];

        return $this->postContent($payload, $accessToken);
    }


    /*
    |============================================================================
    |============================================================================
    |                           Private function 
    |============================================================================
    |============================================================================
    */

    /* ---------------------------------------------------------------------- */
    /*                        Media Upload Methods                             */
    /* ---------------------------------------------------------------------- */

    private function uploadMedia(UploadedFile $media, string $userId, string $accessToken): array
    {
        $token = $this->cacheToken($accessToken);

        // Step 1: Register upload
        $registerResponse = $this->registerUpload($media, $userId, $token);
        if (!$registerResponse['success']) {
            return $registerResponse;
        }

        $uploadUrl = $registerResponse['upload_url'];
        $assetUrn = $registerResponse['asset_urn'];

        // Step 2: Upload the actual file
        $uploadResponse = $this->uploadFile($media, $uploadUrl);
        if (!$uploadResponse['success']) {
            return $uploadResponse;
        }

        return $this->successResponse('Media uploaded successfully', [
            'asset_urn' => $assetUrn,
            'upload_url' => $uploadUrl
        ]);
    }

    private function registerUpload(UploadedFile $media, string $userId, string $token): array
    {
        $mediaType = $this->getMediaType($media);
        $recipes = $mediaType === 'image' ? ['urn:li:digitalmediaRecipe:feedshare-image'] : ['urn:li:digitalmediaRecipe:feedshare-video'];

        $payload = [
            'registerUploadRequest' => [
                'recipes' => $recipes,
                'owner' => 'urn:li:person:' . $userId,
                'serviceRelationships' => [
                    [
                        'relationshipType' => 'OWNER',
                        'identifier' => 'urn:li:userGeneratedContent'
                    ]
                ]
            ]
        ];

        try {
            $resp = $this->client()->post(self::REGISTER_UPLOAD_URL, [
                'headers' => $this->headers($token),
                'json' => $payload,
            ]);

            if ($resp->getStatusCode() !== 200) {
                return $this->errorResponse('فشل في تسجيل رفع الملف');
            }

            $data = json_decode((string) $resp->getBody(), true);
            $uploadUrl = $data['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
            $assetUrn = $data['value']['asset'] ?? null;

            if (!$uploadUrl || !$assetUrn) {
                return $this->errorResponse('فشل في الحصول على رابط الرفع');
            }

            return $this->successResponse('Upload registered', [
                'upload_url' => $uploadUrl,
                'asset_urn' => $assetUrn
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('خطأ في تسجيل رفع الملف: ' . $e->getMessage());
        }
    }

    private function uploadFile(UploadedFile $media, string $uploadUrl): array
    {
        try {
            $resp = $this->client()->post($uploadUrl, [
                'headers' => [
                    'Content-Type' => $media->getMimeType(),
                ],
                'body' => fopen($media->getRealPath(), 'r'),
            ]);

            if ($resp->getStatusCode() !== 201) {
                return $this->errorResponse('فشل في رفع الملف');
            }

            return $this->successResponse('File uploaded successfully');
        } catch (Exception $e) {
            return $this->errorResponse('خطأ في رفع الملف: ' . $e->getMessage());
        }
    }

    /* ---------------------------------------------------------------------- */
    /*                        Internal helpers                                 */
    /* ---------------------------------------------------------------------- */

    private function postContent(array $payload, string $accessToken): array
    {
        $token = $this->cacheToken($accessToken);

        try {
            $resp = $this->client()->post(self::SHARE_URL, [
                'headers' => $this->headers($token),
                'json' => $payload,
            ]);

            $data = json_decode((string) $resp->getBody(), true);
            $this->invalidateUserCache();

            return $this->successResponse('تم نشر المحتوى بنجاح', [
                'id' => $data['id'] ?? null,
            ]);
        } catch (ClientException $e) {
            $err = json_decode($e->getResponse()->getBody()->getContents() ?: '{}', true);
            if ($this->isDuplicatePostError($err)) {
                return $this->errorResponse('المحتوى مكرر');
            }
            Log::alert($e);
            return $this->errorResponse('خطأ من طرف LinkedIn API');
        } catch (ConnectException $e) {
            return $this->errorResponse('فشل الاتصال بـ LinkedIn');
        } catch (Exception $e) {
            return $this->errorResponse('خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    private function client(): Client
    {
        if ($this->client instanceof Client) {
            return $this->client;
        }
        $this->client = new Client([
            'timeout' => 60, // Increased for file uploads
            'connect_timeout' => 15,
            'verify' => true,
        ]);
        return $this->client;
    }

    private function cacheToken(string $raw): string
    {
        if ($tok = Cache::get(self::CACHE_TOKEN_KEY)) {
            return $tok;
        }
        Cache::put(self::CACHE_TOKEN_KEY, $raw, now()->addHours(self::TOKEN_TTL_HOURS));
        return $raw;
    }

    private function headers(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Content-Type' => self::CONTENT_TYPE,
            'X-Restli-Protocol-Version' => self::API_VERSION_HEADER,
            'Accept' => 'application/json',
        ];
    }

    private function isDuplicatePostError(?array $err): bool
    {
        if (!$err) return false;
        $input = $err['errorDetails']['inputErrors'] ?? [];
        foreach ($input as $item) {
            if (($item['code'] ?? '') === 'DUPLICATE_POST') return true;
        }
        return str_contains($err['message'] ?? '', 'Duplicate');
    }

    private function invalidateUserCache(): void
    {
        Cache::forget(self::CACHE_USER_KEY);
    }

    private function getMediaType(UploadedFile $media): string
    {
        $mimeType = $media->getMimeType();
        return str_starts_with($mimeType, 'image/') ? 'image' : 'video';
    }

    /* -------------------------------Validation-------------------------------- */

    private function validatePostContent(string $content): void
    {
        if (empty(trim($content))) {
            throw new LinkedInValidationException('محتوى المنشور فارغ');
        }
        if (mb_strlen($content) > 3000) {
            throw new LinkedInValidationException('محتوى المنشور يتجاوز 3000 حرف');
        }
    }

    private function validateMediaFile(UploadedFile $media): void
    {
        if (!$media->isValid()) {
            throw new LinkedInValidationException('الملف المرفوع غير صالح');
        }

        $mimeType = $media->getMimeType();
        $sizeInMB = $media->getSize() / (1024 * 1024);

        if (str_starts_with($mimeType, 'image/')) {
            if (!in_array($mimeType, self::SUPPORTED_IMAGE_TYPES)) {
                throw new LinkedInValidationException('نوع الصورة غير مدعوم. الأنواع المدعومة: JPEG, PNG, GIF');
            }
            if ($sizeInMB > self::MAX_IMAGE_SIZE_MB) {
                throw new LinkedInValidationException('حجم الصورة يتجاوز ' . self::MAX_IMAGE_SIZE_MB . ' ميجابايت');
            }
        } elseif (str_starts_with($mimeType, 'video/')) {
            if (!in_array($mimeType, self::SUPPORTED_VIDEO_TYPES)) {
                throw new LinkedInValidationException('نوع الفيديو غير مدعوم. الأنواع المدعومة: MP4, AVI, MOV, WMV');
            }
            if ($sizeInMB > self::MAX_VIDEO_SIZE_MB) {
                throw new LinkedInValidationException('حجم الفيديو يتجاوز ' . self::MAX_VIDEO_SIZE_MB . ' ميجابايت');
            }
        } else {
            throw new LinkedInValidationException('نوع الملف غير مدعوم. يجب أن يكون صورة أو فيديو');
        }
    }


    /* ----------------------------- Response helpers ------------------------- */

    private function successResponse(string $msg, array $data = []): array
    {
        return array_merge(['success' => true, 'message' => $msg], $data);
    }

    private function errorResponse(string $msg, array $data = []): array
    {
        return array_merge(['success' => false, 'message' => $msg], $data);
    }
}
