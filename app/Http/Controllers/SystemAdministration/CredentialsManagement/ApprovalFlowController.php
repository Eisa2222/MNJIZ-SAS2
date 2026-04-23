<?php

namespace App\Http\Controllers\SystemAdministration\CredentialsManagement;

use App\Http\Controllers\Controller;
use App\Models\SystemAdministration\CredentialsManagement\ApprovalFlow;
use App\Models\Hr\Employees\Employees;
use App\Services\SystemAdministration\CredentialsManagement\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;
use DomainException;

class ApprovalFlowController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    | Initialize controller with service dependency and middleware.
    */
    public function __construct(private ApprovalService $approvalService)
    {
        $this->middleware('can:إدارة الإعتمادات')
            ->only(['index', 'approve', 'addLevel']);
    }

    /*
    |--------------------------------------------------------------------------
    | Display Approval Flows Index
    |--------------------------------------------------------------------------
    | Shows the main approval management interface with all flows and employees.
    */
    public function index(): View
    {
        $flows      = $this->getApprovalFlows();
        $employees  = Employees::active()->select('id', 'name', 'nickname')->get();
        $flowLabels = ApprovalFlow::getAllTypeLabels();

        return view('system_administration.credentials_management.index', compact('flows', 'employees', 'flowLabels'));
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Approval Level
    |--------------------------------------------------------------------------
    | Handles approval/unapproval of specific levels with proper validation.
    */
    public function approve(Request $request, ApprovalFlow $flow, int $level): JsonResponse
    {
        try {
            $updatedFlow = $this->approvalService->toggleApprovalLevel(
                $flow->id,
                $level,
                (bool) $request->approval,
                $request->employee_id
            );

            return $this->successResponse(
                'تم تحديث الاعتماد بنجاح.',
                $updatedFlow->levels->values()
            );
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Add New Approval Level
    |--------------------------------------------------------------------------
    | Creates a new approval level for the specified flow.
    */
    public function addLevel(ApprovalFlow $flow): JsonResponse
    {
        try {
            $updatedFlow = $this->approvalService->addApprovalLevel($flow);

            return $this->successResponse(
                'تم إضافة مستوى جديد بنجاح.',
                $updatedFlow->levels->values()
            );
        } catch (Exception $exception) {
            return $this->errorResponse('تعذر إضافة المستوى الجديد.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete an approval level
    |--------------------------------------------------------------------------
    */
    public function deleteLevel(ApprovalFlow $flow, int $level): JsonResponse
    {
        try {
            $updatedFlow = $this->approvalService->removeApprovalLevel($flow->id, $level);

            return $this->successResponse(
                'تم حذف المستوى بنجاح.',
                $updatedFlow->levels->values()
            );
        } catch (DomainException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse('حدث خطأ غير متوقع أثناء حذف المستوى.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | Get Approval Flows
    |--------------------------------------------------------------------------
    | Retrieves all approval flows grouped by type.
    */
    private function getApprovalFlows()
    {
        return ApprovalFlow::with(['levels' => function ($query) {
            $query->with('employee:id,name,nickname')->orderBy('level');
        }])
            ->get()
            ->groupBy('type');
    }


    /*
    |--------------------------------------------------------------------------
    | Success Response
    |--------------------------------------------------------------------------
    | Returns a standardized success JSON response.
    */
    private function successResponse(string $message, $data = null): JsonResponse
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
    | Returns a standardized error JSON response.
    */
    private function errorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ]);
    }
}
