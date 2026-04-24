<?php

namespace App\Console\Commands\HR\WPS;

use App\Console\Concerns\IteratesTenants;
use App\Helpers\SettingsHelper;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Models\Tenant;
use App\Services\HR\Payrolls\PayrollCalculatorService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GeneratePayrollCommand extends Command
{
    use IteratesTenants;

    protected $signature = 'payroll:generate-wps';
    protected $description = 'Run the WPS monthly payroll for every active tenant.';

    public function __construct(protected PayrollCalculatorService $calculator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        return $this->perTenant(function (Tenant $tenant) {
            $today = Carbon::now();
            $year  = $today->year;
            $month = $today->month;

            $disbursementDay = (int) SettingsHelper::get('payroll_disbursement_day');

            if (! $disbursementDay) {
                $this->warn("  tenant={$tenant->slug}: no disbursement day configured, skipped.");
                return;
            }

            if (WpsPayroll::processedFor($year, $month)->exists()) {
                $this->line("  tenant={$tenant->slug}: already processed for {$year}-{$month}");
                return;
            }

            DB::transaction(function () use ($year, $month) {
                $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                $this->calculator->processWpsPayroll($endOfMonth);
            });

            $this->info("  tenant={$tenant->slug}: WPS payroll generated for {$year}-{$month}.");
        });
    }
}
