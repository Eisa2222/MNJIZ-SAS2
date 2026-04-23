<?php

declare(strict_types=1);

namespace App\Services\LegalAI;

use App\Helpers\SettingsHelper;
use App\Models\LegalAI\AiChatMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class PdfExportService
{
    /*
    |--------------------------------------------------------------------------
    | تصدير رسالة إلى ملف PDF
    |--------------------------------------------------------------------------
    | هذه الدالة هي المسؤولة الوحيدة عن عملية إنشاء ملفات PDF من رسائل AI.
    */
    public function exportMessageToPdf(AiChatMessage $message): Mpdf
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $htmlContent = $converter->convert($message->message);

        $buildAndNormalizePath = function ($filename) {
            if (!$filename) return null;
            return str_replace('\\', '/', storage_path('app/public/' . $filename));
        };

        $headerPath = $buildAndNormalizePath(SettingsHelper::get('horizontal_header_image'));
        $footerPath = $buildAndNormalizePath(SettingsHelper::get('horizontal_footer_image'));

        $stylesheetPath = resource_path('css/pdf-styles.css');
        $stylesheet = File::exists($stylesheetPath) ? File::get($stylesheetPath) : '';

        $documentTitle = $message->chat->type->label() ?? 'مستند المساعد القانوني';

        $data = [
            'title'           => $documentTitle,
            'chatTitle'       => $message->chat->title,
            'content'         => $htmlContent,
            'headerImagePath' => $headerPath,
            'footerImagePath' => $footerPath,
            'currentDate'     => Carbon::now()->format('Y-m-d'),
            'stylesheet'      => $stylesheet,
        ];

        $viewPath = 'legal-ai.common.pdf.index';
        if (!View::exists($viewPath)) {
            $viewPath = 'legal-ai.common.pdf.index';
            if (!View::exists($viewPath)) {
                throw new \Exception("لم يتم العثور على قالب الـ PDF '{$viewPath}'.");
            }
        }

        $html = View::make($viewPath, $data)->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'almarai',
            'margin_top' => 35,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 5,
            'fontDir' => array_merge((new ConfigVariables())->getDefaults()['fontDir'], [public_path('fonts/Almarai')]),
            'fontdata' => array_merge((new FontVariables())->getDefaults()['fontdata'], [
                'almarai' => ['R' => 'Almarai-Regular.ttf', 'B' => 'Almarai-Bold.ttf', 'useOTL' => 0xFF]
            ]),
            'tempDir' => storage_path('app/public/temp')
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return $mpdf;
    }

    
}
