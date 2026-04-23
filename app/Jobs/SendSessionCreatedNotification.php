<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSessionCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignedEmployees;
    protected $taskData;
    protected $session;

    /**
     * Create a new job instance.
     *
     * @param array $assignedEmployees
     * @param array $taskData
     * @param \App\Models\Session $session
     */
    public function __construct(array $assignedEmployees, $taskData, $session)
    {
        $this->assignedEmployees = $assignedEmployees;
        $this->taskData = $taskData;
        $this->session = $session;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch settings once to avoid multiple queries
        $settings = Settings::find(1);

        // Check if main_email is set
        if (empty($settings->main_email)) {
            // Log the error and optionally notify an administrator
            \Log::error('Main email is not set in settings.');
            return;
        }

        $emailService = app(EmailService::class);

        foreach ($this->assignedEmployees as $employeeId) {
            $user = User::find($employeeId);
            if ($user) {
                // Send notification via EmailService
                $emailService->sendSessionCreatedNotification($user, $settings, $this->taskData, $this->session);
            } else {
                \Log::warning("User with ID {$employeeId} not found.");
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        // Log the failure
        \Log::error('NotifyAssignedEmployees Job failed: ' . $exception->getMessage());
    }
}
