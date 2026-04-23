<?php

namespace App\Http\Controllers\Hr\Payrolls\WPS;

use App\DataTables\Hr\Payrolls\WPS\WpsPayrollDetailDataTable;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Models\Hr\Payrolls\WPS\WpsPayrollDetail;
use App\Models\Hr\Payrolls\WPS\WpsPayrollDetailRevision;
use App\Services\HR\Payrolls\PayrollCalculatorService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class WpsPayrollDetailController extends Controller
{
    protected $calculator;
    private $page = "hr.payrolls.wps.details";

    public function __construct(PayrollCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }


    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(WpsPayroll $wps_payroll, Request $request, WpsPayrollDetailDataTable $dataTable)
    {
        try {
            // Statistics
            // $statusCounts = Employees::with('user.roles')->whereHas('user', function ($query) {
            //     $query->where('status', 'active');
            // })->select('hr_status_id')
            //     ->selectRaw('COUNT(*) as count')
            //     ->groupBy('hr_status_id')
            //     ->pluck('count', 'hr_status_id')
            //     ->toArray();


            // $totalEmployees                 =  array_sum($statusCounts);
            // $unavailableEmployees           = $statusCounts[4] ?? 0;
            // $availableEmployees             = $statusCounts[3] ?? 0;
            // $partiallyAvailableEmployees    = $statusCounts[2] ?? 0;
            // $currentEmployees               = $statusCounts[1] ?? 0;

            // // Filters
            // $hrStatus   = SettingsHrStatus::select('id', 'name')->get();
            // $roles      = Role::select('id', 'name')->get();


            // $dataTable = new WpsPayrollDetailDataTable($wpsPayroll);
            $dataTable->wps_payroll = $wps_payroll;

            return $dataTable->render($this->page . '.index', compact(
                'wps_payroll',
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }

        // if ($request->ajax()) {
        //     $query = $wps_payroll->wpsDetails;

        //     return datatables()->of($query)

        //         ->addColumn('employee_id', function ($row) {
        //             $showUrl = route('hr.payrolls.wps.details.show', [$row->wps_payroll_id, $row->employee_id]);

        //             $name = $row->employee->raw_name;
        //             return "<a href=\"{$showUrl}\">{$name}</a>";
        //         })

        //         ->addColumn('status', function ($row) {
        //             $badge = $row->status_badge;
        //             return "<span class=\"badge bg-{$badge['bg']}\">{$row->status_name}</span>";
        //         })

        //         ->addColumn('action', function ($row) {
        //             return view('hr.payrolls.wps.details.action', compact('row'))->render();
        //         })

        //         ->rawColumns(['employee_id', 'status', 'action'])
        //         ->make(true);
        // }

        // return view('', compact('wps_payroll'));
    }

    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(WpsPayroll $wps_payroll, Employees $employee)
    {
        $wps_payroll_details = WpsPayrollDetail::where('wps_payroll_id', $wps_payroll->id)->where('employee_id', $employee->id)->first();

        return view('hr.payrolls.wps.details.show', compact('wps_payroll_details', 'employee'));
    }

    /*
    |--------------------------------------------------------------------------
    | export_pdf
    |--------------------------------------------------------------------------
    */
    public function export_pdf(WpsPayroll $wps_payroll, Employees $employee)
    {
        try {
            // تحميل سجل الراتب مع بيانات الموظف المرتبطة
            $wps_payroll_details = WpsPayrollDetail::where('wps_payroll_id', $wps_payroll->id)->where('employee_id', $employee->id)->first();

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
            $totalSalary = number_format(
                $wps_payroll_details->getRawOriginal('basic')
                    + $wps_payroll_details->getRawOriginal('transport')
                    + $wps_payroll_details->getRawOriginal('housing')
                    + $wps_payroll_details->getRawOriginal('other'),
                2
            );

            $deductions = $wps_payroll_details->deductions;

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
                .sar{
                    font-size: 8px;
                    font-weight: bold !important;
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

                    <table>
                        <!-- بيانات الموظف -->
                        <tr>
                            <th colspan="2">بيانات الموظف</th>
                        </tr>
                        <tr>
                            <td>اسم الموظف</td>
                            <td>' . ($wps_payroll_details->employee->getRawOriginal('name') ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>رقم الهوية / الاقامة</td>
                            <td>' . ($wps_payroll_details->employee->id_number ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>الرقم الوظيفي</td>
                            <td>' . ($wps_payroll_details->employee->national_number ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>المسمى الوظيفي</td>
                            <td>' . ($wps_payroll_details->employee->job_title ?? 'غير محدد') . '</td>
                        </tr>
                        <tr>
                            <td>الشهر</td>
                            <td>' . $wps_payroll_details->wpsPayroll->run_date->format('m') . '</td>
                        </tr>
                        <tr>
                            <td>السنة</td>
                            <td>' . $wps_payroll_details->wpsPayroll->run_date->format('Y') . '</td>
                        </tr>
                        <!-- ملخص الراتب -->
                        <tr>
                            <th colspan="2">ملخص الراتب</th>
                        </tr>
                        <tr>
                            <td>الراتب الأساسي</td>
                        <td dir="ltr">' . $wps_payroll_details->basic . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td>بدل النقل</td>
                        <td dir="ltr">' . $wps_payroll_details->transport . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td>بدل السكن</td>
                        <td dir="ltr">' . $wps_payroll_details->housing . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td>بدلات أخرى</td>
                        <td dir="ltr">' . $wps_payroll_details->other . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td class="total">إجمالي البدلات</td>
                            <td dir="ltr" class="total">' . $totalSalary . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <th colspan="2">التأمينات</th>
                        </tr>
                        <tr>
                            <td>نسبة التأمينات</td>
                            <td dir="ltr">' . number_format($employee->insurance_percentage) . '<span class="sar"> % </span></td>
                        </tr>
                        <tr>
                            <td>قيمة التأمينات</td>
                            <td dir="ltr" class="total">' . $wps_payroll_details->insurance . '<span class="sar"> SAR </span></td>
                        </tr>
                        <!-- الإجماليات -->
                        <tr>
                            <th colspan="2">الإجماليات</th>
                        </tr>
                        <tr>
                            <td>إجمالي المستحق (الراتب الأساسي + البدلات)</td>
                            <td dir="ltr" class="total">' . $totalSalary . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td>خصومات المخالفات</td>
                            <td dir="ltr" class="total">' . number_format($wps_payroll_details->deductions_without_insurance, 2) . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td>خصم التأمين</td>
                            <td dir="ltr" class="total">' . $wps_payroll_details->insurance . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td>إجمالي الخصومات	</td>
                            <td dir="ltr" class="total">' . $wps_payroll_details->deductions . '<span class="sar"> SAR </span></td>
                        </tr>
                        <tr>
                            <td class="total">صافي الراتب</td>
                            <td dir="ltr" class="total">' . $wps_payroll_details->net . '<span class="sar"> SAR </span></td>
                        </tr>
                    </table>
                </div>
            ';

            $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);

            return response()->streamDownload(
                function () use ($mpdf) {
                    echo $mpdf->Output('', 'S');
                },
                'تقرير_الراتب_' . ($wps_payroll_details->employee->getRawOriginal('name') ?? 'غير_محدد') . '_' . $wps_payroll_details->wpsPayroll->run_date->format('m') . '_' . $wps_payroll_details->wpsPayroll->run_date->format('Y') . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير تقرير الراتب. يرجى المحاولة لاحقاً.');
        }
    }
}
