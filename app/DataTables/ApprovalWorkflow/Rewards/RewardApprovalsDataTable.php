<?php

namespace App\DataTables\ApprovalWorkflow\Rewards;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Rewards\Reward;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;

class RewardApprovalsDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Route Name
    |--------------------------------------------------------------------------
    */
    private string $route = 'approval-workflow.rewards';


    /*
    |--------------------------------------------------------------------------
    | Table ID
    |--------------------------------------------------------------------------
    */
    protected function getTableId(): string
    {
        return 'approval-rewards-table';
    }


    /*
    |--------------------------------------------------------------------------
    | Resource Query
    |--------------------------------------------------------------------------
    */
    protected function resource()
    {
        return Reward::query()
            ->with(['employee:id,name,nickname', 'approvalRequest.requestLevels.employee:id,name,nickname'])
            ->where('status', 'pending');
    }


    /*
    |--------------------------------------------------------------------------
    | Columns Definition
    |--------------------------------------------------------------------------
    */
    protected function getColumns(): array
    {
        return [
            ['data' => 'reward_number', 'name' => 'reward_number', 'title' => 'رقم المكافأة'],
            ['data' => 'employee.name', 'name' => 'employee.name', 'title' => 'اسم الموظف'],
            ['data' => 'reward_type',   'name' => 'reward_type', 'title' => 'نوع المكافأة'],
            ['data' => 'amount',        'name' => 'amount', 'title' => 'المبلغ'],
            ['data' => 'approval_info', 'name' => 'approval_info', 'title' => 'حالة الاعتماد', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ['data' => 'created_at',    'name' => 'created_at', 'title' => 'تاريخ الطلب', 'searchable' => false],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Custom Columns
    |--------------------------------------------------------------------------
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->editColumn('reward_number', fn($row) => $this->routeName(route($this->route . '.show', $row->id), e($row->reward_number)))
            ->editColumn('employee.name', fn($row) => $this->routeName(route($this->route . '.show', $row->id), e($row->employee?->name ?? '-')))
            ->editColumn('reward_type', fn($row) => $row->reward_type?->label() ?? '-')
            ->editColumn('amount', fn($row) => number_format($row->amount, 2) . ' ر.س')
            ->addColumn('approval_info', fn($row) => $this->getApprovalInfoColumn($row))
            ->editColumn('created_at', fn($row) => $row->created_at?->format('d/m/Y') ?? '-');
    }


    /*
    |--------------------------------------------------------------------------
    | Raw Columns
    |--------------------------------------------------------------------------
    */
    protected function rawColumns(): array
    {
        return ['reward_number', 'employee.name', 'approval_info'];
    }


    /*
    |--------------------------------------------------------------------------
    | Custom Filters
    |--------------------------------------------------------------------------
    */
    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('employee_id')) {
            $query->where('employee_id', $req->employee_id);
        }
        if ($req->filled('reward_type')) {
            $query->where('reward_type', $req->reward_type);
        }
        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | Approval Info Column
    |--------------------------------------------------------------------------
    */
    private function getApprovalInfoColumn($item): string
    {
        try {
            $svc = app(ApprovalWorkflowInterface::class);
            $statusData = $svc->getApprovalStatus($item);
            $nextApprover = $statusData['next_approver'] ?? 'غير محدد';
            $currentLevel = $item->approvalRequest?->current_level ?? 0;
            $totalLevels = $item->approvalRequest?->requestLevels()->count() ?? 0;

            return sprintf(
                '<div class="text-center"><span class="badge bg-label-info">المستوى %d من %d</span><br><small class="text-muted">%s</small></div>',
                $currentLevel,
                $totalLevels,
                e($nextApprover)
            );
        } catch (\Throwable $e) {
            return '<span class="text-danger">خطأ</span>';
        }
    }
}
