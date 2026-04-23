<?php

namespace App\Http\Controllers\ApprovalWorkflow\Deductions;

use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\Hr\Deductions\Deduction;
use App\Models\Hr\Employees\Employees;
use App\Enums\Hr\Deduction\DeductionType;
use App\DataTables\ApprovalWorkflow\Deductions\DeductionApprovalsDataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class DeductionApprovalController extends BaseApprovalController
{

    /*
    |--------------------------------------------------------------------------
    | Index Method
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(DeductionApprovalsDataTable::class);
        if ($request->ajax()) {
            return $dataTable->ajax();
        }
        return $dataTable->render($this->getIndexViewName(), array_merge(['routeBaseName' => $this->getRouteBaseName()], $this->getAdditionalData()));
    }


    /*
    |--------------------------------------------------------------------------
    | Get Model Class
    |--------------------------------------------------------------------------
    */
    protected function getModel(): string
    {
        return Deduction::class;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Flow Type
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'deduction';
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
        return 'approval-workflow.deductions';
    }


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.deductions.index';
    }


    /*
    |--------------------------------------------------------------------------
    | Show View Name
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.deductions.show';
    }


    /*
    |--------------------------------------------------------------------------
    | Get Additional Data
    |--------------------------------------------------------------------------
    */
    protected function getAdditionalData(): array
    {
        return [
            'employees' => Employees::active()->select('id', 'name', 'nickname')->get(),
            'deductionTypes' => DeductionType::options(),
        ];
    }
}
