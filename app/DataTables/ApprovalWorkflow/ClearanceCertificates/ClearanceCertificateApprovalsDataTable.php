<?php

namespace App\DataTables\ApprovalWorkflow\ClearanceCertificates;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Self_services\ClearanceCertificate;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ClearanceCertificateApprovalsDataTable extends ArabicSearchDataTable
{
    private string $route = 'approval-workflow.clearance-certificates';

    protected function getTableId(): string
    {
        return 'approval-clearance-certificates-table';
    }

    protected function resource()
    {
        return ClearanceCertificate::query()
            ->with([
                'user:id,name',
                'approvalRequest.requestLevels.employee:id,name,nickname',
            ])
            ->where('status', 'pending');
    }

    protected function getColumns(): array
    {
        return [
            ['data' => 'id', 'name' => 'id', 'title' => '#', 'orderable' => false, 'searchable' => false],
            ['data' => 'user_name',  'name' => 'user.name',      'title' => 'اسم الموظف'],
            ['data' => 'reason',     'name' => 'reason',         'title' => 'سبب الطلب'],
            ['data' => 'approval_info', 'name' => 'approval_info', 'title' => 'حالة الاعتماد', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'تاريخ الطلب', 'searchable' => false, 'className' => 'text-center'],
        ];
    }

    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn('user_name', fn($row) => $row->user->name ?? '-')
            ->addColumn('reason', fn($row) => $this->routeName(route($this->route . '.show', $row->id), e(Str::limit($row->reason, 50))))
            ->addColumn('approval_info', fn($row) => $this->getApprovalInfoColumn($row))
            ->addColumn('created_at', fn($row) => Carbon::parse($row->created_at)->format('d/m/Y H:i') ?? '-');
    }

    protected function rawColumns(): array
    {
        return ['reason', 'approval_info'];
    }

    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('user')) {
            $query->where('user_id', $req->user);
        }
        return $query;
    }

    protected function getSearchableColumns(): array
    {
        return ['user.name', 'reason'];
    }

    protected function getCustomOrder(): array
    {
        return [4, 'desc'];
    }

    private function getApprovalInfoColumn($item): string
    {
        try {
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
