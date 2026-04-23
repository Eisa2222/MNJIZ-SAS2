<?php

namespace App\Http\Controllers\reports;

use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Yajra\DataTables\DataTables;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\LeaveBalance;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\GeneralSetting\SystemSetting\Settings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class LeaveReportController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        // سنوات الرصيد
        $years = Cache::remember('leave_balance_years', 3600, function () {
            return LeaveBalance::select('year')->distinct()->orderBy('year', 'desc')->pluck('year')->toArray();
        });

        if (empty($years)) {
            $years = [Carbon::now()->year];
        }

        $year = $request->input('year');

        if ($request->ajax()) {
            $query = LeaveBalance::with('employee')
                ->when($year, fn($q) => $q->where('year', $year));

            if ($request->filled('filter_employee')) {
                $query->where('employee_id', $request->filter_employee);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                // عمود الاختيار
                ->addColumn('checkbox', fn($row) => '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">')
                // عمود الصورة
                ->addColumn('employee_profile', function ($row) {
                    $url = $row->employee->profile_picture
                        ? asset('storage/' . $row->employee->profile_picture)
                        : asset('assets/img/branding/Alburhan-Logo.png');
                    return "<img src=\"{$url}\" class=\"rounded-circle\" width=\"40\" height=\"40\">";
                })
                // اسم الموظف
                ->addColumn('employee_name', fn($row) => e($row->employee->name))
                // السنة
                ->addColumn('year', fn($row) => $row->year)
                // الصيغ الرقمية
                ->editColumn('total_days', fn($row) => number_format($row->total_days, 2))
                ->editColumn('used_days',  fn($row) => number_format($row->used_days, 2))
                ->editColumn('remaining_days', fn($row) => number_format($row->remaining_days, 2))
                // نسبة الاستخدام
                ->addColumn('usage_percentage', function ($row) {
                    $pct = $row->total_days > 0
                        ? round(($row->used_days / $row->total_days) * 100, 2)
                        : 0;
                    $color = $pct >= 80 ? 'danger' : ($pct >= 50 ? 'warning' : 'success');
                    return "<div class=\"progress-container\">
                              <div class=\"progress\">
                                <div class=\"progress-bar bg-{$color}\" role=\"progressbar\" style=\"width:{$pct}%;\"></div>
                              </div>
                              <span class=\"text-{$color}\">{$pct}%</span>
                            </div>";
                })
                ->rawColumns(['checkbox', 'employee_profile', 'usage_percentage'])
                ->make(true);
        }

        $employees = Cache::remember('employees_for_balance', 3600, fn() => Employees::select('id', 'name', 'nickname')->get());

        return view('reports.leave_reports.index', compact('year', 'years', 'employees'));
    }

    /*
    |--------------------------------------------------------------------------
    | Export Selected
    |--------------------------------------------------------------------------
    | تصدير السجلات المحددة إلى PDF
    */
    public function exportSelected(Request $request)
    {
        try {
            // استقبل الـ IDs من الـ POST
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return redirect()->back()->with('error', 'لم يتم تحديد أي سجلات للتصدير.');
            }

            // جلب البيانات مع ترتيبها حسب الموظف والسنة من الأقدم للأحدث
            $balances = LeaveBalance::with('employee')
                ->whereIn('id', $ids)
                ->orderBy('employee_id')
                ->orderBy('year', 'asc') // ترتيب من السنة الأقدم للأحدث لكل موظف
                ->get();

            // إعداد عنوان وتاريخ الطباعة
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now()->format('H:i:s');

            // إعداد مسارات الهيدر والفوتر
            $headerPath = storage_path('app/public/' . SettingsHelper::get('horizontal_header_image'));
            $footerPath = storage_path('app/public/' . SettingsHelper::get('horizontal_footer_image'));

            // إعداد الخطوط والكونفيج - نفس إعدادات العروض
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

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
                'orientation' => 'P', // Portrait
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

            $mpdf = new Mpdf($config);
            $mpdf->SetDirectionality('rtl');

            // أنماط CSS محسنة
            $stylesheet = '
            body {
                font-family: almarai;
                font-size: 12px;
                line-height: 1.5;
                direction: rtl;
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
                padding: 0;
                margin-left: 20px !important;
                margin-right: 20px !important;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .table th {
                background-color: #f5f5f5;
                border: 1px solid #ddd;
                padding: 8px;
                font-weight: bold;
                text-align: center;
                font-size: 11px;
            }
            .table td {
                border: 1px solid #ddd;
                padding: 6px;
                text-align: center;
                font-size: 11px;
            }
            .table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .report-title {
                font-size: 18px;
                font-weight: bold;
                text-align: center;
                margin: 20px 0;
            }
            .report-info {
                text-align: center;
                color: #666;
                font-size: 12px;
                margin-bottom: 20px;
            }
            ';

            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

            // إعداد الهيدر - نفس أسلوب العروض
            if (file_exists($headerPath)) {
                $headerContent = '
                <div style="padding-left:15px;">
                    <table width="100%" style="padding: 25px 20px 35px 15px;">
                        <tr>
                            <td style="text-align: right; vertical-align: middle;">
                                <img src="' . $headerPath . '" style="height: 60px; max-width: 250px;" />
                            </td>
                            <td style="text-align: left; vertical-align: middle;">
                                <div class="reference-number">
                                    تقرير أرصدة الإجازات - ' . $currentDate . '
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>';
                $mpdf->SetHTMLHeader($headerContent);
            }

            // إعداد الفوتر - نفس أسلوب العروض
            if (file_exists($footerPath)) {
                $footerContent = '
                <div class="footer-container">
                    <img src="' . $footerPath . '" />
                </div>';
                $mpdf->SetHTMLFooter($footerContent);
            }

            // محتوى التقرير
            $content = '
            <div class="content">
                <div class="report-title">تقرير مفصل لأرصدة الإجازات</div>
                <div class="report-info">
                    التاريخ: ' . $currentDate . ' | الوقت: ' . $currentTime . '
                </div>

                <table class="table">';

            $counter = 1;
            $totalBalance = 0;
            $totalUsed = 0;
            $totalRemaining = 0;
            $totalCarriedOver = 0;
            $totalYearBalance = 0;

            // حساب عدد الموظفين الفريدين
            $uniqueEmployeesCount = $balances->pluck('employee_id')->unique()->count();

            // تجميع البيانات حسب الموظف
            $groupedBalances = $balances->groupBy('employee_id');

            // إضافة رأس الجدول مرة واحدة إذا كان موظف واحد فقط
            if ($uniqueEmployeesCount == 1) {
                $content .= '
                    <thead>
                        <tr>
                            <th>اسم الموظف</th>
                            <th>السنة</th>
                            <th>المرحل</th>
                            <th>رصيد السنة</th>
                            <th>الرصيد الكلي</th>
                            <th>المستخدم</th>
                            <th>المتبقي</th>
                            <th>نسبة الاستخدام</th>
                        </tr>
                    </thead>';
            }

            $content .= '<tbody>';

            foreach ($groupedBalances as $employeeId => $employeeBalances) {
                $employeeName = $employeeBalances->first()->employee->name;

                // متغيرات إجماليات الموظف
                $employeeTotalBalance = 0;
                $employeeTotalUsed = 0;
                $employeeTotalRemaining = 0;
                $employeeTotalCarriedOver = 0;
                $employeeTotalYearBalance = 0;

                // إذا كان هناك أكثر من موظف، أضف صف تعريفي للموظف مع رأس الجدول
                if ($uniqueEmployeesCount > 1) {
                    $content .= '
                 <tr>
                     <td colspan="8" style="height: 20px; border: none;"></td>
                 </tr>
                 <tr>
                 <td colspan="8" style="text-align: center; padding: 10px 0; font-size: 13px; font-weight: bold; background-color: #e9e9e9;">
                 <strong>' . $employeeName . '</strong>
                 </td>
                 </tr>
                 <tr>
                     <td colspan="8" style="height: 10px; border: none;"></td>
                 </tr>
                 <tr>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">اسم الموظف</th>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">السنة</th>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">المرحل</th>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">رصيد السنة</th>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">الرصيد الكلي</th>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">المستخدم</th>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">المتبقي</th>
                <th style="background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: center; font-size: 11px;">نسبة الاستخدام</th>
                </tr>';
                }

                // دالة لجلب الرصيد المتبقي من السنة السابقة
                $getPreviousYearBalance = function ($employeeId, $currentYear) {
                    $previousYear = $currentYear - 1;
                    $previousBalance = LeaveBalance::where('employee_id', $employeeId)
                        ->where('year', $previousYear)
                        ->first();

                    return $previousBalance ? $previousBalance->remaining_days : 0;
                };

                // عرض سجلات الموظف مرتبة حسب السنة
                foreach ($employeeBalances as $balance) {
                    // حساب الرصيد المرحل من السنة السابقة
                    $carriedOverBalance = $getPreviousYearBalance($balance->employee_id, $balance->year);

                    // حساب رصيد السنة الحالية (الكلي - المرحل)
                    $yearBalance = $balance->total_days - $carriedOverBalance;

                    $usagePercentage = $balance->total_days > 0
                        ? round(($balance->used_days / $balance->total_days) * 100, 2)
                        : 0;

                    // إضافة إلى الإجماليات العامة
                    $totalBalance += $balance->total_days;
                    $totalUsed += $balance->used_days;
                    $totalRemaining += $balance->remaining_days;
                    $totalCarriedOver += $carriedOverBalance;
                    $totalYearBalance += $yearBalance;

                    // إضافة إلى إجماليات الموظف
                    $employeeTotalBalance += $balance->total_days;
                    $employeeTotalUsed += $balance->used_days;
                    $employeeTotalRemaining += $balance->remaining_days;
                    $employeeTotalCarriedOver += $carriedOverBalance;
                    $employeeTotalYearBalance += $yearBalance;

                    // تنسيق الأرقام
                    $carriedOverFormatted = ($carriedOverBalance == floor($carriedOverBalance))
                        ? number_format($carriedOverBalance, 0)
                        : number_format($carriedOverBalance, 2);

                    $yearBalanceFormatted = ($yearBalance == floor($yearBalance))
                        ? number_format($yearBalance, 0)
                        : number_format($yearBalance, 2);

                    $totalDaysFormatted = ($balance->total_days == floor($balance->total_days))
                        ? number_format($balance->total_days, 0)
                        : number_format($balance->total_days, 2);

                    $usedDaysFormatted = ($balance->used_days == floor($balance->used_days))
                        ? number_format($balance->used_days, 0)
                        : number_format($balance->used_days, 2);

                    $remainingDaysFormatted = ($balance->remaining_days == floor($balance->remaining_days))
                        ? number_format($balance->remaining_days, 0)
                        : number_format($balance->remaining_days, 2);

                    $content .= '
                            <tr>
                                <td style="text-align: right;">' . $balance->employee->name . '</td>
                                <td>' . $balance->year . '</td>
                                <td>' . $carriedOverFormatted . '</td>
                                <td>' . $yearBalanceFormatted . '</td>
                                <td>' . $totalDaysFormatted . '</td>
                                <td>' . $usedDaysFormatted . '</td>
                                <td>' . $remainingDaysFormatted . '</td>
                                <td>' . $usagePercentage . '%</td>
                            </tr>';
                    $counter++;
                }

                // إضافة صف الإجماليات للموظف إذا كان هناك أكثر من موظف
                if ($uniqueEmployeesCount > 1) {
                    // تنسيق إجماليات الموظف
                    $empCarriedOverFormatted = ($employeeTotalCarriedOver == floor($employeeTotalCarriedOver))
                        ? number_format($employeeTotalCarriedOver, 0)
                        : number_format($employeeTotalCarriedOver, 2);

                    $empYearBalanceFormatted = ($employeeTotalYearBalance == floor($employeeTotalYearBalance))
                        ? number_format($employeeTotalYearBalance, 0)
                        : number_format($employeeTotalYearBalance, 2);

                    $empTotalFormatted = ($employeeTotalBalance == floor($employeeTotalBalance))
                        ? number_format($employeeTotalBalance, 0)
                        : number_format($employeeTotalBalance, 2);

                    $empUsedFormatted = ($employeeTotalUsed == floor($employeeTotalUsed))
                        ? number_format($employeeTotalUsed, 0)
                        : number_format($employeeTotalUsed, 2);

                    $empRemainingFormatted = ($employeeTotalRemaining == floor($employeeTotalRemaining))
                        ? number_format($employeeTotalRemaining, 0)
                        : number_format($employeeTotalRemaining, 2);

                    $empUsagePercentage = $employeeTotalBalance > 0
                        ? round(($employeeTotalUsed / $employeeTotalBalance) * 100, 2)
                        : 0;

                    $content .= '
                            <tr style="border-top: 2px solid #333; font-weight: bold; background-color: #e9e9e9;">
                                <td colspan="2" style="text-align: center;">إجمالي ' . $employeeName . '</td>
                                <td>' . $empCarriedOverFormatted . '</td>
                                <td>' . $empYearBalanceFormatted . '</td>
                                <td>' . $empTotalFormatted . '</td>
                                <td>' . $empUsedFormatted . '</td>
                                <td>' . $empRemainingFormatted . '</td>
                                <td>-</td>
                            </tr>';
                }
            }

            $content .= '
                    </tbody>
                </table>

                <h3 style="text-align: center; font-size: 16px; font-weight: bold; margin: 30px 0 20px;">ملخص التقرير الإجمالي</h3>

                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr>';

            // إذا كان هناك أكثر من موظف، أضف عدد الموظفين
            if ($uniqueEmployeesCount > 1) {
                $content .= '
                        <td style="border: 1px solid #ddd; text-align: center; padding: 15px;">
                            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">' . $uniqueEmployeesCount . '</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">عدد الموظفين</div>
                        </td>';
            }

            // تنسيق الأرقام للملخص
            $totalCarriedOverFormatted = ($totalCarriedOver == floor($totalCarriedOver)) ? number_format($totalCarriedOver, 0) : number_format($totalCarriedOver, 2);
            $totalYearBalanceFormatted = ($totalYearBalance == floor($totalYearBalance)) ? number_format($totalYearBalance, 0) : number_format($totalYearBalance, 2);
            $totalBalanceFormatted = ($totalBalance == floor($totalBalance)) ? number_format($totalBalance, 0) : number_format($totalBalance, 2);
            $totalUsedFormatted = ($totalUsed == floor($totalUsed)) ? number_format($totalUsed, 0) : number_format($totalUsed, 2);
            $totalRemainingFormatted = ($totalRemaining == floor($totalRemaining)) ? number_format($totalRemaining, 0) : number_format($totalRemaining, 2);

            $content .= '
                        <td style="border: 1px solid #ddd; text-align: center; padding: 15px;">
                            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">' . $totalCarriedOverFormatted . '</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">إجمالي المرحل</div>
                        </td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 15px;">
                            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">' . $totalYearBalanceFormatted . '</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">إجمالي رصيد السنة</div>
                        </td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 15px;">
                            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">' . $totalBalanceFormatted . '</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">إجمالي الرصيد الكلي</div>
                        </td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 15px;">
                            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">' . $totalUsedFormatted . '</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">إجمالي المستخدم</div>
                        </td>
                        <td style="border: 1px solid #ddd; text-align: center; padding: 15px;">
                            <div style="font-size: 24px; font-weight: bold; color: #2c3e50;">' . $totalRemainingFormatted . '</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">إجمالي المتبقي</div>
                        </td>
                    </tr>
                </table>
            </div>';

            $mpdf->WriteHTML($content);

            // تحديد اسم الملف بالعربية
            $fileName = 'تقرير_أرصدة_الإجازات_' . now()->format('Y_m_d_His') . '.pdf';

            // تنزيل الملف
            return response()->streamDownload(
                fn() => print($mpdf->Output('', 'S')),
                $fileName,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                ]
            );
        } catch (\Exception $e) {
            Log::error('خطأ في تصدير تقرير الإجازات: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير التقرير: ' . $e->getMessage());
        }
    }
}
