<?php

namespace App\DataTables\ApprovalWorkflow\Offers;

use App\DataTables\ArabicSearchDataTable;
use App\Models\OperationsCenter\Offer\Offers;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use Illuminate\Support\Facades\Log;

class OfferApprovalsDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Base route for URLs.
    |--------------------------------------------------------------------------
    */
    private string $route = 'approval-workflow.offers';

    /*
    |--------------------------------------------------------------------------
    | HTML ID attribute for the table.
    |--------------------------------------------------------------------------
    */
    protected function getTableId(): string
    {
        return 'approval-offers-table';
    }

    /*
    |--------------------------------------------------------------------------
    | Base Eloquent query to build the DataTable.
    |--------------------------------------------------------------------------
    */
    protected function resource()
    {
        return Offers::query()
            ->with([
                'customer:id,name',
                'relationshipManager:id,name,nickname',
                'approvalRequest.requestLevels.employee:id,name,nickname',
            ])
            ->where('status', 'pending');
    }


    /*
    |--------------------------------------------------------------------------
    | Define the columns of the table.
    |--------------------------------------------------------------------------
    */
    protected function getColumns(): array
    {
        return [
            ['data' => '', 'orderable' => false, 'searchable' => false],
            ['data' => 'offer_name',                'name' => 'offer_name',                'title' => 'اسم العرض'],
            ['data' => 'offer_number',              'name' => 'offer_number',              'title' => 'رقم العرض', 'className' => 'text-center'],
            ['data' => 'customer_name',             'name' => 'customer.name',             'title' => 'العميل'],
            ['data' => 'relationship_manager_name', 'name' => 'relationshipManager.name',   'title' => 'مسؤول العلاقات'],
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
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Add custom rendering logic for specific columns.
    |--------------------------------------------------------------------------
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn(
                'offer_name',
                fn($row) =>
                $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->offer_name)
                )
            )
            ->addColumn('customer_name', fn($row) => $row->customer->name ?? '-')
            ->addColumn('relationship_manager_name', fn($row) => $row->relationshipManager->name ?? '-')
            ->addColumn('approval_info', fn($row) => $this->getApprovalInfoColumn($row))
            ->addColumn('created_at', fn($row) => $row->created_at?->format('d/m/Y H:i') ?? '-');
    }

    /*
    |--------------------------------------------------------------------------
    | Columns that contain raw HTML and should not be escaped.
    |--------------------------------------------------------------------------
    */
    protected function rawColumns(): array
    {
        return ['offer_name', 'approval_info'];
    }


    /*
    |--------------------------------------------------------------------------
    | Apply additional filters based on request input.
    |--------------------------------------------------------------------------
    */
    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('customer')) {
            $query->where('customer_id', $req->customer);
        }
        if ($req->filled('employee')) {
            $query->where('relationship_manager_id', $req->employee);
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Columns to include in the global search.
    |--------------------------------------------------------------------------
    */
    protected function getSearchableColumns(): array
    {
        return [
            'offer_name',
            'offer_number',
            'customer.name',
            'relationshipManager.name',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Default ordering: newest created_at first.
    |--------------------------------------------------------------------------
    */
    protected function getCustomOrder(): array
    {
        return [6, 'desc'];
    }

    /*
    |--------------------------------------------------------------------------
    |  Render the approval status badge and next approver.
    |  Wraps logic in try/catch and logs any exception in detail.
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
