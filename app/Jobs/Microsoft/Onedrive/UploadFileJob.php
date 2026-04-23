<?php

namespace App\Jobs\Microsoft\Onedrive;

use App\Models\OperationsCenter\Contract\ContractAttachment;
use App\Models\User;
use App\Services\Microsoft\Onedrive\Onedrive;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(public int $userId, public string $contractId, public array $attachmentRecords) {}

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

            foreach ($this->attachmentRecords as $record) {
                $this->processAttachment($onedrive, $accessToken, $userMicrosoftId, $folderId, $record);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    private function processAttachment(Onedrive $onedrive, string $accessToken, string $userMicrosoftId, string $folderId, array $record): void
    {
        ContractAttachment::findOrFail($record['attachment_id']);

        try {
            $response = $onedrive->uploadFile(
                $accessToken,
                $userMicrosoftId,
                $folderId,
                $record['file_path'],
                $record['file_name']
            );

            if (is_array($response) && isset($response['id'], $response['url'])) {
                $this->attachToContract($response, $onedrive, $accessToken, $userMicrosoftId, $record['attachment_id']);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    protected function attachToContract(array $uploadResult, Onedrive $onedrive, string $accessToken, $userMicrosoftId, $attachmentId): void
    {
        $shareLink = $onedrive->createOrganizationLink(
            $accessToken,
            $userMicrosoftId,
            $uploadResult['id']
        );

        $attachment = ContractAttachment::find($attachmentId);
        if ($attachment) {
            $attachment->update([
                'file_id'    => $uploadResult['id'],
                'file_url'   => $uploadResult['url'],
                'share_link' => $shareLink,
            ]);
        }
    }


    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessContractAttachmentsJob failed');
    }
}
