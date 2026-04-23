<?php

namespace App\Services;

use App\Models\general_setting\SettingsLeaveType;
use App\Exceptions\PolicyValidationException;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use Carbon\Carbon;

class LeaveRequestPolicyService
{
    /*
    |--------------------------------------------------------------------------
    | Ensure No Pending
    |--------------------------------------------------------------------------
    | تأكد من عدم وجود طلب إجازة معلق من نفس النوع (باستثناء طلب معين عند التحديث).
    */
    public function ensureNoPending(int $employeeId, int $leaveTypeId, int $exceptRequestId = null): void
    {
        $query = LeaveRequest::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'pending')
            ->whereNull('deleted_at');

        if ($exceptRequestId) {
            $query->where('id', '!=', $exceptRequestId);
        }

        if ($query->exists()) {
            throw new PolicyValidationException('يوجد طلب إجازة معلق لهذا النوع بالفعل.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ensure Minimum Service Years
    |--------------------------------------------------------------------------
    | تحقق من استيفاء حد سنوات الخدمة الدنيا المحدد في الحقل min_service_years.
    */
    public function ensureMinServiceYears(SettingsLeaveType $leaveType, Carbon $now): void
    {
        $minYears = (int) $leaveType->min_service_years;
        if ($minYears > 0) {
            $contractDate = auth()->user()->employee->contract_start_date;
            $serviceYears = $contractDate
                ? Carbon::parse($contractDate)->diffInYears($now)
                : 0;

            if ($serviceYears < $minYears) {
                throw new PolicyValidationException("عذرًا، مدة خدمتك أقل من المطلوب ({$minYears} سنة) لتقديم طلب الإجازة.");
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ensure Gender Applicability
    |--------------------------------------------------------------------------
    | تحقق من أن جنس الموظف مسموح به بناءً على الحقل gender_applicability.
    */
    public function ensureGenderApplicability(?string $employeeGender, SettingsLeaveType $leaveType): void
    {
        if ($leaveType->gender_applicability === 'female' && $employeeGender !== 'female') {
            throw new PolicyValidationException('هذا النوع من الإجازة متاح للإناث فقط.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ensure Attachments
    |--------------------------------------------------------------------------
    | تحقق من أن الإجازة تتطلب مرفقات (has_attachments) وأن المستخدم قد أرفقها.
    | في حال عدم وجود المرفقات المطلوبة، تظهر رسالة مع الوصف من attachment_description.
    */
    public function ensureAttachments(array $uploadedFiles, SettingsLeaveType $leaveType): void
    {
        if ((bool)$leaveType->has_attachments) {
            if (empty($uploadedFiles)) {
                $desc = $leaveType->attachment_description ?: 'المرفقات المطلوبة';
                throw new PolicyValidationException("هذا النوع من الإجازة يتطلب إرفاق : {$desc}.");
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ensure Max Requests
    |--------------------------------------------------------------------------
    | تأكد من أن عدد الطلبات السابقة لهذا النوع لا يتجاوز الحد المسموح به.
    */
    public function ensureMaxRequests(int $employeeId, SettingsLeaveType $leaveType, int $exceptRequestId = null): void
    {
        $max = (int) $leaveType->max_requests;
        if ($max > 0) {
            $query = LeaveRequest::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveType->id)
                ->whereNull('deleted_at');

            if ($exceptRequestId) {
                $query->where('id', '!=', $exceptRequestId);
            }

            $count = $query->count();
            if ($count >= $max) {
                throw new PolicyValidationException("لا يمكنك طلب هذه الإجازة أكثر من مرة واحدة.");
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Default Days
    |--------------------------------------------------------------------------
    | احسب أيام الإجازة الافتراضية لأي نوع إجازة.
    */
    public function calculateDefaultDays(SettingsLeaveType $type, Carbon $now): int
    {
        // سنوات خدمة الموظف
        $contractDate = auth()->user()->employee->contract_start_date;
        $years        = $contractDate ? Carbon::parse($contractDate)->diffInYears($now) : 0;

        // إذا تجاوز العتبة المحددة لسنوات الخدمة
        if (
            $type->service_years_threshold > 0
            && $years >= $type->service_years_threshold
            && $type->days_after_threshold > 0
        ) {
            return (int)$type->days_after_threshold;
        }

        return (int)$type->days;
    }

    /**
     * --------------------------------------------------------------------------
     * Ensure No Overlapping Approved Leaves
     * --------------------------------------------------------------------------
     * تأكد من أن الفترة الجديدة [start, end] لا تتداخل مع أي إجازة معتمدة.
     */
    public function ensureNoOverlap(
        int $employeeId,
        Carbon $startDate,
        Carbon $endDate,
        int $exceptRequestId = null
    ): void {
        $query = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            // يغطي كل حالات التداخل:
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date',   [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date',   '>=', $endDate);
                    });
            });

        if ($exceptRequestId) {
            $query->where('id', '!=', $exceptRequestId);
        }

        if ($query->exists()) {
            throw new PolicyValidationException(
                "لا يمكن تقديم طلب إجازة يتداخل مع إجازة معتمدة سابقة."
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ensure Advance Notice
    |--------------------------------------------------------------------------
    | تأكد من أن الطلب مقدم قبل عدد الأيام المطلوبة كإشعار مسبق.
    */
    public function ensureAdvanceNotice(SettingsLeaveType $leaveType, Carbon $startDate): void
    {
        $advanceNoticeDays = (int) $leaveType->advance_notice_days;

        // إذا كان الإشعار المسبق صفر، لا نقوم بالتحقق
        if ($advanceNoticeDays === 0) {
            return;
        }

        $today = Carbon::today();
        $daysUntilLeave = $today->diffInDays($startDate, false); // false للحصول على رقم سالب إذا كان التاريخ في الماضي

        // تعليق فحص التاريخ السابق - للسماح بإدخال إجازات سابقة
        // إذا كان التاريخ في الماضي أو اليوم
        // if ($daysUntilLeave < 0) {
        //     throw new PolicyValidationException('لا يمكن تقديم طلب إجازة لتاريخ سابق أو اليوم الحالي.');
        // }

        // التحقق من الإشعار المسبق
        if ($daysUntilLeave < $advanceNoticeDays) {
            if ($advanceNoticeDays === 1) {
                throw new PolicyValidationException("يجب تقديم طلب الإجازة قبل يوم واحد على الأقل من تاريخ البداية.");
            } else {
                throw new PolicyValidationException("يجب تقديم طلب الإجازة قبل {$advanceNoticeDays} أيام على الأقل من تاريخ البداية.");
            }
        }
    }
}