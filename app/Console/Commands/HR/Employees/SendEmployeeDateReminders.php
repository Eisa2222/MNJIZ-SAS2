<?php

namespace App\Console\Commands\HR\Employees;

use Illuminate\Console\Command;
use App\Services\HR\Alerts\AlertService;

class SendEmployeeDateReminders extends Command
{
    protected $signature   = 'hr:send-expiry-reminders';
    protected $description = 'إنشاء تنبيهات عند انتهاء تواريخ الموظفين';

    public function __construct(private AlertService $alertService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('بدء فحص تواريخ الموظفين...');

        $totalCreated = $this->alertService->processExpiryReminders();

        $this->info("تم إنشاء {$totalCreated} تنبيه");

        return 0;
    }
}
