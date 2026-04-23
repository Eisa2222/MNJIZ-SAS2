<?php

declare(strict_types=1);

namespace App\Services\LegalAI;

use App\Contracts\LegalAI\AIProvider;
use App\Data\LegalAI\PrecedentData;
use App\Models\LegalAI\AiChat;
use App\Models\LegalAI\AiChatMessage;
use App\Services\LegalAI\Utils\FileParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrecedentService
{
    public function __construct(
        private AIProvider $aiProvider,
        private FileParser $fileParser
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Handle Streamed Precedent Search
    |--------------------------------------------------------------------------
    | Prepares and streams AI-found judicial precedents.
    */
    public function handleStreamedSearch(PrecedentData $data, AiChat $chat): StreamedResponse
    {
        $userMessage = $this->buildUserMessage($data);

        if ($chat->messages()->count() === 0) {
            $chat->title = Str::limit("بحث: " . ($data->queryText ?: $data->document->getClientOriginalName()), 50);
            $chat->save();
        }
        $chat->messages()->create(['sender' => 'user', 'message' => $userMessage]);

        $fullContext = $data->queryText ?? '';
        $fullStoragePath = null;
        try {
            if ($data->document) {
                $tempPath = 'ai_temp_files';
                $filePath = $data->document->store($tempPath, 'local');
                if (!$filePath) throw new \Exception('Failed to store document.');

                $fullStoragePath = storage_path('app/' . $filePath);
                $extractedText = $this->fileParser->parse($fullStoragePath);
                $fullContext .= "\n\n--- محتوى المستند المرفق للتحليل ---\n" . $extractedText;
            }
            if (empty(trim($fullContext))) throw new \Exception('No query text or document provided.');

            $messages = [
                ['role' => 'system', 'content' => $this->getSystemPrompt()],
                ['role' => 'user', 'content' => $fullContext],
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
                        CURLOPT_CONNECTTIMEOUT => 60,
                    ]);

                    curl_exec($curlHandle);

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
                'Connection' => 'keep-alive',
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
    | Save AI Response
    |--------------------------------------------------------------------------
    | Saves the final AI-found precedents to the database.
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
    | Build User Message
    |--------------------------------------------------------------------------
    | Creates a user-facing message confirming the search request.
    */
    private function buildUserMessage(PrecedentData $data): string
    {
        $message = "تم طلب بحث عن سوابق قضائية.";
        if ($data->queryText) {
            $message .= "\n\n**نص البحث:**\n" . $data->queryText;
        }
        if ($data->document) {
            $message .= "\n\n**الملف المرفق للتحليل:** " . $data->document->getClientOriginalName();
        }
        return $message;
    }

    /*
    |--------------------------------------------------------------------------
    | Get System Prompt
    |--------------------------------------------------------------------------
    | Generates the system prompt for finding judicial precedents.
    */
    private function getSystemPrompt(): string
    {
        return <<<PROMPT
        أنت باحث قانوني فائق الذكاء، متخصص في تحليل السوابق القضائية السعودية. مهمتك هي تحليل الواقعة أو المستند المقدم من المستخدم، ثم البحث في قاعدة معارفك الواسعة عن كل السوابق القضائية المشابهة من مختلف المحاكم السعودية.

        **قواعد المخرجات الصارمة:**
        1.  **لا تستخدم مقدمات أو خواتيم:** ابدأ مباشرة بعرض أول سابقة قضائية.
        2.  **التنسيق والترقيم:** قدم كل سابقة قضائية تجدها في شكل منسق وواضح باستخدام Markdown. **قم بترقيم كل سابقة بشكل تسلسلي** (سابقة قضائية رقم (1)، سابقة قضائية رقم (2)، وهكذا). استخدم الهيكل التالي **بالضبط** لكل سابقة:

        ---
        ### سابقة قضائية رقم (X)
        **درجة التطابق:** [قدّر نسبة مئوية (مثال: 85%) تعبر عن مدى تشابه هذه السابق    ة مع حالة المستخدم]
        **المحكمة:** [اذكر اسم المحكمة، مثال: المحكمة التجارية بالرياض]
        **الموضوع:** [اذكر موضوع السابقة، مثال: نزاع على علامة تجارية]
        **ملخص الوقائع:** [قدم ملخصًا موجزًا ودقيقًا لوقائع القضية]
        **منطوق الحكم:** [اذكر منطوق الحكم النهائي بشكل واضح]
        **المبدأ القضائي المستخلص:** [استنتج المبدأ القانوني الأساسي والمفيد من هذه السابقة]

        3.  **لا تضع حداً للعدد:** قم بعرض **جميع** السوابق ذات الصلة التي تجدها، مهما كان عددها.
        4.  **في حالة عدم وجود سوابق:** إذا لم تجد أي سوابق قضائية مشابهة على الإطلاق، أجب فقط بالجملة التالية: "لم يتم العثور على سوابق قضائية مطابقة للواقعة المقدمة في قاعدة المعرفة الحالية."

        ابدأ البحث والتحليل الآن.
    PROMPT;
    }
}
