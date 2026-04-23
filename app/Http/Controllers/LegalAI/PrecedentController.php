<?php

declare(strict_types=1);

namespace App\Http\Controllers\LegalAI;

use App\Enums\LegalAI\AiChatType;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAI\PrecedentRequest;
use App\Models\LegalAI\AiChat;
use App\Services\LegalAI\PrecedentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrecedentController extends Controller
{
    public function __construct(private PrecedentService $precedentService) {}

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    public function dashboard(AiChat $chat = null): View|RedirectResponse
    {
        $authUser = Auth::user();
        $chatType = AiChatType::Precedents;

        if ($chat && ($chat->user_id !== $authUser->id || $chat->type !== $chatType)) {
            abort(403);
        }

        if (!$chat) {
            $chat = $authUser->aiChats()->ofType($chatType)->latest('updated_at')->first();
        }

        if (!$chat) {
            $newChat = AiChat::create(['user_id' => $authUser->id, 'type' => $chatType, 'title' => 'بحث جديد']);
            return redirect()->route('legal-ai.precedents.dashboard', $newChat);
        }

        $chat->touch();
        $chats = $authUser->aiChats()->ofType($chatType)->orderBy('updated_at', 'desc')->get();
        $messages = $chat->messages()->orderBy('created_at')->get();
        $activeProvider = config('legal_ai.default_provider');
        $aiAvatarPath = SettingsHelper::get('ai_avatar') ? asset('storage/' . SettingsHelper::get('ai_avatar')) : asset('assets/img/branding/Alburhan-Logo.png');
        $toolConfig = ['name' => $chatType->label(), 'icon' => $chatType->icon()];

        return view('legal-ai.precedents.index', compact('authUser', 'chats', 'chat', 'messages', 'activeProvider', 'aiAvatarPath', 'toolConfig'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */
    public function create(): JsonResponse
    {
        $newChat = AiChat::create(['user_id' => Auth::id(), 'type' => AiChatType::Precedents, 'title' => 'بحث جديد']);
        return response()->json(['status' => 'success', 'chat' => $newChat]);
    }

    /*
    |--------------------------------------------------------------------------
    | Load Chat
    |--------------------------------------------------------------------------
    */
    public function loadChat(AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id() || $chat->type !== AiChatType::Precedents) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $chat->touch();
        $messages = $chat->messages()->orderBy('created_at')->get();
        return response()->json([
            'status' => 'success',
            'chat' => ['id' => $chat->id, 'title' => $chat->title],
            'messages' => $messages->map(fn($msg) => ['id' => $msg->id, 'sender' => $msg->sender, 'message' => $msg->message, 'created_at' => $msg->created_at->toISOString()]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HANdle Stream
    |--------------------------------------------------------------------------
    */
    public function handleStream(PrecedentRequest $request, AiChat $chat): StreamedResponse
    {
        if ($chat->user_id !== Auth::id()) abort(403);
        $data = $request->toDto();
        try {
            return $this->precedentService->handleStreamedSearch($data, $chat);
        } catch (\Exception $e) {
            Log::error('Precedent Search Stream Prep Error: ' . $e->getMessage());
            return new StreamedResponse(fn() => http_response_code(500), 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save Ai Response
    |--------------------------------------------------------------------------
    */
    public function saveAiResponse(Request $request, AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->precedentService->saveAiResponse($request, $chat);

        return response()->json([
            'status' => 'success',
            'ai_message' => $result['ai_message'],
            'chat' => $result['chat'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
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
