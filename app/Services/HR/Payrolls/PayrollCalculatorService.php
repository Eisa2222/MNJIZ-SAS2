<?php

namespace App\Services\HR\Payrolls;

use App\Enums\Hr\Payrolls\WPS\WpsStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Services\HR\Payrolls\HelpServices\AdvancesService;
use App\Services\HR\Payrolls\HelpServices\DeductionsService;
use App\Services\HR\Payrolls\HelpServices\InsuranceService;
use App\Services\HR\Payrolls\HelpServices\RewardsService;
use App\Services\HR\Payrolls\HelpServices\ViolationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PayrollCalculatorService
{
    public function __construct(
        private PayrollsService     $payrollsService,
        private ViolationService    $violationService,   // المخالفات
        private InsuranceService    $insuranceService,   // التامينات
        private RewardsService      $rewardsService,     // المكافآت
        private DeductionsService   $deductionsService,  // الخصومات
        private AdvancesService     $advancesService,    // السلف
    ) {}

    public function processWpsPayroll(Carbon $date): WpsPayroll
    {
        $this->validateDate($date);

        return DB::transaction(function () use ($date) {
            try {

                $wps_payroll = $this->createWpsPayroll($date);

                [$totalGross, $totalNet, $processedCount] = $this->processEmployees($wps_payroll, $date);

                $this->updateTotalWps($wps_payroll, $totalGross, $totalNet, $processedCount);

                return $wps_payroll;
            } catch (Exception $e) {
                throw new Exception("فشل في معالجة الرواتب: " . $e->getMessage());
            }
        });
    }

    /*
    |============================================================================
    |============================================================================
    |                        Private Methods
    |============================================================================
    |============================================================================
    */
    private function validateDate(Carbon $date): void
    {
        // if ($date->isFuture()) {
        //     throw new Exception('لا يمكن حساب الرواتب لتاريخ مستقبلي');
        // }

        if ($date->diffInMonths(now()) > 12) {
            throw new Exception('لا يمكن حساب الرواتب لتاريخ أقدم من سنة');
        }
    }

    private function createWpsPayroll(Carbon $date): WpsPayroll
    {
        $existing = WpsPayroll::where('reference', 'WPS-' . $date->format('Y-m'))->first();
        if ($existing) {
            throw new Exception('يوجد سجل رواتب بالفعل لهذا الشهر');
        }

        return WpsPayroll::create([
            'reference'     => 'WPS-' . $date->format('Y-m'),
            'run_date'      => $date->format('Y-m-d'),
            'status'        => WpsStatus::GeneratedManually,
            // 'created_by'    => Auth::user()->employee->id,
        ]);
    }

    private function processEmployees(WpsPayroll $wps_payroll, Carbon $date): array
    {
        $totalGross     = 0;
        $totalNet       = 0;
        $processedCount = 0;
        $errors         = [];

        try {
            Employees::active()->with('user')

                ->chunk(200, function ($employees) use ($wps_payroll, $date, &$totalGross, &$totalNet, &$processedCount, &$errors) {
                    foreach ($employees as $employee) {
                        try {
                            $this->processIndividualEmployee(
                                $employee,
                                $wps_payroll,
                                $date,
                                $totalGross,
                                $totalNet,
                                $processedCount
                            );
                        } catch (Exception $e) {
                            $errors[] = [
                                'employee_id'       => $employee->id,
                                'employee_name'     => $employee->name,
                                'error'             => $e->getMessage()
                            ];

                            Log::warning("خطأ في معالجة الموظف {$employee->id}: " . $e->getMessage());
                            continue;
                        }
                    }
                });
        } catch (Exception $e) {
            throw new Exception("خطأ في استعلام الموظفين: " . $e->getMessage());
        }

        // تسجيل الأخطاء إن وجدت
        if (!empty($errors)) {
            Log::warning("أخطاء في معالجة بعض الموظفين", ['errors' => $errors]);
        }

        if ($processedCount === 0) {
            throw new Exception('لم يتم العثور على موظفين نشطين للمعالجة');
        }

        return [$totalGross, $totalNet, $processedCount];
    }

    private function processIndividualEmployee(
        Employees $employee,
        WpsPayroll $wps_payroll,
        Carbon $date,
        float &$totalGross,
        float &$totalNet,
        int &$processedCount
    ): void {
        // احسب الراتب الشهري الخام
        $salaryBreakdown    = $this->payrollsService->getSalaryBreakdownAt($employee->id, $date);
        $grossMonthlySalary = array_sum($salaryBreakdown);


        $violations     = $this->violationService->getSumViolations($employee->id, $date, $wps_payroll);
        $insurance      = $this->insuranceService->getSumInsurance($employee->id, $grossMonthlySalary);
        $rewards        = $this->rewardsService->getSumRewards($employee->id, $date, $wps_payroll);
        $deductions     = $this->deductionsService->getSumDeductions($employee->id, $date, $wps_payroll);
        $advances       = $this->advancesService->getSumAdvances($employee->id, $date, $wps_payroll);

        // حساب الإجماليات
        $AllDeductions  = $violations + $insurance + $deductions + $advances;
        $incentives     = $rewards;
        $net            = $grossMonthlySalary + $incentives - $AllDeductions;


        $wps_payroll->wpsDetails()->create([
            'employee_id'   => $employee->id,
            'basic'         => $salaryBreakdown['basic'] ?? 0,
            'transport'     => $salaryBreakdown['transport'] ?? 0,
            'housing'       => $salaryBreakdown['housing'] ?? 0,
            'other'         => $salaryBreakdown['other'] ?? 0,
            'insurance'     => $insurance,
            'deductions'    => $AllDeductions,
            'incentives'    => $incentives,
            'net'           => $net,
        ]);

        // تجميع الإجماليات
        $totalGross += $grossMonthlySalary;
        $totalNet += $net;
        $processedCount++;
    }

    private function updateTotalWps(
        WpsPayroll $wps_payroll,
        float $totalGross,
        float $totalNet,
        int $employeesCount
    ): void {
        $wps_payroll->update([
            'total_gross'       => round($totalGross, 2),
            'total_net'         => round($totalNet, 2),
            'employees_count'   => $employeesCount,
        ]);
    }
}
