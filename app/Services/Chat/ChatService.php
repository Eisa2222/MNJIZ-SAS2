<?php

namespace App\Services\Chat;

use App\Events\Chat\MessageSent;
use App\Models\Chat\Message;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    /*
    |--------------------------------------------------------------------------
    | Get Chat Users
    |--------------------------------------------------------------------------
    | Retrieve all users except current user with last message time.
    */
    public function getChatUsers()
    {
        $myId = Auth::id();

        $users = User::where('id', '!=', $myId)
            ->with([
                'messagesSent' => fn($query) => $query->where('receiver_id', $myId)->orderBy('created_at', 'desc'),
                'messagesReceived' => fn($query) => $query->where('sender_id', $myId)->orderBy('created_at', 'desc')
            ])
            ->get();

        return $users->map(function ($user) use ($myId) {
            $latestSent = $user->messagesSent->first();
            $latestReceived = $user->messagesReceived->first();

            $latestMessage = $this->getLatestMessage($latestSent, $latestReceived);
            $user->last_message_time = $latestMessage?->created_at;

            $user->unread_count = Message::where('sender_id', $user->id)
                ->where('receiver_id', $myId)
                ->whereNull('read_at')
                ->count();

            return $user;
        })->sortByDesc('last_message_time')->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Messages Between Users
    |--------------------------------------------------------------------------
    | Retrieve messages between current user and specified user.
    */
    public function getMessagesBetweenUsers($userId)
    {
        $myId = Auth::id();

        return Message::betweenUsers($myId, $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Send Message
    |--------------------------------------------------------------------------
    | Create and broadcast a new message.
    */
    public function sendMessage($receiverId, $messageText = null, UploadedFile $attachment = null)
    {
        if (empty($messageText) && !$attachment) {
            throw new \InvalidArgumentException('يجب إما كتابة رسالة أو إرفاق ملف');
        }

        $attachmentData = $attachment ? $this->handleAttachment($attachment) : null;

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $messageText ?: null,
            'attachment_path' => $attachmentData['path'] ?? null,
            'attachment_name' => $attachmentData['name'] ?? null,
            'attachment_size' => $attachmentData['size'] ?? null,
        ]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Total Unread Count
    |--------------------------------------------------------------------------
    */
    public function getTotalUnreadCount()
    {
        return Message::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Message As Read
    |--------------------------------------------------------------------------
    */
    public function markAsRead($messageId)
    {
        return Message::where('id', $messageId)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /*
    |--------------------------------------------------------------------------
    | Mark All Messages As Read
    |--------------------------------------------------------------------------
    | Mark all messages from specific user as read.
    */
    public function markAllAsRead($senderId)
    {
        $unreadMessages = Message::where('sender_id', $senderId)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->get();

        if ($unreadMessages->count() > 0) {
            $result = Message::where('sender_id', $senderId)
                ->where('receiver_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return $result;
        }

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ CRITICAL FIX: Mark My Sent Messages As Read
    |--------------------------------------------------------------------------
    | هذه هي الوظيفة المفقودة التي تحل المشكلة!
    */
    public function markMySentMessagesAsRead($receiverId)
    {
        $myId = Auth::id();

        // تحديث جميع رسائلي المرسلة لهذا المستخدم كمقروءة
        // لأنه فتح المحادثة (هذا يعني أنه قرأ رسائلي)
        $updatedCount = Message::where('sender_id', $myId)
            ->where('receiver_id', $receiverId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $updatedCount;
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helper Methods
    |--------------------------------------------------------------------------
    */
    private function getLatestMessage($sent, $received)
    {
        if ($sent && $received) {
            return $sent->created_at > $received->created_at ? $sent : $received;
        }
        return $sent ?: $received;
    }


    /*
    |--------------------------------------------------------------------------
    | Handles chat-related operations and business logic for the application.
    |--------------------------------------------------------------------------
    | This service provides methods to manage chat sessions, send and receive messages .
    */
    private function handleAttachment(UploadedFile $file)
    {
        $path = $file->store('chat/attachments', 'public');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize()
        ];
    }
}
