<?php

namespace App\Http\Controllers\ApprovalWorkflow\LeaveRequests;

use App\DataTables\ApprovalWorkflow\LeaveRequests\LeaveRequestApprovalsDataTable;
use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\Employees\Employees;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LeaveRequestApprovalController extends BaseApprovalController
{


    /*
    |--------------------------------------------------------------------------
    | Index Method
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(LeaveRequestApprovalsDataTable::class);

        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        return $dataTable->render(
            $this->getIndexViewName(),
            array_merge([
                'statistics'    => $this->getStatistics(),
                'routeBaseName' => $this->getRouteBaseName(),
            ], $this->getAdditionalData())
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Flow Type
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'leave';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Permission Name
    |--------------------------------------------------------------------------
    */
    // protected function getPermissionName(): string
    // {
    //     return 'إعتماد الإجازات';
    // }

    /*
    |--------------------------------------------------------------------------
    | Get Model
    |--------------------------------------------------------------------------
    */
    protected function getModel(): string
    {
        return LeaveRequest::class;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Route Base Name
    |--------------------------------------------------------------------------
    */
    protected function getRouteBaseName(): string
    {
        return 'approval-workflow.leave-requests';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Additional Data
    |--------------------------------------------------------------------------
    */
    protected function getAdditionalData(): array
    {
        return [
            'leaveTypes' => SettingsLeaveType::select('id', 'name')->get(),
            'employees'  => Employees::active()->select('id', 'name', 'nickname')->get(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Index View Name
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.leave-requests.index';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Show View Name
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.leave-requests.show';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Ajax Data
    |--------------------------------------------------------------------------
    */
    protected function getProcessedContent($item): ?string
    {
        return null;
    }
}
