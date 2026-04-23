<?php

namespace App\Services\HR\Payrolls\HelpServices;

use App\Enums\Hr\Advance\AdvanceStatus;
use App\Models\Hr\Advances\Advance;
use App\Models\Hr\Employees\Employees;
use Carbon\Carbon;

class AdvancesService
{
    public function getSumAdvances(int $employeeId, Carbon $date, $wps_payroll): float
    {
        $carbon = Carbon::instance($date);

        $employee = Employees::findOrFail($employeeId);

        $advances = Advance::query()
            ->approvedInMonth($employee->id, $carbon)
            ->get();

        $total = $advances->sum('amount'); //مجموع المبالغ

        $this->updateAppliedFiled($advances, $wps_payroll);

        return $total;
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function updateAppliedFiled($advance, $wps_payroll): void
    {
        foreach ($advance as $exec) {
            $exec->update([
                'applied_to_salary_date'    => now(),
                'wps_payrolls_id'           => $wps_payroll->id,
                'status'                    => AdvanceStatus::Executed,
            ]);
        }
    }
}
