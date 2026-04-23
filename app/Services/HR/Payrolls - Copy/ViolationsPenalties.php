// <?php
// use App\Models\Employee;
// use App\Models\Hr\Employees\Employees;
// use App\Models\Hr\Payrolls\WPS\WpsPayroll;
// use App\Services\HR\Payrolls\HelpServices\InsuranceService;
// use App\Services\HR\Payrolls\HelpServices\ViolationService;
// use Carbon\Carbon;
// use DateTime;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\DB;
// use Exception;
// use Illuminate\Support\Facades\Auth;

// class PayrollCalculatorService
// {
//     /*
//     |--------------------------------------------------------------------------
//     | variables
//     |--------------------------------------------------------------------------
//     */
//     protected ViolationService $violationService;
//     protected PayrollsService $payrollsService;
//     protected InsuranceService $insuranceService;


//     /*
//     |--------------------------------------------------------------------------
//     | construct
//     |--------------------------------------------------------------------------
//     */
//     public function __construct(
//         ViolationService $violationService,
//         PayrollsService $payrollsService,
//         InsuranceService $insuranceService
//     ) {
//         $this->violationService = $violationService;
//         $this->payrollsService  = $payrollsService;
//         $this->insuranceService = $insuranceService;
//     }



//     /*
//     |--------------------------------------------------------------------------
//     | Proces wps payroll
//     | دالة حساب الراتب
//     |--------------------------------------------------------------------------
//     */
//     public function processWpsPayroll(\DateTime $date): WpsPayroll
//     {
//         return DB::transaction(function () use ($date) {

//             // create the wps
//             $wps_payroll = $this->createWpsPayroll($date);

//             // معالجة جميع الموظفين وإرجاع الإجماليات
//             [$totalGross, $totalNet] = $this->processEmployees($wps_payroll, $date);

//             // تحديث الحقول النهائية في سجل التشغيل
//             $this->updatTotalWps($wps_payroll, $totalGross, $totalNet);

//             return $wps_payroll;
//         });
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | private function
//     |--------------------------------------------------------------------------
//     */

//     // create the WPS
//     private function createWpsPayroll(DateTime $date): WpsPayroll
//     {
//         return WpsPayroll::create([
//             'reference'     => 'WPS-' . $date->format('Y-m'),
//             'run_date'      => $date->format('Y-m-d'),
//             'status'        => WpsPayroll::STATUS_GENERATED_MANUALLY,
//             'created_by'    => Auth::user()->employee->id,
//         ]);
//     }

//     // process all employees
//     private function processEmployees(WpsPayroll $run, DateTime $date): array
//     {
//         $totalGross = 0;
//         $totalNet   = 0;

//         Employees::with('user')
//             ->chunk(200, function ($employees) use ($run, $date, &$totalGross, &$totalNet) {
//                 foreach ($employees as $employee) {
//                     // 2.1) احسب الراتب الشهري الخام
//                     $salaryBreakdown     = $this->payrollsService->getSalaryBreakdownAt($employee->id, $date);
//                     $grossMonthlySalary  = array_sum($salaryBreakdown);

//                     // 2.2) احسب الخصومات
//                     // $violations = $this->violationService->getSumViolations($employee->id, $date,$run);
//                     // $insurance  = $this->insuranceService->getSumInsurance($employee->id, $grossMonthlySalary);
//                     // $deductions = $violations + $insurance;

//                     $violations = 0;
//                     $insurance  = 0;
//                     $deductions = $violations + $insurance;

//                     // 2.3) الصافي
//                     $net = $grossMonthlySalary - $deductions;

//                     // 2.4) حفظ التفاصيل
//                     $run->wpsDetails()->create([
//                         'employee_id' => $employee->id,
//                         'basic'       => $salaryBreakdown['basic'],
//                         'transport'   => $salaryBreakdown['transport'],
//                         'housing'     => $salaryBreakdown['housing'],
//                         'other'       => $salaryBreakdown['other'],
//                         'insurance'   => $insurance,
//                         'deductions'  => $deductions,
//                         'net'         => $net,
//                     ]);

//                     // 2.5) تجميع الإجماليات
//                     $totalGross += $grossMonthlySalary;
//                     $totalNet   += $net;
//                 }
//             });

//         return [$totalGross, $totalNet];
//     }

//     // update the WPS for add total
//     private function updatTotalWps(WpsPayroll $run, float  $totalGross, float  $totalNet): void
//     {
//         $run->update([
//             'total_gross' => $totalGross,
//             'total_net'   => $totalNet,
//         ]);
//     }
// }