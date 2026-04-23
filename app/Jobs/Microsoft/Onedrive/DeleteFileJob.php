<?php

namespace App\Jobs\Microsoft\Onedrive;

use App\Models\OperationsCenter\Contract\ContractAttachment;
use App\Models\User;
use App\Services\FilesService;
use App\Services\Microsoft\Onedrive\Onedrive;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $userIdInSystem;
    public string $oneDriveUserId;
    public string $fileId;
    public int    $attachmentId;


    public int $tries = 3;

    public int $timeout = 120;


    public function __construct(
        int    $userIdInSystem,
        string $oneDriveUserId,
        string $fileId,
        int    $attachmentId
    ) {
        $this->userIdInSystem = $userIdInSystem;
        $this->oneDriveUserId = $oneDriveUserId;
        $this->fileId         = $fileId;
        $this->attachmentId   = $attachmentId;
    }

    public function handle(Onedrive $onedrive): void
    {
        Log::info('Starting DeleteFileJob', [
            'userIdInSystem' => $this->userIdInSystem,
            'fileId'         => $this->fileId,
        ]);

        try {
            $user = User::find($this->userIdInSystem);

            if (!$user || empty($user->microsoft_token)) {
                Log::warning('User not found or missing Microsoft token', [
                    'userIdInSystem' => $this->userIdInSystem,
                ]);
                return;
            }

            $accessToken = $onedrive->getValidUserAccessToken($user);
            if (!$accessToken) {
                Log::error('Unable to retrieve valid access token', [
                    'userIdInSystem' => $this->userIdInSystem,
                ]);
                return;
            }

            $deleted = $onedrive->deleteItem(
                $accessToken,
                $this->oneDriveUserId,
                $this->fileId
            );

            if ($deleted) {
                $this->removeAttachmentRecord();
                Log::info('File deleted and DB record removed', [
                    'fileId'       => $this->fileId,
                    'attachmentId' => $this->attachmentId,
                ]);
            } else {
                Log::error('OneDrive deletion returned false', [
                    'fileId' => $this->fileId,
                ]);
            }
        } catch (\Exception $e) {
            // mark job as failed so it surfaces in your queue dashboard
            $this->fail($e);
            Log::error('Exception in DeleteFileJob', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    protected function removeAttachmentRecord(): void
    {
        $attachment = ContractAttachment::find($this->attachmentId);
        if ($attachment) {
            $attachment->delete();
        } else {
            Log::warning('ContractAttachment not found for deletion', [
                'attachmentId' => $this->attachmentId,
            ]);
        }
    }
}
