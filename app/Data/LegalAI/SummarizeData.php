<?php

declare(strict_types=1);

namespace App\Data\LegalAI;

use Illuminate\Http\UploadedFile;

/**
 * Data Transfer Object (DTO) لطلب تلخيص مستند.
 *
 * هذا الكائن يمثل البيانات النظيفة والمتحقق منها اللازمة لبدء عملية التلخيص.
 * استخدام DTO يضمن أن الخدمات (Services) تتعامل دائمًا مع هيكل بيانات
 * متوقع ومحدد، ويفصلها عن تفاصيل طلب الـ HTTP.
 */
final class SummarizeData
{
    /**
     * @param UploadedFile $document الملف المرفوع.
     * @param string $summaryType نوع التلخيص المطلوب ('detailed' or 'short').
     */
    public function __construct(
        public readonly UploadedFile $document,
        public readonly string $summaryType,
    ) {}
}
