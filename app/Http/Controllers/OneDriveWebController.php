<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OneDriveWebController extends Controller
{
    // protected $graphService;

    // public function __construct(MicrosoftGraphService $graphService)
    // {
    //     $this->graphService = $graphService;

    //     // حماية جميع الدوال داخل الكونترولر باستخدام middleware auth
    //     $this->middleware('auth');
    // }

    // /**
    //  * عرض قائمة ملفات OneDrive الخاصة بالمستخدم.
    //  *
    //  * @return \Illuminate\View\View
    //  */
    // public function listUserFiles()
    // {
    //     // الحصول على المستخدم الحالي
    //     $user = Auth::user();

    //     // التأكد من أن المستخدم لديه microsoft_id
    //     if (empty($user->microsoft_id)) {
    //         abort(400, 'لم يتم العثور على Microsoft ID للمستخدم.');
    //     }

    //     // التحقق من إعدادات Microsoft OAuth
    //     if (!$this->graphService->verifySettings()) {
    //         abort(500, 'إعدادات Microsoft Graph غير صحيحة.');
    //     }

    //     // جلب الملفات باستخدام microsoft_id
    //     $files = $this->graphService->getAllUserFiles($user->microsoft_id);

    //     if ($files === null) {
    //         abort(500, 'فشل في جلب ملفات المستخدم.');
    //     }

    //     return view('onedrive.files', ['files' => $files, 'userId' => $user->microsoft_id]);
    // }

    // /**
    //  * عرض تفاصيل ملف معين.
    //  *
    //  * @param string $fileId
    //  * @return \Illuminate\View\View
    //  */
    // public function getFileDetails($fileId)
    // {
    //     // الحصول على المستخدم الحالي
    //     $user = Auth::user();

    //     // التأكد من أن المستخدم لديه microsoft_id
    //     if (empty($user->microsoft_id)) {
    //         abort(400, 'لم يتم العثور على Microsoft ID للمستخدم.');
    //     }

    //     $details = $this->graphService->getFileDetails($user->microsoft_id, $fileId);

    //     if ($details === null) {
    //         abort(500, 'فشل في جلب تفاصيل الملف.');
    //     }

    //     return view('onedrive.file_details', ['file' => $details, 'userId' => $user->microsoft_id]);
    // }

    // /**
    //  * تنزيل ملف معين.
    //  *
    //  * @param string $fileId
    //  * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
    //  */
    // public function downloadFile($fileId)
    // {
    //     // الحصول على المستخدم الحالي
    //     $user = Auth::user();

    //     // التأكد من أن المستخدم لديه microsoft_id
    //     if (empty($user->microsoft_id)) {
    //         abort(400, 'لم يتم العثور على Microsoft ID للمستخدم.');
    //     }

    //     $downloadResponse = $this->graphService->downloadFile($user->microsoft_id, $fileId);

    //     if ($downloadResponse === null) {
    //         abort(500, 'فشل في تحميل الملف.');
    //     }

    //     return $downloadResponse;
    // }

    // /**
    //  * إنشاء رابط تضمين لملف معين وعرضه.
    //  *
    //  * @param string $fileId
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function createEmbedLink($fileId)
    // {
    //     // الحصول على المستخدم الحالي
    //     $user = Auth::user();

    //     // التأكد من أن المستخدم لديه microsoft_id
    //     if (empty($user->microsoft_id)) {
    //         abort(400, 'لم يتم العثور على Microsoft ID للمستخدم.');
    //     }

    //     $embedLink = $this->graphService->getEmbedLink($fileId, $user->microsoft_id);

    //     if ($embedLink === null) {
    //         return redirect()->back()->withErrors(['embed' => 'فشل في إنشاء رابط التضمين.']);
    //     }

    //     return redirect()->route('onedrive.file.embed', ['fileId' => $fileId]);
    // }

    // /**
    //  * عرض رابط التضمين لملف معين.
    //  *
    //  * @param string $fileId
    //  * @return \Illuminate\View\View
    //  */
    // public function showEmbedLink($fileId)
    // {
    //     // الحصول على المستخدم الحالي
    //     $user = Auth::user();

    //     // التأكد من أن المستخدم لديه microsoft_id
    //     if (empty($user->microsoft_id)) {
    //         abort(400, 'لم يتم العثور على Microsoft ID للمستخدم.');
    //     }

    //     $embedLink = $this->graphService->getEmbedLink($fileId, $user->microsoft_id);

    //     if ($embedLink === null) {
    //         abort(500, 'فشل في إنشاء رابط التضمين.');
    //     }

    //     return view('onedrive.embed', ['embedLink' => $embedLink]);
    // }
}
