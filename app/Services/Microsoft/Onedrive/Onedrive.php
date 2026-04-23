<?php

namespace App\Services\Microsoft\Onedrive;

use App\Services\Microsoft\GraphBase\GraphBase;
use Beta\Microsoft\Graph\Model\DriveItem as ModelDriveItem;
use Beta\Microsoft\Graph\Model\Permission;
use Beta\Microsoft\Graph\Model\UploadSession;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Beta\Microsoft\Graph\Model\ItemPreviewInfo;
use GuzzleHttp\Exception\ClientException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Model\DriveItem;

class Onedrive extends GraphBase
{
    private const CHUNK_SIZE = 327680; // 320KB
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 1; // seconds

    public function __construct()
    {
        parent::__construct();
    }


    public function getUserFiles($accessToken, $userId, $folderId = 'root')
    {
        if (!$this->validateAccessToken($accessToken)) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $path = $folderId === 'root'
                ? "/users/{$userId}/drive/root/children"
                : "/users/{$userId}/drive/items/{$folderId}/children";

            $files = $this->graph->createRequest("GET", $path)
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            return $this->processFileList($files);
        } catch (GraphException $e) {
            Log::error('GraphException fetching user files', [
                'message'   => $e->getMessage(),
                'user_id'   => $userId,
                'folder_id' => $folderId
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception fetching user files', [
                'message'   => $e->getMessage(),
                'user_id'   => $userId,
                'folder_id' => $folderId
            ]);
            return null;
        }
    }

    public function downloadFile($accessToken, $userId, $fileId)
    {
        if (!$this->validateAccessToken($accessToken)) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // Get file metadata first
            $file = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}")
                ->setReturnType(DriveItem::class)
                ->execute();

            $fileName = $file->getName() ?? 'downloaded_file';
            $mimeType = $file->getFile()?->getMimeType() ?? 'application/octet-stream';

            // Download file content
            $client = new Client();
            $downloadResponse = $client->get("https://graph.microsoft.com/v1.0/users/{$userId}/drive/items/{$fileId}/content", [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Accept' => 'application/octet-stream',
                ],
                'stream' => true,
            ]);

            return [
                'stream'        => $downloadResponse->getBody(),
                'name'          => $fileName,
                'mimeType'      => $mimeType,
                'size'          => $file->getSize(),
            ];
        } catch (GraphException $e) {
            Log::error('GraphException downloading file', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception downloading file', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return null;
        }
    }

    public function uploadFile($accessToken, $userId, $parentId, $filePath, $fileName)
    {
        if (!$this->validateAccessToken($accessToken) || !file_exists($filePath)) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $fileSize = filesize($filePath);

            // Use resumable upload for large files (>4MB)
            if ($fileSize > 4 * 1024 * 1024) {
                return $this->uploadLargeFile($userId, $parentId, $filePath, $fileName);
            }

            // Simple upload for small files
            $response = $this->graph->createRequest("PUT", "/users/{$userId}/drive/items/{$parentId}:/{$fileName}:/content")
                ->attachBody(file_get_contents($filePath))
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            return [
                'id'    => $response->getId(),
                'name'  => $response->getName(),
                'url'   => $response->getWebUrl(),
                'size'  => $response->getSize(),
            ];
        } catch (GraphException $e) {
            Log::error('GraphException uploading file', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'parent_id' => $parentId,
                'file_name' => $fileName
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception uploading file', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'parent_id' => $parentId,
                'file_name' => $fileName
            ]);
            return null;
        }
    }

    public function createFolder(string $accessToken, string $userId, string $parentId, string $folderName): ?array
    {
        if (!$this->validateAccessToken($accessToken)) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

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
        } catch (GraphException $e) {
            Log::error('GraphException creating folder', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'parent_id' => $parentId,
                'folder_name' => $folderName
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating folder', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'parent_id' => $parentId,
                'folder_name' => $folderName
            ]);
            return null;
        }
    }

    public function deleteItem(string $accessToken, string $userId, string $itemId): bool
    {
        if (!$this->validateAccessToken($accessToken)) {
            return false;
        }
        $this->graph->setAccessToken($accessToken);

        try {
            $this->graph->createRequest("DELETE", "/users/{$userId}/drive/items/{$itemId}")
                ->execute();

            return true;
        } catch (GraphException $e) {
            Log::error('GraphException deleting item', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'item_id' => $itemId
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception deleting item', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'item_id' => $itemId
            ]);
            return false;
        }
    }


    public function createEditLink(string $accessToken, string $userId, string $fileId): ?string
    {
        return $this->createLink($accessToken, $userId, $fileId, 'edit', 'organization', ['write', 'delete']);
    }

    public function createViewLink(string $accessToken, string $userId, string $fileId): ?string
    {
        return $this->createLink($accessToken, $userId, $fileId, 'view', 'organization');
    }

    public function createOrganizationLink(string $accessToken, string $userId, string $fileId): ?string
    {
        return $this->createLink($accessToken, $userId, $fileId, 'edit', 'organization', ['write', 'delete']);
    }

    // لاعادة التسمية
    public function renameFile(string $accessToken, string $userId, string $fileId, string $newFileName)
    {
        if (!$this->validateAccessToken($accessToken)) {
            return false;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            // Get current file info to preserve extension
            $currentFile = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}")
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            $currentName = $currentFile->getName();
            $currentExtension = pathinfo($currentName, PATHINFO_EXTENSION);

            // Add extension if not present
            if (empty(pathinfo($newFileName, PATHINFO_EXTENSION)) && !empty($currentExtension)) {
                $newFileName .= '.' . $currentExtension;
            }

            $response = $this->graph->createRequest("PATCH", "/users/{$userId}/drive/items/{$fileId}")
                ->attachBody(['name' => $newFileName])
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            return $this->driveItemToArray($response);
        } catch (GraphException $e) {
            Log::error('GraphException renaming file', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId,
                'new_name' => $newFileName
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception renaming file', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId,
                'new_name' => $newFileName
            ]);
            return false;
        }
    }

    public function fileExists(string $accessToken, string $userId, string $fileId): bool
    {
        if (!$this->validateAccessToken($accessToken)) {
            return false;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}")
                ->setReturnType(DriveItem::class)
                ->execute();

            return true;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return false;
            }
            Log::error('ClientException checking file existence', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return false;
        } catch (GraphException $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            Log::error('GraphException checking file existence', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception checking file existence', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return false;
        }
    }

    public function getOrCreateFolder(string $accessToken, string $userId, string $parentId, string $folderName): ?string
    {
        $files = $this->getUserFiles($accessToken, $userId, $parentId);

        if ($files === null) {
            return null;
        }

        // Search for existing folder
        foreach ($files as $file) {
            if ($file['name'] === $folderName && $file['folder']) {
                return $file['id'];
            }
        }

        // Create new folder if not found
        $newFolder = $this->createFolder($accessToken, $userId, $parentId, $folderName);
        return $newFolder['id'] ?? null;
    }


    public function getPreviewLink(string $accessToken, string $userId, string $fileId): ?string
    {
        if (!$this->validateAccessToken($accessToken)) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $response = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/preview")
                ->setReturnType(ItemPreviewInfo::class)
                ->execute();

            return $response->getGetUrl();
        } catch (GraphException $e) {
            Log::error('GraphException getting preview link', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting preview link', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return null;
        }
    }

    public function getDefaultFileContent(string $fileType): ?string
    {
        $templatePath = storage_path("app/templates/empty.{$fileType}");
        return file_exists($templatePath) ? file_get_contents($templatePath) : null;
    }

    /*
    |============================================================================
    |============================================================================
    |                         private functions
    |============================================================================
    |============================================================================
    */



    private function validateAccessToken(?string $accessToken): bool
    {
        if (empty($accessToken)) {
            Log::error('Access token is required');
            return false;
        }
        return true;
    }

    private function processFileList($files): array
    {
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->driveItemToArray($file);
        }
        return $result;
    }

    private function uploadLargeFile(string $userId, string $parentId, string $filePath, string $fileName): ?array
    {
        $fileSize = filesize($filePath);
        $uploadUrl = $this->createUploadSession($userId, $parentId, $fileName, $fileSize);

        if (!$uploadUrl) {
            return null;
        }

        $handle = fopen($filePath, 'rb');
        $start = 0;

        while (!feof($handle)) {
            $chunkData = fread($handle, self::CHUNK_SIZE);
            $end = $start + strlen($chunkData) - 1;

            $result = $this->uploadChunk($uploadUrl, $chunkData, $start, $end, $fileSize);

            if ($result === null) {
                fclose($handle);
                return null;
            }

            // If upload is complete, return the result
            if (isset($result['id'])) {
                fclose($handle);
                return [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'url' => $result['webUrl'],
                    'size' => $result['size'],
                ];
            }

            $start = $end + 1;
        }

        fclose($handle);
        return null;
    }

    private function createUploadSession(string $userId, string $parentId, string $fileName, int $fileSize): ?string
    {
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

            return $uploadSession->getUploadUrl();
        } catch (GraphException $e) {
            Log::error('GraphException creating upload session', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'parent_id' => $parentId,
                'file_name' => $fileName
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating upload session', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'parent_id' => $parentId,
                'file_name' => $fileName
            ]);
            return null;
        }
    }

    private function uploadChunk(string $uploadUrl, string $chunkData, int $start, int $end, int $totalSize, int $retryCount = self::MAX_RETRIES): ?array
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
        } catch (ClientException $e) {
            if ($retryCount > 0) {
                Log::warning("Retrying upload chunk: {$start}-{$end}. Retries left: " . ($retryCount - 1));
                sleep(self::RETRY_DELAY);
                return $this->uploadChunk($uploadUrl, $chunkData, $start, $end, $totalSize, $retryCount - 1);
            }

            Log::error('Error uploading chunk after retries', [
                'message' => $e->getMessage(),
                'start' => $start,
                'end' => $end
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error uploading chunk', [
                'message' => $e->getMessage(),
                'start' => $start,
                'end' => $end
            ]);
            return null;
        }
    }

    private function createLink(string $accessToken, string $userId, string $fileId, string $type, string $scope, array $roles = []): ?string
    {
        if (!$this->validateAccessToken($accessToken)) {
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $body = [
                'type' => $type,
                'scope' => $scope,
            ];

            if (!empty($roles)) {
                $body['roles'] = $roles;
            }

            $link = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/createLink")
                ->attachBody($body)
                ->setReturnType(Permission::class)
                ->execute();

            return $link->getLink()?->getWebUrl();
        } catch (GraphException $e) {
            Log::error("GraphException creating {$type} link", [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Exception creating {$type} link", [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'file_id' => $fileId
            ]);
            return null;
        }
    }


    private function driveItemToArray(ModelDriveItem $item): array
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'folder' => $item->getFolder() ? true : false,
            'extension' => $this->getFileExtension($item),
            'size' => $item->getSize(),
            'created' => $item->getCreatedDateTime()?->format('Y-m-d H:i:s'),
            'modified' => $item->getLastModifiedDateTime()?->format('Y-m-d H:i:s'),
            'web_url' => $item->getWebUrl(),
        ];
    }

    private function getFileExtension(ModelDriveItem $driveItem): string
    {
        if ($driveItem->getFile()) {
            $filename = $driveItem->getName();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            return $extension ? '.' . $extension : '';
        }

        if ($driveItem->getFolder()) {
            return 'folder';
        }

        return 'unknown';
    }
}
