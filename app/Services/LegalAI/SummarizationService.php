<?php

declare(strict_types=1);

namespace App\Services\LegalAI;

use App\Contracts\LegalAI\AIProvider;
use App\Data\LegalAI\SummarizeData;
use App\Models\LegalAI\AiChat;
use App\Models\LegalAI\AiChatMessage;
use App\Services\LegalAI\Utils\FileParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class SummarizationService
{
    /**
     * Constructor
     */
    public function __construct(
        private AIProvider $aiProvider,
        private FileParser $fileParser
    ) {}

    /*
    |--------------------------------------------------------------------------
    | تحضير وبث رد التلخيص (Streaming)
    |--------------------------------------------------------------------------
    | هذه هي الدالة الرئيسية التي تعالج طلب التلخيص بالكامل:
    | 1. تحديث عنوان المحادثة وحفظ رسالة "تم رفع الملف".
    | 2. معالجة الملف واستخلاص النص.
    | 3. تجهيز الطلب للبث عبر الـ AI Provider.
    | 4. إعادة StreamedResponse لبث الرد مباشرة للمستخدم.
    */
    public function handleStreamedSummary(SummarizeData $data, AiChat $chat): StreamedResponse
    {
        $file = $data->document;
        $originalFilename = $file->getClientOriginalName();
        $fullStoragePath = null;

        if ($chat->messages()->count() === 0) {
            $chat->title = Str::limit("ملخص: " . $originalFilename, 50);
            $chat->save();
        }

        $summaryTypeName = $data->summaryType === 'short' ? 'مختصر' : 'مفصل';
        $chat->messages()->create([
            'sender' => 'user',
            'message' => "تم طلب تلخيص **{$summaryTypeName}** للملف: **{$originalFilename}**",
        ]);

        try {
            $tempPath = 'ai_temp_files';
            $filePath = $file->store($tempPath, 'local');
            if (!$filePath) {
                throw new \Exception('فشل حفظ الملف. تحقق من أذونات الكتابة.');
            }
            $fullStoragePath = storage_path('app/' . $filePath);

            $extractedText = $this->fileParser->parse($fullStoragePath);
            if (empty(trim((string)$extractedText))) {
                throw new \Exception('لم يتم العثور على نص قابل للقراءة في الملف.');
            }

            $messages = [
                ['role' => 'system', 'content' => $this->getSystemPrompt($data->summaryType)],
                ['role' => 'user', 'content' => "يرجى تلخيص المستند التالي:\n\n---\n\n" . $extractedText],
            ];

            $requestData = $this->aiProvider->prepareStreamRequest($messages);

            return new StreamedResponse(function () use ($requestData, $fullStoragePath) {
                try {
                    set_time_limit(0);
                    ignore_user_abort(true);
                    $curlHandle = curl_init($requestData['url']);
                    curl_setopt_array($curlHandle, [
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $requestData['payload'],
                        CURLOPT_HTTPHEADER => $requestData['headers'],
                        CURLOPT_RETURNTRANSFER => false,
                        CURLOPT_WRITEFUNCTION => function ($ch, $chunk) {
                            echo $chunk;
                            if (ob_get_level() > 0) ob_flush();
                            flush();
                            return strlen($chunk);
                        },
                        CURLOPT_TIMEOUT => 300,
                    ]);
                    curl_exec($curlHandle);
                    if (curl_errno($curlHandle)) {
                        Log::error('cURL Stream Error (Summarization): ' . curl_error($curlHandle));
                    }
                    curl_close($curlHandle);
                } finally {
                    if ($fullStoragePath && file_exists($fullStoragePath)) {
                        unlink($fullStoragePath);
                    }
                }
            }, 200, [
                'Content-Type' => 'text/event-stream; charset=UTF-8',
                'X-Accel-Buffering' => 'no',
                'Cache-Control' => 'no-cache',
            ]);
        } catch (\Exception $e) {
            if ($fullStoragePath && file_exists($fullStoragePath)) {
                unlink($fullStoragePath);
            }
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | حفظ رد الذكاء الاصطناعي (الملخص)
    |--------------------------------------------------------------------------
    | يتم استدعاء هذه الدالة عبر AJAX بعد اكتمال البث في الواجهة الأمامية.
    */
    public function saveAiResponse(Request $request, AiChat $chat): array
    {
        $validated = $request->validate(['message' => 'required|string']);

        $aiMessage = $chat->messages()->create([
            'sender' => 'ai',
            'message' => $validated['message'],
        ]);

        return [
            'ai_message' => $aiMessage,
            'chat' => $chat->fresh(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Prompt النظام (System Prompt)
    |--------------------------------------------------------------------------
    | يوفر التعليمات الأساسية لنموذج الذكاء الاصطناعي لكيفية تلخيص المستند.
    */
    private function getSystemPrompt(string $summaryType = 'detailed'): string
    {
        $basePrompt = <<<PROMPT
        أنت مساعد قانوني خبير ومهمتك هي تحليل المستندات وتقديم ملخص دقيق وموجز. اتبع الخطوات التالية بدقة:

        **الخطوة الأولى: تقييم طبيعة المستند**
        - أولاً، قم بتقييم المستند الذي تم تقديمه. هل هو مستند ذو طابع قانوني واضح (مثل لائحة دعوى، حكم قضائي، عقد، استشارة قانونية)؟

        **الخطوة الثانية: إنتاج المخرجات بناءً على التقييم**
        PROMPT;

        if ($summaryType === 'short') {
            $specificInstruction = <<<PROMPT
            *   **الحالة (أ): إذا كان المستند قانونيًا (تلخيص مختصر):**
                1.  **لا تستخدم أي مقدمات:** ابدأ الملخص مباشرةً.
                2.  **كن موجزًا جدًا:** قدم ملخصًا لا يتجاوز 3 إلى 5 نقاط رئيسية.
                3.  **ركز على الجوهر:** استخلص فقط النقاط الأكثر أهمية مثل الأطراف، الموضوع الرئيسي، والنتيجة النهائية إن كانت واضحة.
                4.  **تجاهل التفاصيل:** لا تذكر التواريخ الثانوية أو التفاصيل الدقيقة غير المؤثرة.

            *   **الحالة (ب): إذا كان المستند غير قانوني:**
                - أجب **فقط وحصراً** بالجملة التالية، بدون أي تعديل أو إضافة:
                "المستند الذي تم إرفاقه لا يبدو أنه ذو طبيعة قانونية. هذه الأداة مخصصة لتحليل وتلخيص المستندات القضائية والقانونية فقط. يرجى إرفاق مستند صحيح."
            PROMPT;
        } else {
            $specificInstruction = <<<PROMPT
            *   **الحالة (أ): إذا كان المستند قانونيًا (تلخيص مفصل):**
                1.  **لا تستخدم أي مقدمات:** ابدأ الملخص مباشرةً دون عبارات مثل "هذا ملخص لـ..." أو "بعد تحليل المستند...".
                2.  **استخلص العناوين الرئيسية:** قم بتحليل المستند واستخرج منه **فقط** العناوين والنقاط الجوهرية الموجودة فيه بالفعل.
                3.  **كن مرنًا:** لا تفترض وجود عناوين محددة مسبقًا. إذا كان المستند لا يحتوي على "دفوع" أو "نتيجة" واضحة، فلا تقم بإضافتها. بدلاً من ذلك، استخلص العناوين الهامة الأخرى التي قد تكون موجودة، مثل "الأسانيد النظامية"، "منطوق الحكم"، "البنود الأساسية للعقد"، أو أي نقاط رئيسية أخرى.
                4.  **التنسيق:** قدم الملخص في شكل نقاط واضحة باستخدام Markdown. استخدم العناوين التي استخلصتها من المستند نفسه.

                **مثال للمخرجات المرنة:**
                ```
                1. **أطراف العلاقة التعاقدية:**
                   - الطرف الأول: [اسم الطرف الأول]
                   - الطرف الثاني: [اسم الطرف الثاني]
                2. **موضوع العقد:**
                   - [شرح موجز لموضوع العقد]
                3. **الالتزامات الرئيسية:**
                   - التزامات الطرف الأول: [شرح موجز]
                   - التزامات الطرف الثاني: [شرح موجز]
                4. **مدة العقد:**
                   - [مدة العقد]
                ```

            *   **الحالة (ب): إذا كان المستند غير قانوني:**
                - أجب **فقط وحصراً** بالجملة التالية، بدون أي تعديل أو إضافة:
                "المستند الذي تم إرفاقه لا يبدو أنه ذو طبيعة قانونية. هذه الأداة مخصصة لتحليل وتلخيص المستندات القضائية والقانونية فقط. يرجى إرفاق مستند صحيح."
            PROMPT;
        }

        return $basePrompt . "\n\n" . $specificInstruction . "\n\nابدأ التحليل الآن.";
    }
}
