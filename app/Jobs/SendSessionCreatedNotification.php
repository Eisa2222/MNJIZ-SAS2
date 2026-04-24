<?php

namespace App\Jobs;

use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use App\Services\EmailService;
use App\Tenancy\Concerns\TenantAwareJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a new court session is created. Runs on the queue, so
 * TenantAwareJob captures the dispatcher's tenant at construction time and
 * RestoreTenantContext middleware re-enters that tenant before handle()
 * runs — otherwise User::find() would query the global users pool and
 * potentially notify another firm's staff.
 */
class SendSessionCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TenantAwareJob;

    protected $assignedEmployees;
    protected $taskData;
    protected $session;

    public function __construct(array $assignedEmployees, $taskData, $session)
    {
        $this->assignedEmployees = $assignedEmployees;
        $this->taskData          = $taskData;
        $this->session           = $session;

        $this->captureTenant();
    }

    public function handle(): void
    {
        $settings = Settings::current();

        if (empty($settings?->main_email)) {
            \Log::error('Main email is not set in settings.');
            return;
        }

        $emailService = app(EmailService::class);

        foreach ($this->assignedEmployees as $employeeId) {
            // User is tenant-scoped via global scope; find() here respects
            // the restored tenant context from the middleware.
            $user = User::find($employeeId);

            if ($user) {
                $emailService->sendSessionCreatedNotification(
                    $user,
                    $settings,
                    $this->taskData,
                    $this->session
                );
            } else {
                \Log::warning("User with ID {$employeeId} not found in tenant context.");
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('SendSessionCreatedNotification failed: ' . $exception->getMessage());
    }
}
