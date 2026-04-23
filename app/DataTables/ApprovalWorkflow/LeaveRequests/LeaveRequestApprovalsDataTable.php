<?php

namespace App\DataTables\ApprovalWorkflow\LeaveRequests;

use App\DataTables\ArabicSearchDataTable;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use Carbon\Carbon;

class LeaveRequestApprovalsDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Route
    |--------------------------------------------------------------------------
    */
    private string $route = 'approval-workflow.leave-requests';

    /*
    |--------------------------------------------------------------------------
    | Get Table Id
    |--------------------------------------------------------------------------
    */
    protected function getTableId(): string
    {
        return 'approval-leave-requests-table';
    }

    /*
    |--------------------------------------------------------------------------
    | Resource
    |--------------------------------------------------------------------------
    */
    protected function resource()
    {
        return LeaveRequest::query()
            ->with([
                'employee:id,name,nickname',
                'leaveType:id,name',
                'approvalRequest.requestLevels.employee:id,name,nickname',
            ])
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
            ['data' => 'employee_name',   'name' => 'employee.name',  'title' => 'الموظف'],
            ['data' => 'leave_type_name', 'name' => 'leaveType.name', 'title' => 'نوع الإجازة'],
            ['data' => 'start_date',      'name' => 'start_date',     'title' => 'تاريخ البداية', 'className' => 'text-center'],
            ['data' => 'end_date',        'name' => 'end_date',       'title' => 'تاريخ النهاية', 'className' => 'text-center'],
            ['data' => 'days_count',      'name' => 'days_count',     'title' => 'عدد الأيام', 'className' => 'text-center'],
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
                'title' => 'تاريخ الطلب',
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
            ->addColumn('employee_name', fn($row) => $this->routeName(route($this->route . '.show', $row->id), e($row->employee->name ?? '-')))
            ->addColumn('leave_type_name', fn($row) => e($row->leaveType->name ?? '-'))
            ->addColumn('start_date', fn($row) => Carbon::parse($row->start_date)->format('Y-m-d'))
            ->addColumn('end_date', fn($row) => Carbon::parse($row->end_date)->format('Y-m-d'))
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
        return ['employee_name', 'approval_info'];
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Custom Filters
    |--------------------------------------------------------------------------
    */
    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('filter_leave_type')) {
            $query->where('leave_type_id', $req->filter_leave_type);
        }
        if ($req->filled('filter_employee')) {
            $query->where('employee_id', $req->filter_employee);
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Searchable Columns
    |--------------------------------------------------------------------------
    */
    protected function getSearchableColumns(): array
    {
        return [
            'employee.name',
            'leaveType.name',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | get Custom Order
    |--------------------------------------------------------------------------
    */
    protected function getCustomOrder(): array
    {
        return [7, 'desc'];
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
