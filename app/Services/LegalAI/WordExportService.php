<?php

declare(strict_types=1);

namespace App\Services\LegalAI;

use App\Helpers\SettingsHelper;
use App\Models\LegalAI\AiChatMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Language;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class WordExportService
{
    /*
    |--------------------------------------------------------------------------
    | تصدير رسالة إلى ملف Word
    |--------------------------------------------------------------------------
    | هذه الدالة مسؤولة عن إنشاء ملفات Word من رسائل AI لجميع الأقسام
    */
    public function exportMessageToWord(AiChatMessage $message): PhpWord
    {
        $phpWord = new PhpWord();

        // إعداد اللغة العربية والاتجاه
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::AR_SA));
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(12);

        // إنشاء القسم الرئيسي
        $section = $phpWord->addSection([
            'marginTop' => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(2),
            'marginLeft' => Converter::cmToTwip(2),
            'marginRight' => Converter::cmToTwip(2),
        ]);

        // إضافة الهيدر
        $this->addHeader($section, $message);

        // إضافة المحتوى
        $this->addContent($section, $message);

        // إضافة الفوتر
        $this->addFooter($section, $message);

        return $phpWord;
    }

    /*
    |--------------------------------------------------------------------------
    | إضافة الهيدر
    |--------------------------------------------------------------------------
    */
    private function addHeader($section, AiChatMessage $message): void
    {
        $header = $section->addHeader();

        // إضافة صورة الهيدر إذا كانت موجودة
        $headerImagePath = SettingsHelper::get('horizontal_header_image');
        if ($headerImagePath && File::exists(storage_path('app/public/' . $headerImagePath))) {
            $header->addImage(
                storage_path('app/public/' . $headerImagePath),
                [
                    'width' => Converter::cmToTwip(15),
                    'height' => Converter::cmToTwip(3),
                    'alignment' => Jc::CENTER,
                ]
            );
        } else {
            // إضافة عنوان نصي إذا لم تكن هناك صورة
            $header->addText(
                env('APP_NAME', 'المساعد القانوني'),
                [
                    'name' => 'Arial',
                    'size' => 16,
                    'bold' => true,
                    'color' => 'c5a879'
                ],
                ['alignment' => Jc::CENTER]
            );
        }

        // إضافة خط فاصل
        $header->addText('', [], ['borderBottomSize' => 6, 'borderBottomColor' => 'c5a879']);
    }

    /*
    |--------------------------------------------------------------------------
    | إضافة المحتوى الرئيسي
    |--------------------------------------------------------------------------
    */
    private function addContent($section, AiChatMessage $message): void
    {
        // إضافة عنوان المحادثة
        $documentTitle = $message->chat->type->label() ?? 'مستند المساعد القانوني';
        $section->addText(
            $documentTitle,
            [
                'name' => 'Arial',
                'size' => 18,
                'bold' => true,
                'color' => '333333'
            ],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
        );

        // إضافة عنوان المحادثة
        $section->addText(
            'موضوع المحادثة: ' . $message->chat->title,
            [
                'name' => 'Arial',
                'size' => 14,
                'bold' => true,
                'color' => '555555'
            ],
            ['alignment' => Jc::RIGHT, 'spaceAfter' => 240]
        );

        // إضافة تاريخ التصدير
        $section->addText(
            'تاريخ التصدير: ' . Carbon::now()->format('Y-m-d H:i'),
            [
                'name' => 'Arial',
                'size' => 11,
                'color' => '666666'
            ],
            ['alignment' => Jc::RIGHT, 'spaceAfter' => 360]
        );

        // تحويل المحتوى من Markdown إلى نص
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $htmlContent = $converter->convert($message->message);

        // تنظيف HTML وتحويله إلى نص منسق
        $plainText = $this->convertHtmlToPlainText($htmlContent);

        // إضافة المحتوى مع تنسيق مناسب
        $section->addText(
            $plainText,
            [
                'name' => 'Arial',
                'size' => 12,
                'color' => '333333'
            ],
            [
                'alignment' => Jc::RIGHT,
                'lineHeight' => 1.5,
                'spaceAfter' => 120
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | إضافة الفوتر
    |--------------------------------------------------------------------------
    */
    private function addFooter($section, AiChatMessage $message): void
    {
        $footer = $section->addFooter();

        // إضافة صورة الفوتر إذا كانت موجودة
        $footerImagePath = SettingsHelper::get('horizontal_footer_image');
        if ($footerImagePath && File::exists(storage_path('app/public/' . $footerImagePath))) {
            $footer->addImage(
                storage_path('app/public/' . $footerImagePath),
                [
                    'width' => Converter::cmToTwip(19),
                    'height' => Converter::cmToTwip(2),
                    'alignment' => Jc::CENTER,
                ]
            );
        } else {
            // إضافة معلومات الفوتر النصية
            $footer->addText(
                'تم إنشاء هذا المستند بواسطة ' . env('APP_NAME', 'المساعد القانوني'),
                [
                    'name' => 'Arial',
                    'size' => 10,
                    'color' => '666666'
                ],
                ['alignment' => Jc::CENTER]
            );
        }

        // إضافة رقم الصفحة
        $footer->addPreserveText(
            'صفحة {PAGE} من {NUMPAGES}',
            [
                'name' => 'Arial',
                'size' => 10,
                'color' => '666666'
            ],
            ['alignment' => Jc::CENTER]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | تحويل HTML إلى نص منسق
    |--------------------------------------------------------------------------
    */
    private function convertHtmlToPlainText(string $html): string
    {
        // إزالة علامات HTML
        $text = strip_tags($html);

        // تنظيف المسافات الزائدة
        $text = preg_replace('/\s+/', ' ', $text);

        // تنظيف الأسطر المتعددة
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);

        return trim($text);
    }
}
