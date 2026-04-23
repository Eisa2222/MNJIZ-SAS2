<?php

namespace App\Http\Controllers\ApprovalWorkflow\ClearanceCertificates;

use App\DataTables\ApprovalWorkflow\ClearanceCertificates\ClearanceCertificateApprovalsDataTable;
use App\Http\Controllers\ApprovalWorkflow\BaseApprovalController;
use App\Models\Self_services\ClearanceCertificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ClearanceCertificateApprovalController extends BaseApprovalController
{
    /*
    |--------------------------------------------------------------------------
    | تنفيذ الدوال المجردة
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View|JsonResponse
    {
        $dataTable = app(ClearanceCertificateApprovalsDataTable::class);

        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        return $dataTable->render(
            $this->getIndexViewName(),
            array_merge(
                ['statistics' => $this->getStatistics()],
                $this->getAdditionalData()
            )
        );
    }

    protected function getModel(): string
    {
        return ClearanceCertificate::class;
    }

    protected function getFlowType(): string
    {
        return 'clearance_certificate';
    }

    // protected function getPermissionName(): string
    // {
    //     return 'إعتماد إخلاء الطرف';
    // }

    protected function getRouteBaseName(): string
    {
        return 'approval-workflow.clearance-certificates';
    }

    protected function getAdditionalData(): array
    {
        return [
            'users' => User::select('id', 'name')->get(),
        ];
    }

    protected function getIndexViewName(): string
    {
        return 'approval-workflow.clearance-certificates.index';
    }

    protected function getShowViewName(): string
    {
        return 'approval-workflow.clearance-certificates.show';
    }

    /**
     * إخلاء الطرف ليس له محتوى لمعالجته (مثل قالب العقد)
     */
    protected function getProcessedContent($item): ?string
    {
        return null;
    }
}
