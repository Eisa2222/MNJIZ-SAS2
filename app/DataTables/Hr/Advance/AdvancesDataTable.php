<?php

namespace App\DataTables\Hr\Advance;

use App\DataTables\ArabicSearchDataTable;
use App\Enums\Hr\Advance\AdvanceStatus;
use App\Models\Hr\Advances\Advance;

class AdvancesDataTable extends ArabicSearchDataTable
{
    private $route = "hr.advances";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "advances-table";
    }

    protected function resource()
    {
        return Advance::query()->with('employee');
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

            ['data' => 'advance_number',     'name' => 'advance_number',    'title' => 'المرجع'],
            ['data' => 'employee_name',      'name' => 'employee.name',     'title' => 'الموظف'],
            ['data' => 'advance_type',       'name' => 'advance_type',      'title' => 'نوع السلفية'],
            ['data' => 'amount',             'name' => 'amount',            'title' => 'المبلغ'],
            ['data' => 'advance_date',       'name' => 'advance_date',      'title' => 'تاريخ السلفة'],
            ['data' => 'due_date',           'name' => 'due_date',          'title' => 'تاريخ الاستحقاق'],
            ['data' => 'status',             'name' => 'status',            'title' => 'الحالة'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '150px',],
        ];
    }


    /*
    |============================================================================
    |                           Helper Methods
    |============================================================================
    */
    protected function addCustomColumns($datatable)
    {
        $datatable
            ->addColumn('checkbox', function ($row) {
                return $this->checkbox($row);
            })

            ->editColumn('advance_number', function ($row) {
                return $this->routeName(route('hr.advances.show', $row->id), $row->advance_number);
            })

            ->addColumn('employee_name', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->employee_id), $row->employee?->getRawNameAttribute() ?? '-');
            })

            ->editColumn('advance_type', function ($row) {
                return $this->getAdvanceType($row);
            })

            ->editColumn('amount', function ($row) {
                return number_format($row->amount ?? 0, 2) . '<span class="icon-saudi_riyal mx-2"></span>';
            })

            ->editColumn('advance_date', function ($row) {
                return $row->advance_date?->format('Y-m-d') ?? '-';
            })

            ->editColumn('due_date', function ($row) {
                return $row->due_date?->format('Y-m-d') ?? '-';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })


            ->addColumn('actions', function ($row) {
                if (!$row->status->canEdit()) {
                    return "لا يمكن التعديل و الحذف";
                }
                return (auth()->user()->can('تعديل سلفة') || auth()->user()->can('حذف سلفة')) ? view('hr.advances.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('advance_type', $request->type);
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'advance_number', 'employee_name', 'amount', 'status', 'actions'];
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
            'advance_number',
            'employee.name',
            'employee.nickname',
            'advance_type',
            'amount',
            'status',
            'due_date',
            'advance_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة سلفة')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة سلفة',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route . '.create') . "'; }",
                ],
            ];
        }
        return [];
    }

    /*
    |============================================================================
    |                            Private Helper Methods
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

    private function getAdvanceType($row): string
    {
        return $row->advance_type->label();
    }
}
