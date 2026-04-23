<?php

declare(strict_types=1);

namespace App\Http\Controllers\LegalAI;

use App\Enums\LegalAI\AiChatType;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAI\SummarizeDocumentRequest;
use App\Models\LegalAI\AiChat;
use App\Services\LegalAI\SummarizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SummarizeController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */
    public function __construct(private SummarizationService $summarizationService) {}

    /*
    |--------------------------------------------------------------------------
    | عرض لوحة تحكم التلخيص (Dashboard)
    |--------------------------------------------------------------------------
    */
    public function dashboard(AiChat $chat = null): View|RedirectResponse
    {
        $authUser = Auth::user();
        $chatType = AiChatType::Summarization;

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
                'title' => 'تلخيص جديد',
            ]);
            return redirect()->route('legal-ai.summarize.dashboard', $newChat);
        }

        $chat->touch();

        $chats = $authUser->aiChats()->ofType($chatType)->orderBy('updated_at', 'desc')->get();
        $messages = $chat->messages()->orderBy('created_at')->get();
        $activeProvider = config('legal_ai.default_provider');
        $aiAvatarPath = SettingsHelper::get('image')
            ? asset('storage/' . SettingsHelper::get('image'))
            : asset('assets/img/avatars/1.png');

        return view('legal-ai.summarize.index', compact('authUser', 'chats', 'chat', 'messages', 'activeProvider', 'aiAvatarPath'));
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء محادثة تلخيص جديدة (AJAX)
    |--------------------------------------------------------------------------
    */
    public function create(): JsonResponse
    {
        $newChat = AiChat::create([
            'user_id' => Auth::id(),
            'type' => AiChatType::Summarization,
            'title' => 'تلخيص جديد',
        ]);

        return response()->json(['status' => 'success', 'chat' => $newChat]);
    }

    /*
    |--------------------------------------------------------------------------
    | تحميل محادثة معينة (AJAX)
    |--------------------------------------------------------------------------
    */
    public function loadChat(AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id() || $chat->type !== AiChatType::Summarization) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat->touch();
        $messages = $chat->messages()->orderBy('created_at')->get();

        return response()->json([
            'status' => 'success',
            'chat' => ['id' => $chat->id, 'title' => $chat->title],
            'messages' => $messages->map(fn($msg) => [
                'id' => $msg->id,
                'sender' => $msg->sender,
                'message' => $msg->message,
                'created_at' => $msg->created_at->toISOString(),
            ]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | معالجة طلب التلخيص (Streaming)
    |--------------------------------------------------------------------------
    */
    public function handleStream(SummarizeDocumentRequest $request, AiChat $chat): StreamedResponse
    {
        if ($chat->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->toDto();

        try {
            return $this->summarizationService->handleStreamedSummary($data, $chat);
        } catch (\Exception $e) {
            Log::error('Summarization Stream Prep Error: ' . $e->getMessage());
            // إرجاع خطأ يمكن للواجهة الأمامية التعامل معه
            return new StreamedResponse(function () use ($e) {
                http_response_code(500);
                echo "error: " . json_encode(['message' => 'فشل تحضير الملف: ' . $e->getMessage()]);
            }, 500, ['Content-Type' => 'text/event-stream']);
        }
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

        $result = $this->summarizationService->saveAiResponse($request, $chat);

        return response()->json([
            'status' => 'success',
            'ai_message' => $result['ai_message'],
            'chat' => $result['chat'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | حذف محادثة تلخيص
    |--------------------------------------------------------------------------
    */
    public function destroy(AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $chat->delete();
        return response()->json(['status' => 'Chat deleted successfully.']);
    }
}
