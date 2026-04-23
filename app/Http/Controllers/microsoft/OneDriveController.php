<?php

namespace App\Http\Controllers\microsoft;

use App\Http\Controllers\Controller;
use App\Services\FilesService;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OneDriveController extends Controller
{
    protected $graphService;
    protected $filesService;


    public function __construct(MicrosoftGraphBaseService $graphService, FilesService $filesService)
    {
        // حماية كل الدوال باستخدام middleware 'auth' و 'permission:إدارة الملفات'
        // $this->middleware(['auth', 'can:إدارة الملفات']);
        $this->graphService = $graphService;
        $this->filesService = $filesService;
    }


    protected function getAccessToken()
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return redirect()->route('microsoft.login')->with('error', 'يرجى ربط حساب Microsoft الخاص بك.');
        }

        // الحصول على رمز الوصول الصالح
        $accessToken = $this->graphService->getValidUserAccessToken($user);

        if (!$accessToken) {
            return redirect()->route('dashboard')->with('error', 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.');
        }

        return $accessToken;
    }

    /**
     * عرض الملفات والمجلدات في الجذر.
     */
    public function index()
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }



        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        $allFiles = $this->filesService->getUserFiles($accessData, $userId);

        if ($allFiles === null) {
            return back()->withErrors('فشل في جلب الملفات. تحقق من السجلات للمزيد من التفاصيل.');
        } 

        // تحويل البيانات إلى Collection إذا لم تكن كذلك بالفعل
        $allFiles = collect($allFiles);


        // تحديد عدد العناصر في كل صفحة
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // قص العناصر حسب الصفحة الحالية
        $currentItems = $allFiles->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // إنشاء كائن LengthAwarePaginator
        $paginatedItems = new LengthAwarePaginator(
            $currentItems,
            $allFiles->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        // إعداد روابط التصفح لاستخدام Bootstrap
        $paginatedItems->withPath(route('onedrive.index'));
        
        return view('onedrive.index', ['files' => $paginatedItems]);
    }

    /**
     * عرض محتوىات مجلد معين.
     */
    public function viewFolder($folderId)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }

        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        $files = $this->filesService->getUserFiles($accessData, $userId, $folderId) ?? [];

        return view('onedrive.index', compact('files'));
    }

    /**
     * تنزيل ملف من OneDrive.
     *
     * @param string $fileId
     * @return \Illuminate\Http\Response
     */
    public function downloadFile($fileId)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        $downloadData = $this->filesService->downloadFile($accessData, $userId, $fileId);

        if ($downloadData) {
            // إنشاء استجابة لبث الملف
            return response()->stream(function () use ($downloadData) {
                while (!$downloadData['stream']->eof()) {
                    echo $downloadData['stream']->read(1024 * 8);
                    flush();
                }
            }, 200, [
                'Content-Type' => $downloadData['mimeType'],
                'Content-Disposition' => 'attachment; filename="' . $downloadData['name'] . '"',
            ]);
        } else {
            return back()->withErrors('Failed to download file. Check logs for details.');
        }
    }

    /**
     * رفع ملف جديد.
     */
    public function uploadFile(Request $request)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        $request->validate([
            'file' => 'required|file',
            'parent_id' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        $parentId = $request->input('parent_id', 'root');

        $uploadResult = $this->filesService->uploadFile(
            $accessData,
            $userId,
            $parentId,
            $file->getPathname(),
            $fileName
        );

        if ($uploadResult) {
            return redirect()->back()->with('success', 'تم رفع الملف بنجاح.');
        } else {
            return back()->withErrors('Failed to upload file. Check logs for details.');
        }
    }

    /**
     * إنشاء مجلد جديد.
     */
    public function createFolder(Request $request)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);



        $request->validate([
            'folder_name' => 'required|string',
            'parent_id' => 'nullable|string',
        ]);

        $folderName = $request->input('folder_name');
        $parentId = $request->input('parent_id', 'root');

        $createResult = $this->filesService->createFolder(
            $accessData,
            $userId,
            $parentId,
            $folderName
        );

        if ($createResult) {
            return redirect()->back()->with('success', 'تم انشاء المجلد بنجاح');
        } else {
            return back()->withErrors('Failed to create folder. Check logs for details.');
        }
    }

    /**
     * حذف ملف أو مجلد.
     */
    public function deleteItem($itemId)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        $deleteResult = $this->filesService->deleteItem($accessData, $userId, $itemId);

        if ($deleteResult) {
            return redirect()->back()->with('success', 'تم حذف العنصر بنجاح.');
        } else {
            return back()->withErrors('Failed to delete item. Check logs for details.');
        }
    }

    /**
     * إنشاء ملف جديد.
     */
    public function createFile(Request $request)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        // التحقق من المدخلات
        $request->validate([
            'file_name' => 'required|string',
            'file_type' => 'required|string|in:txt,docx,xlsx,pptx',
            'parent_id' => 'nullable|string',
        ]);

        // إنشاء اسم الملف الكامل
        $fileName = $request->input('file_name') . '.' . $request->input('file_type');
        $parentId = $request->input('parent_id', 'root');

        // الحصول على محتوى القالب المناسب
        $content = $this->filesService->getDefaultFileContent($request->input('file_type'));

        if ($content === null) {
            return back()->withErrors('Unsupported file type or template not found.');
        }

        // إنشاء ملف مؤقت للمحتوى
        $tempFilePath = tempnam(sys_get_temp_dir(), 'onedrive');
        file_put_contents($tempFilePath, $content);

        // رفع الملف إلى OneDrive
        $uploadResult = $this->filesService->uploadFile(
            $accessData,
            $userId,
            $parentId,
            $tempFilePath,
            $fileName
        );

        // حذف الملف المؤقت
        unlink($tempFilePath);

        if ($uploadResult) {
            return redirect()->back()->with('success', 'تم انشاء الملف بنجاح');
        } else {
            return back()->withErrors('Failed to create file. Check logs for details.');
        }
    }

    /**
     * فتح الملف في محرر Office Online.
     *
     * @param string $fileId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editFile($fileId)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        // إنشاء رابط تحرير
        $editLink = $this->filesService->createEditLink($accessData, $userId, $fileId);

        if ($editLink) {
            // إعادة التوجيه إلى رابط التحرير
            return redirect()->away($editLink);
        } else {
            return back()->withErrors('Failed to create edit link. Check logs for details.');
        }
    }

    /**
     * عرض الملف في عارض Office Online.
     *
     * @param string $fileId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function viewFile($fileId)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);


        // إنشاء رابط عرض
        $viewLink = $this->filesService->createViewLink($accessData, $userId, $fileId);
        if ($viewLink) {
            // إعادة التوجيه إلى رابط العرض
            return redirect()->away($viewLink);
        } else {
            return back()->withErrors('Failed to create view link. Check logs for details.');
        }
    }

    /**
     * رفع ملف كبير باستخدام Upload Sessions مع SDK.
     */
    public function uploadLargeFile(Request $request)
    {
        $accessData = $this->getAccessToken();

        if ($accessData instanceof \Illuminate\Http\RedirectResponse) {
            return $accessData; // إعادة التوجيه في حالة الخطأ
        }


        $userId = $this->graphService->getUserIdByEmail($accessData, Auth::user()->email);

        $request->validate([
            'file' => 'required|file',
            'parent_id' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $parentId = $request->input('parent_id', 'root');
        $fileSize = $file->getSize();

        // إنشاء جلسة تحميل متقطع باستخدام SDK
        $uploadUrl = $this->filesService->createUploadSession($accessData, $userId, $parentId, $fileName, $fileSize);

        if (!$uploadUrl) {
            return back()->withErrors('Failed to create upload session. Check logs for details.');
        }

        // تحديد حجم الجزء (على سبيل المثال، 5MB)
        $chunkSize = 5 * 1024 * 1024;
        $handle = fopen($file->getPathname(), 'rb');
        $bytesUploaded = 0;

        while (!feof($handle)) {
            $chunkData = fread($handle, $chunkSize);
            $chunkLength = strlen($chunkData);

            if ($chunkLength === 0) {
                break;
            }

            $start = $bytesUploaded;
            $end = $bytesUploaded + $chunkLength - 1;

            // تحميل الجزء باستخدام SDK
            $result = $this->filesService->uploadChunk($uploadUrl, $chunkData, $start, $end, $fileSize);

            if ($result === null) {
                fclose($handle);
                return back()->withErrors('Failed to upload file chunk. Check logs for details.');
            }

            $bytesUploaded += $chunkLength;
        }

        fclose($handle);

        return redirect()->back()->with('success', 'تم رفع الملف بنجاح.');
    }
}