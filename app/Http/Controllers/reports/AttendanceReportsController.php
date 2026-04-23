<?php

namespace App\Http\Controllers\reports;

use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Models\Hr\Attendance\Attendance;
use App\Models\Hr\Employees\Employees;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class AttendanceReportsController extends Controller
{
    /* --------------------------------------------------------------------------
     | Index
     |--------------------------------------------------------------------------*/
    public function index()
    {
        $employees = Employees::active()->select('id', 'name', 'nickname', 'profile_picture')->get();
        return view('reports.attendance.index', compact('employees'));
    }

    /* --------------------------------------------------------------------------
     | Table Data
     |--------------------------------------------------------------------------
     */
    public function tableData(Request $request)
    {
        try {
            $employeeId = $request->get('employee_id');
            $dateFrom   = $request->get('date_from');
            $dateTo     = $request->get('date_to');

            $query = Employees::query()
                // إضافة تحديد الأعمدة بشكل واضح للسماح بالفرز والبحث
                ->select([
                    'id',
                    'name as employee_name', // تسمية صريحة للعمود
                    'id as employee_number', // تسمية العمود كما في DataTables
                    'job_title', // استخدام نفس الاسم المستخدم في DataTables
                    'profile_picture'
                ]);

            // بناء كائن DataTables مع إعدادات البحث والفرز المناسبة
            return DataTables::of($query)
                ->addColumn('profile_picture', function ($row) {
                    $imageUrl = $row->profile_picture && Storage::disk('public')->exists($row->profile_picture)
                        ? asset('storage/' . $row->profile_picture)
                        : asset('assets/img/branding/Alburhan-Logo.png');
                    return '<img src="' . $imageUrl . '" class="rounded-circle" width="40" height="40">';
                })
                // لا نحتاج لإضافة أعمدة موجودة بالفعل في الاستعلام
                // تعريف أعمدة البحث والفرز بصورة صريحة
                ->filterColumn('employee_name', function ($query, $keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                })
                ->filterColumn('employee_number', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('id', 'like', "%{$keyword}%")
                            ->orWhere('id', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('job_title', function ($query, $keyword) {
                    $query->where('job_title', 'like', "%{$keyword}%");
                })
                ->addColumn('action', function ($row) {
                    $preview  = route('reports.attendance.preview',  ['employee_id' => $row->id]);
                    $download = route('reports.attendance.download', ['employee_id' => $row->id]);
                    return '<div class="d-flex justify-content-center">
                               <a href="' . $preview  . '" class="btn btn-sm text-info me-2"><i class="ti ti-eye"></i></a>
                               <a href="' . $download . '" class="btn btn-sm text-primary"><i class="ti ti-download"></i></a>
                             </div>';
                })
                ->rawColumns(['profile_picture', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('tableData error', ['msg' => $e->getMessage()]);
            return response()->json(['error' => true, 'message' => 'خطأ في جلب البيانات'], 500);
        }
    }

    /* --------------------------------------------------------------------------
     | Preview
     |--------------------------------------------------------------------------
     */
    public function preview(Request $request)
    {
        try {
            $employeeId = $request->get('employee_id');
            $dateFrom   = $request->get('date_from') ?: Carbon::now()->startOfMonth()->format('Y-m-d');
            $dateTo     = $request->get('date_to')   ?: Carbon::now()->endOfMonth()->format('Y-m-d');

            $from = Carbon::parse($dateFrom)->startOfDay();
            $to   = Carbon::parse($dateTo)->endOfDay();

            $employee = $employeeId ? Employees::find($employeeId) : null;
            if ($employeeId && ! $employee) {
                return response()->json(['error' => 'الموظف غير موجود'], 404);
            }

            $records = Attendance::when($employee, function ($q) use ($employee) {
                $q->where('user_id', $employee->user_id);
            })
                ->whereBetween('date', [
                    $from->toDateString(),
                    $to->toDateString()
                ])
                ->orderBy('date')
                ->get();

            $employeesData = [];
            if (! $employee && $records->isNotEmpty()) {
                $employeesData = Employees::whereIn('user_id', $records->pluck('user_id')->unique())
                    ->get()
                    ->keyBy('user_id')
                    ->toArray();
            }

            return view('reports.attendance.preview', compact(
                'employee',
                'employeesData',
                'dateFrom',
                'dateTo',
                'records'
            ));
        } catch (\Exception $e) {
            Log::error('preview error', ['msg' => $e->getMessage()]);
            return response()->json([
                'error'   => true,
                'message' => 'خطأ أثناء إنشاء المعاينة'
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Download PDF (باستخدام mPDF مع تجنب المشاكل)
    |--------------------------------------------------------------------------
   */
    public function downloadPDF(Request $request)
    {
        try {
            Log::info('== بدء downloadPDF ==');

            // 1) اجلب المعطيات
            $employeeId = $request->get('employee_id');
            $dateFrom   = $request->get('date_from') ?: Carbon::now()->startOfMonth()->toDateString();
            $dateTo     = $request->get('date_to')   ?: Carbon::now()->endOfMonth()->toDateString();

            $from = Carbon::parse($dateFrom)->toDateString();
            $to   = Carbon::parse($dateTo)->toDateString();

            // 2) الموظف (اختياري)
            $employee = $employeeId ? Employees::find($employeeId) : null;
            if ($employeeId && ! $employee) {
                return response()->json(['error' => 'الموظف غير موجود'], 404);
            }

            // 3) جلب السجلات بناءً على حقل date مع علاقة نوع الإجازة
            $records = Attendance::with('leaveType')
                ->when($employee, fn($q) => $q->where('user_id', $employee->user_id))
                ->whereBetween('date', [$from, $to])
                ->orderBy('date')
                ->get();

            // 4) تحضير lookup للموظفين إذا لزم الأمر
            $employeesData = [];
            if (! $employee && $records->isNotEmpty()) {
                $employeesData = Employees::whereIn('user_id', $records->pluck('user_id')->unique())
                    ->get()->keyBy('user_id')->toArray();
            }

            // 5) جلب معلومات الإجازات للموظفين
            $leaveInfo = [];
            foreach ($records as $record) {
                if ($record->day_status == 'leave') {
                    // استخدام العلاقة المحملة لنوع الإجازة
                    if ($record->leaveType) {
                        $leaveType = $record->leaveType->name;
                    } else {
                        // fallback للطريقة القديمة إذا لم يكن هناك نوع إجازة محفوظ
                        $leaveType = DB::table('leave_requests')
                            ->join('settings_leave_types', 'leave_requests.leave_type_id', '=', 'settings_leave_types.id')
                            ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
                            ->where('employees.user_id', $record->user_id)
                            ->where('leave_requests.status', 'approved')
                            ->whereDate('leave_requests.start_date', '<=', $record->date)
                            ->whereDate('leave_requests.end_date', '>=', $record->date)
                            ->value('settings_leave_types.name');
                    }

                    $leaveInfo[$record->user_id . '_' . $record->date] = $leaveType ?: 'إجازة';
                }
            }

            // 6) احسب الإحصائيات
            $totalWorkMinutes     = 0;
            $totalOvertimeMinutes = 0;

            foreach ($records as $r) {
                if ($r->check_in_time && $r->check_out_time) {
                    $diff = Carbon::parse($r->check_out_time)
                        ->diffInMinutes(Carbon::parse($r->check_in_time));
                    $totalWorkMinutes += $diff;
                }
                $totalOvertimeMinutes += $r->overtime_minutes;
            }

            // مساعدة لتنسيق الدقائق إلى hh:mm
            $fmt = fn(int $m) => sprintf('%02d:%02d', intdiv(max(0, $m), 60), max(0, $m) % 60);

            // 7) إعداد mPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode'              => 'utf-8',
                'format'            => 'A4',
                'orientation'       => 'L',
                'margin_left'       => 0,
                'margin_right'      => 0,
                'margin_top'        => 0,
                'margin_bottom'     => 10,
                'margin_header'     => 0,
                'margin_footer'     => 0,
                'default_font'      => 'almarai',
                'fontDir'           => array_merge(
                    (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'],
                    [public_path('fonts/Almarai')]
                ),
                'fontdata'          => array_merge(
                    (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'],
                    ['almarai' => [
                        'R' => 'Almarai-Regular.ttf',
                        'B' => 'Almarai-Bold.ttf',
                        'useOTL'    => 0xFF,
                        'useKashida' => 75,
                    ]]
                ),
                'default_font_size' => 12,
                'tempDir'           => storage_path('app/public/temp'),
            ]);
            $mpdf->SetDirectionality('rtl');
            $mpdf->SetHTMLHeader('');
            $mpdf->SetTopMargin(0);

            // 8) CSS - تصميم محسن مع حجم مناسب لعمود الحالة
            $css = <<<CSS
@page {
    margin: 20px;
    padding-top: 10px;
}

body {
    font-family: almarai;
    font-size: 12px;
    direction: rtl;
    margin: 20px;
    padding: 0;
    color: #333;
    line-height: 1.5;
}

.content {
    margin: 0 20px;
}

.report-header {
    margin: 15px 0 15px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.report-header h2 {
    margin: 0 0 5px 0;
    font-size: 20px;
    font-weight: 700;
    color: #333;
}

.date-range {
    font-size: 14px;
    color: #666;
    display: block;
    margin-top: 4px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    border: 1px solid #ddd;
    font-size: 10px;
}

.data-table th, .data-table td {
    border: 1px solid #ddd;
    padding: 5px 3px;
    text-align: center;
    font-size: 10px;
}

.data-table th {
    background-color: #444;
    color: #fff;
    font-weight: 500;
    white-space: nowrap;
}

/* عمود الإجازة */
.data-table th:nth-child(7),
.data-table td:nth-child(7) {
    width: 80px;
    font-weight: 600;
}

/* عمود الحالة - حجم مناسب */
.data-table th:nth-child(13),
.data-table td:nth-child(13) {
    width: 75px;
    font-size: 9px;
}

.data-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

.late {
    color: #e74c3c;
    font-weight: 500;
}

.early-arrival {
    color: #3498db;
    font-weight: 500;
}

.early-leave {
    color: #f39c12;
    font-weight: 500;
}

.overtime {
    color: #2ecc71;
    font-weight: 500;
}

.absent {
    color: #9b59b6;
    font-weight: bold;
    background-color: #fcfcfc;
}

.leave {
    color: #3498db;
    font-weight: bold;
    background-color: #e8f4f8;
}

.leave-type {
    color: #2c3e50;
    font-weight: 600;
    background-color: #f0f8ff;
}

.remote-work {
    color: #16a085;
    font-weight: 600;
    background-color: #e8f5f5;
}

.total-row {
    background-color: #f5f5f5;
    font-weight: bold;
}

.total-row td {
    border-top: 2px solid #444;
    padding: 8px 5px;
    font-size: 12px;
}

.total-value {
    font-size: 13px;
    font-weight: bold;
}

.work-hours {
    color: #3498db;
}

.overtime-hours {
    color: #2ecc71;
}

/* تنسيقات الحالات - مختصرة */
.status-missing-checkout {
    color: #e74c3c;
    font-weight: 600;
    font-size: 9px;
}

.status-insufficient-hours {
    color: #f39c12;
    font-weight: 600;
    font-size: 9px;
}

.status-present {
    color: #27ae60;
    font-weight: 600;
}

.status-remote-work {
    color: #16a085;
    font-weight: 600;
}

.report-footer {
    margin-top: 20px;
    font-size: 12px;
    color: #777;
    text-align: center;
    padding-top: 10px;
}

.no-records {
    text-align: center;
    padding: 20px;
    color: #777;
    font-style: italic;
}
CSS;
            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

            // 9) بنية HTML
            $html = '<div class="content">';
            $html .= '<div class="report-header">';
            $html .= '<h2>تقرير حضور | ' . ($employee ? e($employee->name) : 'جميع الموظفين') . '</h2>';
            $html .= '<span class="date-range">الفترة: ' . $dateFrom . ' إلى ' . $dateTo . '</span>';
            $html .= '</div>';

            // تحديد عدد الأعمدة حسب نوع التقرير
            $colSpan = !$employee ? 13 : 12;
            $html .= '<table class="data-table"><thead><tr>';

            if (!$employee) {
                $html .= '<th>متسلسل</th>';
            }

            // ترتيب الأعمدة مع وضع الإجازة بعد الدخول والخروج
            $html .= '<th>التاريخ</th><th>اسم الموظف</th><th>رقم الموظف</th>'
                . '<th>دخول</th><th>خروج</th>'
                . '<th>إجازة</th>'
                . '<th>تأخير</th>'
                . '<th>حضور مبكر</th><th>خروج مبكر</th>'
                . '<th>إضافي</th><th>وقت العمل</th><th>الحالة</th>'
                . '</tr></thead><tbody>';

            if ($records->isEmpty()) {
                $html .= '<tr><td colspan="' . $colSpan . '">لا توجد سجلات خلال الفترة</td></tr>';
            } else {
                $serialNumber = 1;
                foreach ($records as $r) {
                    $dateLabel = Carbon::parse($r->date)->format('Y/m/d');
                    $in  = $r->check_in_time  ? Carbon::parse($r->check_in_time)->format('H:i:s')  : '—';
                    $out = $r->check_out_time ? Carbon::parse($r->check_out_time)->format('H:i:s') : '—';
                    $late = $fmt($r->late_minutes);
                    $earlyIn  = $fmt($r->early_arrival_minutes);
                    $earlyOut = $fmt($r->early_leave_minutes);
                    $ot = $fmt($r->overtime_minutes);

                    // حساب مدة العمل - عرض الوقت الفعلي فقط
                    $workTimeDisplay = '—';
                    if ($r->check_in_time && $r->check_out_time) {
                        $dur = Carbon::parse($r->check_out_time)
                            ->diffInMinutes(Carbon::parse($r->check_in_time));
                        $workTimeDisplay = $fmt($dur);
                    }

                    // جلب نوع الإجازة
                    $leaveType = '—';
                    $leaveClass = '';
                    if ($r->day_status == 'leave') {
                        $leaveKey = $r->user_id . '_' . $r->date;
                        $leaveType = $leaveInfo[$leaveKey] ?? 'إجازة';

                        // تحديد فئة CSS مناسبة لنوع الإجازة
                        if (stripos($leaveType, 'عن بعد') !== false || stripos($leaveType, 'عمل عن بعد') !== false) {
                            $leaveClass = 'remote-work';
                        } else {
                            $leaveClass = 'leave-type';
                        }
                    }

                    // تحديد الحالة مع نصوص مختصرة
                    $statusLabel = '';
                    $statusClass = '';

                    if ($r->day_status == 'present') {
                        if ($r->check_in_time && !$r->check_out_time) {
                            $statusLabel = 'توقيع الخروج مفقود';
                            $statusClass = 'status-missing-checkout';
                        } elseif ($r->check_in_time && $r->check_out_time) {
                            // حساب مدة الدوام المطلوبة
                            $requiredMinutes = Carbon::parse($r->scheduled_start_time)
                                ->diffInMinutes(Carbon::parse($r->scheduled_end_time));

                            $actualMinutes = Carbon::parse($r->check_out_time)
                                ->diffInMinutes(Carbon::parse($r->check_in_time));

                            if ($actualMinutes < $requiredMinutes) {
                                $statusLabel = 'وقت العمل غير كافي';
                                $statusClass = 'status-insufficient-hours';
                            } else {
                                $statusLabel = 'حضور';
                                $statusClass = 'status-present';
                            }
                        } else {
                            $statusLabel = 'حضور';
                            $statusClass = 'status-present';
                        }
                    } else if ($r->day_status == 'leave') {
                        // إذا كانت إجازة، تحقق من النوع
                        $leaveKey = $r->user_id . '_' . $r->date;
                        $leaveTypeForStatus = $leaveInfo[$leaveKey] ?? 'إجازة';

                        if (stripos($leaveTypeForStatus, 'عن بعد') !== false || stripos($leaveTypeForStatus, 'عمل عن بعد') !== false) {
                            $statusLabel = $leaveTypeForStatus;
                            $statusClass = 'status-remote-work';
                        } else {
                            $statusLabel = 'إجازة';
                            $statusClass = 'leave';
                        }
                    } else {
                        $statusLabel = match ($r->day_status) {
                            'absent'  => 'غياب',
                            default   => '—',
                        };
                        $statusClass = $r->day_status;
                    }

                    $cls = $r->day_status;

                    $html .= "<tr class=\"{$cls}\">";

                    if (!$employee) {
                        $html .= "<td>{$serialNumber}</td>";
                        $serialNumber++;
                    }

                    $html .= "<td>{$dateLabel}</td>"
                        . "<td>" . ($employee
                            ? e($employee->name)
                            : e($employeesData[$r->user_id]['name'] ?? '---')) . "</td>"
                        . "<td>" . ($employee
                            ? $employee->id
                            : ($employeesData[$r->user_id]['id'] ?? $r->user_id)) . "</td>"
                        . "<td>{$in}</td><td>{$out}</td>"
                        . "<td class=\"{$leaveClass}\">{$leaveType}</td>" // عمود الإجازة
                        . "<td>{$late}</td><td>{$earlyIn}</td><td>{$earlyOut}</td>"
                        . "<td>{$ot}</td>"
                        . "<td class=\"work-hours\"><strong>{$workTimeDisplay}</strong></td>"
                        . "<td class=\"{$statusClass}\">{$statusLabel}</td>"
                        . "</tr>";
                }

                $totalColSpan = !$employee ? 10 : 9;
                $html .= '<tr class="total-row">'
                    . '<td colspan="' . $totalColSpan . '"><strong>الإجمالي</strong></td>'
                    . "<td>{$fmt($totalOvertimeMinutes)}</td>"
                    . "<td>{$fmt($totalWorkMinutes)}</td>"
                    . '<td></td></tr>';
            }

            $html .= '</tbody></table>';
            $html .= '<div class="report-footer">'
                . 'تم الإنشاء في ' . now()->format('Y/m/d H:i:s')
                . '</div></div>';

            // 10) إخراج PDF
            $mpdf->WriteHTML($html);
            $fileName = 'تقرير_حضور_' .
                ($employee ? str_replace(' ', '_', $employee->name) : 'جميع_الموظفين') .
                '_' . now()->format('Y_m_d') . '.pdf';

            return response()->streamDownload(
                fn() => print($mpdf->Output('', 'S')),
                $fileName,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            Log::error('downloadPDF error', ['msg' => $e->getMessage()]);
            return response()->json(['error' => true, 'message' => 'خطأ أثناء التصدير'], 500);
        }
    }

    /* --------------------------------------------------------------------------
     | حساب أيام العمل
     |--------------------------------------------------------------------------*/
    protected function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        while ($start->lte($end)) {
            if (!in_array($start->dayOfWeek, [5, 6])) {  // جمعة وسبت
                $days++;
            }
            $start->addDay();
        }
        return $days;
    }
}
