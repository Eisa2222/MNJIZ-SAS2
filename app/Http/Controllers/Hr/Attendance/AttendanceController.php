<?php

namespace App\Http\Controllers\Hr\Attendance;

use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Attendance\UpdateAttendanceRequest;
use App\Models\Fingerprint;
use App\Models\general_setting\SettingsViolation;
use App\Models\general_setting\SettingsViolationCategory;
use App\Models\Hr\Attendance\Attendance;
use App\Models\Hr\Attendance\AttendanceLog;
use App\Models\Hr\Employees\Employees;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Models\User;
use App\Services\BioStationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    protected $bioStationService;


    /*
    |--------------------------------------------------------------------------
    | max distance in meters
    |--------------------------------------------------------------------------
    */
    const MAX_DISTANCE_METERS = 500; // أقصى مسافة مسموح بها بالمتر

    /*
    |--------------------------------------------------------------------------
    | constructor of this controller
    |--------------------------------------------------------------------------
    */
    public function __construct(BioStationService $bioStationService)
    {
        $this->bioStationService = $bioStationService;

        $this->middleware('can:الحضور والانصراف');
    }

    /*
    |--------------------------------------------------------------------------
    | index of attendance
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $year       = now()->year;
        $month      = now()->month;
        $monthName  = $this->months[$month];

        /* إحصاءات الشهر الحالى */
        $base    = Attendance::whereYear('date', $year)->whereMonth('date', $month);
        $present = (clone $base)->where('day_status', 'present')->count();
        $leave   = (clone $base)->where('day_status', 'leave')->count();
        $absent  = (clone $base)->where('day_status', 'absent')->count();
        $total   = max(1, $present + $leave + $absent);

        $percentPresent = round($present * 100 / $total, 1);
        $percentLeave   = round($leave   * 100 / $total, 1);
        $percentAbsent  = round($absent  * 100 / $total, 1);

        /* خيارات القوائم مُسبقة التكوين */
        $monthOptions = collect($this->months)->map(function ($name, $num) use ($month) {
            $selected = $num == $month ? ' selected' : '';
            return "<option value=\"{$num}\"{$selected}>{$name}</option>";
        })->implode('');

        $roleOptions = Role::select('id', 'name')->get()->map(function ($r) {
            return "<option value=\"{$r->id}\">{$r->name}</option>";
        })->implode('');

        $employeeOptions = Employees::active()->select('id', 'name', 'nickname')
            ->orderBy('name')->get()->map(function ($e) {
                return "<option value=\"{$e->id}\">{$e->name}</option>";
            })->implode('');

        return view('hr.attendance.index', [
            'currentMonth'      => $month,
            'currentMonthName'  => $monthName,
            'percentPresent'    => $percentPresent,
            'percentLeave'      => $percentLeave,
            'percentAbsent'     => $percentAbsent,
            'monthOptions'      => new HtmlString($monthOptions),
            'roleOptions'       => new HtmlString($roleOptions),
            'employeeOptions'   => new HtmlString($employeeOptions),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get attendance data for display in DataTables
    |--------------------------------------------------------------------------
    | الحصول على بيانات الحضور والانصراف للعرض في DataTables.
    */
    public function getData(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $year  = now()->year;
        $month = $request->month ?: now()->month;

        $query = Attendance::with('user')
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        // تعديل الفلتر هنا ليكون حسب حالة الحضور بدلا من حالة الموظف
        if ($request->filled('status')) {
            $query->where('day_status', $request->status);
        }

        if ($request->filled('employee')) $query->where('user_id', $request->employee);

        if ($request->filled('has_updates')) {
            if ($request->has_updates === 'yes') {
                $query->whereHas('logs');
            } else if ($request->has_updates === 'no') {
                $query->whereDoesntHave('logs');
            }
        }
        $present = (clone $query)->where('day_status', 'present')->count();
        $leave   = (clone $query)->where('day_status', 'leave')->count();
        $absent  = (clone $query)->where('day_status', 'absent')->count();
        $total   = max(1, $present + $leave + $absent);

        $stats = [
            'present_pct' => round($present * 100 / $total, 1),
            'leave_pct'   => round($leave   * 100 / $total, 1),
            'absent_pct'  => round($absent  * 100 / $total, 1),
        ];

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', function ($row) {
                return $row->user ? $row->user->name : '—';
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->addColumn('date', fn($r) => $r->date->format('Y-m-d'))
            ->addColumn('status', fn($r) => ['present' => 'حضور', 'leave' => 'إجازة', 'absent' => 'غياب'][$r->day_status] ?? '—')
            ->addColumn('check_in_time', fn($r) => $r->check_in_time?->format('H:i:s') ?? '—')
            ->addColumn('check_out_time', fn($r) => $r->check_out_time?->format('H:i:s') ?? '—')
            ->addColumn('working_hours', function ($r) {
                if ($r->check_in_time && $r->check_out_time) {
                    $m = Carbon::parse($r->check_out_time)->diffInMinutes(Carbon::parse($r->check_in_time));
                    return floor($m / 60) . ' س و ' . ($m % 60) . ' د';
                }
                return '—';
            })
            ->addColumn('has_logs', function ($row) {
                $logsCount = AttendanceLog::where('attendance_id', $row->id)->count();
                if ($logsCount > 0) {
                    return $logsCount;
                }
                return 0;
            })
            /*
            |--------------------------------------------------------------------------
            | إضافة عمود جديد للمخالفات المحتملة
            |--------------------------------------------------------------------------
            | عرض حالة المخالفات المحتملة بشكل منفصل ومميز
            */
            ->addColumn('violations', function ($row) {

                $hasViolation = $row->late_minutes > 0 ||
                    $row->early_leave_minutes > 0 ||
                    $row->day_status == 'absent' ||
                    $row->overtime_minutes > 0;


                if (!$hasViolation) {
                    return null;
                }


                $violationType = null;
                $durationUnit = 'minutes';
                $duration = 0;

                if ($row->late_minutes > 0) {
                    $violationType = 'delay';
                    $duration = $row->late_minutes;
                } elseif ($row->early_leave_minutes > 0) {
                    $violationType = 'early_leave';
                    $duration = $row->early_leave_minutes;
                } elseif ($row->day_status == 'absent') {
                    $violationType = 'absence';
                    $durationUnit = 'days';
                    $duration = 1;
                } elseif ($row->overtime_minutes > 0) {
                    $violationType = 'after_hours';
                    $duration = $row->overtime_minutes;


                    if ($duration >= 60) {
                        $durationUnit = 'hours';
                        $duration = ceil($duration / 60);
                    }
                }
                $applicableViolations = $this->hasApplicableViolations($violationType, $duration, $durationUnit);

                if (!$applicableViolations) {
                    return null;
                }

                $text = '';
                $color = '';

                switch ($violationType) {
                    case 'delay':
                        $text = 'تأخير';
                        $color = 'warning';
                        break;
                    case 'early_leave':
                        $text = 'خروج مبكر';
                        $color = 'danger';
                        break;
                    case 'absence':
                        $text = 'غياب';
                        $color = 'dark';
                        break;
                    case 'after_hours':
                        $text = 'بعد الدوام';
                        $color = 'info';
                        break;
                }

                $displayUnit = ($durationUnit === 'minutes') ? 'دقيقة' : (($durationUnit === 'hours') ? 'ساعة' : 'يوم');

                return [
                    'type' => $violationType,
                    'text' => $text,
                    'color' => $color,
                    'url' => route('attendances.check-violation', $row->id),
                    'details' => "$duration $displayUnit"
                ];
            })
            ->addColumn('actions', function ($row) {
                $editBtn = '';
                $logsBtn = '';

                // فحص صلاحية التعديل على الحضور
                // if (auth()->user()->can('تعديل الحضور والانصراف')) {
                $editBtn = '<a href="' . route('attendances.edit', $row->id) . '" class="btn btn-sm btn-secondry me-1" title="تعديل"><i class="ti ti-edit"></i></a>';
                // }

                // فحص صلاحية عرض سجل التعديلات
                // if (auth()->user()->can('عرض سجل تعديلات الحضور')) {
                $logsBtn = '<a href="' . route('attendances.logs', $row->id) . '" class="btn btn-sm btn-secondry" title="سجل التعديلات"><i class="ti ti-eye"></i></a>';
                // }

                return $editBtn . $logsBtn;
            })
            ->rawColumns(['actions'])
            ->with('stats', $stats)
            ->toJson();
    }

    /*
    |--------------------------------------------------------------------------
    | Edit attendance
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);

        return view('hr.attendance.edit', [
            'attendance' => $attendance,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update attendance
    |--------------------------------------------------------------------------
    */
    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validated = $request->validated();

        // تخزين البيانات القديمة قبل التعديل
        $oldData = [
            'check_in_time'         => $attendance->check_in_time,
            'check_out_time'        => $attendance->check_out_time,
            'day_status'            => $attendance->day_status,
            'early_arrival_minutes' => $attendance->early_arrival_minutes,
            'late_minutes'          => $attendance->late_minutes,
            'early_leave_minutes'   => $attendance->early_leave_minutes,
            'overtime_minutes'      => $attendance->overtime_minutes,
        ];

        // تحديث حالة اليوم أولاً
        $attendance->day_status = $validated['day_status'];

        // إذا كانت الحالة حضور (present)، نتعامل مع أوقات الدخول والخروج
        if ($validated['day_status'] === 'present') {
            $inCarbon = Carbon::createFromFormat('H:i', $validated['check_in_time']);
            $outCarbon = Carbon::createFromFormat('H:i', $validated['check_out_time']);

            $attendance->check_in_time = $inCarbon->format('H:i:s');
            $attendance->check_out_time = $outCarbon->format('H:i:s');

            $scheduledStart = Carbon::parse($attendance->scheduled_start_time);
            $scheduledEnd = Carbon::parse($attendance->scheduled_end_time);

            // احتساب دقائق التأخير / الحضور المبكر
            if ($inCarbon->lt($scheduledStart)) {
                $attendance->early_arrival_minutes = $scheduledStart->diffInMinutes($inCarbon);
                $attendance->late_minutes = 0;
            } else {
                $attendance->late_minutes = $inCarbon->diffInMinutes($scheduledStart);
                $attendance->early_arrival_minutes = 0;
            }

            // احتساب دقائق الخروج المبكر / العمل الإضافي
            if ($outCarbon->lt($scheduledEnd)) {
                $attendance->early_leave_minutes = $scheduledEnd->diffInMinutes($outCarbon);
                $attendance->overtime_minutes = 0;
            } else {
                $attendance->overtime_minutes = $outCarbon->diffInMinutes($scheduledEnd);
                $attendance->early_leave_minutes = 0;
            }
        }
        // إذا كانت الحالة غياب (absent) أو إجازة (leave)، فنقوم بتفريغ كل الحقول المتعلقة بالوقت
        else {
            $attendance->check_in_time = null;
            $attendance->check_out_time = null;
            $attendance->early_arrival_minutes = 0;
            $attendance->late_minutes = 0;
            $attendance->early_leave_minutes = 0;
            $attendance->overtime_minutes = 0;
        }

        // حفظ التغييرات
        $attendance->save();

        // تسجيل عملية التعديل
        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $attendance->user_id,
            'editor_id'     => Auth::id(),

            'old_data' => [
                'check_in_time'         => optional($oldData['check_in_time'])->format('H:i:s'),
                'check_out_time'        => optional($oldData['check_out_time'])->format('H:i:s'),
                'day_status'            => $oldData['day_status'],
                'early_arrival_minutes' => $oldData['early_arrival_minutes'],
                'late_minutes'          => $oldData['late_minutes'],
                'early_leave_minutes'   => $oldData['early_leave_minutes'],
                'overtime_minutes'      => $oldData['overtime_minutes'],
            ],

            'new_data' => [
                'check_in_time'         => optional($attendance->check_in_time)->format('H:i:s'),
                'check_out_time'        => optional($attendance->check_out_time)->format('H:i:s'),
                'day_status'            => $attendance->day_status,
                'early_arrival_minutes' => $attendance->early_arrival_minutes,
                'late_minutes'          => $attendance->late_minutes,
                'early_leave_minutes'   => $attendance->early_leave_minutes,
                'overtime_minutes'      => $attendance->overtime_minutes,
            ],

            'edit_reason' => $validated['edit_reason'],
            'edited_at'   => now(),
        ]);

        return redirect()
            ->route('attendances.index')
            ->with('success', 'تم تحديث بيانات الحضور بنجاح');
    }

    /*
    |--------------------------------------------------------------------------
    | Show attendance logs
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);
        $logs = AttendanceLog::with('user')
            ->where('attendance_id', $id)
            ->orderBy('edited_at', 'desc')
            ->get();

        return view('hr.attendance.show', [
            'attendance' => $attendance,
            'logs' => $logs,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Check in function
    |--------------------------------------------------------------------------
    | تسجيل الحضور
    */
    public function check_in(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return back()->with('error', 'المستخدم غير مسجل الدخول.');
        }

        $today = Carbon::today()->toDateString();
        $now   = Carbon::now();

        // منع التسجيل المسبق
        if (Attendance::where('user_id', $user->id)->where('date', $today)->exists()) {
            return back()->with('error', 'لقد سجلت الحضور اليوم بالفعل.');
        }

        $scheduledStart = Carbon::parse(SettingsHelper::get('work_start_time'));
        if ($now->lt($scheduledStart)) {
            return back()->with('error', 'لا يمكنك تسجيل الحضور قبل بدء الدوام.');
        }

        $lat = $request->latitude;
        $lng = $request->longitude;
        if (! $lat || ! $lng) {
            return back()->with('error', 'الموقع الجغرافي مطلوب.');
        }

        $companyLat = SettingsHelper::get('company_latitude');
        $companyLng = SettingsHelper::get('company_longitude');
        if ($this->calculateDistance($lat, $lng, $companyLat, $companyLng) > self::MAX_DISTANCE_METERS) {
            return back()->with('error', 'أنت خارج نطاق الشركة.');
        }

        Attendance::create([
            'user_id'               => $user->id,
            'date'                  => $today,
            'check_in_time'         => $now->format('H:i:s'),
            'latitude'              => $lat,
            'longitude'             => $lng,
            'scheduled_start_time'  => SettingsHelper::get('work_start_time'),
            'scheduled_end_time'    => SettingsHelper::get('work_end_time'),
            'late_minutes'          => max(0, $now->diffInMinutes($scheduledStart)),
            'early_arrival_minutes' => 0,
            'early_leave_minutes'   => 0,
            'overtime_minutes'      => 0,
            'day_status'            => 'present',
        ]);

        return back()->with('success', 'تم تسجيل الحضور.');
    }

    /*
    |--------------------------------------------------------------------------
    | Check out function
    |--------------------------------------------------------------------------
    | تسجيل الانصراف
    */
    public function check_out(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return back()->with('error', 'المستخدم غير مسجل الدخول.');
        }

        $today = Carbon::today()->toDateString();
        $now   = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $attendance) {
            return back()->with('error', 'لم تسجل حضوراً اليوم.');
        }
        if ($attendance->check_out_time) {
            return back()->with('error', 'لقد سجلت الانصراف بالفعل.');
        }

        $scheduledEnd = Carbon::parse($attendance->scheduled_end_time);

        $lat = $request->latitude;
        $lng = $request->longitude;
        if (! $lat || ! $lng) {
            return back()->with('error', 'الموقع الجغرافي مطلوب.');
        }
        $companyLat = SettingsHelper::get('company_latitude');
        $companyLng = SettingsHelper::get('company_longitude');
        if ($this->calculateDistance($lat, $lng, $companyLat, $companyLng) > self::MAX_DISTANCE_METERS) {
            return back()->with('error', 'أنت خارج نطاق الشركة.');
        }

        $earlyLeave = max(0, $scheduledEnd->diffInMinutes($now));
        $overtime   = max(0, $now->diffInMinutes($scheduledEnd));

        $attendance->update([
            'check_out_time'       => $now->format('H:i:s'),
            'check_out_latitude'   => $lat,
            'check_out_longitude'  => $lng,
            'early_leave_minutes'  => $earlyLeave,
            'overtime_minutes'     => $overtime,
            'day_status'           => 'present',
        ]);

        return back()->with('success', 'تم تسجيل الانصراف.');
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate distance between two geographical points
    |--------------------------------------------------------------------------
    | دالة لحساب المسافة بين نقطتين جغرافيتين .
    */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000;
        $latFrom     = deg2rad($lat1);
        $lonFrom     = deg2rad($lng1);
        $latTo       = deg2rad($lat2);
        $lonTo       = deg2rad($lng2);
        $latDelta    = $latTo - $latFrom;
        $lonDelta    = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) *
                pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    private function formatMinutes($mins): string
    {
        return floor($mins / 60) . ' س و ' . ($mins % 60) . ' د';
    }

    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    private array $months = [
        1 => 'يناير',
        2 => 'فبراير',
        3 => 'مارس',
        4 => 'أبريل',
        5 => 'مايو',
        6 => 'يونيو',
        7 => 'يوليو',
        8 => 'أغسطس',
        9 => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر',
    ];

    /*
    |--------------------------------------------------------------------------
    | تحليل فترة التأخير/المغادرة المبكرة وتحديد نوع المخالفة
    |--------------------------------------------------------------------------
    */
    public function checkViolation($id)
    {
        $attendance = Attendance::findOrFail($id);
        $violationType = null;
        $duration = 0;
        $durationUnit = 'minutes';

        // تحديد نوع المخالفة: غياب
        if ($attendance->day_status === 'absent') {
            $violationType = 'absence';
            $durationUnit = 'days';
            $duration = 1; // يوم واحد
        }
        // تحديد نوع المخالفة: تأخير
        elseif ($attendance->late_minutes > 0) {
            $violationType = 'delay';
            $duration = $attendance->late_minutes;
            $durationUnit = 'minutes';
        }
        // تحديد نوع المخالفة: مغادرة مبكرة
        elseif ($attendance->early_leave_minutes > 0) {
            $violationType = 'early_leave';
            $duration = $attendance->early_leave_minutes;
            $durationUnit = 'minutes';
        }
        // إذا كان هناك وقت إضافي بعد الدوام
        elseif ($attendance->overtime_minutes > 0) {
            $violationType = 'after_hours';
            $duration = $attendance->overtime_minutes;
            $durationUnit = 'minutes';
        }

        return view('hr.attendance.violations.check_violation', compact('attendance', 'violationType', 'duration', 'durationUnit'));
    }


    /*
    |--------------------------------------------------------------------------
    | التحقق من وجود مخالفات قابلة للتطبيق على حالة معينة
    |--------------------------------------------------------------------------
    */
    private function hasApplicableViolations($violationType, $duration, $durationUnit)
    {
        // لا نقوم بأي تحويل تلقائي للوحدات، بل نبحث عن المخالفات المطابقة مباشرة
        $query = SettingsViolation::where('violation_type', $violationType)
            ->where('status', 'active');

        // 1. البحث المطابق تماماً حسب وحدة المدة
        $exactMatchQuery = (clone $query)
            ->where('duration_unit', $durationUnit)
            ->where(function ($q) use ($duration) {
                $q->where('duration_from', '<=', $duration)
                    ->where(function ($subq) use ($duration) {
                        $subq->whereNull('duration_to')
                            ->orWhere('duration_to', '>=', $duration);
                    });
            });

        // إذا وجدنا مخالفات مطابقة بنفس وحدة المدة
        if ($exactMatchQuery->count() > 0) {
            return true;
        }

        // 2. حالة خاصة للبقاء بعد الدوام (تحويل من دقائق إلى ساعات)
        if ($violationType === 'after_hours' && $durationUnit === 'minutes' && $duration >= 60) {
            // تحويل الدقائق إلى ساعات للمقارنة مع المخالفات المعرفة بالساعات
            $durationInHours = ceil($duration / 60);

            $hourBasedQuery = (clone $query)
                ->where('duration_unit', 'hours')
                ->where(function ($q) use ($durationInHours) {
                    $q->where('duration_from', '<=', $durationInHours)
                        ->where(function ($subq) use ($durationInHours) {
                            $subq->whereNull('duration_to')
                                ->orWhere('duration_to', '>=', $durationInHours);
                        });
                });

            if ($hourBasedQuery->count() > 0) {
                return true;
            }
        }

        // 3. حالة خاصة للتأخير (تحويل من دقائق إلى ساعات إذا كان لديك مخالفات تأخير بالساعات)
        if ($violationType === 'delay' && $durationUnit === 'minutes' && $duration >= 60) {
            // تحويل الدقائق إلى ساعات للمقارنة مع المخالفات المعرفة بالساعات
            $durationInHours = ceil($duration / 60);

            $hourBasedQuery = (clone $query)
                ->where('duration_unit', 'hours')
                ->where(function ($q) use ($durationInHours) {
                    $q->where('duration_from', '<=', $durationInHours)
                        ->where(function ($subq) use ($durationInHours) {
                            $subq->whereNull('duration_to')
                                ->orWhere('duration_to', '>=', $durationInHours);
                        });
                });

            if ($hourBasedQuery->count() > 0) {
                return true;
            }
        }

        // 4. حالة خاصة للخروج المبكر (مماثلة للتأخير)
        if ($violationType === 'early_leave' && $durationUnit === 'minutes' && $duration >= 60) {
            // تحويل الدقائق إلى ساعات
            $durationInHours = ceil($duration / 60);

            $hourBasedQuery = (clone $query)
                ->where('duration_unit', 'hours')
                ->where(function ($q) use ($durationInHours) {
                    $q->where('duration_from', '<=', $durationInHours)
                        ->where(function ($subq) use ($durationInHours) {
                            $subq->whereNull('duration_to')
                                ->orWhere('duration_to', '>=', $durationInHours);
                        });
                });

            if ($hourBasedQuery->count() > 0) {
                return true;
            }
        }
        return false;
    }
}
