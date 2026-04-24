<?php

namespace App\Services\ElectronicServices\LeaveRequests\Request;

use App\Enums\ElectronicServices\LeaveRequests\LeaveRequestsStatus;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequestAttachment;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\LeaveBalance;
use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Services\ApprovalWorkflow\ApprovalWorkflowService;
use App\Services\LeaveRequestPolicyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeLeaveRequestService
{
    public function __construct(private ApprovalWorkflowService $approvalWorkflowService)
    {
        $this->approvalWorkflowService = $approvalWorkflowService;
    }

    /**
     * حساب عدد الأيام مع مراعاة أيام العطلة الأسبوعية
     * بنفس منطق JavaScript تماماً
     */
    private function calculateWorkingDays(Carbon $startDate, Carbon $endDate, bool $countWeekends): float
    {
        // إذا كان يحسب العطل أو كان يوماً واحداً، نحسب جميع الأيام
        if ($countWeekends || $startDate->isSameDay($endDate)) {
            return $startDate->diffInDays($endDate) + 1;
        }

        // جلب أيام العطلة من الإعدادات - نفس طريقة JavaScript
        $settings = Settings::current();
        $weeklyDaysOff = [];

        if ($settings && isset($settings->weekly_days_off)) {
            if (is_string($settings->weekly_days_off)) {
                $weeklyDaysOff = json_decode($settings->weekly_days_off, true) ?: [];
            } elseif (is_array($settings->weekly_days_off)) {
                $weeklyDaysOff = $settings->weekly_days_off;
            }
        }

        // إذا لم توجد أيام عطلة محددة، نحسب جميع الأيام
        if (empty($weeklyDaysOff)) {
            return $startDate->diffInDays($endDate) + 1;
        }

        // تحويل أسماء الأيام إلى أرقام Carbon - مطابق تماماً للـ JavaScript
        $dayNameToNumber = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $weekendNumbers = [];
        foreach ($weeklyDaysOff as $dayName) {
            if (isset($dayNameToNumber[$dayName])) {
                $weekendNumbers[] = $dayNameToNumber[$dayName];
            }
        }

        // حساب أيام العمل (استبعاد أيام العطلة) - نفس منطق JavaScript
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // إذا لم يكن اليوم الحالي يوم عطلة، نحسبه
            if (!in_array($currentDate->dayOfWeek, $weekendNumbers)) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /*
    |--------------------------------------------------------------------------
    | Index Statistics and Filters
    |--------------------------------------------------------------------------
    | Prepare statistics and filter data for the leave requests index page.
    */
    public function getIndexData()
    {
        try {
            // Statistics
            $statusCounts = LeaveRequest::where('employee_id', Auth::user()->employee->id)
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalRequests = array_sum($statusCounts);
            $pendingRequests = $statusCounts[LeaveRequestsStatus::Pending->value] ?? 0;
            $approvedRequests = $statusCounts[LeaveRequestsStatus::Approved->value] ?? 0;
            $rejectedRequests = $statusCounts[LeaveRequestsStatus::Rejected->value] ?? 0;

            $employeeGender = auth()->user()->employee->gender ?? null;

            // Filters
            $requestStatus = LeaveRequestsStatus::options();
            $leaveTypes = SettingsLeaveType::where(function ($query) use ($employeeGender) {
                if ($employeeGender === 'male') {
                    $query->where('gender_applicability', 'both');
                } else {
                    $query->where('gender_applicability', 'both')
                        ->orWhere('gender_applicability', 'female');
                }
            })->where('is_global', false)->orderBy('id', 'desc')->get(['id', 'name']);

            return [
                'statistics' => [
                    'totalRequests' => $totalRequests,
                    'pendingRequests' => $pendingRequests,
                    'approvedRequests' => $approvedRequests,
                    'rejectedRequests' => $rejectedRequests,
                ],
                'filters' => [
                    'requestStatus' => $requestStatus,
                    'leaveTypes' => $leaveTypes,
                ]
            ];
        } catch (\Exception $e) {
            Log::error($e);
            throw new \Exception('حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Form Data
    |--------------------------------------------------------------------------
    | Prepare data needed for the create leave request form.
    */
    public function getCreateFormData()
    {
        $employeeGender = auth()->user()->employee->gender ?? null;

        $leaveTypes = SettingsLeaveType::select('id', 'name', 'leave_unit_type', 'count_weekends')
            ->where(function ($query) use ($employeeGender) {
                if ($employeeGender === 'male') {
                    $query->where('gender_applicability', 'both');
                } else {
                    $query->where('gender_applicability', 'both')
                        ->orWhere('gender_applicability', 'female');
                }
            })
            ->where('status', 'active')
            ->where('is_global', false)
            ->orderBy('id', 'desc')
            ->get();

        // جلب أيام العطلة الأسبوعية من الإعدادات
        $settings = Settings::current();
        $weeklyDaysOff = [];
        if ($settings && isset($settings->weekly_days_off)) {
            if (is_string($settings->weekly_days_off)) {
                $weeklyDaysOff = json_decode($settings->weekly_days_off, true) ?: [];
            } elseif (is_array($settings->weekly_days_off)) {
                $weeklyDaysOff = $settings->weekly_days_off;
            }
        }

        return [
            'leaveTypes' => $leaveTypes,
            'weeklyDaysOff' => $weeklyDaysOff
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Store Leave Request
    |--------------------------------------------------------------------------
    | Create a new leave request with all validations and business logic.
    */
    public function store(array $input, LeaveRequestPolicyService $policy)
    {
        $employee = auth()->user()->employee;
        $employeeId = $employee->id;
        $now = Carbon::now();
        $leaveType = SettingsLeaveType::findOrFail($input['leave_type_id']);

        try {
            DB::beginTransaction();

            /* 1. فحص السياسات */
            $policy->ensureNoPending($employeeId, $leaveType->id);
            $policy->ensureMaxRequests($employeeId, $leaveType);
            $policy->ensureMinServiceYears($leaveType, $now);
            $policy->ensureGenderApplicability($employee->gender, $leaveType);

            /* 2. تحديد الأيام وعددها - حساب مطابق تماماً للـ Frontend */
            $isHalfDay = $leaveType->leave_unit_type === 'half_day';

            if ($leaveType->has_attachments) {
                $policy->ensureAttachments($input['additional_attachments'] ?? [], $leaveType);
            }

            $start = Carbon::parse($input['start_date']);

            if ($isHalfDay) {
                $daysCount = 0.5;
                $end = $start->copy();
            } else {
                $end = Carbon::parse($input['end_date']);
                // استخدام نفس منطق JavaScript للحساب
                $daysCount = $this->calculateWorkingDays($start, $end, $leaveType->count_weekends);
            }

            /* 2.5. فحص الإشعار المسبق */
            $policy->ensureAdvanceNotice($leaveType, $start);

            /* 3. منع التداخل مع أي إجازة معتمدة سابقة */
            $policy->ensureNoOverlap($employeeId, $start, $end);

            /* 4. تحقق من الرصيد لكن لا تمنع الطلب */
            $insufficientBalance = false;
            $warningMessage = '';
            if ($leaveType->is_deductible) {
                $annualType = SettingsLeaveType::where('is_carry_forwardable', true)->firstOrFail();
                $balance = LeaveBalance::firstOrNew([
                    'employee_id' => $employeeId,
                    'leave_type_id' => $annualType->id,
                    'year' => $now->year,
                ]);
                if (!$balance->exists) {
                    $balance->total_days = 0;
                    $balance->used_days = 0;
                    $balance->remaining_days = 0;
                    $balance->last_updated_by = $employeeId;
                    $balance->save();
                }
                if ($daysCount > $balance->remaining_days) {
                    $insufficientBalance = true;
                    $warningMessage = "عدد الأيام المطلوبة ({$daysCount}) يتجاوز الرصيد المتبقي ({$balance->remaining_days}).";
                }
            }

            /* 5. إنشاء طلب الإجازة */
            $leaveReq = LeaveRequest::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveType->id,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'days_count' => $daysCount,
                'status' => 'pending',
                'reason' => $input['reason'] ?? null,
                'created_by' => $employeeId,
            ]);

            foreach ($input['additional_attachments'] ?? [] as $attach) {
                if (!empty($attach['file'])) {
                    $path = $attach['file']->store('leave_attachments', 'public');
                    LeaveRequestAttachment::create([
                        'leave_request_id' => $leaveReq->id,
                        'file_path' => $path,
                        'file_name' => $attach['name'] ?? $attach['file']->getClientOriginalName(),
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم تقديم الطلب بنجاح',
                'warning' => $insufficientBalance ? $warningMessage . ' الطلب مرفوع في انتظار موافقة الإدارة.' : null
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في إضافة طلب الإجازة', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Form Data
    |--------------------------------------------------------------------------
    | Prepare data needed for the edit leave request form.
    */
    public function getEditFormData($id)
    {
        $leaveRequest = LeaveRequest::with('attachments')->findOrFail($id);

        $employeeGender = auth()->user()->employee->gender ?? null;

        $leaveTypes = SettingsLeaveType::select('id', 'name', 'leave_unit_type', 'count_weekends')
            ->where(function ($query) use ($employeeGender) {
                if ($employeeGender === 'male') {
                    $query->where('gender_applicability', 'both');
                } else {
                    $query->where('gender_applicability', 'both')
                        ->orWhere('gender_applicability', 'female');
                }
            })
            ->where('status', 'active')
            ->where('is_global', false)
            ->orderBy('id', 'desc')
            ->get();

        // جلب أيام العطلة الأسبوعية من الإعدادات
        $settings = Settings::current();
        $weeklyDaysOff = [];
        if ($settings && isset($settings->weekly_days_off)) {
            if (is_string($settings->weekly_days_off)) {
                $weeklyDaysOff = json_decode($settings->weekly_days_off, true) ?: [];
            } elseif (is_array($settings->weekly_days_off)) {
                $weeklyDaysOff = $settings->weekly_days_off;
            }
        }

        return [
            'leaveRequest' => $leaveRequest,
            'leaveTypes' => $leaveTypes,
            'weeklyDaysOff' => $weeklyDaysOff
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Update Leave Request
    |--------------------------------------------------------------------------
    | Update an existing leave request with all validations and business logic.
    */
    public function update(array $input, $id, LeaveRequestPolicyService $policy)
    {
        $employee = auth()->user()->employee;
        $employeeId = $employee->id;
        $now = Carbon::now();

        $leaveReq = LeaveRequest::findOrFail($id);

        if (!$leaveReq->status->canEditOrDelete()) {
            throw new \Exception('لا يمكن تعديل هذا الطلب لأنه ' . $leaveReq->status->label() . '.');
        }

        $leaveType = SettingsLeaveType::findOrFail($input['leave_type_id']);

        try {
            DB::beginTransaction();

            $policy->ensureNoPending($employeeId, $leaveType->id, $id);
            $policy->ensureMaxRequests($employeeId, $leaveType, $id);
            $policy->ensureMinServiceYears($leaveType, $now);
            $policy->ensureGenderApplicability($employee->gender, $leaveType);

            /* تحديد الأيام وعددها - حساب مطابق تماماً للـ Frontend */
            $isHalfDay = $leaveType->leave_unit_type === 'half_day';

            $start = Carbon::parse($input['start_date']);

            if ($isHalfDay) {
                $daysCount = 0.5;
                $end = $start->copy();
            } else {
                $end = Carbon::parse($input['end_date']);
                // استخدام نفس منطق JavaScript للحساب
                $daysCount = $this->calculateWorkingDays($start, $end, $leaveType->count_weekends);
            }

            $policy->ensureAdvanceNotice($leaveType, $start);

            $policy->ensureNoOverlap($employeeId, $start, $end, $leaveReq->id);

            foreach ($input['remove_attachments'] ?? [] as $attId) {
                if ($att = LeaveRequestAttachment::find($attId)) {
                    Storage::disk('public')->delete($att->file_path);
                    $att->delete();
                }
            }

            $leaveReq->update([
                'leave_type_id' => $leaveType->id,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'days_count' => $daysCount,
                'status' => 'pending',
                'reason' => $input['reason'] ?? null,
                'updated_by' => $employeeId,
            ]);

            foreach ($input['additional_attachments'] ?? [] as $attach) {
                if (!empty($attach['file'])) {
                    $path = $attach['file']->store('leave_attachments', 'public');
                    LeaveRequestAttachment::create([
                        'leave_request_id' => $leaveReq->id,
                        'file_path' => $path,
                        'file_name' => $attach['name'] ?? $attach['file']->getClientOriginalName(),
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم تحديث طلب الإجازة بنجاح.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطأ في تحديث طلب الإجازة', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show Leave Request Details
    |--------------------------------------------------------------------------
    | Get detailed information for displaying a specific leave request.
    */
    public function getShowData($id)
    {
        $leaveRequest = LeaveRequest::with([
            'employee',
            'leaveType',
            'attachments',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee'
        ])->findOrFail($id);

        $statusInfo = $this->prepareStatusInfo($leaveRequest);

        $approvalStages = [];

        if ($leaveRequest->approvalRequest) {
            $approvalStages = $leaveRequest->approvalRequest->getApprovalStages();
        }

        return [
            'leaveRequest' => $leaveRequest,
            'statusInfo' => $statusInfo,
            'approvalStages' => $approvalStages
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare Status Information
    |--------------------------------------------------------------------------
    | Prepare status-related information for display.
    */
    private function prepareStatusInfo(LeaveRequest $leaveRequest): array
    {
        $statusInfo = [
            'isPending' => $leaveRequest->status === LeaveRequestsStatus::Pending,
            'isApproved' => $leaveRequest->status === LeaveRequestsStatus::Approved,
            'isRejected' => $leaveRequest->status === LeaveRequestsStatus::Rejected,
            'showStatusChange' => $leaveRequest->status !== LeaveRequestsStatus::Pending,
            'hasUpdates' => $leaveRequest->updated_by && $leaveRequest->updated_at != $leaveRequest->created_at,
        ];

        if ($statusInfo['isApproved']) {
            $statusInfo['statusMessage'] = 'تم اعتماد طلب الإجازة بنجاح';
            $statusInfo['statusIcon'] = 'ti-check-circle';
            $statusInfo['alertClass'] = 'alert-success';
        } elseif ($statusInfo['isRejected']) {
            $statusInfo['statusMessage'] = 'تم رفض طلب الإجازة';
            $statusInfo['statusIcon'] = 'ti-x-circle';
            $statusInfo['alertClass'] = 'alert-danger';
        }

        return $statusInfo;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Leave Request
    |--------------------------------------------------------------------------
    | Delete a leave request and its associated attachments.
    */
    public function delete($id)
    {
        $leaveReq = LeaveRequest::findOrFail($id);

        if (!$leaveReq->status->canEditOrDelete()) {
            throw new \Exception('لا يمكن حذف هذا الطلب لأنه ' . $leaveReq->status->label() . '.');
        }

        foreach ($leaveReq->attachments as $att) {
            if ($att->file_path && Storage::disk('public')->exists($att->file_path)) {
                Storage::disk('public')->delete($att->file_path);
            }
        }
        LeaveRequestAttachment::where('leave_request_id', $leaveReq->id)->delete();

        $leaveReq->delete();

        return [
            'success' => true,
            'message' => 'تم حذف طلب الإجازة بنجاح.'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Mass Delete Leave Requests
    |--------------------------------------------------------------------------
    | Delete multiple leave requests and their associated attachments.
    */
    public function massDelete(array $ids)
    {
        if (!$ids || !is_array($ids)) {
            return ['success' => false, 'message' => 'معرفات غير صحيحة'];
        }

        try {
            // فحص وجود طلبات معتمدة ضمن الطلبات المراد حذفها
            $approvedRequests = LeaveRequest::whereIn('id', $ids)
                ->where('status', 'approved')
                ->count();

            // إذا كان هناك طلبات معتمدة، نرفض عملية الحذف الجماعي
            if ($approvedRequests > 0) {
                return [
                    'success' => false,
                    'message' => 'لا يمكن حذف الطلبات المعتمدة. برجاء إلغاء تحديد الطلبات المعتمدة.'
                ];
            }

            // جلب الطلبات بعد التأكد من عدم وجود طلبات معتمدة بينها
            $leaveRequests = LeaveRequest::whereIn('id', $ids)->get();

            foreach ($leaveRequests as $leaveRequest) {
                foreach ($leaveRequest->attachments as $attach) {
                    if ($attach->file_path && Storage::disk('public')->exists($attach->file_path)) {
                        Storage::disk('public')->delete($attach->file_path);
                    }
                }
                LeaveRequestAttachment::where('leave_request_id', $leaveRequest->id)->delete();

                $leaveRequest->delete();
            }

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false];
        }
    }
}
