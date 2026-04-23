<?php

namespace App\DataTables\ApprovalWorkflow\Custodies;

use App\DataTables\ArabicSearchDataTable;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;

class CustodyRequestApprovalDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    private string $route = 'approval-workflow.custodies';


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    protected function getTableId(): string
    {
        return 'approval-custodies-table';
    }


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    protected function resource()
    {
        return CustodyRequest::query()
            ->with([
                'employee:id,name,nickname',
                'item:id,name',
                'approvalRequest.requestLevels.employee:id,name,nickname',
            ])
            ->where('status', 'pending');
    }


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    protected function getColumns(): array
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => 'رقم الطلب', 'className' => 'text-center'],
            ['data' => 'employee_name', 'name' => 'employee.name', 'title' => 'اسم الموظف'],
            ['data' => 'item_name', 'name' => 'item.name', 'title' => 'الصنف (العهدة)'],
            ['data' => 'request_type', 'name' => 'request_type', 'title' => 'نوع الطلب'],
            ['data' => 'approval_info', 'name' => 'approval_info', 'title' => 'حالة الاعتماد', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاريخ الطلب', 'searchable' => false, 'className' => 'text-center'],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->editColumn('id', function ($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->id)
                );
            })
            ->addColumn('employee_name', function ($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->employee?->name ?? '-')
                );
            })
            ->addColumn('item_name', fn($row) => e($row->item?->name ?? '-'))
            ->editColumn('request_type', function ($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->request_type?->label() ?? '-')
                );
            })
            ->addColumn('approval_info', fn($row) => $this->getApprovalInfoColumn($row))
            ->editColumn('created_at', fn($row) => $row->created_at?->format('d/m/Y') ?? '-');
    }


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    protected function rawColumns(): array
    {
        return ['id', 'employee_name', 'request_type', 'approval_info'];
    }


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('employee_id')) {
            $query->where('employee_id', $req->employee_id);
        }
        if ($req->filled('request_type')) {
            $query->where('request_type', $req->request_type);
        }
        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | Section Title
    |--------------------------------------------------------------------------
    | Description of this section.
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
