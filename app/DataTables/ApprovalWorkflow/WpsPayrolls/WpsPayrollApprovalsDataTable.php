<?php

namespace App\DataTables\ApprovalWorkflow\WpsPayrolls;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;

class WpsPayrollApprovalsDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Route
    |--------------------------------------------------------------------------
    */
    private string $route = 'approval-workflow.wps-payrolls';

    /*
    |--------------------------------------------------------------------------
    | Get Table Id
    |--------------------------------------------------------------------------
    */
    protected function getTableId(): string
    {
        return 'approval-wps-payrolls-table';
    }

    /*
    |--------------------------------------------------------------------------
    | Resource
    |--------------------------------------------------------------------------
    */
    protected function resource()
    {
        return WpsPayroll::query()
            ->with(['approvalRequest.requestLevels.employee:id,name,nickname'])
            ->where('status', 'pending');
    }

    /*
    |--------------------------------------------------------------------------
    | Get Columns
    |--------------------------------------------------------------------------
    */
    protected function getColumns(): array
    {
        return [
            ['data' => '', 'orderable' => false, 'searchable' => false],
            ['data' => 'reference', 'name' => 'reference', 'title' => 'مرجع المسير'],
            ['data' => 'run_date', 'name' => 'run_date', 'title' => 'تاريخ المسير', 'className' => 'text-center'],
            ['data' => 'total_net', 'name' => 'total_net', 'title' => 'صافي المبلغ', 'className' => 'text-center'],
            [
                'data' => 'approval_info',
                'name' => 'approval_info',
                'title' => 'حالة الاعتماد',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
            [
                'data' => 'created_at',
                'name' => 'created_at',
                'title' => 'تاريخ الإنشاء',
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Add Custom Columns
    |--------------------------------------------------------------------------
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn('reference', fn($row) => $this->routeName(route($this->route . '.show', $row->id), e($row->reference ?? 'غير محدد')))
            ->addColumn('run_date', fn($row) => $row->run_date?->format('F Y') ?? 'غير محدد')
            ->addColumn('total_net', fn($row) => number_format($row->total_net ?? 0, 2) . ' ر.س')
            ->addColumn('approval_info', fn($row) => $this->getApprovalInfoColumn($row))
            ->addColumn('created_at', fn($row) => $row->created_at?->format('d/m/Y H:i') ?? '-');
    }

    /*
    |--------------------------------------------------------------------------
    | Raw Columns
    |--------------------------------------------------------------------------
    */
    protected function rawColumns(): array
    {
        return ['reference', 'approval_info'];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Searchable Columns
    |--------------------------------------------------------------------------
    */
    protected function getSearchableColumns(): array
    {
        return ['reference'];
    }

    /*
    |--------------------------------------------------------------------------
    | get Custom Order
    |--------------------------------------------------------------------------
    */
    protected function getCustomOrder(): array
    {
        return [5, 'desc'];
    }

    /*
    |--------------------------------------------------------------------------
    | get Approval Info Column
    |--------------------------------------------------------------------------
    */
    private function getApprovalInfoColumn($item): string
    {
        try {
            /** @var ApprovalWorkflowInterface $svc */
            $svc = app(ApprovalWorkflowInterface::class);

            $statusData = $svc->getApprovalStatus($item);
            $nextApprover = $statusData['next_approver'] ?? 'غير محدد';
            $currentLevel = $item->approvalRequest?->current_level ?? 0;
            $totalLevels = $item->approvalRequest?->requestLevels()->count() ?? 0;

            return sprintf(
                '<div class="text-center">
                    <span class="badge bg-label-info">المستوى %d من %d</span><br>
                    <small class="text-muted">%s</small>
                </div>',
                $currentLevel,
                $totalLevels,
                e($nextApprover)
            );
        } catch (\Throwable $e) {
            return '<span class="text-danger">خطأ في عرض الحالة</span>';
        }
    }
}