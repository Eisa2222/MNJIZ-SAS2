<?php

namespace App\Services;

use Beta\Microsoft\Graph\Model\DriveItem as ModelDriveItem;
use Beta\Microsoft\Graph\Model\Permission;
use Beta\Microsoft\Graph\Model\UploadSession;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Beta\Microsoft\Graph\Model\ItemPreviewInfo;
use Microsoft\Graph\Model\DriveItem;

class FilesService extends MicrosoftGraphBaseService
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getUserFiles($accessToken, $userId, $folderId = 'root')
    {
        if (!$accessToken) {
            // Log::error('Access token is required to fetch user files.');
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

            $result = [];
            foreach ($files as $file) {
                $result[] = $this->driveItemToArray($file);
            }

            return $result;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException fetching user files: ' . $e->getMessage());
            // Log::error('GraphException response: ' . $e->getResponseBody());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception fetching user files: ' . $e->getMessage());
            return null;
        }
    }


    protected function driveItemToArray(ModelDriveItem $item)
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'folder' => $item->getFolder() ? true : false,
            // يمكنك إضافة خصائص أخرى حسب الحاجة
            'extension' => $this->getFileExtension($item),
        ];
    }

    private function getFileExtension(ModelDriveItem $driveItem)
    {
        // تحقق مما إذا كان العنصر ملفًا أو مجلدًا
        if ($driveItem->getFile()) {
            $filename = $driveItem->getName();
            // استخدم pathinfo لاستخراج الامتداد
            return '.' . pathinfo($filename, PATHINFO_EXTENSION);
        } elseif ($driveItem->getFolder()) {
            return 'folder';
        } else {
            return 'unknown';
        }
    }

    public function downloadFile($accessToken, $userId, $fileId)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول
        try {
            // طلب محتوى الملف مباشرة من endpoint /content
            $response = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}/content")
                ->execute();

            // الحصول على معلومات الملف (اسم ونوعه)
            $file = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}")
                ->setReturnType(DriveItem::class)
                ->execute();

            $fileName = $file->getName() ?? 'downloaded_file';
            $mimeType = $file->getFile() ? $file->getFile()->getMimeType() : 'application/octet-stream';

            // استخدام Guzzle لتحميل الملف من /content endpoint
            $client = new Client();
            $downloadResponse = $client->get("https://graph.microsoft.com/v1.0/users/{$userId}/drive/items/{$fileId}/content", [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    // 'Authorization' => "Bearer " . $this->getAccessToken(),
                    'Accept' => 'application/octet-stream',
                ],
                'stream' => true,
            ]);

            return [
                'stream' => $downloadResponse->getBody(),
                'name' => $fileName,
                'mimeType' => $mimeType,
            ];
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException downloading file: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception downloading file: ' . $e->getMessage());
            return null;
        }
    }


    public function uploadFile($accessToken, $userId, $parentId, $filePath, $fileName)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $response = $this->graph->createRequest("PUT", "/users/{$userId}/drive/items/{$parentId}:/{$fileName}:/content")
                // ->attachBody(fopen($filePath, 'r'))
                ->attachBody(file_get_contents($filePath))

                ->setReturnType(ModelDriveItem::class)
                ->execute();

            // return $this->driveItemToArray($response);
            return [
                'id' => $response->getId(),
                'url' => $response->getWebUrl(),
            ];
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException uploading file: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception uploading file: ' . $e->getMessage());
            return null;
        }
    }

    // public function uploadFileReturnId($accessToken, $userId, $parentId, $filePath, $fileName)
    // {
    //     if (!$accessToken) {
    //         // Log::error('Access token is required to fetch user files.');
    //         return null;
    //     }

    //     $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

    //     try {
    //         $response = $this->graph->createRequest("PUT", "/users/{$userId}/drive/items/{$parentId}:/{$fileName}:/content")
    //             ->attachBody(fopen($filePath, 'r'))
    //             ->setReturnType(ModelDriveItem::class)
    //             ->execute();

    //         return $this->driveItemToArray($response);
    //     } catch (\Microsoft\Graph\Exception\GraphException $e) {
    //         // Log::error('GraphException uploading file: ' . $e->getMessage());
    //         return null;
    //     } catch (\Exception $e) {
    //         // Log::error('Exception uploading file: ' . $e->getMessage());
    //         return null;
    //     }
    // }



    public function createFolder($accessToken, $userId, $parentId, $folderName)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

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
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException creating folder: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception creating folder: ' . $e->getMessage());
            return null;
        }
    }


    public function deleteItem($accessToken, $userId, $itemId)
    {

        if (!$accessToken) {
            // Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $this->graph->createRequest("DELETE", "/users/{$userId}/drive/items/{$itemId}")
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException deleting item: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            // Log::error('Exception deleting item: ' . $e->getMessage());
            return false;
        }
    }

    // public function deleteFile($accessToken, $fileId)
    // {
    //     $this->graph->setAccessToken($accessToken);

    //     try {
    //         $this->graph->createRequest("DELETE", "/drive/items/{$fileId}")
    //             ->execute();

    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error("Error deleting file from OneDrive: " . $e->getMessage());
    //         return false;
    //     }
    // }


    public function createEditLink($accessToken, $userId, $fileId)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to fetch user files.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $link = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/createLink")
                ->attachBody([
                    'type' => 'edit', // نوع الرابط: edit أو view
                    'scope' => 'organization', // نطاق الرابط: anonymous أو organization
                    'roles' => ['write', 'delete'], // السماح بالكتابة (التعديل) والحذف

                ])
                ->setReturnType(Permission::class)
                ->execute();

            return $link->getLink()->getWebUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException creating edit link: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception creating edit link: ' . $e->getMessage());
            return null;
        }
    }


    public function createViewLink($accessToken, $userId, $fileId)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to create a view link.');
            return null;
        }


        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول
        try {
            $link = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/createLink")
                ->attachBody([
                    'type' => 'view', // نوع الرابط: view أو edit
                    'scope' => 'organization', // نطاق الرابط: anonymous أو organization
                ])
                ->setReturnType(Permission::class)
                ->execute();

            return $link->getLink()->getWebUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException creating view link: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception creating view link: ' . $e->getMessage());
            return null;
        }
    }


    public function createUploadSession($accessToken, $userId, $parentId, $fileName, $fileSize)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to create a view link.');
            return null;
        }


        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول
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

            return $uploadSession->getUploadUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException creating upload session: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception creating upload session: ' . $e->getMessage());
            return null;
        }
    }


    public function uploadChunk($uploadUrl, $chunkData, $start, $end, $totalSize, $retryCount = 3)
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
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($retryCount > 0) {
                // Log::warning("Retrying upload chunk: {$start}-{$end}. Retries left: " . ($retryCount - 1));
                sleep(1); // انتظار قبل إعادة المحاولة
                return $this->uploadChunk($uploadUrl, $chunkData, $start, $end, $totalSize, $retryCount - 1);
            } else {
                $response = $e->getResponse();
                $body = json_decode($response->getBody(), true);
                // Log::error('Error uploading chunk after retries: ' . ($body['error']['message'] ?? $e->getMessage()));
                return null;
            }
        } catch (\Exception $e) {
            // Log::error('Error uploading chunk: ' . $e->getMessage());
            return null;
        }
    }


    public function getDefaultFileContent($fileType)
    {
        $templatePath = storage_path("app/templates/empty.{$fileType}");

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        return null;
    }


    public function getPreviewLink($userId, $fileId)
    {
        try {
            $response = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/preview")
                ->setReturnType(ItemPreviewInfo::class)
                ->execute();

            return $response->getGetUrl() ?? null;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException getting preview link: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception getting preview link: ' . $e->getMessage());
            return null;
        }
    }


    public function getOrCreateFolder($accessToken, $userId, $parentId, $folderName)
    {
        // الحصول على قائمة الملفات في المجلد الأب
        $files = $this->getUserFiles($accessToken, $userId, $parentId);

        if ($files === null) {
            return null;
        }

        // البحث عن المجلد بالاسم المحدد
        foreach ($files as $file) {
            if ($file['name'] === $folderName && $file['folder']) {
                return $file['id'];
            }
        }

        // إذا لم يتم العثور على المجلد، نقوم بإنشائه
        $newFolder = $this->createFolder($accessToken, $userId, $parentId, $folderName);

        return $newFolder['id'] ?? null;
    }


    public function createOrganizationLink($accessToken, $userId, $fileId)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to create organization edit link.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $response = $this->graph->createRequest("POST", "/users/{$userId}/drive/items/{$fileId}/createLink")
                ->attachBody([
                    'type' => 'edit', // نوع الرابط: edit للسماح بالتعديل
                    'scope' => 'organization', // نطاق الرابط: organization يقيد الوصول بأعضاء المؤسسة فقط
                    'roles' => ['write', 'delete'], // السماح بالكتابة (التعديل) والحذف
                ])
                ->setReturnType(\Beta\Microsoft\Graph\Model\Permission::class) // تأكد من استخدام الفئة الصحيحة
                ->execute();

            $link = $response->getLink();
            if ($link) {
                return $link->getWebUrl();
            } else {
                Log::error("Failed to get the link from permissions response.");
                return null;
            }
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException creating organization edit link: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception creating organization edit link: ' . $e->getMessage());
            return null;
        }
    }


    // public function renameFile($accessToken, $fileId, $newName)
    // {
    //     if (!$accessToken) {
    //         // Log::error('Access token is required to rename file.');
    //         return false;
    //     }
    //     $this->graph->setAccessToken($accessToken);

    //     try {
    //         $body = [
    //             "name" => $newName,
    //         ];

    //         $this->graph->createRequest("PATCH", "/drive/items/{$fileId}")
    //             ->attachBody($body)
    //             ->execute();

    //         Log::info("File renamed successfully: {$fileId} to {$newName}");
    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error("Error renaming file in OneDrive: " . $e->getMessage());
    //         return false;
    //     }
    // }

    // لاعادة التسمية
    public function renameFile($accessToken, $userId, $fileId, $newFileName)
    {
        if (!$accessToken) {
            Log::error('Access token is required to rename file.');
            return false;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            // الحصول على معلومات الملف الحالي لاسترجاع الامتداد
            $currentFile = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}")
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            // استخراج الامتداد من اسم الملف الحالي
            $currentName = $currentFile->getName(); // اسم الملف مع الامتداد
            $currentExtension = pathinfo($currentName, PATHINFO_EXTENSION);

            // تسجيل الامتداد الحالي
            Log::info("Current extension of file ID {$fileId}: {$currentExtension}");

            // التحقق مما إذا كان الاسم الجديد يحتوي على امتداد
            if (pathinfo($newFileName, PATHINFO_EXTENSION) === '') {
                $newFileName .= '.' . $currentExtension; // إضافة الامتداد
                Log::info("New file name after appending extension: {$newFileName}");
            } else {
                Log::info("New file name already has extension: {$newFileName}");
            }

            // تسجيل اسم الملف الجديد قبل إرسال الطلب
            Log::info("Renaming file ID {$fileId} to {$newFileName}");

            // إرسال طلب إعادة التسمية
            $response = $this->graph->createRequest("PATCH", "/users/{$userId}/drive/items/{$fileId}")
                ->attachBody([
                    'name' => $newFileName,
                ])
                ->setReturnType(ModelDriveItem::class)
                ->execute();

            // تسجيل الاستجابة
            Log::info("Rename response for file ID {$fileId}: " . json_encode($response));

            return $this->driveItemToArray($response);
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('GraphException renaming file: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error('Exception renaming file: ' . $e->getMessage());
            return false;
        }
    }

    // للتاكد هل الملفات موجوة ام لا
    public function fileExists($accessToken, $userId, $fileId)
    {
        $this->graph->setAccessToken($accessToken);

        try {
            $response = $this->graph->createRequest("GET", "/users/{$userId}/drive/items/{$fileId}")
                ->setReturnType(\Microsoft\Graph\Model\DriveItem::class)
                ->execute();

            return true; // الملف موجود
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            if ($statusCode == 404) {
                Log::warning("File not found in OneDrive: {$fileId}");
                return false; // الملف غير موجود
            }
            Log::error("ClientException checking file existence in OneDrive: " . $e->getMessage());
            return false;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            if ($e->getCode() == 404) {
                Log::warning("File not found in OneDrive: {$fileId}");
                return false; // الملف غير موجود
            }
            Log::error("GraphException checking file existence in OneDrive: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Exception checking file existence in OneDrive: " . $e->getMessage());
            return false;
        }
    }
}
