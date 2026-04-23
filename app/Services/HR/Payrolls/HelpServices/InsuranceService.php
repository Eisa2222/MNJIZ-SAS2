<?php

namespace App\Services\HR\Payrolls\HelpServices;

use App\Enums\Hr\Employee\InsuranceStatus;
use App\Helpers\SettingsHelper;
use App\Models\Hr\Employees\Employees;

class InsuranceService
{
    //  ارجاع قيمة التامين حسب الموظف
    public function getSumInsurance(int $employeeId, $grossMonthlySalary): float
    {
        // هل خيار التامينات مفعل
        if (SettingsHelper::get('insurance_deduction') == 0) {
            return 0;
        }

        $employee = Employees::findOrFail($employeeId);

        if ($employee->insurance_status !== InsuranceStatus::Added) {
            return 0;
        }

        return $grossMonthlySalary * (SettingsHelper::get('insurance_percentage') / 100);
    }
}
