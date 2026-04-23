<?php

namespace App\Http\Controllers\microsoft;

use App\Http\Controllers\Controller;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WordFilesController extends Controller
{
    // protected $graphService;

    // public function __construct(MicrosoftGraphBaseService $graphService)
    // {
    //     $this->graphService = $graphService;
    // }

    // /**
    //  * عرض الصفحة الرئيسية لإدخال بريد المستخدم.
    //  *
    //  * @return \Illuminate\View\View
    //  */
    // public function index()
    // {
    //     return view('onedrive.index');
    // }

    // /**
    //  * معالجة طلب الحصول على User ID بناءً على البريد الإلكتروني.
    //  *
    //  * @param Request $request
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function getUserId(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //     ]);

    //     $email = $request->input('email');
    //     $userId = $this->graphService->getUserIdByEmail($email);

    //     if ($userId === null) {
    //         return redirect()->back()->withErrors(['email' => 'فشل في جلب User ID. تأكد من صحة البريد الإلكتروني.']);
    //     }

    //     return redirect()->route('onedrive.files', ['userId' => $userId]);
    // }

    // /**
    //  * عرض قائمة ملفات OneDrive الخاصة بالمستخدم.
    //  *
    //  * @param string $userId
    //  * @return \Illuminate\View\View
    //  */
    // public function listUserFiles($userId)
    // {
    //     // التحقق من إعدادات Microsoft OAuth
    //     if (!$this->graphService->verifySettings()) {
    //         abort(500, 'إعدادات Microsoft Graph غير صحيحة.');
    //     }

    //     // جلب الملفات
    //     $files = $this->graphService->getAllUserFiles($userId);

    //     if ($files === null) {
    //         abort(500, 'فشل في جلب ملفات المستخدم.');
    //     }

    //     return view('onedrive.files', ['files' => $files, 'userId' => $userId]);
    // }

    // /**
    //  * عرض تفاصيل ملف معين.
    //  *
    //  * @param string $userId
    //  * @param string $fileId
    //  * @return \Illuminate\View\View
    //  */
    // public function getFileDetails($userId, $fileId)
    // {
    //     $details = $this->graphService->getFileDetails($userId, $fileId);

    //     if ($details === null) {
    //         abort(500, 'فشل في جلب تفاصيل الملف.');
    //     }

    //     return view('onedrive.file_details', ['file' => $details, 'userId' => $userId]);
    // }

    // /**
    //  * تنزيل ملف معين.
    //  *
    //  * @param string $userId
    //  * @param string $fileId
    //  * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
    //  */
    // public function downloadFile($userId, $fileId)
    // {
    //     $downloadResponse = $this->graphService->downloadFile($userId, $fileId);

    //     if ($downloadResponse === null) {
    //         abort(500, 'فشل في تحميل الملف.');
    //     }

    //     return $downloadResponse;
    // }

    // /**
    //  * إنشاء رابط تضمين لملف معين وعرضه.
    //  *
    //  * @param Request $request
    //  * @param string $userId
    //  * @param string $fileId
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function createEmbedLink(Request $request, $userId, $fileId)
    // {
    //     $embedLink = $this->graphService->getEmbedLink($fileId, $userId);

    //     if ($embedLink === null) {
    //         return redirect()->back()->withErrors(['embed' => 'فشل في إنشاء رابط التضمين.']);
    //     }

    //     return redirect()->route('onedrive.file.embed', ['userId' => $userId, 'fileId' => $fileId]);
    // }

    // /**
    //  * عرض رابط التضمين لملف معين.
    //  *
    //  * @param string $userId
    //  * @param string $fileId
    //  * @return \Illuminate\View\View
    //  */
    // public function showEmbedLink($userId, $fileId)
    // {
    //     $embedLink = $this->graphService->getEmbedLink($fileId, $userId);

    //     if ($embedLink === null) {
    //         abort(500, 'فشل في إنشاء رابط التضمين.');
    //     }

    //     return view('onedrive.embed', ['embedLink' => $embedLink]);
    // }
}
