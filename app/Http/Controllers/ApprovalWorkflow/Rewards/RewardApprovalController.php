<?php

namespace App\Http\Controllers\ApprovalWorkflow\Rewards;

use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\Hr\Rewards\Reward;
use App\Models\Hr\Employees\Employees;
use App\Enums\Hr\Reward\RewardType;
use App\DataTables\ApprovalWorkflow\Rewards\RewardApprovalsDataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class RewardApprovalController extends BaseApprovalController
{

    /*
    |--------------------------------------------------------------------------
    | Index Method
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(RewardApprovalsDataTable::class);

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
    | Get Model
    |--------------------------------------------------------------------------
    */
    protected function getModel(): string
    {
        return Reward::class;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Flow Type
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'reward';
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
    | Route Base Name
    |--------------------------------------------------------------------------
    */
    protected function getRouteBaseName(): string
    {
        return 'approval-workflow.rewards';
    }


    /*
    |--------------------------------------------------------------------------
    | Get Index View Name
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.rewards.index';
    }


    /*
    |--------------------------------------------------------------------------
    | Get Show View Name
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.rewards.show';
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
            'rewardTypes' => RewardType::options(),
        ];
    }
}
