<?php

namespace App\Services\Social\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Exception;

class TwitterService
{
    /* -------------------------------------------------------------------------- */
    /*                              Configuration                                 */
    /* -------------------------------------------------------------------------- */

    private const API_VERSION         = '2';
    private const DEFAULT_TIMEOUT     = 30;
    private const CONNECTION_TIMEOUT  = 10;

    private const CACHE_KEY           = 'x_twitter_core';
    private const CACHE_TTL_MINUTES   = 120;

    private const MAX_TWEET_LENGTH    = 280;

    // Media constraints
    private const MAX_IMAGE_SIZE_MB = 5;
    private const MAX_VIDEO_SIZE_MB = 512;
    private const SUPPORTED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const SUPPORTED_VIDEO_TYPES = ['video/mp4', 'video/mov', 'video/avi'];

    private ?TwitterOAuth $client = null;

    /* -------------------------------------------------------------------------- */
    /*                             Public methods                                 */
    /* -------------------------------------------------------------------------- */

    public function testConnection(string $apiKey, string $apiSecret, string $token, string $tokenSecret): array
    {
        return $this->getUser($apiKey, $apiSecret, $token, $tokenSecret);
    }


    // just text 
    public function postTweet(string $text, string $apiKey, string $apiSecret, string $token, string $tokenSecret): array
    {
        if (!$this->validateTweetText($text)) {
            return $this->errorResponse('Invalid tweet text');
        }

        if (!$this->initialiseClient($apiKey, $apiSecret, $token, $tokenSecret)) {
            return $this->errorResponse('Unable to build Twitter client');
        }

        try {
            $resp = $this->client->post('tweets', ['text' => $text]);
            $code = $this->client->getLastHttpCode();

            if ($code === 201 && isset($resp->data->id)) {
                return $this->successResponse('Tweet posted', [
                    'tweet' => $this->formatTweet($resp->data),
                ]);
            }

            return $this->handleTwitterError($code, $resp);
        } catch (Exception $e) {
            return $this->errorResponse('Tweet exception: ' . $e->getMessage());
        }
    }


    // text and media
    public function postTweetWithMedia(string $text, UploadedFile $media, string $apiKey, string $apiSecret, string $token, string $tokenSecret): array
    {
        if (!$this->validateTweetText($text)) {
            return $this->errorResponse('Invalid tweet text');
        }

        try {
            $this->validateMediaFile($media);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        if (!$this->initialiseClient($apiKey, $apiSecret, $token, $tokenSecret)) {
            return $this->errorResponse('Unable to build Twitter client');
        }

        // Upload media first
        $mediaUpload = $this->uploadMedia($media);
        if (!$mediaUpload['success']) {
            return $mediaUpload;
        }

        try {
            $resp = $this->client->post('tweets', [
                'text' => $text,
                'media' => [
                    'media_ids' => [$mediaUpload['media_id']]
                ]
            ]);
            $code = $this->client->getLastHttpCode();

            if ($code === 201 && isset($resp->data->id)) {
                return $this->successResponse('Tweet with media posted', [
                    'tweet' => $this->formatTweet($resp->data),
                    'media_id' => $mediaUpload['media_id']
                ]);
            }

            return $this->handleTwitterError($code, $resp);
        } catch (Exception $e) {
            return $this->errorResponse('Tweet with media exception: ' . $e->getMessage());
        }
    }


    // just media
    public function postMediaOnly(UploadedFile $media, string $apiKey, string $apiSecret, string $token, string $tokenSecret): array
    {
        try {
            $this->validateMediaFile($media);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }

        if (!$this->initialiseClient($apiKey, $apiSecret, $token, $tokenSecret)) {
            return $this->errorResponse('Unable to build Twitter client');
        }

        // Upload media first
        $mediaUpload = $this->uploadMedia($media);
        if (!$mediaUpload['success']) {
            return $mediaUpload;
        }

        try {
            $resp = $this->client->post('tweets', [
                'media' => [
                    'media_ids' => [$mediaUpload['media_id']]
                ]
            ]);
            $code = $this->client->getLastHttpCode();

            if ($code === 201 && isset($resp->data->id)) {
                return $this->successResponse('Media posted', [
                    'tweet' => $this->formatTweet($resp->data),
                    'media_id' => $mediaUpload['media_id']
                ]);
            }

            return $this->handleTwitterError($code, $resp);
        } catch (Exception $e) {
            return $this->errorResponse('Media post exception: ' . $e->getMessage());
        }
    }


    // Fetches authenticated user – uses unified cache.
    public function getUser(string $apiKey, string $apiSecret, string $token, string $tokenSecret): array
    {
        if ($cached = Cache::get(self::CACHE_KEY)) {
            return $cached;
        }

        if (!$this->initialiseClient($apiKey, $apiSecret, $token, $tokenSecret)) {
            return $this->errorResponse('Invalid API credentials');
        }

        try {
            $resp = $this->client->get('users/me', [
                'user.fields' => 'id,name,username,description,public_metrics,profile_image_url,location,url',
            ]);
            $code = $this->client->getLastHttpCode();

            if ($code === 200) {
                $payload = $this->successResponse('User info OK', [
                    'user_info' => $this->formatUser($resp),
                ]);

                Cache::put(self::CACHE_KEY, $payload, now()->addMinutes(self::CACHE_TTL_MINUTES));
                return $payload;
            }

            return $this->errorResponse('User info failed – code ' . $code);
        } catch (Exception $e) {
            return $this->errorResponse('User info exception: ' . $e->getMessage());
        }
    }


    /* -------------------------------------------------------------------------- */
    /*                             Media Upload Methods                           */
    /* -------------------------------------------------------------------------- */

    private function uploadMedia(UploadedFile $media): array
    {
        try {
            $mediaType = $this->getMediaType($media);

            if ($mediaType === 'image') {
                return $this->uploadImage($media);
            } else {
                return $this->uploadVideo($media);
            }
        } catch (Exception $e) {
            return $this->errorResponse('Media upload failed: ' . $e->getMessage());
        }
    }

    private function uploadImage(UploadedFile $image): array
    {
        $this->client->setApiVersion('1.1');
        try {
            $media = $this->client->upload('media/upload', [
                'media' => $image->getRealPath()
            ]);

            if (isset($media->media_id_string)) {
                return $this->successResponse('Image uploaded', [
                    'media_id' => $media->media_id_string,
                    'media_type' => 'image'
                ]);
            }

            return $this->errorResponse('Image upload failed');
        } catch (Exception $e) {
            return $this->errorResponse('Image upload exception: ' . $e->getMessage());
        } finally {
            $this->client->setApiVersion('2');
        }
    }

    private function uploadVideo(UploadedFile $video): array
    {
        try {
            $this->client->setApiVersion('1.1');

            $media = $this->client->upload('media/upload', [
                'media' => $video->getRealPath(),
                'media_type' => $video->getMimeType(),
                'media_category' => 'tweet_video'
            ], ['chunkedUpload' => true]);

            if (isset($media->media_id)) {
                $this->waitForVideoProcessing($media->media_id);

                return $this->successResponse('Video uploaded', [
                    'media_id' => $media->media_id_string,
                    'media_type' => 'video'
                ]);
            }

            return $this->errorResponse('Video upload failed', (array)$media->errors);
        } catch (Exception $e) {
            return $this->errorResponse('Video upload exception: ' . $e->getMessage());
        } finally {
            $this->client->setApiVersion('2');
        }
    }

    private function waitForVideoProcessing(string $mediaId): void
    {
        $maxAttempts = 30;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $status = $this->client->get('media/upload', [
                'command' => 'STATUS',
                'media_id' => $mediaId
            ]);

            if (isset($status->processing_info)) {
                $state = $status->processing_info->state;

                if ($state === 'succeeded') {
                    break;
                } elseif ($state === 'failed') {
                    throw new Exception('Video processing failed');
                }

                $checkAfter = $status->processing_info->check_after_secs ?? 5;
                sleep($checkAfter);
            } else {
                break;
            }

            $attempt++;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                             Private Methods                                */
    /* -------------------------------------------------------------------------- */

    private function initialiseClient(string $apiKey, string $apiSecret, string $token, string $tokenSecret): bool
    {
        if ($this->client instanceof TwitterOAuth) {
            return true;
        }

        try {
            $this->client = new TwitterOAuth($apiKey, $apiSecret, $token, $tokenSecret);
            $this->client->setApiVersion(self::API_VERSION);
            $this->client->setTimeouts(self::CONNECTION_TIMEOUT, self::DEFAULT_TIMEOUT);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function validateTweetText(string $text): bool
    {
        $len = mb_strlen(trim($text));
        return $len > 0 && $len <= self::MAX_TWEET_LENGTH;
    }


    private function validateMediaFile(UploadedFile $media): void
    {
        if (!$media->isValid()) {
            throw new Exception('الملف المرفوع غير صالح');
        }

        $mimeType  = $media->getMimeType();
        $sizeInMB  = $media->getSize() / (1024 * 1024);   // بالميجابايت
        $mediaType = $this->getMediaType($media);         // image | video

        if ($mediaType === 'image') {
            if (!in_array($mimeType, self::SUPPORTED_IMAGE_TYPES)) {
                throw new Exception('صيغة الصورة غير مدعومة من تويتر');
            }
            if ($sizeInMB > self::MAX_IMAGE_SIZE_MB) {
                throw new Exception('حجم الصورة يتجاوز ' . self::MAX_IMAGE_SIZE_MB . ' م.ب');
            }
        } else { // video
            if (!in_array($mimeType, self::SUPPORTED_VIDEO_TYPES)) {
                throw new Exception('صيغة الفيديو غير مدعومة من تويتر');
            }
            if ($sizeInMB > self::MAX_VIDEO_SIZE_MB) {
                throw new Exception('حجم الفيديو يتجاوز ' . self::MAX_VIDEO_SIZE_MB . ' م.ب');
            }
        }
    }

    // Determine if uploaded file is image or video.
    private function getMediaType(UploadedFile $media): string
    {
        $mime = $media->getMimeType();
        return str_starts_with($mime, 'image/') ? 'image' : 'video';
    }


    private function handleTwitterError(int $code, object $resp): array
    {
        if ($code === 403) {
            $detail = $resp->detail ?? '';
            if (str_contains($detail, 'duplicate content')) {
                return $this->errorResponse('تغريدة/منشور مكرر');
            }
            if (str_contains($detail, 'oauth1 app permissions')) {
                return $this->errorResponse('التطبيق يفتقر إلى صلاحية النشر، تحقق من إعدادات Twitter Developer');
            }
            return $this->errorResponse('غير مسموح: ' . $detail);
        }

        if ($code === 429) {
            return $this->errorResponse('تم تجاوز حد طلبات Twitter – حاول مرة أخرى لاحقًا');
        }

        return $this->errorResponse('Twitter API خطأ عام – كود ' . $code);
    }


    /* -------------------------------------------------------------------------- */
    /*                         Response & Formatting helpers                      */
    /* -------------------------------------------------------------------------- */

    private function successResponse(string $msg, array $data = []): array
    {
        return array_merge(['success' => true,  'message' => $msg], $data);
    }

    private function errorResponse(string $msg, array $data = []): array
    {
        return array_merge(['success' => false, 'message' => $msg], $data);
    }

    private function formatTweet(object $data): array
    {
        return [
            'id'   => $data->id,
            'text' => $data->text,
            'url'  => "https://twitter.com/i/web/status/{$data->id}",
        ];
    }

    private function formatUser(object $resp): array
    {
        $u = $resp->data ?? $resp;
        return [
            'id'            => $u->id,
            'name'          => $u->name,
            'username'      => $u->username,
            'description'   => $u->description    ?? null,
            'profile_image' => $u->profile_image_url ?? null,
            'location'      => $u->location       ?? null,
            'website'       => $u->url            ?? null,
            'followers'     => $u->public_metrics->followers_count ?? null,
            'following'     => $u->public_metrics->following_count ?? null,
        ];
    }
}
