<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    /**
     * رمز إعادة تعيين كلمة المرور.
     *
     * @var string
     */
    public $token;

    /**
     * إنشاء إشعار جديد.
     *
     * @param string $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * تحديد قنوات الإشعار.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * بناء رسالة البريد الإلكتروني للإشعار باستخدام قالب مخصص.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // توليد رابط إعادة تعيين كلمة المرور
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // استخدام قالب Blade مخصص للبريد الإلكتروني
        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور') // عنوان البريد الإلكتروني
            ->view('emails.password-reset', [    // اسم قالب Blade المخصص
                'resetUrl' => $resetUrl,
                'appName' => config('app.name'),
            ]);
            
    }
}
