<?php

namespace App\DataTables\ApprovalWorkflow;

use App\DataTables\ArabicSearchDataTable;
use App\Models\ApprovalSystem\ApprovalRequest;
use App\Models\SystemAdministration\CredentialsManagement\ApprovalFlow;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;
use App\Helpers\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class UnifiedApprovalDataTable extends ArabicSearchDataTable
{
    /*
    |--------------------------------------------------------------------------
    | Table ID
    |--------------------------------------------------------------------------
    */
    protected function getTableId(): string
    {
        return 'unified-approvals-table';
    }

    /*
    |--------------------------------------------------------------------------
    | Resource Query
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | Resource Query
    |--------------------------------------------------------------------------
    */
    protected function resource()
    {
        $employeeId = auth()->user()?->employee?->id;
        if (!$employeeId) {
            return ApprovalRequest::whereRaw('1 = 0');
        }

        $allowedFlowTypes = [];
        $flowTypes = array_keys(ApprovalFlow::getAllTypeLabels());
        foreach ($flowTypes as $flowType) {
            if (Helpers::isAssignedToApprovalType($flowType, $employeeId)) {
                $allowedFlowTypes[] = $flowType;
            }
        }

        if (empty($allowedFlowTypes)) {
            return ApprovalRequest::whereRaw('1 = 0');
        }

        return ApprovalRequest::query()
            ->select('approval_requests.*')
            ->with([
                'flow:id,type',
                'approvable',
                'requestLevels.employee:id,name,nickname',
                'requestedBy:id,name'
            ])
            ->where('approval_requests.status', 'pending')
            ->whereNotNull('approvable_id')
            ->whereNotNull('approvable_type')
            ->whereHas('approvable')
            ->whereHas('requestLevels', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                    ->whereColumn('level', 'approval_requests.current_level');
            })
            ->whereHas('flow', function ($query) use ($allowedFlowTypes) {
                $query->whereIn('type', $allowedFlowTypes);
            })
            ->orderBy('approval_requests.created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */
    protected function getColumns(): array
    {
        return [
            ['data' => '', 'orderable' => false, 'searchable' => false, 'width' => '5%'],
            ['data' => 'type_name', 'name' => 'flow.type', 'title' => 'نوع الطلب', 'className' => 'text-center'],
            ['data' => 'item_info', 'name' => 'item_info', 'title' => 'تفاصيل الطلب', 'orderable' => false, 'searchable' => false],
            ['data' => 'requested_by', 'name' => 'requestedBy.name', 'title' => 'مقدم الطلب', 'className' => 'text-center'],
            ['data' => 'approval_info', 'name' => 'approval_info', 'title' => 'حالة الاعتماد', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ['data' => 'created_at', 'name' => 'approval_requests.created_at', 'title' => 'تاريخ الطلب', 'className' => 'text-center'],
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
            ->addColumn('type_name', function ($row) {
                $typeLabels = ApprovalFlow::getAllTypeLabels();
                $typeName = $typeLabels[$row->flow->type] ?? $row->flow->type;
                $color = $this->getTypeColor($row->flow->type);
                return sprintf('<span class="badge bg-label-%s" style="">%s</span>', $color, e($typeName));
            })
            ->addColumn('item_info', function ($row) {
                return $this->getItemInfo($row);
            })
            ->addColumn('requested_by', function ($row) {
                return e($row->requestedBy->name ?? 'غير محدد');
            })
            ->addColumn('approval_info', function ($row) {
                return $this->getApprovalInfoColumn($row);
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at?->format('d/m/Y H:i') ?? '-';
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Raw Columns
    |--------------------------------------------------------------------------
    */
    protected function rawColumns(): array
    {
        return ['type_name', 'item_info', 'approval_info'];
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Custom Filters
    |--------------------------------------------------------------------------
    */
    protected function applyCustomFilters($query)
    {
        $request = request();
        $employeeId = auth()->user()?->employee?->id;

        if ($request->filled('filter_type')) {
            if (Helpers::isAssignedToApprovalType($request->filter_type, $employeeId)) {
                $query->whereHas('flow', function ($q) use ($request) {
                    $q->where('type', $request->filter_type);
                });
            }
        }

        if ($request->filled('filter_employee')) {
            $query->where('approval_requests.requested_by_user_id', $request->filter_employee);
        }

        if ($request->filled('filter_date_from')) {
            $query->whereDate('approval_requests.created_at', '>=', $request->filter_date_from);
        }

        if ($request->filled('filter_date_to')) {
            $query->whereDate('approval_requests.created_at', '<=', $request->filter_date_to);
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Searchable Columns
    |--------------------------------------------------------------------------
    */
    protected function getSearchableColumns(): array
    {
        return ['requestedBy.name', 'flow.type'];
    }

    /*
    |--------------------------------------------------------------------------
    | Global Search
    |--------------------------------------------------------------------------
    */
    protected function applyGlobalSearch($instance)
    {
        $searchValue = request('search.value');
        if (!$searchValue) return;

        $instance->where(function ($query) use ($searchValue) {
            $query->whereHas('requestedBy', function ($q) use ($searchValue) {
                $q->where('name', 'LIKE', "%{$searchValue}%");
            });

            $flowTypes = ApprovalFlow::getAllTypeLabels();
            $matchingTypes = [];

            foreach ($flowTypes as $key => $label) {
                if (stripos($label, $searchValue) !== false) {
                    $matchingTypes[] = $key;
                }
            }

            if (!empty($matchingTypes)) {
                $query->orWhereHas('flow', function ($q) use ($matchingTypes) {
                    $q->whereIn('type', $matchingTypes);
                });
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Order
    |--------------------------------------------------------------------------
    */
    protected function getCustomOrder(): array
    {
        return [5, 'desc'];
    }

    /*
    |--------------------------------------------------------------------------
    | Action Buttons
    |--------------------------------------------------------------------------
    */
    protected function getActionButtons(): array
    {
        return [
            [
                'extend' => 'print',
                'text' => 'طباعة',
                'exportOptions' => ['columns' => ':not(:last-child)'],
            ],
            [
                'extend' => 'pdf',
                'text' => 'PDF',
                'exportOptions' => ['columns' => ':not(:last-child)'],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Type Color
    |--------------------------------------------------------------------------
    */
    protected function getTypeColor(string $type): string
    {
        return match ($type) {
            'offer' => 'primary',
            'contract' => 'success',
            'leave' => 'warning',
            'wps' => 'info',
            'clearance_certificate' => 'dark',
            'advance' => 'secondary',
            'reward' => 'success',
            'deduction' => 'danger',
            'content' => 'info',
            'custody' => 'primary',
            default => 'secondary',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Get Item Info
    |--------------------------------------------------------------------------
    */
    protected function getItemInfo($approvalRequest): string
    {
        $item = $approvalRequest->approvable;
        if (!$item) {
            return '<span class="text-warning">غير متاح</span>';
        }

        try {
            $type = $approvalRequest->flow->type;
            $url = $this->getOldRouteForType($type, $item->id);
            $title = $this->getRealNameByType($type, $item);

            return sprintf('<a href="%s" class="text-decoration-none">%s</a>', $url, e($title));
        } catch (\Throwable $e) {
            return '<span class="text-warning">غير متاح</span>';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Old Route For Type
    |--------------------------------------------------------------------------
    */
    protected function getOldRouteForType(string $type, int $itemId): string
    {
        return match ($type) {
            'offer' => route('approval-workflow.offers.show', $itemId),
            'contract' => route('approval-workflow.contracts.show', $itemId),
            'leave' => route('approval-workflow.leave-requests.show', $itemId),
            'wps' => route('approval-workflow.wps-payrolls.show', $itemId),
            'clearance_certificate' => route('approval-workflow.clearance-certificates.show', $itemId),
            'advance' => route('approval-workflow.advances.show', $itemId),
            'reward' => route('approval-workflow.rewards.show', $itemId),
            'deduction' => route('approval-workflow.deductions.show', $itemId),
            'content' => route('approval-workflow.content.show', $itemId),
            'custody' => route('approval-workflow.custodies.show', $itemId),
            default => route('approval-workflow.unified.show', [$type, $itemId]),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Get Real Name By Type
    |--------------------------------------------------------------------------
    */
    protected function getRealNameByType(string $type, $item): string
    {
        return match ($type) {
            'offer' => $item->offer_name ?? "عرض #{$item->id}",
            'contract' => $item->contract_name ?? "عقد #{$item->id}",
            'leave' => ($item->leaveType->name ?? 'إجازة') . " - {$item->days_count} يوم",
            'wps' => "مسيرة رواتب " . ($item->payroll_month ?? "#{$item->id}"),
            'clearance_certificate' => "إخلاء طرف - " . ($item->employee->name ?? "موظف #{$item->id}"),
            'advance' => "سلفة {$item->amount} ريال - " . ($item->employee->name ?? "موظف #{$item->id}"),
            'reward' => "مكافأة {$item->amount} ريال - " . ($item->employee->name ?? "موظف #{$item->id}"),
            'deduction' => "خصم {$item->amount} ريال - " . ($item->employee->name ?? "موظف #{$item->id}"),
            'content' => $item->title ?? "محتوى #{$item->id}",
            'custody' => "عهدة - " . ($item->employee->name ?? "موظف #{$item->id}"),
            default => "طلب #{$item->id}",
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Get Approval Info Column
    |--------------------------------------------------------------------------
    */
    protected function getApprovalInfoColumn($approvalRequest): string
    {
        try {
            if (!$approvalRequest->approvable) {
                return '<span class="text-danger">بيانات غير متوفرة</span>';
            }

            $svc = app(ApprovalWorkflowInterface::class);
            $statusData = $svc->getApprovalStatus($approvalRequest->approvable);

            if (!$statusData || !isset($statusData['next_approver'])) {
                return '<span class="text-warning">غير محدد</span>';
            }

            $nextApprover = $statusData['next_approver'] ?? 'غير محدد';
            $currentLevel = $approvalRequest->current_level ?? 0;
            $totalLevels = $approvalRequest->requestLevels()->count() ?? 0;

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
            return '<span class="text-warning">غير متاح</span>';
        }
    }
}
