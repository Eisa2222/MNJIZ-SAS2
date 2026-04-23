<?php

namespace App\Services\ApprovalWorkflow;

use App\Models\ApprovalSystem\{ApprovalRequest, ApprovalLog, ApprovalRequestLevel};
use App\Models\SystemAdministration\CredentialsManagement\ApprovalFlow;
use App\Models\Hr\Employees\Employees;
use App\Services\ElectronicServices\Custody\Log\CustodyLogService;
use App\Services\ElectronicServices\LeaveRequests\LeaveBalanceManagement\LeaveBalanceManagementService;
use App\Services\OrganizationCenter\Tasks\Task\TaskService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService implements ApprovalWorkflowInterface
{

    public function __construct(private TaskService $taskService) {}

    /*
    |--------------------------------------------------------------------------
    | إنشاء طلب اعتماد جديد
    |--------------------------------------------------------------------------
    */
    public function createApprovalRequest(Model $model, string $flowType, int $requestedByUserId): ?ApprovalRequest
    {
        try {
            $flow = ApprovalFlow::where('type', $flowType)->first();

            if (!$flow) {
                $this->approveDirectly($model);
                return null;
            }

            $activeLevels = $flow->levels()->whereNotNull('employee_id')->orderBy('level')->get();
            if ($activeLevels->isEmpty()) {
                $this->approveDirectly($model);
                return null;
            }

            return DB::transaction(function () use ($model, $flow, $requestedByUserId, $activeLevels, $flowType) {
                $approvalRequest = ApprovalRequest::create([
                    'approval_flow_id'      => $flow->id,
                    'approvable_type'       => get_class($model),
                    'approvable_id'         => $model->id,
                    'current_level'         => 1,
                    'status'                => ApprovalRequest::STATUS_PENDING,
                    'requested_by_user_id'  => $requestedByUserId,
                ]);

                foreach ($activeLevels as $index => $level) {
                    ApprovalRequestLevel::create([
                        'approval_request_id'   => $approvalRequest->id,
                        'level'                 => $index + 1,
                        'employee_id'           => $level->employee_id,
                    ]);
                }

                //////////////////////////////////////////////////////////////////////
                $firstLevelApprover     = $activeLevels->first();
                $creatorEmployee        = Employees::where('user_id', $requestedByUserId)->first();

                if (
                    $firstLevelApprover && $creatorEmployee &&
                    $firstLevelApprover->employee_id !== $creatorEmployee->id
                ) {
                    $this->taskService->createApprovalTask($flowType, $model, $firstLevelApprover->employee->user->id);
                }
                //////////////////////////////////////////////////////////////

                $this->updateModelStatus($model, 'pending');

                return $approvalRequest;
            });
        } catch (\Exception $e) {
            return null;
        }
    }



    /*
    |--------------------------------------------------------------------------
    | اعتماد المستوى الحالي
    |--------------------------------------------------------------------------
    */
    public function approve(int $requestId, int $employeeId): array
    {
        try {
            return DB::transaction(function () use ($requestId, $employeeId) {

                $request = ApprovalRequest::with(['requestLevels.employee', 'approvable'])->findOrFail($requestId);

                $model = $request->approvable;
                Log::error($model);

                if (!$this->canApprove($requestId, $employeeId)) {
                    return ['success' => false, 'message' => 'ليس لديك الصلاحية لاعتماد هذا الطلب.'];
                }

                if (!$request->isPending()) {
                    return ['success' => false, 'message' => 'هذا الطلب تم التعامل معه بالفعل.'];
                }

                $employee = Employees::find($employeeId);
                if (!$employee) {
                    return ['success' => false, 'message' => 'لا يمكن العثور على بيانات الموظف.'];
                }

                $flowType = $request->flow->type ?? '';

                $requiresSignature = in_array($flowType, ['offer', 'contract']);

                if ($requiresSignature && !$employee->signature) {
                    return ['success' => false, 'message' => 'لا يمكن الاعتماد. يرجى إضافة توقيعك أولاً من ملفك الشخصي.'];
                }

                ApprovalLog::create([
                    'approval_request_id'   => $request->id,
                    'level'                 => $request->current_level,
                    'employee_id'           => $employeeId,
                    'action'                => ApprovalLog::ACTION_APPROVED,
                    'signature_path'        => $employee->signature,
                    'additional_data'       => [
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'timestamp'     => now()->toISOString(),
                    ]
                ]);

                $nextLevel = $request->requestLevels()
                    ->where('level', $request->current_level + 1)
                    ->first();

                if ($nextLevel) {
                    $request->update(['current_level' => $request->current_level + 1]);
                    $nextApprover = $nextLevel->employee;
                    $isCompleted = false;

                    //  إرسال مهمة للمعتمد التالي
                    $this->taskService->createApprovalTask($flowType, $model, $nextLevel->employee->user->id);
                } else {
                    $request->update([
                        'status' => ApprovalRequest::STATUS_APPROVED,
                        'completed_at' => now(),
                    ]);
                    $this->updateModelStatus($request->approvable, 'approved');


                    /**
                     * This section is executed after the final approval of specific components.
                     *
                     * It handles post-approval logic, ensuring that all necessary actions are performed
                     * once the designated parts have received their final authorization.
                     *
                     * هذه الشيفرة تنفذ بعد الانتهاء من الاعتماد النهائي لجزئيات معينة،
                     * وتضمن تنفيذ الإجراءات اللازمة بعد الموافقة النهائية.
                     */
                    $this->executePostApprovalActions($request->approvable, $request->flow->type);


                    $nextApprover = null;
                    $isCompleted = true;
                }


                return [
                    'success' => true,
                    'message' => 'تم اعتماد الطلب بنجاح.',
                    'next_approver' => $nextApprover ? [
                        'id' => $nextApprover->id,
                        'name' => $nextApprover->name,
                        'user_id' => $nextApprover->user_id,
                    ] : null,
                    'is_completed' => $isCompleted,
                    'current_level' => $request->current_level,
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'حدث خطأ أثناء معالجة الطلب.'];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | رفض الطلب
    |--------------------------------------------------------------------------
    */
    public function reject(int $requestId, int $employeeId, string $reason): array
    {
        try {
            return DB::transaction(function () use ($requestId, $employeeId, $reason) {
                $request = ApprovalRequest::with(['approvable'])->findOrFail($requestId);

                if (!$this->canApprove($requestId, $employeeId)) {
                    return ['success' => false, 'message' => 'ليس لديك الصلاحية لرفض هذا الطلب.'];
                }

                if (!$request->isPending()) {
                    return ['success' => false, 'message' => 'هذا الطلب تم التعامل معه بالفعل.'];
                }

                ApprovalLog::create([
                    'approval_request_id' => $request->id,
                    'level' => $request->current_level,
                    'employee_id' => $employeeId,
                    'action' => ApprovalLog::ACTION_REJECTED,
                    'reason' => $reason,
                    'additional_data' => [
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'timestamp' => now()->toISOString(),
                    ]
                ]);

                $request->update([
                    'status' => ApprovalRequest::STATUS_REJECTED,
                    'completed_at' => now(),
                ]);

                $this->updateModelStatus($request->approvable, 'rejected');

                return [
                    'success' => true,
                    'message' => 'تم رفض الطلب بنجاح.',
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'حدث خطأ أثناء معالجة الطلب.'];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | إلغاء الاعتماد
    |--------------------------------------------------------------------------
    */
    public function revoke(int $requestId, int $employeeId): array
    {
        try {
            return DB::transaction(function () use ($requestId, $employeeId) {
                $request = ApprovalRequest::with(['logs', 'requestLevels'])->findOrFail($requestId);

                if (! $this->canRevoke($requestId, $employeeId)) {
                    return ['success' => false, 'message' => 'لا يمكنك إلغاء الاعتماد.'];
                }

                $levels = $request->requestLevels()->pluck('level')->all();
                $logs   = $request->logs()->orderBy('created_at', 'desc')->get();

                $lastActions = [];
                foreach ($levels as $lvl) {
                    $lastLog = $logs->firstWhere('level', $lvl);
                    $lastActions[$lvl] = $lastLog?->action;
                }

                $approvedLevels = array_keys(
                    array_filter($lastActions, fn($action) => $action === ApprovalLog::ACTION_APPROVED)
                );

                if (empty($approvedLevels)) {
                    return ['success' => false, 'message' => 'لا يوجد اعتماد لإلغائه.'];
                }

                $levelToRevoke = max($approvedLevels);

                ApprovalLog::create([
                    'approval_request_id' => $request->id,
                    'level'               => $levelToRevoke,
                    'employee_id'         => $employeeId,
                    'action'              => ApprovalLog::ACTION_REVOKED,
                    'additional_data'     => [
                        'revoked_approval_id' => null,
                        'ip_address'          => request()->ip(),
                        'user_agent'          => request()->userAgent(),
                        'timestamp'           => now()->toISOString(),
                    ]
                ]);

                $request->update([
                    'status'        => ApprovalRequest::STATUS_PENDING,
                    'current_level' => $levelToRevoke,
                    'completed_at'  => null,
                ]);

                $this->updateModelStatus($request->approvable, 'pending');

                $this->executePostRevokeActions($request->approvable, $request->flow->type);

                return ['success' => true, 'message' => 'تم إلغاء الاعتماد بنجاح.'];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'حدث خطأ أثناء إلغاء الاعتماد.'];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | التحقق من صلاحية المعتمد للاعتماد
    |--------------------------------------------------------------------------
    */
    public function canApprove(int $requestId, int $employeeId): bool
    {
        try {
            $request = ApprovalRequest::with(['requestLevels'])->find($requestId);
            if (!$request || !$request->isPending()) {
                return false;
            }

            $currentLevel = $request->requestLevels()
                ->where('level', $request->current_level)
                ->first();

            return $currentLevel && (int)$currentLevel->employee_id === (int)$employeeId;
        } catch (\Exception $e) {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | التحقق من إمكانية الإلغاء
    |--------------------------------------------------------------------------
    */
    public function canRevoke(int $requestId, int $employeeId): bool
    {
        try {
            $request = ApprovalRequest::with('logs')->find($requestId);
            if (!$request || $request->status === 'rejected') {
                return false;
            }

            // 🔧 العثور على آخر مستوى معتمد فعلياً
            $actualHighestApprovedLevel = null;
            $levels = $request->requestLevels()->orderBy('level')->get();

            foreach ($levels as $level) {
                $lastLog = $request->logs()
                    ->where('level', $level->level)
                    ->orderBy('created_at', 'desc')
                    ->first();

                // إذا كان آخر إجراء هو approved
                if ($lastLog && $lastLog->action === 'approved') {
                    $actualHighestApprovedLevel = $level->level;
                }
            }

            if (!$actualHighestApprovedLevel) {
                return false;
            }

            // فحص إذا كان الموظف الحالي هو من اعتمد آخر مستوى
            $lastApprovalLog = $request->logs()
                ->where('level', $actualHighestApprovedLevel)
                ->where('action', 'approved')
                ->orderBy('created_at', 'desc')
                ->first();

            return $lastApprovalLog && (int)$lastApprovalLog->employee_id === (int)$employeeId;
        } catch (\Exception $e) {
            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على حالة الاعتماد للنموذج
    |--------------------------------------------------------------------------
    */
    public function getApprovalStatus(Model $model): array
    {
        $request = $model->approvalRequest;

        if (!$request) {
            return [
                'status' => 'approved',
                'status_label' => 'معتمد',
                'has_approval_process' => false,
                'completion_percentage' => 100,
                'next_approver' => null,
                'current_level' => 0,
                'total_levels' => 0,
            ];
        }

        return [
            'status' => $request->status,
            'status_label' => $this->getStatusLabel($request->status),
            'has_approval_process' => true,
            'current_level' => $request->current_level,
            'total_levels' => $request->requestLevels()->count(),
            'completion_percentage' => $request->getCompletionPercentage(),
            'next_approver' => $request->isPending() ? $request->getCurrentLevelApprover()?->name : null,
            'requested_by' => $request->requestedBy?->name,
            'requested_at' => $request->created_at?->format('d/m/Y H:i'),
            'completed_at' => $request->completed_at?->format('d/m/Y H:i'),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على معلومات مراحل الاعتماد
    |--------------------------------------------------------------------------
    */
    public function getApprovalStagesInfo(Model $model): array
    {
        $request = $model->approvalRequest;

        if (!$request) {
            return [];
        }

        return $request->getApprovalStages();
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على المعتمد التالي
    |--------------------------------------------------------------------------
    */
    public function getNextApprover(int $requestId): ?array
    {
        try {
            $request = ApprovalRequest::with(['requestLevels.employee'])->find($requestId);

            if (!$request || !$request->isPending()) {
                return null;
            }

            $currentApprover = $request->getCurrentLevelApprover();

            if (!$currentApprover) {
                return null;
            }

            return [
                'id' => $currentApprover->id,
                'name' => $currentApprover->name,
                'level' => $request->current_level,
                'user_id' => $currentApprover->user_id,
                'email' => $currentApprover->email ?? null,
                'phone' => $currentApprover->phone ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على إحصائيات الاعتماد
    |--------------------------------------------------------------------------
    */
    public function getApprovalStatistics(string $flowType): array
    {
        $flow = ApprovalFlow::where('type', $flowType)->first();

        if (!$flow) {
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ];
        }

        $baseQuery = ApprovalRequest::where('approval_flow_id', $flow->id);

        return [
            'total' => $baseQuery->count(),
            'pending' => (clone $baseQuery)->where('status', ApprovalRequest::STATUS_PENDING)->count(),
            'approved' => (clone $baseQuery)->where('status', ApprovalRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $baseQuery)->where('status', ApprovalRequest::STATUS_REJECTED)->count(),
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | الحصول على الاعتمادات المعلقة للموظف
    |--------------------------------------------------------------------------
    */
    public function getPendingApprovalsForEmployee(int $employeeId, ?string $flowType = null): Collection
    {
        $query = ApprovalRequest::with(['approvable', 'flow', 'requestLevels'])
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->whereHas('requestLevels', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->whereColumn('current_level', '=', function ($subQuery) use ($employeeId) {
                $subQuery->select('level')
                    ->from('approval_request_levels')
                    ->whereColumn('approval_request_id', 'approval_requests.id')
                    ->where('employee_id', $employeeId);
            });

        if ($flowType) {
            $query->whereHas('flow', function ($q) use ($flowType) {
                $q->where('type', $flowType);
            });
        }

        return $query->orderBy('created_at', 'asc')->get();
    }



    /*
    |--------------------------------------------------------------------------
    | العلاقة العامة مع طلب الاعتماد (Polymorphic).
    |--------------------------------------------------------------------------
    */
    private function approveDirectly(Model $model): void
    {
        $this->updateModelStatus($model, 'approved');
    }


    /*
    |--------------------------------------------------------------------------
    | تحديث حالة النموذج
    |--------------------------------------------------------------------------
    */
    private function updateModelStatus(Model $model, string $status): void
    {
        try {
            if (method_exists($model, 'getFillable') && in_array('status', $model->getFillable())) {
                $model->update(['status' => $status]);
            }
        } catch (\Exception $e) {
        }
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على تسمية الحالة
    |--------------------------------------------------------------------------
    */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'قيد الانتظار',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            default => $status,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | تنفيذ الإجراءات بعد الاعتماد النهائي لبعض المكونات
    |--------------------------------------------------------------------------
    */
    private function executePostApprovalActions(Model $model, string $flowType): void
    {
        try {
            $actions = [
                'custody' => [
                    'service' => CustodyLogService::class,
                    'method' => 'log',
                    'param' => $model->id
                ],
                'leave' => [
                    'service' => LeaveBalanceManagementService::class,
                    'method' => 'handleApproval',
                    'param' => $model->id
                ]
            ];

            if (isset($actions[$flowType])) {
                $action = $actions[$flowType];
                $service = app($action['service']);
                $service->{$action['method']}($action['param']);
            }
        } catch (\Exception $e) {
        }
    }

    /*
    |--------------------------------------------------------------------------
    | تنفيذ الإجراءات بعد إلغاء الاعتماد النهائي لبعض المكونات
    |--------------------------------------------------------------------------
    */
    private function executePostRevokeActions(Model $model, string $flowType): void
    {
        try {
            $revokeActions = [
                'custody' => [
                    'service' => CustodyLogService::class,
                    'method' => 'reverseLog',
                    'param' => $model->id
                ],
                'leave' => [
                    'service' => LeaveBalanceManagementService::class,
                    'method' => 'handleApprovalReversal',
                    'param' => $model->id
                ]
            ];

            if (isset($revokeActions[$flowType])) {
                $action = $revokeActions[$flowType];
                $service = app($action['service']);
                $service->{$action['method']}($action['param']);
            }
        } catch (\Exception $e) {
        }
    }
}
