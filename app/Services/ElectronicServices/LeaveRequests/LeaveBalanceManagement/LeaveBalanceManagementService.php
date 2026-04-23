<?php

namespace App\Services\ElectronicServices\LeaveRequests\LeaveBalanceManagement;

use App\Contracts\ErrorHandlerInterface;
use App\Enums\ElectronicServices\LeaveRequests\LeaveRequestsStatus;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\LeaveBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeaveBalanceManagementService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    /*
    |--------------------------------------------------------------------------
    | Handle Approval Balance Deduction
    |--------------------------------------------------------------------------
    | Execute balance deduction logic when leave request is approved.
    */
    public function handleApproval(int $leaveRequestId): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($leaveRequestId) {
            $leaveRequest = LeaveRequest::with(['leaveType', 'employee.user'])->findOrFail($leaveRequestId);

            $leaveType = $leaveRequest->leaveType;
            if (!$leaveType || !$leaveType->is_deductible) {
                Log::info('Leave type is not deductible, skipping balance deduction.', [
                    'leave_request_id' => $leaveRequestId,
                    'leave_type_id' => $leaveType?->id,
                    'is_deductible' => $leaveType?->is_deductible ?? false
                ]);
                return;
            }

            Log::info('Deducting balance for approved leave request.', [
                'leave_request_id' => $leaveRequestId,
                'employee_id' => $leaveRequest->employee_id,
                'days_count' => $leaveRequest->days_count
            ]);

            $validUserId = $this->getUserIdFromEmployee($leaveRequest);
            $balance = $this->getOrCreateAnnualLeaveBalance(
                $leaveRequest->employee_id,
                $leaveRequest->created_at->year,
                $validUserId
            );

            if ($balance) {
                $balance->decrement('remaining_days', $leaveRequest->days_count);
                $balance->increment('used_days', $leaveRequest->days_count);
                $balance->last_updated_by = $validUserId;
                $balance->save();

                Log::info('Balance successfully deducted.', [
                    'leave_request_id' => $leaveRequestId,
                    'balance_id' => $balance->id,
                    'deducted_days' => $leaveRequest->days_count,
                    'remaining_days' => $balance->remaining_days
                ]);
            }
        }), 'حدث خطأ أثناء خصم رصيد الإجازة');
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Approval Reversal Balance Restoration
    |--------------------------------------------------------------------------
    | Execute balance restoration logic when approved request is revoked/rejected.
    */
    public function handleApprovalReversal(int $leaveRequestId): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($leaveRequestId) {
            $leaveRequest = LeaveRequest::with(['leaveType', 'employee.user'])->findOrFail($leaveRequestId);

            $leaveType = $leaveRequest->leaveType;
            if (!$leaveType || !$leaveType->is_deductible) {
                Log::info('Leave type is not deductible, skipping balance restoration.', [
                    'leave_request_id' => $leaveRequestId,
                    'leave_type_id' => $leaveType?->id,
                    'is_deductible' => $leaveType?->is_deductible ?? false
                ]);
                return;
            }

            Log::info('Restoring balance for revoked/rejected leave request.', [
                'leave_request_id' => $leaveRequestId,
                'employee_id' => $leaveRequest->employee_id,
                'days_count' => $leaveRequest->days_count
            ]);

            $validUserId = $this->getUserIdFromEmployee($leaveRequest);
            $balance = $this->getOrCreateAnnualLeaveBalance(
                $leaveRequest->employee_id,
                $leaveRequest->created_at->year,
                $validUserId
            );

            if ($balance) {
                $balance->increment('remaining_days', $leaveRequest->days_count);
                $balance->decrement('used_days', $leaveRequest->days_count);
                $balance->last_updated_by = $validUserId;
                $balance->save();

                Log::info('Balance successfully restored.', [
                    'leave_request_id' => $leaveRequestId,
                    'balance_id' => $balance->id,
                    'restored_days' => $leaveRequest->days_count,
                    'remaining_days' => $balance->remaining_days
                ]);
            }
        }), 'حدث خطأ أثناء إعادة رصيد الإجازة');
    }

    /*
    |--------------------------------------------------------------------------
    | Get User ID From Employee
    |--------------------------------------------------------------------------
    | Convert employee_id to user_id using the relationship with fallback options.
    */
    private function getUserIdFromEmployee(LeaveRequest $leaveRequest): int
    {
        $currentUserId = auth()->id();
        if ($currentUserId) {
            Log::debug('Using current authenticated user', ['user_id' => $currentUserId]);
            return $currentUserId;
        }

        if ($leaveRequest->created_by) {
            $creatorEmployee = \App\Models\Hr\Employees\Employees::with('user')
                ->find($leaveRequest->created_by);

            if ($creatorEmployee && $creatorEmployee->user_id) {
                Log::debug('Using creator employee user_id', [
                    'employee_id' => $creatorEmployee->id,
                    'user_id' => $creatorEmployee->user_id
                ]);
                return $creatorEmployee->user_id;
            }
        }

        if ($leaveRequest->updated_by) {
            $updaterEmployee = \App\Models\Hr\Employees\Employees::with('user')
                ->find($leaveRequest->updated_by);

            if ($updaterEmployee && $updaterEmployee->user_id) {
                Log::debug('Using updater employee user_id', [
                    'employee_id' => $updaterEmployee->id,
                    'user_id' => $updaterEmployee->user_id
                ]);
                return $updaterEmployee->user_id;
            }
        }

        if ($leaveRequest->employee && $leaveRequest->employee->user_id) {
            Log::debug('Using main employee user_id', [
                'employee_id' => $leaveRequest->employee->id,
                'user_id' => $leaveRequest->employee->user_id
            ]);
            return $leaveRequest->employee->user_id;
        }

        $adminUser = \App\Models\User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'super-admin', 'system-admin']);
        })->first();

        if ($adminUser) {
            Log::warning('Using system admin as fallback', [
                'admin_user_id' => $adminUser->id,
                'leave_request_id' => $leaveRequest->id
            ]);
            return $adminUser->id;
        }

        $firstUser = \App\Models\User::first();
        if ($firstUser) {
            Log::warning('Using first system user as last resort', [
                'first_user_id' => $firstUser->id,
                'leave_request_id' => $leaveRequest->id
            ]);
            return $firstUser->id;
        }

        Log::critical('No valid users found in system', [
            'leave_request_id' => $leaveRequest->id
        ]);
        throw new \Exception('خطأ نظام: لا يمكن العثور على مستخدم صالح لتحديث رصيد الإجازة');
    }

    /*
    |--------------------------------------------------------------------------
    | Get or Create Annual Leave Balance
    |--------------------------------------------------------------------------
    | Helper method to retrieve or create annual leave balance record.
    */
    private function getOrCreateAnnualLeaveBalance(int $employeeId, int $year, int $lastUpdatedBy): ?LeaveBalance
    {
        try {
            $annualLeaveType = SettingsLeaveType::where('is_carry_forwardable', true)->first();

            if (!$annualLeaveType) {
                Log::warning('No annual leave type found with is_carry_forwardable = true');
                return null;
            }

            $balance = LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $annualLeaveType->id,
                    'year' => $year,
                ],
                [
                    'total_days' => 0,
                    'used_days' => 0,
                    'remaining_days' => 0,
                    'last_updated_by' => $lastUpdatedBy,
                ]
            );

            return $balance;
        } catch (\Exception $e) {
            Log::error('Error retrieving/creating annual leave balance.', [
                'employee_id' => $employeeId,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            throw ValidationException::withMessages([
                'balance' => 'حدث خطأ في جلب أو إنشاء رصيد الإجازة السنوية'
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Balance Operation
    |--------------------------------------------------------------------------
    | Validate if balance operation can be performed for the leave request.
    */
    public function validateBalanceOperation(int $leaveRequestId, string $operation): bool
    {
        try {
            $leaveRequest = LeaveRequest::with('leaveType')->findOrFail($leaveRequestId);
            $leaveType = $leaveRequest->leaveType;

            if (!$leaveType || !$leaveType->is_deductible) {
                return false;
            }

            $annualLeaveType = SettingsLeaveType::where('is_carry_forwardable', true)->first();
            if (!$annualLeaveType) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error validating balance operation.', [
                'leave_request_id' => $leaveRequestId,
                'operation' => $operation,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
