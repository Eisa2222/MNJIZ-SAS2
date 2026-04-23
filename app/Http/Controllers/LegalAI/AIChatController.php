<?php

declare(strict_types=1);

namespace App\Http\Controllers\LegalAI;

use App\Enums\LegalAI\AiChatType;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAI\SendChatMessageRequest;
use App\Models\LegalAI\AiChat;
use App\Models\LegalAI\AiChatMessage;
use App\Services\LegalAI\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;



class AIChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */
    public function __construct(private ChatService $chatService) {}


    /*
    |--------------------------------------------------------------------------
    | عرض لوحة تحكم الدردشة (Dashboard)
    |--------------------------------------------------------------------------
    */
    public function dashboard(AiChat $chat = null): View|RedirectResponse
    {
        $authUser = Auth::user();
        $chatType = AiChatType::GeneralChat;

        if ($chat && ($chat->user_id !== $authUser->id || $chat->type !== $chatType)) {
            abort(403, 'لا تملك صلاحية الوصول لهذه المحادثة.');
        }

        if (!$chat) {
            $chat = $authUser->aiChats()->ofType($chatType)->latest('updated_at')->first();
        }

        if (!$chat) {
            $newChat = AiChat::create([
                'user_id' => $authUser->id,
                'type' => $chatType,
                'title' => 'محادثة جديدة',
            ]);
            return redirect()->route('legal-ai.chat.dashboard', $newChat);
        }

        $chat->touch();

        $chats = $authUser->aiChats()->ofType($chatType)->orderBy('updated_at', 'desc')->get();
        $messages = $chat->messages()->orderBy('created_at')->get();
        $activeProvider = config('legal_ai.default_provider');
        $toolName = AiChatType::Summarization->label();
        $aiAvatarPath = SettingsHelper::get('image')
            ? asset('storage/' . SettingsHelper::get('image'))
            : asset('assets/img/avatars/1.png');

        return view('legal-ai.chat.index', compact('authUser', 'chats', 'chat', 'messages', 'activeProvider', 'toolName', 'aiAvatarPath'));
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء محادثة جديدة
    |--------------------------------------------------------------------------
    */
    public function create(): JsonResponse
    {
        $newChat = AiChat::create([
            'user_id' => Auth::id(),
            'type' => AiChatType::GeneralChat,
            'title' => 'محادثة جديدة',
        ]);

        return response()->json([
            'status' => 'success',
            'chat' => [
                'id' => $newChat->id,
                'title' => $newChat->title,
                'updated_at' => $newChat->updated_at->diffForHumans(null, true, true),
                'last_message' => null
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | تحميل محادثة معينة (AJAX)
    |--------------------------------------------------------------------------
    */
    public function loadChat(AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id() || $chat->type !== AiChatType::GeneralChat) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat->touch();
        $messages = $chat->messages()->orderBy('created_at')->get();

        return response()->json([
            'status' => 'success',
            'chat' => [
                'id' => $chat->id,
                'title' => $chat->title,
            ],
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => $message->sender,
                    'message' => $message->message,
                    'file_path' => $message->file_path,
                    'created_at' => $message->created_at->toISOString(),
                ];
            })
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | إرسال رسالة (طريقة الـ Streaming)
    |--------------------------------------------------------------------------
    */
    public function sendStreamedMessage(SendChatMessageRequest $request, AiChat $chat): StreamedResponse
    {
        if ($chat->user_id !== Auth::id()) {
            abort(403);
        }

        $messageData = $request->toDto();

        // الآن سيتم تمرير الطلب مباشرة إلى الخدمة دون أي تحقق تلقائي
        return $this->chatService->handleStreamedMessage($messageData, $chat);
    }

    /*
    |--------------------------------------------------------------------------
    | حفظ رد الذكاء الاصطناعي (بعد اكتمال التدفق)
    |--------------------------------------------------------------------------
    */
    public function saveAiResponse(Request $request, AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        try {
            $result = $this->chatService->saveAiResponse($request, $chat);
            $freshChat = $chat->fresh()->load('messages');

            return response()->json([
                'status' => 'AI response saved successfully.',
                'ai_message' => $result['ai_message'],
                'chat' => $result['chat'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | حذف محادثة
    |--------------------------------------------------------------------------
    | يقوم بحذف محادثة معينة وجميع الرسائل المرتبطة بها.
    */
    public function destroy(AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat->delete();

        return response()->json(['status' => 'Chat deleted successfully.']);
    }

    /*
    |--------------------------------------------------------------------------
    | [اختياري] إرسال رسالة (طريقة الرد الكامل)
    |--------------------------------------------------------------------------
    |
    | يمكن الاحتفاظ بهذه الدالة كـ fallback أو للاستخدام في واجهات برمجة التطبيقات (APIs)
    | التي لا تدعم التدفق.
    */
    public function sendMessage(SendChatMessageRequest $request, AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->chatService->handleMessage($request, $chat);
            return response()->json([
                'status' => 'Message Sent!',
                'user_message' => $result['user_message'],
                'chat' => $result['chat'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
}
