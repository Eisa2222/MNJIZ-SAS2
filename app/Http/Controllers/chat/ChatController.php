<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /*
    |--------------------------------------------------------------------------
    | Chat Index
    |--------------------------------------------------------------------------
    | Display main chat interface with users list.
    */
    public function index(Request $request)
    {
        $users = $this->chatService->getChatUsers();

        $selectedUserId = $request->get('user_id');

        return view('chat.index', compact('users', 'selectedUserId'));
    }

    /*
    |--------------------------------------------------------------------------
    | Get Messages
    |--------------------------------------------------------------------------
    | ✅ SOLUTION: Get messages first, render view, then mark as read
    */
    public function getMessages($userId)
    {
        $messages = $this->chatService->getMessagesBetweenUsers($userId);
        $html = view('chat.partials.messages', compact('messages'))->render();
        $this->chatService->markAllAsRead($userId);
        return $html;
    }

    /*
    |--------------------------------------------------------------------------
    | Send Message
    |--------------------------------------------------------------------------
    | Send new message with optional attachment.
    */
    public function sendMessage(SendMessageRequest $request)
    {
        $message = $this->chatService->sendMessage(
            $request->receiver_id,
            $request->message,
            $request->file('attachment')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Mark As Read
    |--------------------------------------------------------------------------
    | Mark specific message as read.
    */
    public function markAsRead($messageId)
    {
        $updated = $this->chatService->markAsRead($messageId);

        return response()->json([
            'status' => $updated ? 'success' : 'error',
            'message' => $updated ? 'Message marked as read' : 'Message not found or already read'
        ]);
    }
}
