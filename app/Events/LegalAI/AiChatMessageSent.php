<?php

declare(strict_types=1);

namespace App\Events\LegalAI;

use App\Models\LegalAI\AiChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


/*
|--------------------------------------------------------------------------
| حدث يتم إطلاقه عند إنشاء رسالة جديدة في دردشة الذكاء الاصطناعي.
|--------------------------------------------------------------------------
| يتم بث هذا الحدث إلى الواجهة الأمامية عبر WebSockets.
*/

class AiChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /*
    |--------------------------------------------------------------------------
    | Constract
    |--------------------------------------------------------------------------
    */
    public function __construct(public AiChatMessage $message)
    {
        //
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على القنوات التي يجب بث الحدث إليها.
    |--------------------------------------------------------------------------
    */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ai-chat-channel.' . $this->message->ai_chat_id)
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | تحديد اسم الحدث الذي سيتم بثه إلى الواجهة الأمامية.
    |--------------------------------------------------------------------------
    */
    public function broadcastAs(): string
    {
        return 'ai-chat.message.sent';
    }


    /*
    |--------------------------------------------------------------------------
    | تحديد البيانات التي سيتم بثها مع الحدث.
    |--------------------------------------------------------------------------
    | هذه الدالة اختيارية، ولكنها أفضل ممارسة لتحديد البيانات المرسلة بدقة
    |      بدلاً من إرسال كل الخصائص العامة في الكلاس.
    */
    public function broadcastWith(): array
    {
        $this->message->load('chat.user');

        return [
            'message' => $this->message->toArray(),
        ];
    }
}
