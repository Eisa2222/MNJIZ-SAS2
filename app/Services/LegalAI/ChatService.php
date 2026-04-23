<?php

declare(strict_types=1);

namespace App\Services\LegalAI;

use App\Contracts\LegalAI\AIProvider;
use App\Data\LegalAI\ChatMessageData;
use App\Events\LegalAI\AiChatMessageSent;
use App\Helpers\SettingsHelper;
use App\Models\LegalAI\AiChat;
use App\Models\LegalAI\AiChatMessage;
use App\Services\LegalAI\Utils\FileParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatService
{
    public function __construct(
        private AIProvider $aiProvider
    ) {}

    /*
    |--------------------------------------------------------------------------
    | الطريقة التقليدية: انتظار الرد الكامل (Non-Streaming)
    |--------------------------------------------------------------------------
    |
    | هذه الدالة تنتظر حتى يكتمل رد الـ AI بالكامل، ثم تحفظه
    | وتعيد استجابة JSON. مناسبة للطلبات التي لا تدعم التدفق.
    */
    public function handleMessage(Request $request, AiChat $chat): array
    {
        $userInput = $this->prepareAndSaveUserMessage($request, $chat);

        $aiResponseText = $this->aiProvider->generateResponse([
            ['role' => 'system', 'content' => 'أنت مساعد قانوني ذكي.'],
            ['role' => 'user', 'content' => $userInput['fullPrompt']]
        ]);

        $aiMessage = $chat->messages()->create([
            'sender' => 'ai',
            'message' => $aiResponseText,
        ]);

        event(new AiChatMessageSent($aiMessage));

        return [
            'user_message' => $userInput['userMessage'],
            'ai_message'   => $aiMessage,
            'chat'         => $chat->fresh(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | الطريقة الاحترافية: بث الرد (Streaming)
    |--------------------------------------------------------------------------
    |
    | هذه الدالة تطلب من مزود الخدمة إرسال الرد كـ "تدفق".
    | تقوم بتمرير هذا التدفق مباشرة إلى المتصفح دون انتظار اكتماله.
    | **ملاحظة:** رسالة الـ AI لا يتم حفظها في قاعدة البيانات هنا،
    | بل يجب حفظها من خلال طلب AJAX منفصل من الواجهة الأمامية بعد اكتمال التدفق.
    */
    public function handleStreamedMessage(ChatMessageData $data, AiChat $chat): StreamedResponse
    {
        $userInput = $this->prepareAndSaveUserMessage($data, $chat);
        $messages = [
            ['role' => 'system', 'content' => 'أنت مساعد قانوني متخصص...'],
            ['role' => 'user', 'content' => $userInput['fullPrompt']]
        ];
        $requestData = $this->aiProvider->prepareStreamRequest($messages);

        return new StreamedResponse(function () use ($requestData) {

            set_time_limit(0);

            ignore_user_abort(true);

            $buffer = '';
            $curlHandle = curl_init($requestData['url']);

            curl_setopt_array($curlHandle, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $requestData['payload'],
                CURLOPT_HTTPHEADER => $requestData['headers'],
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buffer) {
                    echo $chunk;

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();

                    return strlen($chunk);
                },

                CURLOPT_TIMEOUT => 300,
                CURLOPT_LOW_SPEED_LIMIT => 50,
                CURLOPT_LOW_SPEED_TIME => 60,
            ]);

            curl_exec($curlHandle);

            if (curl_errno($curlHandle)) {
                Log::error('cURL Stream Error: ' . curl_error($curlHandle));
            }

            curl_close($curlHandle);
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=UTF-8',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | دالة مساعدة مشتركة (Private Helper Function)
    |--------------------------------------------------------------------------
    |
    | لتقليل تكرار الكود، هذه الدالة تعالج كل ما يتعلق برسالة المستخدم:
    | تحليل الملف، حفظ الرسالة، تحديث عنوان المحادثة، وإرجاع البيانات المحضرة.
    */
    private function prepareAndSaveUserMessage(ChatMessageData $data, AiChat $chat): array
    {
        $userMessageText = $data->message ?? '';

        // تحديث عنوان المحادثة إذا كانت هذه هي الرسالة الأولى
        if ($chat->messages()->count() === 0 && !empty(trim($userMessageText))) {
            $chat->title = Str::limit($userMessageText, 50);
            $chat->save();
        }

        // حفظ رسالة المستخدم في قاعدة البيانات
        $userMessage = $chat->messages()->create([
            'sender' => 'user',
            'message' => $userMessageText,
        ]);

        return [
            'userMessage' => $userMessage,
            'fullPrompt' => $userMessageText,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | حفظ رسالة الـ AI بعد اكتمال التدفق.
    |--------------------------------------------------------------------------
    | سيتم استدعاء هذه الدالة عبر طلب AJAX منفصل.
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
}
