<?php

namespace App\Jobs\Mail;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTechnicalSupportEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $data;
    protected $type; // 'user' أو 'technical'

    public $tries = 3;
    public $timeout = 120;

    /*
    |--------------------------------------------------------------------------
    | constructor
    |--------------------------------------------------------------------------
    */
    public function __construct($user, array $data, $type = 'user')
    {
        $this->user = $user;
        $this->data = $data;
        $this->type = $type;
    }


    /*
    |--------------------------------------------------------------------------
    | handle method
    |--------------------------------------------------------------------------
    */
    public function handle()
    {
        $supportData = $this->prepareSupportData();
        $email = is_array($this->user) ? $this->user['email'] : $this->user->email;

        if ($this->type == 'user') {
            $this->sendNotificationToUserByEmail($email, $supportData);
        } elseif ($this->type == 'technical') {
            $this->sendTechnicalSupportToEmail($email, $supportData);
        } elseif($this->type == 'reply') {
            $this->sendTechnicalReplyToEmail($email, $supportData);
        }
    }



    /*
    |--------------------------------------------------------------------------
    | prepare support data
    |--------------------------------------------------------------------------
    */
    private function prepareSupportData()
    {
        return [
            'user_name'             => $this->data['user_name'],
            'subject'               => $this->data['subject'] ?? 'طلب دعم فني',
            'pdf_attachment'        => $this->data['pdf_attachment'] ?? null,
            'office_name'        => $this->data['office_name'] ?? null,
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | send email to user
    |--------------------------------------------------------------------------
    */
    private function sendNotificationToUserByEmail($email, $supportData)
    {
        try {
            $user = User::where('email', $email)->first();
            if ($user) {
                $emailService = app(EmailService::class);
                $emailService->sendNotificationToUserByEmail($supportData, $email);
            } else {
                Log::warning("لم يتم العثور على المستخدم بالبريد ....: {$email}.");
            }
        } catch (\Exception $e) {
            Log::error("فشل إرسال إشعار البريد للمستخدم {$email}: " . $e->getMessage());
        }
    }



    /*
    |--------------------------------------------------------------------------
    | send email to technical support
    |--------------------------------------------------------------------------
    */
    private function sendTechnicalSupportToEmail($email, $supportData)
    {
        try {
            $emailService = app(EmailService::class);
            $emailService->sendTechnicalSupportToEmail($supportData, $email);
        } catch (\Exception $e) {
            Log::error("فشل إرسال إشعار الدعم الفني للمستخدم {$email}: " . $e->getMessage());
        }
    }



    /*
    |--------------------------------------------------------------------------
    | send reply email to user and technical support
    |--------------------------------------------------------------------------
    */
    private function sendTechnicalReplyToEmail($email, $supportData)
    {
        try {
            $emailService = app(EmailService::class);
            $emailService->sendTechnicalReplyToEmail($supportData, $email);
        } catch (\Exception $e) {
            Log::error("فشل إرسال إشعار الدعم الفني للمستخدم {$email}: " . $e->getMessage());
        }
    }


    /*
    |--------------------------------------------------------------------------
    | in failure
    |--------------------------------------------------------------------------
    */
    public function failed(\Throwable $exception)
    {
        // Log::error('f' . $exception->getMessage());
        Log::error('failed to send email notification in SendTechnicalSupportEmailNotificationJob: ' . $exception->getMessage());
    }
}
