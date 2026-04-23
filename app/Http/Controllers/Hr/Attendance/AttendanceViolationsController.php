<?php

namespace App\Http\Controllers\Hr\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Attendance\StoreAttendanceViolationRequest;
use App\Http\Requests\Hr\ViolationsPenalties\StoreViolationsPenalties;
use App\Models\ElectronicServices\UserViolation;
use App\Models\general_setting\SettingsViolation;
use App\Models\general_setting\SettingsViolationCategory;
use App\Models\Hr\Attendance\Attendance;
use App\Models\User;
use App\Services\HR\ViolationsPenalties\ViolationPenaltyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceViolationsController extends Controller
{

    public function __construct(private ViolationPenaltyService $penaltyService)
    {
        $this->penaltyService = $penaltyService;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Violations
    |--------------------------------------------------------------------------
    */
    public function getViolations(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'violation_type' => 'required|in:delay,early_leave,absence,after_hours',
            'duration' => 'required|numeric',
            'duration_unit' => 'required|in:minutes,hours,days',
        ]);

        $attendance = Attendance::with('user')->findOrFail($request->attendance_id);
        $violationType = $request->violation_type;
        $duration = $request->duration;
        $durationUnit = $request->duration_unit;

        // استراتيجية البحث عن المخالفات:
        // 1. البحث أولاً عن المخالفات المطابقة تماماً بنفس الوحدة
        $exactMatchQuery = SettingsViolation::where('violation_type', $violationType)
            ->where('status', 'active')
            ->where('duration_unit', $durationUnit)
            ->where(function ($q) use ($duration) {
                $q->where('duration_from', '<=', $duration)
                    ->where(function ($subq) use ($duration) {
                        $subq->whereNull('duration_to')
                            ->orWhere('duration_to', '>=', $duration);
                    });
            });

        $exactMatches = $exactMatchQuery->with('category')->get();

        $violations = collect();

        // إضافة المخالفات المطابقة تماماً
        $violations = $violations->merge($exactMatches);

        // 2. إذا كان النوع after_hours والوحدة دقائق والمدة ≥ 60، نبحث أيضاً عن المخالفات بالساعات
        if ($violationType === 'after_hours' && $durationUnit === 'minutes' && $duration >= 60) {
            $hourBasedDuration = ceil($duration / 60);

            $hourBasedQuery = SettingsViolation::where('violation_type', $violationType)
                ->where('status', 'active')
                ->where('duration_unit', 'hours')
                ->where(function ($q) use ($hourBasedDuration) {
                    $q->where('duration_from', '<=', $hourBasedDuration)
                        ->where(function ($subq) use ($hourBasedDuration) {
                            $subq->whereNull('duration_to')
                                ->orWhere('duration_to', '>=', $hourBasedDuration);
                        });
                });

            $hourBasedMatches = $hourBasedQuery->with('category')->get();
            $violations = $violations->merge($hourBasedMatches);
        }

        // تكرار نفس المنطق للتأخير والخروج المبكر إذا كان ذلك مناسباً

        // إذا لم نجد أي مخالفات
        if ($violations->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'لم يتم العثور على مخالفات تنطبق على هذه المدة والنوع',
                'attendance' => $attendance,
                'violations' => []
            ]);
        }

        // حساب عدد مرات تكرار كل مخالفة للموظف
        $userId = $attendance->user_id;
        $violationsWithOccurrence = $violations->map(function ($violation) use ($userId) {
            $occurrence = UserViolation::where('user_id', $userId)
                ->where('settings_violation_id', $violation->id)
                ->where('status', '!=', 'cancelled')
                ->count() + 1;

            $violation->occurrence = $occurrence;

            // الحصول على العقوبة غير المنسقة
            $rawPenalty = $this->penaltyService->calculate($violation, $occurrence);

            // تنسيق العقوبة مباشرة
            $violation->applicable_penalty = SettingsViolation::formatPenalty($rawPenalty);

            // إضافة معلومات أكثر للعرض
            $violation->formatted_time_range = $this->formatTimeRange($violation);

            return $violation;
        });

        return response()->json([
            'success' => true,
            'attendance' => $attendance,
            'violations' => $violationsWithOccurrence,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Violation
    |--------------------------------------------------------------------------
    */
    public function store(StoreAttendanceViolationRequest $request)
    {
        // التحقق من وجود حقول الحضور الإضافية الضرورية
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
        ]);

        $attendance = Attendance::with('user')->findOrFail($request->attendance_id);
        // هنا نقوم بتجهيز البيانات المطلوبة للطلب StoreViolationsPenalties
        $data = $request->validated();
        // إذا لم يحدد المستخدم user_id، سنستخدم user_id من الحضور
        $data['user_id'] = $data['user_id'] ?? $attendance->user_id;

        // تحديد تاريخ المخالفة بناء على تاريخ الحضور إذا لم يكن محددًا
        $data['violation_date'] = $data['violation_date'] ?? Carbon::parse($attendance->date)->format('Y-m-d') . ' ' . now()->format('H:i:s');

        try {
            // استخدام السيرفس لإنشاء المخالفة
            $notes = $request->notes ?? 'تم إنشاء المخالفة من نظام الحضور والغياب';

            $userViolation = $this->penaltyService->createViolation(
                $data,
                $notes,
                $attendance->id
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل المخالفة بنجاح',
                'violation' => $userViolation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'pendingViolationId' => $e->getCode() ?: null
            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على نطاق المدة منسقاً بشكل مقروء
    |--------------------------------------------------------------------------
    */
    private function formatTimeRange($violation)
    {
        $unit = $violation->duration_unit;
        $from = $violation->duration_from;
        $to = $violation->duration_to;

        $unitMap = [
            'minutes' => 'دقيقة',
            'hours' => 'ساعة',
            'days' => 'يوم'
        ];

        $unitLabel = $unitMap[$unit] ?? $unit;

        if ($to) {
            return "من {$from} إلى {$to} {$unitLabel}";
        } else {
            return "أكثر من {$from} {$unitLabel}";
        }
    }
}
