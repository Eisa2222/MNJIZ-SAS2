<?php

namespace App\DataTables\Hr\Deduction;

use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Deductions\Deduction;

class DeductionsDataTable extends ArabicSearchDataTable
{
    private $route = "hr.deductions";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "deductions-table";
    }

    protected function resource()
    {
        $query = Deduction::query()->with('employee');

        return $this->applyRequestFilters($query, ['employee_id', 'deduction_type', 'status']);
    }

    protected function getColumns(): array
    {
        return [
            [
                'data' => '',
                'orderable'  => false,
                'searchable' => false,
            ],
            [
                'data' => 'id',
                'name' => 'id',
                'title' => 'ID',
                'visible' => false,
                'orderable' => true,
                'searchable' => false,
            ],
            // Checkbox
            [
                'data'       => 'checkbox',
                'name'       => 'checkbox',
                'title'      => '<span class="custom-checkbox-header"><input type="checkbox" id="select-all"></span>',
                'orderable'  => false,
                'searchable' => false,
                'width'      => '10px',
                'className'  => 'custom-checkbox',
                'titleAttr'  => 'تحديد الكل',
            ],

            ['data' => 'deduction_number',      'name' => 'deduction_number',       'title' => 'المرجع'],
            ['data' => 'employee_name',         'name' => 'employee.name',          'title' => 'الموظف'],
            ['data' => 'deduction_type',        'name' => 'deduction_type',         'title' => 'نوع الخصم المالي'],
            ['data' => 'amount',                'name' => 'amount',                 'title' => 'القيمة'],
            ['data' => 'deduction_date',        'name' => 'deduction_date',         'title' => 'تاريخ الخصم المالي'],
            ['data' => 'status',                'name' => 'status',                 'title' => 'الحالة'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '150px',],
        ];
    }

    /*
    |============================================================================
    |                              Helper Methods
    |============================================================================
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn('checkbox', function ($row) {
                return $this->checkbox($row);
            })

            ->editColumn('deduction_number', function ($row) {
                return $this->routeName(route('hr.deductions.show', $row->id), $row->deduction_number);
            })

            ->addColumn('employee_name', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->employee_id), $row->employee?->getRawNameAttribute() ?? '-');
            })

            ->editColumn('deduction_type', function ($row) {
                return $this->getDeductionType($row);
            })

            ->editColumn('amount', function ($row) {
                return number_format($row->amount ?? 0, 2) . '<span class="icon-saudi_riyal mx-2"></span>';
            })

            ->editColumn('deduction_date', function ($row) {
                return $row->deduction_date?->format('Y-m-d') ?? '-';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                if (!$row->status->canEdit()) {
                    return "لا يمكن التعديل و الحذف";
                }
                return (auth()->user()->can('تعديل خصم مالي') || auth()->user()->can('حذف خصم مالي')) ? view('hr.deductions.action', ['row' => $row, 'route' => $this->route])->render()  : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('deduction_type', $request->type);
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        return $query;
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'deduction_number', 'employee_name', 'amount', 'status', 'actions'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // الحقول المسموح البحث فيها
    protected function getSearchableColumns(): array
    {
        return [
            'deduction_number',
            'employee.name',
            'employee.nickname',
            'deduction_type',
            'amount',
            'status',
            'deduction_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة خصم مالي')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة خصم مالي',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
                ],
            ];
        }
        return [];
    }

    /*
    |============================================================================
    |                         Private Helper Methods
    |============================================================================
    */
    private function statusBadge($row): string
    {

        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->status->color(),
            $row->status->label()
        );
    }

    private function getDeductionType($row): string
    {
        return $row->deduction_type->label();
    }
}
