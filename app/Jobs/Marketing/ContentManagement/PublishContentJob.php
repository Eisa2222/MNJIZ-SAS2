<?php

namespace App\Jobs\Marketing\ContentManagement;

use App\Enums\Marketing\ContentManagement\MediaType;
use App\Enums\Marketing\ContentManagement\PublishType;
use App\Models\Marketing\ContentManagement\SocialPublication\SocialPublication;
use App\Models\general_setting\SettingsSocial;
use App\Services\Social\LinkedIn\LinkedInService;
use App\Services\Social\Twitter\TwitterService;
use App\Services\Marketing\ContentManagement\ContentManagementService;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublishContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    public SocialPublication $attempt;

    public function __construct(SocialPublication $attempt)
    {
        $this->attempt = $attempt->fresh(['contentManagement']);
        $this->captureTenant();
    }

    public function handle(
        LinkedInService $linkedIn,
        TwitterService  $twitter,
        ContentManagementService $cmService
    ): void {
        Log::info('[Job] START', [
            'attempt_id' => $this->attempt->id,
            'content_id' => $this->attempt->content_management_id,
            'platform'   => $this->attempt->platform,
            'scheduled'  => $this->attempt->scheduled_for,
        ]);

        $content  = $this->attempt->contentManagement;
        $settings = SettingsSocial::where('name', $this->attempt->platform)->first();

        $text   = $content->content_text ?? "";
        $token  = $settings->access_token;


        $uploadedFile = null;
        if ($content->media) {
            $filePath = Storage::disk('public')->path($content->media);

            // التحقق من وجود الملف
            if (!file_exists($filePath)) {
                throw new \RuntimeException("الملف غير موجود: " . $filePath);
            }

            $originalName = basename($content->media);
            $mimeType = Storage::disk('public')->mimeType($content->media);

            $uploadedFile = new UploadedFile(
                $filePath,
                $originalName,
                $mimeType,
                null,
                true // test mode
            );
        }



        try {
            if ($this->attempt->platform === 'linkedin') {

                if ($content->media_type === MediaType::Image || $content->media_type === MediaType::Video) { // ملف فقط 
                    $resp = $linkedIn->publishMediaOnly($uploadedFile, $token);
                } elseif ($content->media_type === MediaType::ImageText || $content->media_type === MediaType::VideoText) { // نص وملف 
                    $resp = $linkedIn->publishPostWithMedia($text, $uploadedFile, $token);
                } else { // نص فقط 
                    $resp = $linkedIn->publishPost($text, $token);
                }
            } else { // منصة X

                if ($content->media_type === MediaType::Image || $content->media_type === MediaType::Video) { // ملف فقط 
                    $resp = $twitter->postMediaOnly(
                        $uploadedFile,
                        $settings->api_key,
                        $settings->api_secret,
                        $settings->access_token,
                        $settings->access_token_secret
                    );
                } elseif ($content->media_type === MediaType::ImageText || $content->media_type === MediaType::VideoText) { // نص وملف 
                    $resp = $twitter->postTweetWithMedia(
                        $text,
                        $uploadedFile,
                        $settings->api_key,
                        $settings->api_secret,
                        $settings->access_token,
                        $settings->access_token_secret
                    );
                } else { // نص فقط 
                    $resp = $twitter->postTweet(
                        $text,
                        $settings->api_key,
                        $settings->api_secret,
                        $settings->access_token,
                        $settings->access_token_secret
                    );
                }

            }


            // 1) تحقّق من النجاح
            if (!$resp['success']) {
                throw new \RuntimeException($resp['message'] ?? 'نشر فشل');
            }

            // 2) استخرج المعرّف فقط
            $postId = match ($this->attempt->platform) {
                'linkedin' => $resp['id']          ?? null,
                'x'        => $resp['tweet']['id'] ?? null,
            };

            if (!$postId) {
                throw new \RuntimeException('تعذّر استخراج معرف المنشور');
            }

            Log::info('[Job] SUCCESS publish', ['attempt_id' => $this->attempt->id, 'postId' => $postId]);

            $this->attempt->update([
                'status'           => 'success',
                'platform_post_id' => $postId,
                'published_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[Job] FAILED publish', [
                'attempt_id' => $this->attempt->id,
                'msg'        => $e->getMessage(),
            ]);

            $this->attempt->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return;
        }

        /* إعادة الجدولة */
        if ($content->publish_type === PublishType::Recurring) {
            $next = $cmService->computeNextRun($content, Carbon::parse($this->attempt->scheduled_for)->addMinute());

            Log::debug('[Job] next run', ['attempt_id' => $this->attempt->id, 'next' => $next]);

            if ($next) {
                SocialPublication::create([
                    'content_management_id' => $content->id,
                    'platform'              => $this->attempt->platform,
                    'scheduled_for'         => $next,
                    'status'                => 'pending',
                ]);
                Log::info('[Job] NEW attempt created', [
                    'content_id' => $content->id,
                    'platform'   => $this->attempt->platform,
                    'next'       => $next,
                ]);
            }
        }

        /* تحديث حالة القطعة */
        $pending = $content->socialPublications()->where('status', 'pending')->exists();
        if (!$pending) {
            $content->update(['publication_status' => 'published']);
            Log::info('[Job] content marked published', ['content_id' => $content->id]);
        }

        Log::info('[Job] END', ['attempt_id' => $this->attempt->id]);
    }
}
