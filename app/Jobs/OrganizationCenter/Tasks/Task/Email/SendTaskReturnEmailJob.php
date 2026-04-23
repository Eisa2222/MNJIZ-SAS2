<?php

namespace App\Jobs\OrganizationCenter\Tasks\Task\Email;


use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskReturnEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskData;
    protected $recipientEmail;


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
            app(EmailService::class)->sendTaskReturnNotification(
                $this->taskData,
                $this->recipientEmail
            );
        } catch (\Exception $e) {
        }
    }
}
