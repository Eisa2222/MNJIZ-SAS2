<?php


use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance\Attendance;
use App\Models\Payroll;
use App\Models\Hr\Employees\Employees;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:إدارة المرتبات');
    }

    /**
     * عرض قائمة الرواتب باستخدام DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Payroll::with('employee');

            return datatables()->of($query)
                ->addColumn('employee_name', function ($row) {
                    return optional($row->employee)->name ?? 'غير محدد';
                })
                ->editColumn('basic_salary', function ($row) {
                    return number_format($row->basic_salary, 2);
                })
                ->addColumn('total_allowances', function ($row) {
                    $total = ($row->transport_allowance ?? 0) + ($row->housing_allowance ?? 0) + ($row->other_allowances ?? 0);
                    return number_format($total, 2);
                })
                ->addColumn('total_earnings', function ($row) {
                    $total = ($row->basic_salary ?? 0) +
                        ($row->transport_allowance ?? 0) +
                        ($row->housing_allowance ?? 0) +
                        ($row->other_allowances ?? 0);
                    return number_format($total, 2);
                })
                ->editColumn('total_deductions', function ($row) {
                    return number_format($row->total_deductions, 2);
                })
                ->editColumn('net_salary', function ($row) {
                    return number_format($row->net_salary, 2);
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('hr.payroll.wps.show', $row->id);
                    $exportUrl = route('hr.payroll.wps.exportPdf', $row->id);
                    return '<a href="' . $showUrl . '" class="btn btn-info btn-sm">عرض</a> ' .
                        '<a href="' . $exportUrl . '" class="btn btn-secondary btn-sm">تصدير PDF</a>';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('hr.payroll.index');
    }

    /**
     * عرض نموذج إضافة راتب يدوي.
     */
    public function create()
    {
        $employees = Employees::all();
        return view('hr.payroll.create', compact('employees'));
    }

    /**
     * حفظ سجل راتب جديد.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'month'        => 'required',
            'year'         => 'required',
            'basic_salary' => 'required|numeric',
        ]);

        Payroll::create($validated);

        return redirect()->route('hr.payroll.wps.index')
            ->with('success', 'تمت إضافة الراتب بنجاح.');
    }

    /**
     * عرض تفاصيل سجل راتب.
     */
    public function show($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);
        return view('hr.payroll.show', compact('payroll'));
    }

    /**
     * عرض نموذج تعديل الراتب.
     */
    public function edit($id)
    {
        $payroll = Payroll::findOrFail($id);
        $employees = Employees::all();
        return view('hr.payroll.edit', compact('payroll', 'employees'));
    }

    /**
     * تحديث بيانات سجل الراتب.
     */
    public function update(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);

        $validated = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'month'        => 'required',
            'year'         => 'required',
            'basic_salary' => 'required|numeric',
        ]);

        $payroll->update($validated);

        return redirect()->route('hr.payroll.wps.index')
            ->with('success', 'تم تحديث بيانات الراتب بنجاح.');
    }

    /**
     * حذف سجل راتب.
     */
    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();

        return redirect()->route('hr.payroll.wps.index')
            ->with('success', 'تم حذف الراتب بنجاح.');
    }

    /**
     * دالة مساعدة لحساب عدد أيام العمل (مع استبعاد نهايات الأسبوع).
     */
    protected function calculateWorkingDays(Carbon $startDate, Carbon $endDate, array $weekendDays = [5, 6])
    {
        $totalWorkingDays = 0;
        // حلقة تبدأ من تاريخ البداية وتنتهي عندما يكون التاريخ أقل من أو يساوي تاريخ النهاية
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // استُخدم format('N') للحصول على رقم اليوم: الإثنين=1 ... الأحد=7
            if (!in_array($date->format('N'), $weekendDays)) {
                $totalWorkingDays++;
            }
        }
        return $totalWorkingDays;
    }

    protected function calculateEmployeePresentDaysInRange($employee, Carbon $startDate, Carbon $endDate): int
    {
        $attendanceRecords = Attendance::where('user_id', $employee->user_id)
            ->whereBetween('check_in_time', [$startDate, $endDate])
            ->get();

        $uniqueDays = $attendanceRecords->groupBy(function ($item) {
            return Carbon::parse($item->check_in_time)->format('Y-m-d');
        })->count();

        return $uniqueDays;
    }



    public function generatePayroll(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:' . now()->year,
        ]);

        $month = $request->input('month');
        $year  = $request->input('year');

        // استرجاع يوم صرف المرتب من الإعدادات (مثلاً 21)
        $settingsDisbursementDay = SettingsHelper::get('payroll_disbursement_day');

        // حساب فترة الرواتب: من صرف الشهر السابق حتى صرف الشهر الحالي
        $temp = Carbon::createFromDate($year, $month, 1);
        $daysInTargetMonth = $temp->daysInMonth;
        $effectiveDisbursementDay = $settingsDisbursementDay <= $daysInTargetMonth ? $settingsDisbursementDay : $daysInTargetMonth;
        // تاريخ نهاية فترة الرواتب (اليوم الذي يُصرف فيه الراتب في الشهر الحالي)
        $payrollEndDate = Carbon::createFromDate($year, $month, $effectiveDisbursementDay)->endOfDay();
        // تاريخ بداية فترة الرواتب (نفس اليوم من الشهر السابق)
        $payrollStartDate = (clone $payrollEndDate)->subMonth()->startOfDay();

        // تأكد من أن الفترة قد انتهت بالفعل (على سبيل المثال، لا تُولد الرواتب إذا كان تاريخ الصرف الحالي في المستقبل)
        if ($payrollEndDate->gt(Carbon::now())) {
            return redirect()->route('hr.payroll.wps.index')
                ->with('error', 'لا يمكن توليد الرواتب لفترة لم تكتمل بعد.');
        }

        DB::beginTransaction();
        $createdCount = 0;
        $updatedCount = 0;
        try {
            $employees = Employees::all();

            if ($employees->isEmpty()) {
                DB::commit();
                return redirect()->route('hr.payroll.wps.index')
                    ->with('info', 'لا يوجد موظفون في النظام لتوليد الرواتب.');
            }

            foreach ($employees as $employee) {
                // تحديد فترة عمل الموظف وفق تواريخ العقد
                $employeeStartDate = $employee->contract_start_date
                    ? Carbon::parse($employee->contract_start_date)->startOfDay()
                    : $employee->created_at->startOfDay();
                $employeeEndDate = $employee->contract_end_date
                    ? Carbon::parse($employee->contract_end_date)->endOfDay()
                    : null;

                // تحديد الفترة الفعلية للموظف ضمن فترة الرواتب
                $effectiveStart = $employeeStartDate->lt($payrollStartDate) ? $payrollStartDate : $employeeStartDate;
                $effectiveEnd = ($employeeEndDate && $employeeEndDate->lt($payrollEndDate)) ? $employeeEndDate : $payrollEndDate;

                // تخطي الموظف إذا كان عقده لا يغطي فترة الحساب
                if (($employeeEndDate && $employeeEndDate->lt($payrollStartDate)) || $employeeStartDate->gt($payrollEndDate)) {
                    continue;
                }

                // حساب إجمالي أيام العمل ضمن الفترة (مع استبعاد نهايات الأسبوع)
                $totalWorkingDays = $this->calculateWorkingDays($effectiveStart, $effectiveEnd);
                if ($totalWorkingDays == 0) {
                    $totalWorkingDays = 1;
                }

                // حساب أيام الحضور خلال الفترة المحددة
                $presentDays = $this->calculateEmployeePresentDaysInRange($employee, $payrollStartDate, $payrollEndDate);
                $absentDays = $totalWorkingDays - $presentDays;

                // --- تطبيق طريقة الجمع ---
                $basicSalary        = $employee->basic_salary ?? 0;
                $transportAllowance = $employee->transportation_allowance ?? 0;
                $housingAllowance   = $employee->housing_allowance ?? 0;
                $otherAllowances    = $employee->other_allowances ?? 0;

                $totalSalary = $basicSalary + $transportAllowance + $housingAllowance + $otherAllowances;
                $dailyRate = $totalWorkingDays > 0 ? ($totalSalary / $totalWorkingDays) : 0;
                $absenceDeduction = $absentDays * $dailyRate;
                $netSalary = $totalSalary - $absenceDeduction;

                $data = [
                    'employee_id'         => $employee->id,
                    'month'               => $month,   // يمكن تخزين الشهر والسنة التي يمثلها تاريخ الصرف
                    'year'                => $year,
                    'total_working_days'  => $totalWorkingDays,
                    'absent_days'         => $absentDays,
                    'absence_deduction'   => $absenceDeduction,
                    'basic_salary'        => $basicSalary,
                    'transport_allowance' => $transportAllowance,
                    'housing_allowance'   => $housingAllowance,
                    'other_allowances'    => $otherAllowances,
                    'total_deductions'    => $absenceDeduction,
                    'net_salary'          => $netSalary,
                ];

                // التحقق من وجود سجل راتب مسبق للموظف لهذه الفترة
                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                if ($existingPayroll) {
                    $existingPayroll->update($data);
                    $updatedCount++;
                } else {
                    Payroll::create($data);
                    $createdCount++;
                }
            }

            DB::commit();

            if ($createdCount == 0 && $updatedCount == 0) {
                $message = "لم يتم توليد أي سجلات للموظفين لهذه الفترة؛ ربما لا يوجد موظفون لديهم نشاط خلال هذه الفترة.";
            } elseif ($updatedCount > 0 && $createdCount > 0) {
                $message = "تم إنشاء {$createdCount} سجل جديد وتحديث {$updatedCount} سجل موجود.";
            } elseif ($updatedCount > 0) {
                $message = "تم تحديث {$updatedCount} سجل موجود.";
            } elseif ($createdCount > 0) {
                $message = "تم إنشاء {$createdCount} سجل جديد.";
            }

            return redirect()->route('hr.payroll.wps.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generating payroll: " . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء توليد الرواتب.');
        }
    }




    /**
     * دالة مساعدة لحساب أيام حضور الموظف خلال شهر معين.
     */
    protected function calculateEmployeePresentDays($employee, $month, $year): int
    {
        $attendanceRecords = Attendance::where('user_id', $employee->user_id)
            ->whereYear('check_in_time', $year)
            ->whereMonth('check_in_time', $month)
            ->get();

        $uniqueDays = $attendanceRecords->groupBy(function ($item) {
            return \Carbon\Carbon::parse($item->check_in_time)->format('Y-m-d');
        })->count();

        return $uniqueDays;
    }


    public function exportPdf($id)
    {
        try {
            // تحميل سجل الراتب مع بيانات الموظف المرتبطة
            $payroll = Payroll::with('employee')->findOrFail($id);

            // إعداد إعدادات mPDF مع دعم الخطوط والاتجاهات
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            $headerPath = storage_path('app/public/' . SettingsHelper::get('horizontal_header_image'));
            $footerPath = storage_path('app/public/' . SettingsHelper::get('horizontal_footer_image'));

            $config = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'almarai',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 30,
                'margin_bottom' => 35,
                'margin_header' => 0,
                'margin_footer' => 0,
                'orientation' => 'P',
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts/Almarai'),
                ]),
                'fontdata' => array_merge($fontData, [
                    'almarai' => [
                        'R' => 'Almarai-Regular.ttf',
                        'B' => 'Almarai-Bold.ttf',
                        'L' => 'Almarai-Light.ttf',
                        'useOTL' => 0xFF,
                        'useKashida' => 75,
                    ]
                ]),
                'default_font_size' => 12,
                'tempDir' => storage_path('app/public/temp')
            ];


            $mpdf = new \Mpdf\Mpdf($config);
            $mpdf->SetDirectionality('rtl');

            // حساب القيم بناءً على طريقة الجمع:
            // نجمع الراتب الأساسي مع البدلات
            $totalSalary = $payroll->basic_salary
                + $payroll->transport_allowance
                + $payroll->housing_allowance
                + $payroll->other_allowances;
            // نحسب المعدل اليومي بتقسيم إجمالي الراتب على إجمالي أيام العمل
            $dailyRate = ($payroll->total_working_days > 0)
                ? ($totalSalary / $payroll->total_working_days)
                : 0;
            // خصم الغياب = المعدل اليومي × أيام الغياب
            $absenceDeductionCalculated = $dailyRate * $payroll->absent_days;
            // صافي الراتب = إجمالي الراتب - خصم الغياب
            $netSalaryCalculated = $totalSalary - $absenceDeductionCalculated;

            $disbursementDay = SettingsHelper::get('payroll_disbursement_day') ?? 21;

            // تحديد تاريخ صرف المرتب في الشهر الحالي باستخدام بيانات السجل
            $payrollEnd = \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, $disbursementDay);
            // الفترة تبدأ من نفس اليوم من الشهر السابق
            $payrollStart = (clone $payrollEnd)->subMonth();
            // تعريف ستايل CSS للتقرير بشكل مضغوط دون مساحات بيضاء زائدة
            $stylesheet = '
                body {
                    font-family: almarai;
                    font-size: 12px;
                    line-height: 1.5;
                    direction: rtl;
                    unicode-bidi: bidi-override;
                    margin: 0;
                    padding: 0;
                }
                .page-header {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 60px;
                }
               .footer-container {
                width: 100%;
                padding: 0;
                margin: 0;
                position: fixed;
                bottom: 0;
                }
                .footer-container img {
                    width: 100%;
                    height: 80px;
                    object-fit: cover;
                }
                .content {
                    margin-top: 70px;
                    margin-bottom: 70px;
                    padding: 0;
                    margin-left: 40px !important;
                    margin-right: 40px !important;
                }
                h2, h3 {
                    margin: 2mm 0;
                    text-align: center;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0;
                    padding: 0;
                }
                th, td {
                    border: 1px solid #000;
                    padding: 2mm;
                    text-align: right;
                }
                th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                }
                .total {
                    font-weight: bold;
                }
                .note {
                    font-size: 9px;
                    margin-top: 2mm;
                    text-align: center;
                }
            ';

            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

            // إعداد محتوى الهيدر (Header) للتقرير
            if (file_exists($headerPath)) {
                $headerContent = '
                    <div style="width: 100%; text-align: right; padding: 25px 20px 35px 5px;">
                        <img src="' . $headerPath . '" style="height: 60px; max-width: 250px;" />
                    </div>';
                $mpdf->SetHTMLHeader($headerContent);
            }
            if (file_exists($footerPath)) {
                $footerContent = '
                <div class="footer-container">
                    <img src="' . $footerPath . '" />
                </div>';
                $mpdf->SetHTMLFooter($footerContent);
            }
            // بناء محتوى تقرير PDF باستخدام تصميم عمودي (القيم أسفل العناوين)
            $content = '
                <div class="content">
                    <h3>تقرير الراتب</h3>
                     <p style="text-align: center; font-weight: bold;">
                    فترة الحساب: من ' . $payrollStart->format('d-m-Y') . ' إلى ' . $payrollEnd->format('d-m-Y') . '
                </p>
                    <table>
                        <!-- بيانات الموظف -->
                        <tr>
                            <th colspan="2">بيانات الموظف</th>
                        </tr>
                        <tr>
                            <td>اسم الموظف</td>
                            <td>' . ($payroll->employee->name ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>رقم الموظف</td>
                            <td>' . ($payroll->employee->employee_id ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>القسم</td>
                            <td>' . ($payroll->employee->department ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>المسمى الوظيفي</td>
                            <td>' . ($payroll->employee->job_title ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>الشهر</td>
                            <td>' . $payroll->month . '</td>
                        </tr>
                        <tr>
                            <td>السنة</td>
                            <td>' . $payroll->year . '</td>
                        </tr>
                        <!-- ملخص الراتب -->
                        <tr>
                            <th colspan="2">ملخص الراتب</th>
                        </tr>
                        <tr>
                            <td>الراتب الأساسي</td>
                            <td>' . number_format($payroll->basic_salary, 2) . ' ر.س</td>
                        </tr>
                        <tr>
                            <td>بدل النقل</td>
                            <td>' . number_format($payroll->transport_allowance, 2) . ' ر.س</td>
                        </tr>
                        <tr>
                            <td>بدل السكن</td>
                            <td>' . number_format($payroll->housing_allowance, 2) . ' ر.س</td>
                        </tr>
                        <tr>
                            <td>بدلات أخرى</td>
                            <td>' . number_format($payroll->other_allowances, 2) . ' ر.س</td>
                        </tr>
                        <tr>
                            <td class="total">إجمالي البدلات</td>
                            <td class="total">' . number_format(
                $payroll->transport_allowance +
                    $payroll->housing_allowance +
                    $payroll->other_allowances,
                2
            ) . ' ر.س</td>
                        </tr>
                        <!-- تفاصيل الفترة والخصومات -->
                        <tr>
                            <th colspan="2">تفاصيل الفترة والخصومات</th>
                        </tr>
                        <tr>
                            <td>إجمالي أيام العمل</td>
                            <td>' . $payroll->total_working_days . '</td>
                        </tr>
                        <tr>
                            <td>أيام الغياب</td>
                            <td>' . $payroll->absent_days . '</td>
                        </tr>
                        <tr>
                            <td>المعدل اليومي (إجمالي الراتب ÷ أيام العمل)</td>
                            <td>' . number_format($dailyRate, 2) . ' ر.س</td>
                        </tr>
                        <tr>
                            <td class="total">خصم الغياب (المعدل اليومي × أيام الغياب)</td>
                            <td class="total">' . number_format($absenceDeductionCalculated, 2) . ' ر.س</td>
                        </tr>
                        <!-- الإجماليات -->
                        <tr>
                            <th colspan="2">الإجماليات</th>
                        </tr>
                        <tr>
                            <td>إجمالي المستحق (الراتب الأساسي + البدلات)</td>
                            <td>' . number_format($totalSalary, 2) . ' ر.س</td>
                        </tr>
                          <tr>
                            <td>إجمالي الخصومات	</td>
                            <td>' . number_format($absenceDeductionCalculated, 2) . ' ر.س</td>
                        </tr>
                        <tr>
                            <td class="total">صافي الراتب</td>
                            <td class="total">' . number_format($netSalaryCalculated, 2) . ' ر.س</td>
                        </tr>
                    </table>
                    <p class="note">
                        ملاحظة: تم احتساب الراتب وفق طريقة الجمع؛ حيث يُجمع الراتب الأساسي مع البدلات ثم يُقسم على أيام العمل لحساب المعدل اليومي ويتم خصم قيمة أيام الغياب.
                    </p>
                </div>
            ';

            $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);

            return response()->streamDownload(
                function () use ($mpdf) {
                    echo $mpdf->Output('', 'S');
                },
                'تقرير_الراتب_' . ($payroll->employee->name ?? 'غير_محدد') . '_' . $payroll->month . '_' . $payroll->year . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير تقرير الراتب. يرجى المحاولة لاحقاً.');
        }
    }
}
