<?php

namespace App\Jobs\Mail;

use App\Models\User;
use App\Services\EmailService;
use App\Services\MicrosoftGraphBaseService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    protected $user;
    protected $data;

    public $tries = 3; // عدد محاولات إعادة التشغيل
    public $timeout = 120; // وقت الانتظار بالثواني

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    | إنشاء الوظيفة مع البيانات المطلوبة.
    */
    public function __construct($user, array $data)
    {
        $this->user = $user;
        $this->data = $data;
        $this->captureTenant();
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Job Execution
    |--------------------------------------------------------------------------
    | تنفيذ الوظيفة الرئيسية لإضافة المهام والأحداث.
    */
    public function handle(TaskService $taskService, MicrosoftGraphBaseService $graphService)
    {
        // إعداد بيانات المهمة
        $taskData = $this->prepareTaskData();

        // إرسال إشعار إلى المستخدم عبر البريد الإلكتروني
        $this->sendNotificationToUserByEmail($this->user['email'], $taskData);
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Task Data
    |--------------------------------------------------------------------------
    | تجهيز بيانات المهمة المطلوبة.
    */
    private function prepareTaskData()
    {
        return [
            'user_name' => $this->data['user_name'],
            'title' => $this->data['title'],
            'description' => $this->data['description'],
            'office_name' => $this->data['office_name'],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Send Notification to User
    |--------------------------------------------------------------------------
    | إرسال إشعار للمستخدم بالبريد الإلكتروني عند إضافة المهمة والحدث.
    */
    private function sendNotificationToUserByEmail($email, $taskData)
    {
        try {
            $user = User::where('email', $email)->first();
            if ($user) {


                $emailService = app(EmailService::class);
                // استدعاء الدالة مع جميع المعطيات المطلوبة
                $emailService->sendEmailAlert($taskData, $email);
            } else {
                Log::warning("لم يتم العثور على المستخدم بالبريد الإلكتروني: {$email} للإشعار.");
            }
        } catch (\Exception $e) {
            Log::error("فشل إرسال الإشعار للمستخدم {$email}: " . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Job Failure
    |--------------------------------------------------------------------------
    | التعامل مع الأخطاء عند فشل الوظيفة.
    */
    public function failed(\Throwable $exception)
    {
        Log::error('فشل تنفيذ الوظيفة SyncTaskWithMicrosoftJob: ' . $exception->getMessage());
    }
}
