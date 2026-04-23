<?php

namespace App\Services;

use App\Models\judicial_affairs\Session;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\MessageLog;
use App\Helpers\General;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class SessionReminderService
{
    /*
    |--------------------------------------------------------------------------
    | Grahp Services
    |--------------------------------------------------------------------------
    */
    protected $graphService;

    /*
    |--------------------------------------------------------------------------
    | Email Services
    |--------------------------------------------------------------------------
    */
    protected $emailService;

    /*
    |--------------------------------------------------------------------------
    | Constract
    |--------------------------------------------------------------------------
    */
    public function __construct(MicrosoftGraphBaseService $graphService, EmailService $emailService)
    {
        $this->graphService = $graphService;
        $this->emailService = $emailService;
    }


    /*
    |--------------------------------------------------------------------------
    | Send Sms Reminder
    |--------------------------------------------------------------------------
    */
    public function sendSmsReminder(Session $session): array
    {
        $users = $session->assignedEmployees;

        if ($users->isEmpty()) {
            return [
                'sent' => false,
                'recipients' => [],
                'failed' => []
            ];
        }

        $message = "تذكير: لديك {$session->session_name} وذلك في تمام الساعة {$session->session_time} بتاريخ {$session->getHijriSessionDateAttribute()}.";

        $successfulRecipients = [];
        $failedNumbers = [];

        foreach ($users as $user) {
            $employee = $user->employee;

            if ($employee && $employee->mobile) {
                $formattedNumber = ltrim($employee->mobile, '0');

                $sent = General::sendSMS($message, $formattedNumber);

                if ($sent) {
                    $successfulRecipients[] = [
                        'type' => 'employee',
                        'id' => $employee->id,
                    ];
                } else {
                    $failedNumbers[] = $formattedNumber;
                }
            } else {
                Log::warning('Employee has no valid mobile number', [
                    'employee_id' => $employee ? $employee->id : null,
                    'user_id' => $user->id
                ]);
            }
        }

        // تسجيل الرسائل الناجحة في سجل الرسائل
        if (!empty($successfulRecipients)) {
            MessageLog::create([
                'sender_id' => 1,
                'message_text' => $message,
                'platform' => 'SMS',
                'recipients' => $successfulRecipients,
            ]);
        }

        // تسجيل الأرقام التي فشل إرسال الرسائل إليها
        if (!empty($failedNumbers)) {
            Log::error('Failed to send SMS to some numbers', [
                'failed_numbers' => $failedNumbers,
                'session_id' => $session->id
            ]);
        }

        return [
            'sent' => !empty($successfulRecipients),
            'recipients' => $successfulRecipients,
            'failed' => $failedNumbers,
            'message' => $message
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Send Email Reminder
    |--------------------------------------------------------------------------
    */
    public function sendEmailReminder(Session $session): array
    {
        $settings = Settings::find(1);
        $users = $session->assignedEmployees;

        if ($users->isEmpty()) {
            return [
                'sent' => false,
                'recipients' => [],
                'failed' => []
            ];
        }

        $successfulRecipients = [];
        $failedEmails = [];

        foreach ($users as $user) {
            $employee = $user->employee;

            // استخدام حقل work_email للموظف
            if ($employee && $employee->work_email) {
                try {
                    // تحضير تاريخ ووقت الجلسة بتنسيق مناسب للعرض
                    $sessionDateTime = Carbon::parse($session->session_date . ' ' . $session->session_time)->format('Y-m-d H:i');

                    // إعداد محتوى البريد الإلكتروني باستخدام قالب
                    $body = view('emails.session-reminder', [
                        'user' => $user,
                        'settings' => $settings,
                        'taskData' => $sessionDateTime,
                        'session' => $session,
                    ])->render();

                    // إعداد موضوع البريد
                    $subject = "تذكير: {$session->session_name}";

                    // إرسال البريد الإلكتروني عبر Microsoft Graph API
                    $this->graphService->sendEmail($employee->work_email, $subject, $body);

                    $successfulRecipients[] = [
                        'type' => 'employee',
                        'id' => $employee->id,
                    ];
                } catch (\Exception $e) {
                    Log::error('Failed to send email reminder', [
                        'employee_id' => $employee->id,
                        'email' => $employee->work_email,
                        'error' => $e->getMessage(),
                        'session_id' => $session->id
                    ]);
                    $failedEmails[] = $employee->work_email;
                }
            } else {
                Log::warning('Employee has no valid work email address', [
                    'employee_id' => $employee ? $employee->id : null,
                    'user_id' => $user->id,
                    'session_id' => $session->id
                ]);
            }
        }

        // تسجيل رسائل البريد الإلكتروني الناجحة في سجل الرسائل
        // if (!empty($successfulRecipients)) {
        //     MessageLog::create([
        //         'sender_id' => 1,
        //         'message_text' => "تذكير بالبريد الإلكتروني: {$session->session_name}",
        //         'platform' => 'Email',
        //         'recipients' => $successfulRecipients,
        //     ]);
        // }

        return [
            'sent' => !empty($successfulRecipients),
            'recipients' => $successfulRecipients,
            'failed' => $failedEmails
        ];
    }

    /*
     |--------------------------------------------------------------------------
     | Send Objection Reminder
     |--------------------------------------------------------------------------
     | ترسل رسالة تذكير بآخر مهلة للاعتراض لجميع الموظفين المكلَّفين.
     */
    public function sendObjectionReminder(Session $session): array
    {
        $users = $session->assignedEmployees;

        if ($users->isEmpty()) {
            return ['sent' => false, 'recipients' => [], 'failed' => []];
        }

        // وقت انتهاء المهلة (مثلاً 23:59) يحدَّد من env
        // $deadlineTime      = env('OBJECTION_DEADLINE_TIME', '23:59');
        // التاريخ فقط بدون الوقت
        $objectionDate = Carbon::parse($session->last_objection_deadline)->format('Y-m-d');

        $subject = "تذكير: موعد آخر مهلة للاعتراض على الجلسة ({$session->session_name})";
        $body    = view('emails.objection-reminder', [
            'user'             => null,
            'settings'         => Settings::find(1),
            'objectionDate' => $objectionDate,
            'session'          => $session,
        ])->render();

        $successful = [];
        $failed     = [];

        foreach ($users as $user) {
            $employee = $user->employee;
            if ($employee && $employee->work_email) {
                try {
                    $this->graphService->sendEmail($employee->work_email, $subject, $body);
                    $successful[] = ['type' => 'employee', 'id' => $employee->id];
                } catch (\Exception $e) {
                    Log::error('Failed to send objection email reminder', [
                        'employee_id' => $employee->id,
                        'email'       => $employee->work_email,
                        'error'       => $e->getMessage(),
                        'session_id'  => $session->id
                    ]);
                    $failed[] = $employee->work_email;
                }
            } else {
                Log::warning('Employee has no valid work email for objection reminder', [
                    'session_id' => $session->id,
                    'user_id'    => $user->id
                ]);
            }
        }

        return [
            'sent'       => (bool) $successful,
            'recipients' => $successful,
            'failed'     => $failed,
        ];
    }


    /*
     |--------------------------------------------------------------------------
     | Maybe Send Objection Reminder
     |--------------------------------------------------------------------------
     |يتحقق هل تبقّى 30 دقيقة على آخر مهلة للاعتراض؛
     | إذا نعم → يرسل التذكير مرة واحدة فقط.
     */
    public function maybeSendObjectionReminder(Session $session): void
    {
        // تُرسل فقط للجلسات ذات الحكم الموضوعى ولم يُرسل لها تذكير سابق
        if (
            $session->summary_report_status !== 'حكم موضوعي' ||
            empty($session->last_objection_deadline)         ||
            !empty($session->objection_reminder_sent_at)
        ) {
            return;
        }

        $deadlineTime = env('OBJECTION_DEADLINE_TIME', '23:59');
        $deadline     = Carbon::createFromFormat(
            'Y-m-d H:i',
            $session->last_objection_deadline . ' ' . $deadlineTime
        );

        $reminderTime = $deadline->copy()->subMinutes(30);
        $now          = Carbon::now();

        // نافذة إرسال دقيقة واحدة (يمكن تعديلها إذا لزم)
        if ($now->between($reminderTime, $reminderTime->copy()->addMinute())) {
            $result = $this->sendObjectionReminder($session);

            if ($result['sent']) {
                $session->objection_reminder_sent_at = $now;
                $session->save();
            }
        }
    }



    /*
    |--------------------------------------------------------------------------
    | Send All Reminders
    |--------------------------------------------------------------------------
    */
    public function sendAllReminders(Session $session): array
    {
        $smsResults = $this->sendSmsReminder($session);

        $emailResults = $this->sendEmailReminder($session);

        $this->maybeSendObjectionReminder($session);


        if ($smsResults['sent'] || $emailResults['sent']) {
            $session->reminder_sent_at = Carbon::now();
            $session->save();
        }

        return [
            'sms' => $smsResults,
            'email' => $emailResults,
            'success' => ($smsResults['sent'] || $emailResults['sent'])
        ];
    }
}