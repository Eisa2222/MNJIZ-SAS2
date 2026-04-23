<?php

namespace App\Http\Controllers\ApprovalWorkflow\Content;

use App\DataTables\ApprovalWorkflow\Content\ContentApprovalsDataTable;
use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\general_setting\Marketing\SettingsContentType;
use App\Models\Marketing\ContentManagement\ContentManagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentApprovalController extends BaseApprovalController
{
    /*
    |--------------------------------------------------------------------------
    | Index Page
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(ContentApprovalsDataTable::class);

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
    | Model Definition
    |--------------------------------------------------------------------------
    */
    protected function getModel(): string
    {
        return ContentManagement::class;
    }

    /*
    |--------------------------------------------------------------------------
    | Approval Flow Type
    |--------------------------------------------------------------------------
    */
    protected function getFlowType(): string
    {
        return 'content';
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Name
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
        return 'approval-workflow.content';
    }

    /*
    |--------------------------------------------------------------------------
    | Index View Name
    |--------------------------------------------------------------------------
    */
    protected function getIndexViewName(): string
    {
        return 'approval-workflow.content.index';
    }

    /*
    |--------------------------------------------------------------------------
    | Show View Name
    |--------------------------------------------------------------------------
    */
    protected function getShowViewName(): string
    {
        return 'approval-workflow.content.show';
    }

    /*
    |--------------------------------------------------------------------------
    | Additional Data
    |--------------------------------------------------------------------------
    */
    protected function getAdditionalData(): array
    {
        return [
            'contentTypes' => SettingsContentType::select('id', 'name')->get(),
        ];
    }
}
