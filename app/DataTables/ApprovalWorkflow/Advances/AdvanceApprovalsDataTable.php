<?php

namespace App\DataTables\ApprovalWorkflow\Advances;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Advances\Advance;
use App\Services\ApprovalWorkflow\ApprovalWorkflowInterface;

class AdvanceApprovalsDataTable extends ArabicSearchDataTable
{

    /*
    |--------------------------------------------------------------------------
    | Route Name
    |--------------------------------------------------------------------------
    | الاسم الأساسي للمسارات المستخدمة في توليد الروابط.
    */
    private string $route = 'approval-workflow.advances';

    /*
    |--------------------------------------------------------------------------
    | Table ID
    |--------------------------------------------------------------------------
    | المعرف الفريد لجدول HTML.
    */
    protected function getTableId(): string
    {
        return 'approval-advances-table';
    }

    /*
    |--------------------------------------------------------------------------
    | Resource Query
    |--------------------------------------------------------------------------
    | الاستعلام الأساسي لجلب البيانات من قاعدة البيانات.
    */
    protected function resource()
    {
        return Advance::query()
            ->with([
                'employee:id,name,nickname',
                'approvalRequest.requestLevels.employee:id,name,nickname',
            ])
            ->where('status', 'pending');
    }

    /*
    |--------------------------------------------------------------------------
    | Columns Definition
    |--------------------------------------------------------------------------
    | تعريف أعمدة الجدول التي ستظهر في الواجهة.
    */
    protected function getColumns(): array
    {
        return [
            ['data' => 'advance_number', 'name' => 'advance_number', 'title' => 'رقم السلفة', 'className' => 'text-center'],
            ['data' => 'employee_name',  'name' => 'employee.name', 'title' => 'اسم الموظف'],
            ['data' => 'advance_type',   'name' => 'advance_type', 'title' => 'نوع السلفة'],
            ['data' => 'amount',         'name' => 'amount', 'title' => 'المبلغ'],
            ['data' => 'approval_info',  'name' => 'approval_info', 'title' => 'حالة الاعتماد', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ['data' => 'created_at',     'name' => 'created_at', 'title' => 'تاريخ الطلب', 'searchable' => false, 'className' => 'text-center'],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Columns
    |--------------------------------------------------------------------------
    | تعديل محتوى الأعمدة لإضافة روابط، تنسيق، أو أي منطق مخصص.
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->editColumn('advance_number', function ($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->advance_number)
                );
            })

            ->addColumn('employee_name', function ($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->employee?->name ?? '-')
                );
            })

            ->editColumn('advance_type', function ($row) {
                return $this->routeName(
                    route($this->route . '.show', $row->id),
                    e($row->advance_type?->label() ?? '-')
                );
            })

            ->editColumn('amount', fn($row) => number_format($row->amount, 2) . ' ' . 'ر.س')

            ->addColumn('approval_info', fn($row) => $this->getApprovalInfoColumn($row))

            ->editColumn('created_at', fn($row) => $row->created_at?->format('d/m/Y') ?? '-');
    }

    /*
    |--------------------------------------------------------------------------
    | Raw Columns
    |--------------------------------------------------------------------------
    | تحديد الأعمدة التي تحتوي على HTML لمنع تهريبها (escaping).
    */
    protected function rawColumns(): array
    {
        // **تعديل:** إضافة الأعمدة التي أصبحت روابط إلى هذه المصفوفة.
        return ['advance_number', 'employee_name', 'advance_type', 'approval_info'];
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Filters
    |--------------------------------------------------------------------------
    | تطبيق الفلاتر الإضافية على الاستعلام بناءً على مدخلات المستخدم.
    */
    protected function applyCustomFilters($query)
    {
        $req = request();
        if ($req->filled('employee_id')) {
            $query->where('employee_id', $req->employee_id);
        }
        if ($req->filled('advance_type')) {
            $query->where('advance_type', $req->advance_type);
        }
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Approval Info Column
    |--------------------------------------------------------------------------
    | دالة مساعدة لتوليد محتوى عمود "حالة الاعتماد".
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
