<?php

declare(strict_types=1);

namespace App\Http\Controllers\LegalAI;

use App\Http\Controllers\Controller;
use App\Models\LegalAI\AiChatMessage;
use App\Services\LegalAI\PdfExportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExportController extends Controller
{
    public function __construct(private PdfExportService $pdfExportService) {}

    /*
    |--------------------------------------------------------------------------
    | تصدير رسالة محددة إلى PDF
    |--------------------------------------------------------------------------
    | هذا هو المسار المركزي لتصدير أي رسالة من أي أداة.
    */
    public function exportMessage(AiChatMessage $message)
    {
        if ($message->chat->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك.');
        }

        try {
            $mpdf = $this->pdfExportService->exportMessageToPdf($message);

            $fileName = str_replace(' ', '_', $message->chat->title) . '.pdf';

            return $mpdf->Output($fileName, 'I');
        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response('حدث خطأ أثناء إنشاء ملف الـ PDF. يرجى مراجعة السجلات.', 500);
        }
    }
}
