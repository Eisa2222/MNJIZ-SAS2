<?php

namespace App\DataTables\ApprovalWorkflow\Contracts;

use App\DataTables\ArabicSearchDataTable;
use App\Models\OperationsCenter\Contract\Contract;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;

class ContractApprovalsDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Route for URLs
    |--------------------------------------------------------------------------
    */
    private string $route = 'approval-workflow.contracts';

    protected function getTableId(): string
    {
        return 'approval-contracts-table';
    }

    protected function resource()
    {
        return Contract::query()
            ->with([
                'customer:id,name',
                'approvalRequest.requestLevels.employee:id,name,nickname',
            ])
            ->where('status', 'pending');
    }

    protected function getColumns(): array
    {
        return [
            ['data' => '', 'orderable' => false, 'searchable' => false],
            ['data' => 'contract_name',   'name' => 'contract_name',   'title' => 'اسم العقد'],
            ['data' => 'contract_number', 'name' => 'contract_number', 'title' => 'رقم العقد', 'className' => 'text-center'],
            ['data' => 'customer_name',   'name' => 'customer.name',   'title' => 'العميل'],
            [
                'data'       => 'approval_info',
                'name'       => 'approval_info',
                'title'      => 'حالة الاعتماد',
                'orderable'  => false,
                'searchable' => false,
                'className'  => 'text-center',
            ],
            [
                'data'       => 'created_at',
                'name'       => 'created_at',
                'title'      => 'تاريخ الإنشاء',
                'searchable' => false,
                'className'  => 'text-center',
            ],
        ];
    }

    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn(
                'contract_name',
                fn($row) =>
                $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->contract_name)
                )
            )
            ->addColumn('customer_name', fn($row) => $row->customer->name ?? '-')
            ->addColumn('approval_info', fn($row) => $this->getApprovalInfoColumn($row))
            ->addColumn('created_at', fn($row) => $row->created_at?->format('d/m/Y H:i') ?? '-');
    }

    protected function rawColumns(): array
    {
        return ['contract_name', 'approval_info'];
    }

    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('customer')) {
            $query->where('customer_id', $req->customer);
        }

        return $query;
    }

    protected function getSearchableColumns(): array
    {
        return [
            'contract_name',
            'contract_number',
            'customer.name',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [5, 'desc'];
    }

    private function getApprovalInfoColumn($item): string
    {
        try {
            /** @var ApprovalWorkflowInterface $svc */
            $svc = app(ApprovalWorkflowInterface::class);

            $statusData   = $svc->getApprovalStatus($item);
            $nextApprover = $statusData['next_approver'] ?? 'غير محدد';
            $currentLevel = $item->approvalRequest?->current_level  ?? 0;
            $totalLevels  = $item->approvalRequest?->requestLevels()->count() ?? 0;

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
