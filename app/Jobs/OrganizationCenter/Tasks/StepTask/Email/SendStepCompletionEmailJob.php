<?php

namespace App\Jobs\OrganizationCenter\Tasks\StepTask\Email;


use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendStepCompletionEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskData;
    protected $recipientEmail;

    /*
    |--------------------------------------------------------------------------
    | construct
    |--------------------------------------------------------------------------
    */
    public function __construct(array $taskData, string $recipientEmail)
    {
        $this->taskData         = $taskData;
        $this->recipientEmail   = $recipientEmail;
    }


    /*
    |--------------------------------------------------------------------------
    | handle
    |--------------------------------------------------------------------------
    */
    public function handle()
    {
        try {
            app(EmailService::class)->sendStepCompleteNotification(
                $this->taskData,
                $this->recipientEmail
            );
        } catch (\Exception $e) {
        }
    }
}
