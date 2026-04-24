<?php

namespace App\Console\Commands\HR\Employees;

use App\Console\Concerns\IteratesTenants;
use App\Models\Tenant;
use App\Services\HR\Alerts\AlertService;
use Illuminate\Console\Command;

class SendEmployeeDateReminders extends Command
{
    use IteratesTenants;

    protected $signature   = 'hr:send-expiry-reminders';
    protected $description = 'Emit employee-expiry alerts (contract, iqama, license, …) for every active tenant.';

    public function __construct(private AlertService $alertService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        return $this->perTenant(function (Tenant $tenant) {
            $this->info("  tenant={$tenant->slug}: scanning employee expiry dates...");
            $total = $this->alertService->processExpiryReminders();
            $this->info("  tenant={$tenant->slug}: {$total} alert(s) created.");
        });
    }
}
