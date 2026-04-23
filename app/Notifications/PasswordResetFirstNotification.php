<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetFirstNotification extends Notification
{
    use Queueable;

    protected $resetUrl;
    protected $settings;
    protected $user;

    /**
     * إنشاء Notification جديدة.
     *
     * @param string $resetUrl
     * @param \App\Models\GeneralSetting\SystemSetting\Settings $settings
     * @param \App\Models\User $user
     */
    public function __construct($resetUrl, $settings, $user)
    {
        $this->resetUrl = $resetUrl;
        $this->settings = $settings;
        $this->user = $user;
    }

    /**
     * تحديد قنوات الإرسال.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * تمثيل البريد الإلكتروني للـ Notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('تعيين كلمة المرور الخاصة بك')
            ->view('emails.password_reset_first', [
                'resetUrl' => $this->resetUrl,
                'settings' => $this->settings,
                'user' => $this->user,
            ]);
    }

    /**
     * تمثيل المصفوفة للـ Notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
