<?php

namespace App\Services\HR\Payrolls\HelpServices;

use App\Enums\Hr\Deduction\DeductionStatus;
use App\Models\Hr\Deductions\Deduction;
use App\Models\Hr\Employees\Employees;
use Carbon\Carbon;

class DeductionsService
{

    public function getSumDeductions(int $employeeId, Carbon $date, $wps_payroll): float
    {
        $carbon = Carbon::instance($date);

        $employee = Employees::findOrFail($employeeId);

        $deductions = Deduction::query()
            ->approvedInMonth($employee->id, $carbon)
            ->get();

        $total = $deductions->sum('amount'); //مجموع المبالغ

        $this->updateAppliedFiled($deductions, $wps_payroll);

        return $total;
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function updateAppliedFiled($deduction, $wps_payroll): void
    {
        foreach ($deduction as $exec) {
            $exec->update([
                'applied_to_salary_date'    => now(),
                'wps_payrolls_id'           => $wps_payroll->id,
                'status'                    => DeductionStatus::Executed,
            ]);
        }
    }
}
