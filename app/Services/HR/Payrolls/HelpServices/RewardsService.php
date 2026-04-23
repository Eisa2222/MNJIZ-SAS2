<?php

namespace App\Services\HR\Payrolls\HelpServices;

use App\Enums\Hr\Reward\RewardStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Rewards\Reward;
use Carbon\Carbon;

class RewardsService
{
    public function getSumRewards(int $employeeId, \DateTime $date, $wps_payroll): float
    {
        $carbon = Carbon::instance($date);

        $employee = Employees::findOrFail($employeeId);

        $rewards = Reward::query()
            ->approvedInMonth($employee->id, $carbon)
            ->get();

        $total = $rewards->sum('amount'); //مجموع المبالغ

        $this->updateAppliedFiled($rewards, $wps_payroll);

        return $total;
    }

    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function updateAppliedFiled($rewards, $wps_payroll): void
    {
        foreach ($rewards as $exec) {
            $exec->update([
                'applied_to_salary_date'    => now(),
                'wps_payrolls_id'           => $wps_payroll->id,
                'status'                    => RewardStatus::Executed,
            ]);
        }
    }
}
