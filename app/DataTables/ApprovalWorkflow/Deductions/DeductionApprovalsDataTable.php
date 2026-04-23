<?php

namespace App\DataTables\ApprovalWorkflow\Deductions;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Deductions\Deduction;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;

class DeductionApprovalsDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Route Name
    |--------------------------------------------------------------------------
    */
    private string $route = 'approval-workflow.deductions';

    /*
    |--------------------------------------------------------------------------
    | Table ID
    |--------------------------------------------------------------------------
    */
    protected function getTableId(): string
    {
        return 'approval-deductions-table';
    }

    /*
    |--------------------------------------------------------------------------
    | Resource Query
    |--------------------------------------------------------------------------
    */
    protected function resource()
    {
        return Deduction::query()
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
            ['data' => 'deduction_number', 'name' => 'deduction_number', 'title' => 'رقم الخصم'],
            ['data' => 'employee.name', 'name' => 'employee.name', 'title' => 'اسم الموظف'],
            ['data' => 'deduction_type',   'name' => 'deduction_type', 'title' => 'نوع الخصم'],
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
            ->editColumn('deduction_number', fn($row) => $this->routeName(route($this->route . '.show', $row->id), e($row->deduction_number)))
            ->editColumn('employee.name', fn($row) => $this->routeName(route($this->route . '.show', $row->id), e($row->employee?->name ?? '-')))
            ->editColumn('deduction_type', fn($row) => $row->deduction_type?->label() ?? '-')
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
        return ['deduction_number', 'employee.name', 'approval_info'];
    }

    /*
    |--------------------------------------------------------------------------
    | Searchable Columns
    |--------------------------------------------------------------------------
    */
    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('employee_id')) {
            $query->where('employee_id', $req->employee_id);
        }
        if ($req->filled('deduction_type')) {
            $query->where('deduction_type', $req->deduction_type);
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Approval Info Column
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
            return sprintf('<div class="text-center"><span class="badge bg-label-info">المستوى %d من %d</span><br><small class="text-muted">%s</small></div>', $currentLevel, $totalLevels, e($nextApprover));
        } catch (\Throwable $e) {
            return '<span class="text-danger">خطأ</span>';
        }
    }
}
