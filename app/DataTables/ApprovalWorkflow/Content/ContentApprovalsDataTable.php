<?php

namespace App\DataTables\ApprovalWorkflow\Content;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Marketing\ContentManagement\ContentManagement;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use Illuminate\Support\Str;

class ContentApprovalsDataTable extends ArabicSearchDataTable
{
    /*
    |--------------------------------------------------------------------------
    | Route Name
    |--------------------------------------------------------------------------
    */
    private string $route = 'approval-workflow.content';

    /*
    |--------------------------------------------------------------------------
    | Table ID
    |--------------------------------------------------------------------------
    */
    protected function getTableId(): string
    {
        return 'approval-content-table';
    }

    /*
    |--------------------------------------------------------------------------
    | Resource Query
    |--------------------------------------------------------------------------
    */
    protected function resource()
    {
        return ContentManagement::query()
            ->with(['contentType:id,name', 'approvalRequest.requestLevels.employee:id,name,nickname'])
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
            ['data' => 'id', 'name' => 'id', 'title' => 'المعرف'],
            ['data' => 'content_text', 'name' => 'content_text', 'title' => 'نص المحتوى'],
            ['data' => 'contentType.name', 'name' => 'contentType.name', 'title' => 'نوع المحتوى'],
            ['data' => 'approval_info', 'name' => 'approval_info', 'title' => 'حالة الاعتماد', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاريخ الإنشاء', 'searchable' => false],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Columns Rendering
    |--------------------------------------------------------------------------
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->editColumn('id', function($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->id)
                );
            })
            ->editColumn('content_text', function($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    Str::limit(e($row->content_text), 50)
                );
            })
            ->editColumn('contentType.name', fn($row) => $row->contentType?->name ?? '-')
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
        return ['id', 'content_text', 'approval_info'];
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Filters
    |--------------------------------------------------------------------------
    */
    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('content_type_id')) {
            $query->where('content_type_id', $req->content_type_id);
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

            return sprintf(
                '<div class="text-center"><span class="badge bg-label-info">المستوى %d من %d</span><br><small class="text-muted">%s</small></div>',
                $currentLevel,
                $totalLevels,
                e($nextApprover)
            );
        } catch (\Throwable $e) {
            return '<span class="text-danger">خطأ في عرض الحالة</span>';
        }
    }
}
