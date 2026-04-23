<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventCreatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $eventData;
    public $userEmail;
    public $userName;

    /**
     * إنشاء مثيل جديد من الـ Mailable.
     *
     * @param array $eventData بيانات الحدث
     * @param string $userEmail بريد المستخدم
     * @return void
     */
    public function __construct(array $eventData, string $userEmail ,string $userName="")
    {
        $this->eventData = $eventData;
        $this->userEmail = $userEmail;
        $this->userName = $userName;
    }

    /**
     * بناء رسالة البريد الإلكتروني.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('تم إنشاء حدث جديد في تقويمك')
            ->view('emails.event_created_notification')
            ->with([
                'subject' => $this->eventData['subject'],
                'body' => $this->eventData['body'] ?? '',
                'startDateTime' => $this->eventData['startDateTime'],
                'endDateTime' => $this->eventData['endDateTime'],
                'timeZone' => 'Asia/Riyadh',
                'location' =>  'غير محدد',
                'userName' =>$this->userName,
            ]);
    }
}
