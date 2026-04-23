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

class RenameFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $userIdInSystem;
    public string $oneDriveUserId;
    public string $fileId;
    public string $newFileName;
    public int    $attachmentId;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        int    $userIdInSystem,
        string $oneDriveUserId,
        string $fileId,
        string $newFileName,
        int    $attachmentId
    ) {
        $this->userIdInSystem = $userIdInSystem;
        $this->oneDriveUserId = $oneDriveUserId;
        $this->fileId         = $fileId;
        $this->newFileName    = $newFileName;
        $this->attachmentId   = $attachmentId;
    }

    public function handle(Onedrive $onedrive): void
    {
        try {
            $user = User::find($this->userIdInSystem);

            if (! $user || empty($user->microsoft_token)) {
                return;
            }

            $accessToken = $onedrive->getValidUserAccessToken($user);
            if (! $accessToken) {
                Log::error('Unable to retrieve valid access token', [
                    'userIdInSystem' => $this->userIdInSystem,
                ]);
                return;
            }

            $renamed = $onedrive->renameFile(
                $accessToken,
                $this->oneDriveUserId,
                $this->fileId,
                $this->newFileName
            );

            if ($renamed) {
                $shareLink = $onedrive->createOrganizationLink(
                    $accessToken,
                    $this->oneDriveUserId,
                    $this->fileId
                );
                $this->updateAttachmentRecord($shareLink);
                Log::info('File renamed and DB record updated', [
                    'fileId'      => $this->fileId,
                    'newFileName' => $this->newFileName,
                    'attachmentId' => $this->attachmentId,
                ]);
            } else {
                Log::error('OneDrive rename returned false', [
                    'fileId' => $this->fileId,
                ]);
            }
        } catch (\Exception $e) {
            $this->fail($e);
            Log::error('Exception in RenameFileJob', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    protected function updateAttachmentRecord(string $shareLink): void
    {
        $attachment = ContractAttachment::find($this->attachmentId);
        if ($attachment) {
            $attachment->update([
                'name'       => $this->newFileName,
                'share_link' => $shareLink,
            ]);
        } else {
            Log::warning('ContractAttachment not found for update', [
                'attachmentId' => $this->attachmentId,
            ]);
        }
    }
}
