<?php

namespace App\Services\Common;

use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Illuminate\Support\Facades\View;
use Mpdf\HTMLParserMode;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfExportService
{
    /**
     * MPDF configuration array.
     *
     * @var array
     */
    protected array $config;

    /**
     * Constructor: initialize MPDF configuration.
     *
     * @param array $customConfig Additional MPDF settings.
     */
    public function __construct(array $customConfig = [])
    {
        $defaultVars = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontVars    = (new \Mpdf\Config\FontVariables())->getDefaults();

        $this->config = array_merge([
            'mode'             => 'utf-8',
            'format'           => 'A4',
            'default_font'     => 'almarai',
            'margin_left'      => 0,
            'margin_right'     => 0,
            'margin_top'       => 30,
            'margin_bottom'    => 35,
            'margin_header'    => 0,
            'margin_footer'    => 0,
            'orientation'      => 'P',
            'fontDir'          => array_merge(
                $defaultVars['fontDir'],
                [public_path('fonts/Almarai')]
            ),
            'fontdata'         => array_merge(
                $fontVars['fontdata'],
                [
                    'almarai' => [
                        'R'         => 'Almarai-Regular.ttf',
                        'B'         => 'Almarai-Bold.ttf',
                        'L'         => 'Almarai-Light.ttf',
                        'useOTL'    => 0xFF,
                        'useKashida' => 75,
                    ],
                ]
            ),
            'default_font_size' => 12,
            'tempDir'          => storage_path('app/public/temp'),
        ], $customConfig);
    }


    public function exportHtml(string $bodyContent, string $fileName, $templateImage = true, $signature = false,  $is_private_and_secret = false): StreamedResponse
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($bodyContent, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        $bodyContent = $dom->saveHTML();

        $cssPath = resource_path('css/pdf.css');
        $css = file_get_contents($cssPath);

        $mpdf = new Mpdf($this->config);
        $mpdf->SetDirectionality('rtl');

        $template_image      =  storage_path('app/public/' . SettingsHelper::get('template_image'));
        $signature_image     =  $signature && $templateImage ? storage_path('app/public/' . SettingsHelper::get('signature')) : null;

        if (!empty($template_image) && file_exists($template_image) && $templateImage) {
            $mpdf->SetWatermarkImage($template_image, 1, [210, 297], [0, 0]);
            $mpdf->showWatermarkImage = true;
        }


        if ($css) {
            $mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);
        }

        $layoutHtml = View::make('pdf.layout', [
            'content'                   => $bodyContent,
            'signature_image'           => $signature_image,
            'is_private_and_secret'     => $is_private_and_secret,
        ])->render();

        $mpdf->WriteHTML($layoutHtml, HTMLParserMode::HTML_BODY);

        return response()->streamDownload(
            fn() => print $mpdf->Output($fileName, Destination::STRING_RETURN),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }



    public function exportPage(string $view, array $data, string $fileName, $templateImage = true): StreamedResponse
    {
        try {
            // إنشاء المحتوى من الـ view
            $bodyContent = View::make($view, $data)->render();

            // معالجة HTML لضمان التوافق مع mPDF
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($bodyContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            $bodyContent = $dom->saveHTML();

            // تحميل CSS
            $cssPath = resource_path('css/pdf.css');
            $css = file_exists($cssPath) ? file_get_contents($cssPath) : '';

            // إنشاء mPDF
            $mpdf = new Mpdf($this->config);
            $mpdf->SetDirectionality('rtl');

            // إعداد صورة القالب
            $template_image = storage_path('app/public/' . SettingsHelper::get('template_image'));

            // التحقق من وجود صورة القالب قبل تطبيقها
            if (!empty($template_image) && file_exists($template_image) && $templateImage) {
                $mpdf->SetWatermarkImage($template_image, 1, [210, 297], [0, 0]);
                $mpdf->showWatermarkImage = true;
            }

            // إضافة CSS إذا كان موجود
            if (!empty($css)) {
                $mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);
            }

            // إنشاء التخطيط الرئيسي
            $layoutHtml = View::make('pdf.layout', [
                'content'                   => $bodyContent,
                'signature_image'           => false,
                'is_private_and_secret'     => false,
            ])->render();

            // كتابة HTML إلى PDF
            $mpdf->WriteHTML($layoutHtml, HTMLParserMode::HTML_BODY);

            // إرجاع الاستجابة
            return response()->streamDownload(
                function () use ($mpdf, $fileName) {
                    echo $mpdf->Output($fileName, Destination::STRING_RETURN);
                },
                $fileName,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                ]
            );
        } catch (\Exception $e) {
            Log::error('PDF Export Error: ' . $e->getMessage(), [
                'view' => $view,
                'fileName' => $fileName,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
