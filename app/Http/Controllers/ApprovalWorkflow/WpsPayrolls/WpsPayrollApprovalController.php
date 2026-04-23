<?php

namespace App\Http\Controllers\ApprovalWorkflow\WpsPayrolls;

use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\DataTables\ApprovalWorkflow\WpsPayrolls\WpsPayrollApprovalsDataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class WpsPayrollApprovalController extends BaseApprovalController
{

    /*
    |--------------------------------------------------------------------------
    | Index Method
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(WpsPayrollApprovalsDataTable::class);
        if ($request->ajax()) return $dataTable->ajax();
        return $dataTable->render($this->getIndexViewName(), ['routeBaseName' => $this->getRouteBaseName()]);
    }


    /*
    |--------------------------------------------------------------------------
    | Get Model
    |--------------------------------------------------------------------------
    */
    protected function getModel(): string
    {
        return WpsPayroll::class;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Flow Type
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'wps';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Permission Name
    |--------------------------------------------------------------------------
    */
    // protected function getPermissionName(): string
    // {
    //     return 'إعتماد العروض';
    // }

    /*
    |--------------------------------------------------------------------------
    | Get Route Base Name
    |--------------------------------------------------------------------------
    */
    protected function getRouteBaseName(): string
    {
        return 'approval-workflow.wps-payrolls';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Index View Name
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.wps-payrolls.index';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Show View Name
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.wps-payrolls.show';
    }

    /*
    |--------------------------------------------------------------------------
    | Additional Data
    |--------------------------------------------------------------------------
    */
    protected function getAdditionalData(): array
    {
        return [];
    }
}
