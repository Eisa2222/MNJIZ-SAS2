<?php

namespace App\Http\Controllers\ApprovalWorkflow;

use App\DataTables\ApprovalWorkflow\UnifiedApprovalDataTable;
use App\Http\Controllers\Controller;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use App\Models\SystemAdministration\CredentialsManagement\ApprovalFlow;
use App\Models\Hr\Employees\Employees;
use App\Helpers\Helpers;
use App\Models\ApprovalSystem\ApprovalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class UnifiedApprovalController extends Controller
{
    protected ApprovalWorkflowInterface $approvalWorkflowService;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */
    public function __construct(ApprovalWorkflowInterface $approvalWorkflowService)
    {
        $this->approvalWorkflowService = $approvalWorkflowService;

        $this->middleware(function ($request, $next) {
            $employeeId = auth()->user()?->employee?->id;
            if (!$employeeId) {
                abort(403, 'لم يتم العثور على بيانات الموظف.');
            }

            $hasAnyAccess = false;
            $flowTypes = array_keys(ApprovalFlow::getAllTypeLabels());

            foreach ($flowTypes as $flowType) {
                if (Helpers::isAssignedToApprovalType($flowType, $employeeId)) {
                    $hasAnyAccess = true;
                    break;
                }
            }

            if (!$hasAnyAccess) {
                abort(403, 'ليس لديك الصلاحية للوصول إلى نظام الاعتمادات.');
            }

            return $next($request);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(UnifiedApprovalDataTable::class);

        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        return $dataTable->render('approval-workflow.unified.index', [
            'statistics' => $this->getPersonalizedStatistics(),
            'flowTypes' => $this->getMyFlowTypes(),
            'employees' => $this->getAllActiveEmployees(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */
    public function show(string $type, string $id): View
    {
        $validTypes = array_keys(ApprovalFlow::getAllTypeLabels());
        if (!in_array($type, $validTypes)) {
            abort(404, 'نوع الاعتماد غير صحيح.');
        }

        $employeeId = auth()->user()?->employee?->id;
        if (!Helpers::isAssignedToApprovalType($type, $employeeId)) {
            abort(403, 'ليس لديك الصلاحية لعرض هذا النوع من الاعتمادات.');
        }

        $item = $this->getItemByTypeAndId($type, $id);
        $approvalStatus = $this->approvalWorkflowService->getApprovalStatus($item);
        $approvalStages = $this->approvalWorkflowService->getApprovalStagesInfo($item);
        $approvalLogs = $this->getApprovalLogs($item);
        $userPermissions = $this->getCurrentUserPermissions($item);

        $viewName = $this->getShowViewName($type);

        return view($viewName, [
            'item' => $item,
            'type' => $type,
            'typeName' => ApprovalFlow::getAllTypeLabels()[$type],
            'approvalStatus' => $approvalStatus,
            'approvalStages' => $approvalStages,
            'approvalLogs' => $approvalLogs,
            'userPermissions' => $userPermissions,
            'processedContent' => $this->getProcessedContent($item, $type),
            'routeBaseName' => $this->getRouteBaseName($type),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */
    public function approve(Request $request, string $type, string $id): JsonResponse
    {
        return $this->performApprovalAction($type, $id, 'approve');
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */
    public function reject(Request $request, string $type, string $id): JsonResponse
    {
        $this->validateRejectReason($request);
        return $this->performApprovalAction($type, $id, 'reject', $request->reason);
    }

    /*
    |--------------------------------------------------------------------------
    | Revoke
    |--------------------------------------------------------------------------
    */
    public function revoke(Request $request, string $type, string $id): JsonResponse
    {
        return $this->performApprovalAction($type, $id, 'revoke');
    }

    /*
    |--------------------------------------------------------------------------
    | Get Personalized Statistics
    |--------------------------------------------------------------------------
    */
    private function getPersonalizedStatistics(): array
    {
        $employeeId = auth()->user()?->employee?->id;

        $myPendingRequests = ApprovalRequest::query()
            ->where('approval_requests.status', 'pending')
            ->whereHas('requestLevels', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                    ->whereColumn('level', 'approval_requests.current_level');
            })
            ->count();

        $myApprovedRequests = ApprovalRequest::query()
            ->whereHas('logs', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                    ->where('action', 'approved');
            })
            ->count();

        $myRejectedRequests = ApprovalRequest::query()
            ->whereHas('logs', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                    ->where('action', 'rejected');
            })
            ->count();

        $totalMyRequests = $myPendingRequests + $myApprovedRequests + $myRejectedRequests;

        return [
            'total' => $totalMyRequests,
            'pending' => $myPendingRequests,
            'approved' => $myApprovedRequests,
            'rejected' => $myRejectedRequests,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get My Flow Types
    |--------------------------------------------------------------------------
    */
    private function getMyFlowTypes(): array
    {
        $employeeId = auth()->user()?->employee?->id;
        $allFlowTypes = ApprovalFlow::getAllTypeLabels();
        $myFlowTypes = [];

        foreach ($allFlowTypes as $key => $label) {
            if (Helpers::isAssignedToApprovalType($key, $employeeId)) {
                $myFlowTypes[$key] = $label;
            }
        }

        return $myFlowTypes;
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Active Employees - الحل النهائي
    |--------------------------------------------------------------------------
    */
    private function getAllActiveEmployees()
    {
        try {
            $employees = Employees::query()
                ->whereNotNull('user_id')
                ->whereHas('user', function ($query) {
                    $query->where('status', 'active');
                })
                ->select('id', 'name', 'nickname', 'user_id')
                ->orderBy('name')
                ->get();

            // تحويل آمن للبيانات
            $result = [];
            foreach ($employees as $employee) {
                if (isset($employee->user_id) && $employee->user_id) {
                    $result[] = (object) [
                        'id' => $employee->user_id,
                        'name' => $employee->name ?? 'غير محدد',
                        'nickname' => $employee->nickname ?? '',
                        'user_id' => $employee->user_id, // إضافة user_id للأمان
                    ];
                }
            }

            return collect($result);
        } catch (\Exception $e) {
            \Log::error('Error fetching employees: ' . $e->getMessage());
            return collect();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Get Item By Type And Id
    |--------------------------------------------------------------------------
    */
    private function getItemByTypeAndId(string $type, string $id)
    {
        $modelMapping = [
            'offer' => \App\Models\OperationsCenter\Offer\Offers::class,
            'contract' => \App\Models\OperationsCenter\Contract\Contract::class,
            'leave' => \App\Models\ElectronicServices\LeaveRequests\LeaveRequest::class,
            'wps' => \App\Models\Hr\Payrolls\WPS\WpsPayroll::class,
            'clearance_certificate' => \App\Models\ElectronicServices\ClearanceCertificate\ClearanceCertificate::class,
            'advance' => \App\Models\Hr\Advances\Advance::class,
            'reward' => \App\Models\Hr\Rewards\Reward::class,
            'deduction' => \App\Models\Hr\Deductions\Deduction::class,
            'content' => \App\Models\ContentManagement\Content::class,
            'custody' => \App\Models\ElectronicServices\Custody\Request\CustodyRequest::class,
        ];

        if (!isset($modelMapping[$type])) {
            abort(404, 'نوع غير صحيح.');
        }

        $modelClass = $modelMapping[$type];
        return $modelClass::findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Show View Name
    |--------------------------------------------------------------------------
    */
    private function getShowViewName(string $type): string
    {
        return "approval-workflow.unified.show.{$type}";
    }

    /*
    |--------------------------------------------------------------------------
    | Perform Approval Action
    |--------------------------------------------------------------------------
    */
    private function performApprovalAction(string $type, string $id, string $action, ?string $reason = null): JsonResponse
    {
        try {
            $employeeId = auth()->user()?->employee?->id;
            if (!$employeeId) {
                return $this->errorResponse('لم يتم العثور على بيانات الموظف.');
            }

            if (!Helpers::isAssignedToApprovalType($type, $employeeId)) {
                return $this->errorResponse('ليس لديك الصلاحية لتنفيذ هذا الإجراء.');
            }

            $item = $this->getItemByTypeAndId($type, $id);
            $approvalRequest = $item->approvalRequest;

            if (!$approvalRequest) {
                return $this->errorResponse('لا يوجد طلب اعتماد لهذا العنصر.');
            }

            $canPerformAction = match ($action) {
                'approve', 'reject' => $this->approvalWorkflowService->canApprove($approvalRequest->id, $employeeId),
                'revoke' => $this->approvalWorkflowService->canRevoke($approvalRequest->id, $employeeId),
                default => false
            };

            if (!$canPerformAction) {
                return $this->errorResponse('ليس لديك الصلاحية لتنفيذ هذا الإجراء.');
            }

            $result = match ($action) {
                'approve' => $this->approvalWorkflowService->approve($approvalRequest->id, $employeeId),
                'reject' => $this->approvalWorkflowService->reject($approvalRequest->id, $employeeId, $reason),
                'revoke' => $this->approvalWorkflowService->revoke($approvalRequest->id, $employeeId),
                default => ['success' => false, 'message' => 'إجراء غير مدعوم.']
            };

            return response()->json($result);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Current User Permissions
    |--------------------------------------------------------------------------
    */
    private function getCurrentUserPermissions($item): array
    {
        $employeeId = auth()->user()?->employee?->id;
        $request = $item->approvalRequest;

        $permissions = [
            'canApprove' => false,
            'canReject' => false,
            'canRevoke' => false,
            'canView' => true,
        ];

        if ($request && $employeeId) {
            $canApprove = $this->approvalWorkflowService->canApprove($request->id, $employeeId);
            $canRevoke = $this->approvalWorkflowService->canRevoke($request->id, $employeeId);

            $permissions['canApprove'] = $canApprove;
            $permissions['canReject'] = $canApprove;
            $permissions['canRevoke'] = $canRevoke;
        }

        return $permissions;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Approval Logs
    |--------------------------------------------------------------------------
    */
    private function getApprovalLogs($item)
    {
        return $item->approvalRequest ?
            $item->approvalRequest->logs()->reorder('created_at', 'asc')->get() :
            collect();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Processed Content
    |--------------------------------------------------------------------------
    */
    private function getProcessedContent($item, string $type): ?string
    {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Reject Reason
    |--------------------------------------------------------------------------
    */
    private function validateRejectReason(Request $request): void
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ], [
            'reason.required' => 'يجب إدخال سبب الرفض.',
            'reason.max' => 'سبب الرفض لا يجب أن يتجاوز 1000 حرف.'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Error Response
    |--------------------------------------------------------------------------
    */
    private function errorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    private function getRouteBaseName(string $type): string
    {
        $routeMapping = [
            'offer' => 'approval-workflow.offers',
            'contract' => 'approval-workflow.contracts',
            'leave' => 'approval-workflow.leave-requests',
            'wps' => 'approval-workflow.wps-payrolls',
            'clearance_certificate' => 'approval-workflow.clearance-certificates',
            'advance' => 'approval-workflow.advances',
            'reward' => 'approval-workflow.rewards',
            'deduction' => 'approval-workflow.deductions',
            'content' => 'approval-workflow.content',
            'custody' => 'approval-workflow.custodies',
        ];

        return $routeMapping[$type] ?? 'approval-workflow.unified';
    }
}
