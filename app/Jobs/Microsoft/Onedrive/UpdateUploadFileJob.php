<?php

namespace App\Jobs\Microsoft\Onedrive;

use App\Models\OperationsCenter\Contract\ContractAttachment;
use App\Models\User;
use App\Services\FilesService;
use App\Services\Microsoft\Onedrive\Onedrive;
use App\Services\MicrosoftGraphBaseService;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UpdateUploadFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(
        public int $userId,
        public array $updateData
    ) {
        $this->captureTenant();
    }

    /**
     * Execute the job.
     */
    public function handle(Onedrive $onedrive): void
    {
        try {
            $user = User::findOrFail($this->userId);

            if (!$user->microsoft_token) {
                throw new \Exception('Microsoft token is required');
            }

            $accessToken = $onedrive->getValidUserAccessToken($user);
            if (!$accessToken) {
                throw new \Exception('Failed to get valid access token');
            }

            $userMicrosoftId    = $onedrive->getUserIdByEmail($accessToken, $user->email);
            $folderId           = $onedrive->getOrCreateFolder($accessToken, $userMicrosoftId, 'root', 'العقود');

            // Process deletions
            $this->handleDeletions($onedrive, $accessToken, $userMicrosoftId);

            // Process renames
            $this->handleRenames($onedrive, $userMicrosoftId);

            // Process new attachments
            $this->handleNewAttachments();
        } catch (\Exception $e) {
            Log::error('Failed to process attachment updates', [
                'contract_id'   => $this->updateData['contract_id'],
                'user_id'       => $this->userId,
                'error'         => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function handleDeletions(Onedrive $onedrive, string $accessToken, string $userMicrosoftId): void
    {
        foreach ($this->updateData['delete_attachments'] as $attachId) {
            try {
                $attachment = ContractAttachment::findOrFail($attachId);

                if ($attachment->file_id && $onedrive->fileExists($accessToken, $userMicrosoftId, $attachment->file_id)) {
                    $onedrive->deleteItem($accessToken, $userMicrosoftId, $attachment->file_id);
                }

                $attachment->delete();
            } catch (\Exception $e) {
                Log::error('Failed to delete attachment', [
                    'attachment_id' => $attachId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function handleRenames(Onedrive $onedrive, string $userMicrosoftId): void
    {
        foreach ($this->updateData['existing_attachment_names'] as $attId => $newName) {
            try {
                $attachment     = ContractAttachment::findOrFail($attId);
                $ext            = pathinfo($attachment->name, PATHINFO_EXTENSION);
                $fileName       = pathinfo($newName, PATHINFO_EXTENSION) ? $newName : "{$newName}.{$ext}";

                if ($attachment->file_id) {
                    RenameFileJob::dispatch($this->userId, $userMicrosoftId, $attachment->file_id, $fileName, $attachment->id);
                }
            } catch (\Exception $e) {
                Log::error('Failed to queue rename job', [
                    'attachment_id' => $attId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function handleNewAttachments()
    {
        try {
            UploadFileJob::dispatch(
                $this->userId,
                $this->updateData['contract_id'],
                $this->updateData['new_attachments']
            );
        } catch (\Exception $e) {
            Log::error('Failed to queue rename job');
        }

    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessAttachmentUpdatesJob failed', [
            'contract_id' => $this->updateData['contract_id'],
            'user_id' => $this->userId,
            'error' => $exception->getMessage()
        ]);
    }
}
