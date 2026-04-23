<?php

namespace App\DataTables\Hr\EditRequest;


use App\DataTables\ArabicSearchDataTable;
use App\Models\ElectronicServices\EditRequest\EmployeeEditRequest;

class EditRequestDataTable extends ArabicSearchDataTable
{
    private $route = "hr.modification-requests";


    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "modification-requests-table";
    }

    protected function resource()
    {
        return EmployeeEditRequest::query()->with('employee');
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

            ['data' => 'employee_id',      'name' => 'employee_id',      'title' => 'الموظف'],
            ['data' => 'created_at',       'name' => 'created_at',       'title' => 'تاريخ الطلب'],
            ['data' => 'count',            'name' => 'count',            'title' => 'عدد التعديلات'],
            ['data' => 'status',           'name' => 'status',           'title' => 'الحالة'],
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

            ->editColumn('employee_id', function ($row) {
                return $this->routeName(route($this->route . ".show", $row->id), $row->employee->name);
            })

            ->addColumn('count', function ($row) {
                return $row->fields->count();
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d') ?? '-';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
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
        return ['checkbox', 'employee_id', 'status', 'actions'];
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
            'employee.name',
            'employee.nickname',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
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
}
