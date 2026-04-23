<?php

namespace App\DataTables\Hr\Payrolls\WPS;



use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Payrolls\WPS\WpsPayrollDetailRevision;

class WpsPayrollRevisionDataTable extends ArabicSearchDataTable
{
    private $page = "hr.payrolls.wps.details.revision";
    private $route = "hr.payrolls.wps.details.revision";

    public $wps_payroll_details;


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
        return WpsPayrollDetailRevision::where('wps_payroll_detail_id', $this->wps_payroll_details->id);
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

            ['data' => 'basic',          'name' => 'basic',          'title' => 'الراتب الاساسي',       'width' => '20px'],
            ['data' => 'transport',      'name' => 'transport',      'title' => 'بدل النقل',            'width' => '20px'],
            ['data' => 'housing',        'name' => 'housing',        'title' => 'بدل السكن',            'width' => '20px'],
            ['data' => 'other',          'name' => 'other',          'title' => 'بدلات اخرى',           'width' => '20px'],
            ['data' => 'insurance',     'name' => 'insurance',     'title' => 'التأمينات',          'width' => '20px'],
            ['data' => 'deductions',     'name' => 'deductions',     'title' => 'الاستقطاعات',          'width' => '20px'],

            ['data' => 'incentives',     'name' => 'incentives',     'title' => 'الحوافز',              'width' => '20px'],
            ['data' => 'notes',         'name' => 'notes',         'title' => 'الملاحظات ',              'width' => '10px'],

            ['data' => 'status',         'name' => 'status',         'title' => 'الحالة ',              'width' => '10px'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '20px'],
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

            ->addColumn('basic', function ($row) {
                return $row->new_values['basic'] ?? '-';
            })

            ->addColumn('transport', function ($row) {
                return $row->new_values['transport'] ?? '-';
            })

            ->addColumn('housing', function ($row) {
                return $row->new_values['housing'] ?? '-';
            })

            ->addColumn('other', function ($row) {
                return $row->new_values['other'] ?? '-';
            })

            // التامين
            ->addColumn('insurance', function ($row) {
                return $row->new_values['insurance'] ?? '-';
            })
            // الاستقطاعات
            ->addColumn('deductions', function ($row) {
                return $row->new_values['deductions'] ?? '-';
            })
            // الحوافز
            ->addColumn('incentives', function ($row) {
                return $row->new_values['incentives'] ?? '-';
            })
            // الصافى
            ->addColumn('net', function ($row) {
                return $row->new_values['net'] ?? '-';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })


            ->addColumn('actions', function ($row) {
                if (! $row->isPending()) {
                    return '<small>تم التعامل معها لا يمكن التعديل عليها</small>';
                }
                return view($this->page . '.action', [
                    'row'           => $row,
                    'wps_payroll'   => $row->wpsPayrollDetail->wpsPayroll,
                    'employee'      => $row->wpsPayrollDetail->employee
                ])->render();
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
        return ['checkbox', 'reference',  'status', 'total_gross', 'total_net', 'actions'];
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

    protected function getButtons(): array
    {
        return [
            [
                'text'      => ' توليد الرواتب يدويا',
                'className' => 'btn btn-primary btn-add',
                'attr'      => [
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#generateModal'
                ],
            ],
        ];
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
