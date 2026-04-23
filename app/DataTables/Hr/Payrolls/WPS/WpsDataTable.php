<?php

namespace App\DataTables\Hr\Payrolls\WPS;


use App\DataTables\ArabicSearchDataTable;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;

class WpsDataTable extends ArabicSearchDataTable
{
    private $page = "hr.payrolls.wps";
    private $route = "hr.payrolls.wps";

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
        return WpsPayroll::query();
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

            ['data' => 'reference',     'name' => 'reference',       'title' => 'المرجع',               'width' => '20px'],
            ['data' => 'run_date',      'name' => 'run_date',        'title' => 'التاريخ ',        'width' => '20px'],
            // ['data' => 'created_by',    'name' => 'created_by',      'title' => 'تم بواسطة',             'width' => '20px'],
            ['data' => 'status',        'name' => 'status',          'title' => 'الحالة',                'width' => '20px'],
            ['data' => 'total_gross',   'name' => 'total_gross',     'title' => 'الإجمالي قبل المعالجات', 'width' => '20px'],
            ['data' => 'total_net',     'name' => 'total_net',       'title' => 'الإجمالي بعد المعالجات', 'width' => '20px'],

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

            ->addColumn('reference', function ($row) {
                return $this->routeName(route($this->route . '.details.index', $row->id), $row->reference);
            })


            ->editColumn('run_date', function ($row) {
                return $row->run_date->format("Y-m-d");
            })

            // ->editColumn('created_by', function ($row) {
            //     return $row->created_by ? $row->createdBy->raw_name : "تلقائي من النظام";
            // })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->editColumn('total_gross', function ($row) {
                return $row->total_gross ? '<div>' . number_format($row->total_gross, 2) . '<span class="sar pe-1 text-primary">SAR</span></div>' : 0;
            })

            ->editColumn('total_net', function ($row) {
                return $row->total_net ? '<div>' . number_format($row->total_net, 2) . '<span class="sar pe-1 text-primary">SAR</span></div>' : 0;
            })


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
