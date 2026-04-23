<?php

declare(strict_types=1);

namespace App\Http\Controllers\LegalAI;

use App\Enums\LegalAI\AiChatType;
use App\Enums\LegalAI\DraftingType;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LegalAI\DraftingRequest;
use App\Models\LegalAI\AiChat;
use App\Services\LegalAI\DraftingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DraftingController extends Controller
{
    public function __construct(private DraftingService $draftingService) {}

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    | Displays the main drafting tool interface.
    */
    public function dashboard(AiChat $chat = null): View|RedirectResponse
    {
        $authUser = Auth::user();
        $chatType = AiChatType::Drafting;

        if ($chat && ($chat->user_id !== $authUser->id || $chat->type !== $chatType)) {
            abort(403);
        }

        if (!$chat) {
            $chat = $authUser->aiChats()->ofType($chatType)->latest('updated_at')->first();
        }

        if (!$chat) {
            $newChat = AiChat::create(['user_id' => $authUser->id, 'type' => $chatType, 'title' => 'صياغة جديدة']);
            return redirect()->route('legal-ai.drafting.dashboard', $newChat);
        }

        $chat->touch();

        $chats = $authUser->aiChats()->ofType($chatType)->orderBy('updated_at', 'desc')->get();
        $messages = $chat->messages()->orderBy('created_at')->get();
        $activeProvider = config('legal_ai.default_provider');

        $aiAvatarPath = SettingsHelper::get('ai_avatar') ? asset('storage/' . SettingsHelper::get('ai_avatar')) : asset('assets/img/branding/Alburhan-Logo.png');
        $toolConfig = [
            'name' => AiChatType::Drafting->label(),
            'icon' => AiChatType::Drafting->icon(),
        ];

        $draftingTypes = DraftingType::all();

        return view('legal-ai.drafting.index', compact('authUser', 'chats', 'chat', 'messages', 'activeProvider', 'aiAvatarPath', 'toolConfig', 'draftingTypes'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Chat
    |--------------------------------------------------------------------------
    | Creates a new drafting session via AJAX.
    */
    public function create(): JsonResponse
    {
        $newChat = AiChat::create(['user_id' => Auth::id(), 'type' => AiChatType::Drafting, 'title' => 'صياغة جديدة']);
        return response()->json(['status' => 'success', 'chat' => $newChat]);
    }

    /*
    |--------------------------------------------------------------------------
    | Load Chat
    |--------------------------------------------------------------------------
    | Loads a specific drafting session's data via AJAX.
    */
    public function loadChat(AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id() || $chat->type !== AiChatType::Drafting) {
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
    | Handle Stream
    |--------------------------------------------------------------------------
    | Handles the main drafting request and streams the response.
    */
    public function handleStream(DraftingRequest $request, AiChat $chat): StreamedResponse
    {
        if ($chat->user_id !== Auth::id()) abort(403);

        $data = $request->toDto();

        try {
            return $this->draftingService->handleStreamedDraft($data, $chat);
        } catch (\Exception $e) {
            Log::error('Drafting Stream Prep Error: ' . $e->getMessage());
            return new StreamedResponse(function () use ($e) {
                http_response_code(500);
                echo "error: " . json_encode(['message' => 'فشل تحضير الطلب: ' . $e->getMessage()]);
            }, 500, ['Content-Type' => 'text/event-stream']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save AI Response
    |--------------------------------------------------------------------------
    | Saves the final AI-generated draft after streaming is complete.
    */
    public function saveAiResponse(Request $request, AiChat $chat): JsonResponse
    {
        if ($chat->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->draftingService->saveAiResponse($request, $chat);

        return response()->json([
            'status' => 'success',
            'ai_message' => $result['ai_message'],
            'chat' => $result['chat'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy Chat
    |--------------------------------------------------------------------------
    | Deletes a drafting session.
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
