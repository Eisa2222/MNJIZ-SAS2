<?php

declare(strict_types=1);

namespace App\Services\LegalAI\Utils;

use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;


class FileParser
{
    /*
    |--------------------------------------------------------------------------
    | يقوم بتحليل ملف واستخراج محتواه النصي.
    |--------------------------------------------------------------------------
    */
    public function parse(string $fullPath): ?string
    {
        if (!file_exists($fullPath)) {
            return null;
        }

        try {
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            $extractedText = match ($extension) {
                'pdf' => $this->parsePdf($fullPath),
                'doc', 'docx' => $this->parseDoc($fullPath),
                default => ''
            };

            return $this->cleanText($extractedText);
        } catch (\Exception $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | يستخرج النص من ملف PDF.
    |--------------------------------------------------------------------------
    */
    private function parsePdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    /**
     * يستخرج النص من ملفات DOC أو DOCX.
     */

    /*
    |--------------------------------------------------------------------------
    | يستخرج النص من ملفات DOC أو DOCX.
    |--------------------------------------------------------------------------
    */
    private function parseDoc(string $path): string
    {
        $phpWord = IOFactory::load($path);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                }
            }
        }
        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | يقوم بتنظيف النص من الأحرف غير المرغوب فيها والمسافات الزائدة.
    |--------------------------------------------------------------------------
    */
    private function cleanText(string $text): string
    {
        $cleanText = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
        $cleanText = preg_replace('/[^\p{Arabic}\p{L}\p{N}\p{Z}\p{P}\p{S}]/u', ' ', $cleanText);
        $cleanText = preg_replace('/\s+/', ' ', $cleanText);
        return trim($cleanText);
    }
}
