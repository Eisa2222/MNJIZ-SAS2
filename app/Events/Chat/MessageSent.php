<?php

namespace App\Events\Chat;

use App\Models\Chat\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcast Channel
    |--------------------------------------------------------------------------
    | Determine the channel for broadcasting.
    */

    public function broadcastOn()
    {
        $ids = [$this->message->sender_id, $this->message->receiver_id];
        sort($ids);
        $channelName = 'chat.' . $ids[0] . '.' . $ids[1];

        return new PrivateChannel($channelName);
    }

    /*
    |--------------------------------------------------------------------------
    | Broadcast Data
    |--------------------------------------------------------------------------
    | Data to be sent with the event.
    */

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'created_at' => $this->message->created_at->format('h:i A'),
            'has_attachment' => $this->message->has_attachment,
            'attachment_name' => $this->message->attachment_name,
            'attachment_url' => $this->message->attachment_url,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'image' => $this->message->sender->image,
            ],
        ];
    }
}
