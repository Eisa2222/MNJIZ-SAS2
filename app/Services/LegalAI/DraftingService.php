<?php

declare(strict_types=1);

namespace App\Services\LegalAI;

use App\Contracts\LegalAI\AIProvider;
use App\Data\LegalAI\DraftingData;
use App\Enums\LegalAI\DraftingType;
use App\Models\LegalAI\AiChat;
use App\Models\LegalAI\AiChatMessage;
use App\Services\LegalAI\Utils\FileParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class DraftingService
{
    public function __construct(
        private AIProvider $aiProvider,
        private FileParser $fileParser
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Handle Streamed Draft
    |--------------------------------------------------------------------------
    | Prepares and streams the AI-generated draft based on user inputs.
    */
    public function handleStreamedDraft(DraftingData $data, AiChat $chat): StreamedResponse
    {
        if ($chat->messages()->count() === 0) {
            $draftingTypeLabel = $this->getDraftingTypeLabel($data->draftingType);
            $chat->title = Str::limit("مسودة: " . $draftingTypeLabel, 50);
            $chat->save();
        }

        $userMessage = $this->buildUserMessage($data);
        $chat->messages()->create(['sender' => 'user', 'message' => $userMessage]);

        $fullContext = $data->rawText ?? '';
        $fullStoragePath = null;

        try {
            if ($data->document) {
                $tempPath = 'ai_temp_files';
                $filePath = $data->document->store($tempPath, 'local');
                if (!$filePath) throw new \Exception('Failed to store document.');

                $fullStoragePath = storage_path('app/' . $filePath);
                $extractedText = $this->fileParser->parse($fullStoragePath);
                $fullContext .= "\n\n--- محتوى المستند المرفق ---\n" . $extractedText;
            }

            if (empty(trim($fullContext))) throw new \Exception('No text or document provided.');

            $messages = [
                ['role' => 'system', 'content' => $this->getSystemPrompt($data->draftingType)],
                ['role' => 'user', 'content' => $fullContext],
            ];

            $requestData = $this->aiProvider->prepareStreamRequest($messages);

            return new StreamedResponse(function () use ($requestData, $fullStoragePath) {
                // [الحل هنا] إضافة كود cURL الكامل
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

                    if (curl_errno($curlHandle)) {
                        Log::error('cURL Stream Error (Drafting): ' . curl_error($curlHandle));
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
    | Saves the final AI-generated draft to the database.
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
    | Creates a user-facing message confirming the drafting request.
    */
    private function buildUserMessage(DraftingData $data): string
    {
        $draftingTypeLabel = $this->getDraftingTypeLabel($data->draftingType);
        $message = "تم طلب صياغة **{$draftingTypeLabel}**.";
        if ($data->rawText) {
            $message .= "\n\n**النقاط الأساسية:**\n" . $data->rawText;
        }
        if ($data->document) {
            $message .= "\n\n**الملف المرفق:** " . $data->document->getClientOriginalName();
        }
        return $message;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Drafting Type Label
    |--------------------------------------------------------------------------
    | Returns a human-readable label for the drafting type.
    */
    private function getDraftingTypeLabel(string $type): string
    {
        $draftingType = DraftingType::tryFrom($type);
        return $draftingType ? $draftingType->label() : 'مستند قانوني';
    }


    /*
    |--------------------------------------------------------------------------
    | Get System Prompt
    |--------------------------------------------------------------------------
    | Generates the dynamic system prompt for the AI model.
    */
    private function getSystemPrompt(string $draftingType): string
    {
        $baseInstructions = <<<PROMPT
        أنت محامٍ سعودي محترف ومبدع، متخصص في الصياغة القانونية الدقيقة. مهمتك هي صياغة المستند المطلوب **بأفضل شكل ممكن** باستخدام المعلومات والسياق الذي يقدمه المستخدم.

        **قواعد الصياغة الأساسية:**
        1.  **افترض صلة المحتوى:** تعامل مع النص أو المستند المرفق على أنه المادة الخام الأساسية للقضية. مهمتك هي استخلاص المعلومات منه، وليس الحكم عليه.
        2.  **الالتزام بالقالب:** اتبع هيكل القالب المطلوب بدقة.
        3.  **املأ الفراغات بذكاء:** استنتج المعلومات (مثل أسماء الأطراف، أرقام القضايا، التواريخ) من السياق لملء أماكن `[مكان إدخال النص]`.
        4.  **التعامل مع المعلومات المفقودة (الأهم):** إذا لم تجد معلومة معينة بشكل صريح (مثل رقم الوكالة)، **لا تخترعها ولا تكتب "غير متوفر"**. ببساطة، **احذف السطر أو القسم المتعلق بها بالكامل** من المخرج النهائي لإنتاج مستند نظيف.
        5.  **الصياغة الإبداعية:** في الأقسام التي تتطلب كتابة حرة (مثل "الجواب التفصيلي" أو "أسباب الاعتراض")، استخدم المعلومات المقدمة لبناء حجج قانونية قوية ومنطقية بلغة رصينة.

        **القالب المطلوب للصياغة:**
        PROMPT;

        $template = $this->getTemplateForType($draftingType);

        $finalInstruction = "\n\nابدأ الآن في صياغة المستند بناءً على القالب والتعليمات أعلاه.";

        return $baseInstructions . "\n\n" . $template . $finalInstruction;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Template For Type
    |--------------------------------------------------------------------------
    | Returns the specific template for each drafting document type.
    */
    private function getTemplateForType(string $draftingType): string
    {
        switch (DraftingType::tryFrom($draftingType)) {
            case DraftingType::DefenseMemo:
                return <<<TEMPLATE
                فضيلة رئيس [مكان إدخال النص]				سلمه الله
                السلام عليكم ورحمة الله وبركاته، وبعد:

                بالإشارة إلى الدعوى رقم ([مكان إدخال النص]) وتاريخ [مكان إدخال النص]، فإن الجواب على ما أوردته المدعية بما يلي:

                **أولًا/ الجواب الإجمالي:**
                [هنا قم بصياغة رد عام وموجز ينكر ادعاءات المدعي بشكل عام]

                **ثانيًا/ الجواب التفصيلي:**
                [هنا قم بالرد على كل ادعاء من ادعاءات المدعي على حدة، وفندها باستخدام الوقائع والأدلة المقدمة من المستخدم]

                **ثالثًا/ الطلبات:**
                تأسيسًا على ما سبق؛ أطلب من فضيلتكم ما يلي:
                1. [صياغة الطلبات النهائية بناءً على الموقف، مثل رد الدعوى]

                والله يحفظكم ويرعاكم ..

                المدعى عليه (وكالة):
                المحامي [اسم المحامي]
                بموجب الوكالة رقم ([مكان إدخال النص])، وتاريخ [مكان إدخال النص]
                TEMPLATE;

            case DraftingType::ReconsiderationPetition:
                return <<<TEMPLATE
                **مذكرة التماس إعادة نظر**

                فضيلة رئيس [مكان إدخال النص]				سلمه الله
                فضيلة رئيس [مكان إدخال النص]				سلمه الله
                فضيلة رئيس [مكان إدخال النص]				سلمه الله
                السلام عليكم ورحمة الله وبركاته، وبعد:

                **الملتمس (المدعي/المدعى عليه سابقًا):** [استنتج من السياق]
                **الملتمس ضده:** [استنتج من السياق]

                **بيانات الحكم:**
                - **تاريخ الصك:** [مكان إدخال النص]
                - **رقم القضية:** [مكان إدخال النص]
                - **تاريخ القضية:** [مكان إدخال النص]
                - **رقم الصك:** [مكان إدخال النص]

                **أولًا/ منطوق الحكم:**
                (حكمت الدائرة بـ [لخص منطوق الحكم من السياق المقدم]).

                **ثانيًا/ أسباب الالتماس:**
                [حلل النص المقدم من المستخدم وحدد سبب الالتماس بناءً على الحالات النظامية (غش، تزوير، ظهور أوراق قاطعة، تناقض)، وقم بصياغة الأسباب الشكلية والموضوعية هنا بشكل قانوني دقيق]

                **ثالثًا/ الطلبات:**
                تأسيسًا على ما سبق؛ أطلب من فضيلتكم ما يلي:
                1. قبول الالتماس شكلاً وموضوعاً.
                2. نقض الحكم الملتمس فيه وإعادة نظر الدعوى.
                3. [صياغة الطلب الجديد في موضوع الدعوى]

                والله يحفظكم ويرعاكم ..

                الملتمس (وكالة):
                المحامي [اسم المحامي]
                بموجب الوكالة رقم ([مكان إدخال النص])، وتاريخ [مكان إدخال النص]
                TEMPLATE;

            case DraftingType::ReplyMemo:
                return <<<TEMPLATE
                فضيلة رئيس [مكان إدخال النص]				سلمه الله
                السلام عليكم ورحمة الله وبركاته، وبعد:

                بالإشارة إلى المذكرة الجوابية المقدمة من المدعى عليه في تاريخ [مكان إدخال النص]، بشأن الدعوى رقم ([مكان إدخال النص]) وتاريخ [مكان إدخال النص]، فإن الرد على ما ورد في المذكرة بما يلي:

                **أولًا/ الردود الإجمالية:**
                [هنا قم بصياغة رد عام يؤكد على صحة الدعوى الأصلية]

                **ثانيًا/ الردود التفصيلية:**
                [هنا قم بالرد على كل نقطة ودفع قدمه الخصم في مذكرته الجوابية، وفندها باستخدام الوقائع والأدلة المقدمة]

                **ثالثًا/ الطلبات:**
                تأسيسًا على ما سبق؛ أطلب من فضيلتكم ما يلي:
                1. [أعد تأكيد الطلبات الأصلية في صحيفة الدعوى]

                والله يحفظكم ويرعاكم ..

                المدعي (وكالة):
                المحامي [اسم المحامي]
                بموجب الوكالة رقم ([مكان إدخال النص])، وتاريخ [مكان إدخال النص]
                TEMPLATE;

            case DraftingType::LawsuitForm:
                return <<<TEMPLATE
                **صحيفة دعوى**

                فضيلة رئيس: [مكان إدخال النص، مثل: المحكمة العامة بـ...]                    سلمه الله
                السلام عليكم ورحمة الله وبركاته، وبعد:

                **المدعي:** [استنتج اسم المدعي من السياق]
                **المدعى عليه:** [استنتج اسم المدعى عليه من السياق]
                **الموضوع:** [استنتج موضوع الدعوى من السياق، مثل: مطالبة مالية، حضانة، ...]

                **أولاً/ الوقائع:**
                [هنا قم بأخذ النقاط التي قدمها المستخدم وأعد صياغتها وترتيبها بشكل زمني ومنطقي كسرد للوقائع]

                **ثانياً/ الأسانيد:**
                [بناءً على الوقائع، استنتج الأسانيد النظامية والشرعية التي تدعم موقف المدعي وقم بسردها هنا]

                **ثالثاً: الطلبات:**
                [بناءً على كل ما سبق، قم بصياغة طلبات المدعي بشكل واضح ودقيق ومرقم]

                المدعي وكالة / [اسم المحامي]
                بموجب الوكالة رقم [مكان إدخال النص]
                TEMPLATE;

            case DraftingType::ObjectionMemo:
                return <<<TEMPLATE
                فضيلة رئيس [مكان إدخال النص]				سلمه الله
                فضيلة رئيس [مكان إدخال النص]				سلمه الله
                السلام عليكم ورحمة الله وبركاته، وبعد:

                **المستأنف:** [استنتج من السياق]
                **المستأنف ضده:** [استنتج من السياق]

                **بيانات الحكم المستأنف:**
                - **تاريخ الصك:** [مكان إدخال النص]
                - **رقم القضية:** [مكان إدخال النص]
                - **تاريخ القضية:** [مكان إدخال النص]
                - **رقم الصك:** [مكان إدخال النص]

                **أولًا/ منطوق الحكم:**
                (حكمت الدائرة بـ [لخص منطوق الحكم من السياق المقدم]).

                **ثانيًا/ أسباب الاعتراض:**
                **أ- الأسباب الشكلية (إن وجدت):**
                [حلل السياق واستخرج أي دفوع شكلية للاعتراض]

                **ب- الأسباب الموضوعية:**
                [حلل السياق واستخرج أسباب الاعتراض الموضوعية، وادعمها بالأسانيد]

                **ثالثًا/ الطلبات:**
                تأسيسًا على ما سبق؛ أطلب من فضيلتكم ما يلي:
                1. قبول الاعتراض شكلاً وموضوعاً.
                2. نقض الحكم المستأنف.
                3. الحكم مجددًا بـ [صياغة الطلب الجديد]

                والله يحفظكم ويرعاكم ..

                المستأنف (وكالة):
                المحامي [اسم المحامي]
                بموجب الوكالة رقم ([مكان إدخال النص])، وتاريخ [مكان إدخال النص]
                TEMPLATE;

            default:
                return "يرجى اختيار نوع مستند صالح للصياغة.";
        }
    }
}
