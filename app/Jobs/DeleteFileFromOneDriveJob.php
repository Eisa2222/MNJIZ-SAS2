<?php

namespace App\Jobs;

use App\Models\OperationsCenter\Contract\ContractAttachment;
use App\Models\User;
use App\Services\FilesService;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteFileFromOneDriveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userIdInSystem;
    public $userId;
    public $fileId;
    public $attachmentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userIdInSystem, $userId, $fileId, $attachmentId)
    {
        $this->userIdInSystem = $userIdInSystem; // معرف المستخدم في النظام
        $this->userId = $userId; // معرف المستخدم في OneDrive
        $this->fileId = $fileId;
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

            // تنفيذ عملية الحذف
            $result = $filesService->deleteItem($accessToken, $this->userId, $this->fileId);

            if ($result) {
                // حذف السجل من قاعدة البيانات
                $attachment = ContractAttachment::find($this->attachmentId);
                if ($attachment) {
                    $attachment->delete();
                }
                Log::info("Successfully deleted file: {$this->fileId}");
            } else {
                // التعامل مع فشل حذف الملف
                Log::error("Failed to delete file: {$this->fileId}");
            }
        } catch (\Exception $e) {
            Log::error("Exception in DeleteFileFromOneDriveJob: " . $e->getMessage());
        }
    }
}
