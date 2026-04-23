<?php

namespace App\Http\Controllers\ApprovalWorkflow;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

abstract class BaseApprovalController extends Controller
{
    protected ApprovalWorkflowInterface $approvalWorkflowService;

    public function __construct(ApprovalWorkflowInterface $approvalWorkflowService)
    {
        $this->approvalWorkflowService = $approvalWorkflowService;


        $this->middleware(function ($request, $next) {
            $employeeId = auth()->user()?->employee?->id;
            $flowType = $this->getFlowType();
            $hasAccess = Helpers::isAssignedToApprovalType($flowType, $employeeId);

            if (!$hasAccess) {
                abort(403, 'ليس لديك الصلاحية للوصول إلى هذا القسم. يجب أن تكون مُعيَّناً في نظام الاعتمادات.');
            }

            return $next($request);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | الدوال المجردة - يجب تنفيذها في الكلاسات الفرعية
    |--------------------------------------------------------------------------
    */

    abstract protected function getFlowType(): string;
    abstract protected function getModel(): string;
    abstract protected function getRouteBaseName(): string;
    abstract protected function getAdditionalData(): array;

    /*
    |--------------------------------------------------------------------------
    | الحصول على اسم view للعرض - يجب تنفيذها في الكلاسات الفرعية
    |--------------------------------------------------------------------------
    */
    abstract protected function getShowViewName(): string;

    /*
    |--------------------------------------------------------------------------
    | الحصول على اسم view للفهرس - يجب تنفيذها في الكلاسات الفرعية
    |--------------------------------------------------------------------------
    */
    abstract protected function getIndexViewName(): string;


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->getAjaxData($request);
        }

        $statistics = $this->getStatistics();
        $additionalData = $this->getAdditionalData();

        $viewName = $this->getIndexViewName();

        return view($viewName, array_merge([
            'statistics' => $statistics,
            'flowType' => $this->getFlowType(),
            'routeBaseName' => $this->getRouteBaseName(),
        ], $additionalData));
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */
    public function show(string $id): View
    {
        $item               = $this->findItemOrFail($id);
        $approvalStatus     = $this->approvalWorkflowService->getApprovalStatus($item);
        $approvalStages     = $this->approvalWorkflowService->getApprovalStagesInfo($item);
        $approvalLogs       = $this->getApprovalLogs($item);
        $userPermissions    = $this->getCurrentUserPermissions($item);
        $processedContent   = $this->getProcessedContent($item);

        $viewName           = $this->getShowViewName();

        return view($viewName, [
            'item'              => $item,
            'approvalStatus'    => $approvalStatus,
            'approvalStages'    => $approvalStages,
            'approvalLogs'      => $approvalLogs,
            'userPermissions'   => $userPermissions,
            'processedContent'  => $processedContent,
            'flowType'          => $this->getFlowType(),
            'routeBaseName'     => $this->getRouteBaseName(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Approval Actions
    |--------------------------------------------------------------------------
    */
    public function approve(Request $request, string $id): JsonResponse
    {
        return $this->performApprovalAction($id, 'approve');
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */
    public function reject(Request $request, string $id): JsonResponse
    {
        $this->validateRejectReason($request);
        return $this->performApprovalAction($id, 'reject', $request->reason);
    }


    /*
    |--------------------------------------------------------------------------
    | Revoke
    |--------------------------------------------------------------------------
    */
    public function revoke(Request $request, string $id): JsonResponse
    {
        return $this->performApprovalAction($id, 'revoke');
    }

    /*
    |--------------------------------------------------------------------------
    | الدوال المساعدة المشتركة
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Perform Approval Action
    | This method handles the logic for approving, rejecting, or revoking an approval request.
    |--------------------------------------------------------------------------
    */
    protected function performApprovalAction(string $id, string $action, ?string $reason = null): JsonResponse
    {
        $employeeId      = $this->getCurrentEmployeeId();
        $item            = $this->findItemOrFail($id);
        $approvalRequest = $item->approvalRequest;

        if (!$approvalRequest) {
            return $this->errorResponse('لا يوجد طلب اعتماد لهذا العنصر.');
        }

        if (!$employeeId) {
            return $this->errorResponse('لم يتم العثور على بيانات الموظف الخاصة بك.');
        }

        $canPerformAction = match ($action) {
            'approve', 'reject' => $this->approvalWorkflowService->canApprove($approvalRequest->id, $employeeId),
            'revoke' => $this->approvalWorkflowService->canRevoke($approvalRequest->id, $employeeId),
            default => false
        };

        if (!$canPerformAction) {
            return $this->errorResponse('ليس لديك الصلاحية لتنفيذ هذا الإجراء.');
        }

        try {
            $result = $this->executeApprovalAction($action, $approvalRequest->id, $employeeId, $reason);
            return response()->json($result);
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Approval Info Column
    |--------------------------------------------------------------------------
    | This method generates the HTML for the approval info column in the DataTable.
    */
    protected function getApprovalInfoColumn($item): string
    {
        $approvalStatus = $this->approvalWorkflowService->getApprovalStatus($item);
        $nextApprover = $approvalStatus['next_approver'] ?? 'غير محدد';
        $level = $item->approvalRequest?->current_level ?? 0;
        $totalLevels = $item->approvalRequest?->requestLevels()->count() ?? 0;

        return sprintf(
            '<div class="text-center">
                <span class="badge bg-label-info">المستوى %d من %d</span><br>
                <small class="text-muted">%s</small>
            </div>',
            $level,
            $totalLevels,
            e($nextApprover)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Get Statistics
    |--------------------------------------------------------------------------
    | This method retrieves statistics about the approval workflow items.
    */
    protected function getStatistics(): array
    {
        $modelClass = $this->getModel();
        $approvalStats = $this->approvalWorkflowService->getApprovalStatistics($this->getFlowType());

        return [
            'total' => $modelClass::count(),
            'pending' => $modelClass::where('status', 'pending')->count(),
            'approved' => $modelClass::where('status', 'approved')->count(),
            'rejected' => $modelClass::where('status', 'rejected')->count(),
            'approvalStats' => $approvalStats,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Current User Permissions
    |--------------------------------------------------------------------------
    | This method checks the current user's permissions for the given item.
    */
    protected function getCurrentUserPermissions($item): array
    {
        $employeeId = $this->getCurrentEmployeeId();
        $request   = $item->approvalRequest;

        $permissions = [
            'canApprove' => false,
            'canReject'  => false,
            'canRevoke'  => false,
            'canView'    => true,
        ];

        if ($request && $employeeId) {
            $canApprove = $this->approvalWorkflowService->canApprove($request->id, $employeeId);
            $canRevoke = $this->approvalWorkflowService->canRevoke($request->id, $employeeId);

            $permissions['canApprove'] = $canApprove;
            $permissions['canReject']  = $canApprove;
            $permissions['canRevoke']  = $canRevoke;
        }

        return $permissions;
    }





    private function shouldShowButtons($stages, $permissions): array
    {
        $hasApproveButton = false;
        $hasRejectButton = false;
        $hasRevokeButton = false;

        foreach ($stages as $stage) {
            if ($stage['can_action'] && $permissions['canApprove']) {
                $hasApproveButton = true;
            }
            if ($stage['can_action'] && $permissions['canReject']) {
                $hasRejectButton = true;
            }
            if ($stage['can_revoke'] && $permissions['canRevoke']) {
                $hasRevokeButton = true;
            }
        }

        return [
            'has_approve_button' => $hasApproveButton,
            'has_reject_button' => $hasRejectButton,
            'has_revoke_button' => $hasRevokeButton,
            'has_any_button' => $hasApproveButton || $hasRejectButton || $hasRevokeButton,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Find Item or Fail
    |--------------------------------------------------------------------------
    | This method attempts to find an item by its ID, or throws a 404 error if not found.
    */
    private function findItemOrFail(string $id)
    {
        $modelClass = $this->getModel();
        return $modelClass::findOrFail($id);
    }


    /*
    |--------------------------------------------------------------------------
    | Get Current Employee ID
    |--------------------------------------------------------------------------
    | This method retrieves the current authenticated employee's ID.
    */
    private function getCurrentEmployeeId(): ?int
    {
        return auth()->user()->employee?->id;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Reject Reason
    |--------------------------------------------------------------------------
    | This method validates the reason for rejecting an approval request.
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
    | Execute Approval Action
    |--------------------------------------------------------------------------
    | This method executes the specified approval action (approve, reject, revoke).
    */
    private function executeApprovalAction(string $action, int $requestId, int $employeeId, ?string $reason = null): array
    {
        return match ($action) {
            'approve' => $this->approvalWorkflowService->approve($requestId, $employeeId),
            'reject' => $this->approvalWorkflowService->reject($requestId, $employeeId, $reason),
            'revoke' => $this->approvalWorkflowService->revoke($requestId, $employeeId),
            default => ['success' => false, 'message' => 'إجراء غير مدعوم.']
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Get Approval Logs
    |--------------------------------------------------------------------------
    | This method retrieves the approval logs for a given item.
    */
    private function getApprovalLogs($item)
    {
        return $item->approvalRequest ?
            $item->approvalRequest->logs()->reorder('created_at', 'asc')->get() :
            collect();
    }


    /*
    |--------------------------------------------------------------------------
    | Create Action Button
    |--------------------------------------------------------------------------
    | This method generates the HTML for an action button (approve, reject, revoke).
    */
    private function createActionButton(string $action, string $itemId, string $color, string $icon, string $title): string
    {
        $class = $action === 'reject' ? 'reject-btn' : ($action . '-btn');

        return sprintf(
            '<button class="btn btn-sm btn-%s %s" data-id="%s" title="%s">
                <i class="ti %s ti-xs"></i>
            </button>',
            $color,
            $class,
            $itemId,
            $title,
            $icon
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Success and Error Responses
    |--------------------------------------------------------------------------
    | These methods generate standardized JSON responses for success and error cases.
    */
    protected function successResponse(string $message, $data = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Error Response
    |--------------------------------------------------------------------------
    | This method generates a standardized JSON error response.
    */
    protected function errorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | دالة افتراضية لمعالجة المحتوى - يمكن إعادة تعريفها في الكلاسات الفرعية
    |--------------------------------------------------------------------------
    */
    protected function getProcessedContent($item): ?string
    {
        return null;
    }
}
