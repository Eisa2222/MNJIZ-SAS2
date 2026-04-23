<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Employees\EmployeeSalaryHistory;
use Carbon\Carbon;

class BackfillSalaryHistory extends Command
{
    protected $signature = 'backfill:salary-history {--chunk=100}';
    protected $description = 'Fill employee_salary_histories for all existing employees';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $bar = $this->output->createProgressBar(Employees::count());
        $bar->start();

        Employees::chunk($chunkSize, function($emps) use ($bar) {
            foreach ($emps as $emp) {
                // إذا لديه سجل سابق تجاهل
                $exists = EmployeeSalaryHistory::where('employee_id', $emp->id)->exists();
                if ($exists) {
                    $bar->advance();
                    continue;
                }

                EmployeeSalaryHistory::create([
                    'employee_id'              => $emp->id,
                    'basic_salary'             => $emp->basic_salary            ?? 0,
                    'transportation_allowance' => $emp->transportation_allowance ?? 0,
                    'housing_allowance'        => $emp->housing_allowance       ?? 0,
                    'other_allowances'         => $emp->other_allowances        ?? 0,
                    'effective_from'           => Carbon::now(),  
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\n✅ Done backfilling salary history.");
    }
}
