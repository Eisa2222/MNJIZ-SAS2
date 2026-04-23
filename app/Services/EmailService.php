<?php

namespace App\Services;

use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $graphService;

    public function __construct(MicrosoftGraphBaseService $graphService)
    {
        $this->graphService = $graphService;
    }

    //  ارسال اشعار بعد اضافة مهمة جديدة
    public function sendTaskAlert(array $eventData, string $userEmail, string $userName, $office_name)
    {

        $body = view('emails.task.send_task_alert', [
            'subject'       => $eventData['task_name'],
            'body'          => $eventData['body'] ?? '',
            'task_priority' => $eventData['task_priority'],
            'addDateTime'   => now(),
            'endDateTime'   => $eventData['endDateTime'] ?? null,
            'timeZone'      => 'Asia/Riyadh',
            'location'      => 'غير محدد',
            'userName'      => $userName,
            'office_name'   => $office_name,
            'task_id'       =>  $eventData['task_id'],
            'showEndDate'   =>  $eventData['showEndDate'],
            'note'          =>  $eventData['note'] ?? '',
        ])->render();

        return $this->graphService->sendEmail($userEmail, $eventData['task_name'], $body);
    }

    // لارسال اشعار عبر البريد
    public function sendEmailAlert(array $emailData, string $userEmail)
    {
        // log::info("sendEmailAlert");

        $body = view('emails.mail.email_alert', [
            'user_name'     => $emailData['user_name'],
            'title'         => $emailData['title'],
            'description'   => $emailData['description'],
            'office_name'   => $emailData['office_name'],
            'date'          => now(),
        ])->render();

        // إرسال البريد الإلكتروني عبر Microsoft Graph API
        return $this->graphService->sendEmail($userEmail, $emailData['title'], $body);
    }


    /*
    |--------------------------------------------------------------------------
    | لارسال اشعار عبر البريد لمنشي المهمة
    |--------------------------------------------------------------------------
    | رسالة توضح :
    |   1- اكمال المهمة
    */
    public function sendTaskCompleteNotification(array $emailData, string $userEmail)
    {
        // log::info("sendEmailAlert");

        $body = view('emails.task.task_complete', [
            'title'                 => $emailData['title'],
            'task_url'              => $emailData['task_url'],
            'user_name'             => $emailData['user_name'],
            'task_name'             => $emailData['task_name'],
            'completion_date'       => $emailData['completion_date'],
            'duration'              => $emailData['duration'],
            'office_name'           => $emailData['office_name'],
        ])->render();

        // إرسال البريد الإلكتروني عبر Microsoft Graph API
        return $this->graphService->sendEmail($userEmail, $emailData['title'], $body);
    }

    // اشعار ارجاع المهمة
    public function sendTaskReturnNotification(array $emailData, string $userEmail)
    {
        // log::info("sendTaskReturnNotification");

        $body = view('emails.task.task_return', [
            'title'                 => $emailData['title'],
            'task_url'              => $emailData['task_url'],
            'user_name'             => $emailData['user_name'],
            'task_name'             => $emailData['task_name'],
            'createdAt'             => $emailData['createdAt'],
            'reason'                => $emailData['reason'],
            'office_name'           => $emailData['office_name'],
        ])->render();

        // إرسال البريد الإلكتروني عبر Microsoft Graph API
        return $this->graphService->sendEmail($userEmail, $emailData['title'], $body);
    }


    /*
    |--------------------------------------------------------------------------
    | لارسال اشعار عبر البريد لمنشي المهمة
    |--------------------------------------------------------------------------
    | رسالة توضح :
    |   1- اكمال الخطوة
    */
    public function sendStepCompleteNotification(array $emailData, string $userEmail)
    {

        $body = view('emails.task.step_complete', [
            'title'                 => $emailData['title'],
            'task_url'              => $emailData['task_url'],
            'user_name'             => $emailData['user_name'],
            'task_name'             => $emailData['task_name'],
            'step_name'             => $emailData['step_name'],
            'completion_date'       => $emailData['completion_date'],
            'duration'              => $emailData['duration'],
            'office_name'           => $emailData['office_name'],
            'reject_reason'         => $emailData['reject_reason'] ?? '',
        ])->render();

        // إرسال البريد الإلكتروني عبر Microsoft Graph API
        return $this->graphService->sendEmail($userEmail, $emailData['title'], $body);
    }



    public function sendRemiderCreatedNotification(Task $task, User $user, $email, $message = null)
    {
        try {
            // تجنب استخدام TaskReminderMail تماماً واستخدام القالب مباشرة
            $body = view('emails.task_reminder', [
                'task' => $task,
                'user' => $user,
            ])->render();

            // إرسال البريد مباشرة باستخدام graphService
            return $this->graphService->sendEmail(
                $email,
                $message ?? "تذكير بمهمة: {$task->task_name}",
                $body
            );
        } catch (\Exception $e) {
            Log::error("Failed to send reminder email: " . $e->getMessage());
            throw $e;
        }
    }


    // لاستعادة كلمة المرور
    public function sendPasswordResetNotification(User $user, $token)
    {
        // Log::info("Starting password reset notification");

        // الحصول على البريد الإلكتروني للمستخدم
        $userEmail = $user->email;

        // توليد رابط إعادة تعيين كلمة المرور
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $userEmail,
        ], false));

        // بناء محتوى البريد الإلكتروني باستخدام قالب Blade
        $body = view('emails.password-reset', [
            'resetUrl' => $resetUrl,
            'appName' => config('app.name'),
        ])->render();

        // إعداد موضوع البريد
        $subject = "إعادة تعيين كلمة المرور";

        // إرسال البريد الإلكتروني عبر Microsoft Graph API
        try {
            $this->graphService->sendEmail($userEmail, $subject, $body);
            // Log::info("Password reset email sent successfully to {$userEmail}");
        } catch (\Exception $e) {
            // Log::error("Failed to send password reset email to {$userEmail}: " . $e->getMessage());
        }
    }


    public function sendPasswordResetFirstNotification(User $user, $token, $settings)
    {
        // Log::info("Starting password reset First notification");


        // الحصول على البريد الإلكتروني للمستخدم
        $userEmail = $user->email;

        // توليد رابط إعادة تعيين كلمة المرور
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $userEmail,
        ], false));



        // بناء محتوى البريد الإلكتروني باستخدام قالب Blade
        $body = view('emails.password_reset_first', [
            'user' => $user,
            'settings' => $settings,
            'resetUrl' => $resetUrl,
            'appName' => config('app.name'),
        ])->render();

        // إعداد موضوع البريد
        $subject = " تعيين كلمة المرور";

        // إرسال البريد الإلكتروني عبر Microsoft Graph API
        try {
            $this->graphService->sendEmail($userEmail, $subject, $body);
            // Log::info("Password reset email sent successfully to {$userEmail}");
        } catch (\Exception $e) {
            // Log::error("Failed to send password reset email to {$userEmail}: " . $e->getMessage());
        }
    }


    public function sendSessionCreatedNotification($user, $settings, $taskData, $session)
    {


        // الحصول على البريد الإلكتروني للمستخدم
        $userEmail = $user->email;
        Log::info($userEmail);

        // بناء محتوى البريد الإلكتروني باستخدام قالب Blade
        $body = view('emails.session_created', [
            'user' => $user,
            'settings' => $settings,
            'session' => $session,
            'taskData' => $taskData,
        ])->render();



        // إعداد موضوع البريد
        $subject = 'تم تعينك في جلسة بتاريخ ' . $taskData;

        // إرسال البريد الإلكتروني عبر Microsoft Graph API
        try {
            $this->graphService->sendEmail($userEmail, $subject, $body);
        } catch (\Exception $e) {
            // Log::error("Failed to send password reset email to {$userEmail}: " . $e->getMessage());
        }
    }



    /*
    |--------------------------------------------------------------------------
    | send email to user after ticket created
    |--------------------------------------------------------------------------
    */
    public function sendNotificationToUserByEmail(array $emailData, string $userEmail)
    {
        Log::info("sendNotificationToUserByEmail");

        $body = view('emails.mail.ticket_user_notification', [
            'office_name'     => $emailData['office_name'],
            'user_name'     => $emailData['user_name'],
            'subject'       => 'الدعم الفني',
        ])->render();

        $attachments = []; // for the attachments
        if (isset($emailData['pdf_attachment']) && $emailData['pdf_attachment']) {
            $localFilePath = public_path(str_replace(url('/'), '', $emailData['pdf_attachment']));
            if (file_exists($localFilePath)) {
                $fileContent = file_get_contents($localFilePath);
                $attachments[] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => basename($localFilePath),
                    'contentBytes' => base64_encode($fileContent),
                ];
            } else {
                Log::warning("الملف غير موجود: {$localFilePath}");
            }
        }
        // تأكد من أن الدالة sendEmail في graphService تقبل معلمة للمرفقات (مثلاً كـ array)
        return $this->graphService->sendEmail($userEmail, 'الدعم الفني', $body, $attachments);
    }


    /*
    |--------------------------------------------------------------------------
    | send email to tichnical support
    |--------------------------------------------------------------------------
    */
    public function sendTechnicalSupportToEmail(array $emailData, string $supportEmail)
    {
        Log::info("sendTechnicalSupportToEmail");

        $body = view('emails.mail.ticket_technical_notification', [
            'office_name'     => $emailData['office_name'],
            'user_name'     => $emailData['user_name'],
            'subject'       => 'الدعم الفني',
        ])->render();

        $attachments = []; // for the attachments
        if (isset($emailData['pdf_attachment']) && $emailData['pdf_attachment']) {
            $localFilePath = public_path(str_replace(url('/'), '', $emailData['pdf_attachment']));
            if (file_exists($localFilePath)) {
                $fileContent = file_get_contents($localFilePath);
                $attachments[] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => basename($localFilePath),
                    'contentBytes' => base64_encode($fileContent),
                ];
            } else {
                Log::warning("الملف غير موجود: {$localFilePath}");
            }
        }

        return $this->graphService->sendEmail($supportEmail, 'الدعم الفني', $body, $attachments);
    }


    /*
    |--------------------------------------------------------------------------
    | send email to user and technical support after ticket reply
    |--------------------------------------------------------------------------
    */
    public function sendTechnicalReplyToEmail(array $emailData, string $supportEmail)
    {
        Log::info("sendTechnicalReplyToEmail");

        $body = view('emails.mail.ticket_reply_technical_notification', [
            'office_name'     => $emailData['office_name'],
            'user_name'     => $emailData['user_name'],
            'subject'       => 'الدعم الفني',
        ])->render();

        $attachments = []; // for the attachments
        if (isset($emailData['pdf_attachment']) && $emailData['pdf_attachment']) {
            $localFilePath = public_path(str_replace(url('/'), '', $emailData['pdf_attachment']));
            if (file_exists($localFilePath)) {
                $fileContent = file_get_contents($localFilePath);
                $attachments[] = [
                    '@odata.type' => '#microsoft.graph.fileAttachment',
                    'name' => basename($localFilePath),
                    'contentBytes' => base64_encode($fileContent),
                ];
            } else {
                Log::warning("الملف غير موجود: {$localFilePath}");
            }
        }

        return $this->graphService->sendEmail($supportEmail, 'الدعم الفني', $body, $attachments);
    }
}