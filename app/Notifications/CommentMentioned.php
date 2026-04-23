<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // تم التغيير هنا
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CommentMentioned extends Notification implements ShouldBroadcast // تم التغيير هنا
{
    use Queueable;

    private $comment;
    private $mentioner;
    private $userId;
    private $session;


    public function __construct($comment, $mentioner, $userId)
    {
        $this->comment      = $comment;
        $this->mentioner    = $mentioner;
        $this->userId       = $userId;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }


    public function toArray($notifiable)
    {
        return [
            'comment_id'        => $this->comment->id,
            'session_id'        => $this->comment->session_id,
            'mentioner_id'      => $this->mentioner->id,
            'message'           => "{$this->mentioner->name} قام بذكرك في تعليق على {$this->comment->session->session_name}.",
            'url' => route('legal-affairs.sessions.show', $this->comment->session_id),

        ];
    }

    /**
     * تحويل الإشعار إلى رسالة بث.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->comment->content,
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * تحديد قناة البث.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->userId);
    }
}
