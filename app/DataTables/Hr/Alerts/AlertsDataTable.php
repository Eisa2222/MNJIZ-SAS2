<?php

namespace App\DataTables\Hr\Alerts;


use App\DataTables\ArabicSearchDataTable;
use App\Enums\Hr\Alert\AlertStatus;
use App\Models\Hr\Alert\Alert;

class AlertsDataTable extends ArabicSearchDataTable
{
    private $route = "hr.alerts";
    private $page  = "hr.alerts";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "alerts-table";
    }

    protected function resource()
    {
        return Alert::query()->with('employee');
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

            ['data' => 'type',              'name' => 'type',               'title' => 'التنبيه',       'width' => '150px',],
            ['data' => 'employee_name',     'name' => 'employee_name',      'title' => 'الموظف',        'width' => '150px',],
            ['data' => 'status',            'name' => 'status',             'title' => 'الحالة',        'width' => '50px',],
            ['data' => 'created_at',        'name' => 'created_at',         'title' => 'تاريخ التنبيه', 'width' => '50px',     'defaultContent' => '-',],
            ['data' => 'updated_by',        'name' => 'updated_by',         'title' => 'تم بواسطة',      'width' => '50px',],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '50px'],
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

            ->editColumn('type', function ($row) {
                return $this->getType($row);
            })

            ->addColumn('employee_name', function ($row) {
                return $this->routeName(route('account.employee.profile', $row->employee_id), $row->employee?->getRawNameAttribute() ?? '-');
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at->format("Y-m-d");
            })

            ->addColumn('updated_by', function ($row) {
                return $row->updatedBy ? $this->routeName(route('account.employee.profile', $row->updated_by), $row->updatedBy?->getRawNameAttribute() ?? '-') : '';
            })


            ->addColumn('actions', function ($row) {
                return $row->status != AlertStatus::New
                    ? "<p class='py-2 mb-0'>تم التجديد</p>"
                    : view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render();
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'employee_name', 'updated_by', 'status', 'actions'];
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

    private function getType($row): string
    {
        return $row->type->label();
    }
}
