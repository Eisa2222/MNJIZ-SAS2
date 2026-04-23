<?php

namespace App\Jobs;

use App\Models\OperationsCenter\Contract\ContractAttachment;
use App\Models\User; // تأكد من استيراد نموذج User
use App\Services\FilesService;
use App\Services\MicrosoftGraphBaseService; // استيراد خدمة MicrosoftGraphBaseService
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadFileToOneDriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userIdInSystem;
    public $userId;
    public $folderId;
    public $filePath;
    public $fileName;
    public $attachmentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userIdInSystem, $userId, $folderId, $filePath, $fileName, $attachmentId)
    {
        $this->userIdInSystem = $userIdInSystem;
        $this->userId = $userId;
        $this->folderId = $folderId;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->attachmentId = $attachmentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FilesService $filesService, MicrosoftGraphBaseService $graphService)
    {
        try {
            // الحصول على المستخدم من قاعدة البيانات
            $user = User::find($this->userIdInSystem);

            if (!$user || !$user->microsoft_token) {
                Log::error("User not found or Microsoft token not available.");
                return;
            }

            // الحصول على رمز وصول صالح
            $accessToken = $graphService->getValidUserAccessToken($user);

            if (!$accessToken) {
                Log::error("Failed to get a valid access token for user.");
                return;
            }

            // تنفيذ عملية الرفع
            $uploadResult = $filesService->uploadFile(
                $accessToken,
                $this->userId,
                $this->folderId,
                $this->filePath,
                $this->fileName
            );

            // تسجيل نتيجة الرفع
            Log::info('Upload result: ' . json_encode($uploadResult));

            if ($uploadResult && is_array($uploadResult)) {
                // تحديث السجل في قاعدة البيانات
                $link = $filesService->createOrganizationLink($accessToken, $this->userId, $uploadResult['id']);

                $attachment = ContractAttachment::find($this->attachmentId);
                if ($attachment) {
                    $attachment->file_id = $uploadResult['id'];
                    $attachment->file_url = $uploadResult['url'];
                    $attachment->share_link = $link; // تأكد من وجود حقل 'share_link' في جدولك
                    $attachment->save();
                }
                Log::info("Successfully uploaded file: {$this->fileName}");
            } else {
                // التعامل مع فشل رفع الملف
                Log::error("Failed to upload file: {$this->fileName}");
                // يمكنك حذف السجل أو تحديثه للدلالة على الفشل
            }

            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        } catch (\Exception $e) {
            Log::error("Exception in UploadFileToOneDriveJob: " . $e->getMessage());
        }
    }
}
