<?php

namespace App\Services\HR\Payrolls\HelpServices;

use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Violations\Violation;
use App\Models\Hr\Violations\ViolationExecution;
use Carbon\Carbon;

class ViolationService
{
    public function getSumViolations(int $employeeId, \DateTime $date, $wps_payroll): float
    {
        $carbon = Carbon::instance($date);

        $employee = Employees::findOrFail($employeeId);

        $executions = ViolationExecution::query()
            ->deduction()
            ->forApprovedViolationsOf($employee->id, $carbon)
            ->get();

        $total = $executions->sum('deduction_amount'); //مجموع مبالغ الخصم

        $this->updateAppliedFiled($executions, $wps_payroll);

        $this->updateToExecuted($executions);

        return $total;
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function updateAppliedFiled($executions, $wps_payroll): void
    {
        foreach ($executions as $exec) {
            $exec->update([
                'applied_to_salary_date'    => now(),
                'wps_payrolls_id'           => $wps_payroll->id,
            ]);
        }
    }


    private function updateToExecuted($executions): void
    {
        $violationIds = $executions
            ->pluck('violation.id')
            ->unique()
            ->filter()
            ->all();

        if (empty($violationIds)) {
            return;
        }

        Violation::whereIn('id', $violationIds)
            ->update([
                'status' => 'executed',
            ]);
    }
}
