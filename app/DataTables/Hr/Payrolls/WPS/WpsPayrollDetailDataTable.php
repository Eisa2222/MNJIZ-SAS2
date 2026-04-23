<?php

namespace App\DataTables\Hr\Payrolls\WPS;



use App\DataTables\ArabicSearchDataTable;
use App\Enums\Hr\Payrolls\WPS\WpsStatus;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Models\Hr\Payrolls\WPS\WpsPayrollDetail;

class WpsPayrollDetailDataTable extends ArabicSearchDataTable
{
    private $page = "hr.payrolls.wps.details";
    private $route = "hr.payrolls.wps.details";

    public $wps_payroll;


    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "wps-table";
    }

    protected function resource()
    {
        return WpsPayrollDetail::where('wps_payroll_id', $this->wps_payroll->id);
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

            ['data' => 'employee_id',    'name' => 'employee_id',    'title' => 'الموظف',               'width' => '150px'],
            ['data' => 'basic',          'name' => 'basic',          'title' => 'الراتب الاساسي',       'width' => '20px'],
            ['data' => 'transport',      'name' => 'transport',      'title' => 'بدل النقل',            'width' => '20px'],
            ['data' => 'housing',        'name' => 'housing',        'title' => 'بدل السكن',            'width' => '20px'],
            ['data' => 'other',          'name' => 'other',          'title' => 'بدلات اخرى',           'width' => '20px'],
            ['data' => 'deductions',     'name' => 'deductions',     'title' => 'الاستقطاعات',          'width' => '20px'],
            ['data' => 'incentives',     'name' => 'incentives',     'title' => 'الحوافز',              'width' => '20px'],
            ['data' => 'net',            'name' => 'net',            'title' => 'الصافي',               'width' => '20px'],
            ['data' => 'status',         'name' => 'status',         'title' => 'الحالة ',              'width' => '10px'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '10px'],
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

            ->addColumn('employee_id', function ($row) {
                return $this->routeName(route($this->route . '.show', [$row->wps_payroll_id, $row->employee_id]), $row->employee->raw_name);
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            // إضافة عمود actions
            ->addColumn('actions', function ($row) {

                return view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render();
            });
    }


    // protected function applyCustomFilters($query)
    // {
    //     $request = request();

    //     if ($request->filled('hr_status_id')) {
    //         $query->where('hr_status_id', $request->hr_status_id);
    //     }

    //     if ($request->role) {
    //         $query->whereHas('user.roles', function ($q) use ($request) {
    //             $q->where('id', $request->role);
    //         });
    //     }

    //     if ($request->insurance_status) {
    //         $query->where('insurance_status', $request->insurance_status);
    //     }

    //     return $query;
    // }


    protected function rawColumns(): array
    {
        return ['checkbox', 'employee_id',  'status', 'total_gross', 'total_net', 'actions'];
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
            'name',
            'nickname',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }


    // في DataTable Class
    protected function getButtons(): array
    {
        if ($this->wps_payroll->canBeApproved()) {
            return [
                [
                    'text'      => 'تصديق المسير',
                    'className' => 'btn btn-primary btn-add approve-payroll-btn',
                    'attr'      => [
                        'data-url' => route('hr.payrolls.wps.approve', $this->wps_payroll->id),
                        'id' => 'approve-payroll-btn'
                    ],
                ],
            ];
        } else
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
}
