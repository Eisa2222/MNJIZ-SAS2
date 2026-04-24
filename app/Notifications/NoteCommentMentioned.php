<?php

namespace App\Notifications;

use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoteCommentMentioned extends Notification
{
    use Queueable, TenantAwareJob;

    private $reply;
    private $mentioner;
    private $userId;
    private $note;
    /**
     * Create a new notification instance.
     */
    public function __construct($reply, $mentioner, $userId,$note)
    {
        $this->reply = $reply;
        $this->mentioner = $mentioner;
        $this->userId = $userId;
        $this->note = $note;
        $this->captureTenant();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'reply_id' => $this->reply->id,
            'note_id' => $this->note->id,
            'mentioner_id' => $this->mentioner->id,
            'message' => "{$this->mentioner->name} قام بذكرك في تعليق على {$this->note->lawsuit->name}.",
            'url' => route('show.note.notification', [$this->note->lawsuit->id,$this->note->id,$this->reply->id]), // تأكد من وجود هذا الـ Route

        ];
    }

    // public function toBroadcast($notifiable)
    // {
    //     return new BroadcastMessage([
    //         'message' => $this->comment->content,
    //         'created_at' => now()->toDateTimeString(),
    //     ]);
    // }

    // /**
    //  * تحديد قناة البث.
    //  *
    //  * @return \Illuminate\Broadcasting\PrivateChannel
    //  */
    // public function broadcastOn()
    // {
    //     return new PrivateChannel('App.Models.User.' . $this->userId);
    // }

}
