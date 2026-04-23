<?php

namespace App\DataTables\Hr\Custody\Item;

use App\DataTables\ArabicSearchDataTable;
use App\Enums\ElectronicServices\Custody\Requests\CustodyReturnStatus;
use App\Enums\Hr\Custody\Item\Custody;
use App\Enums\Hr\Custody\Item\CustodyUseStatus;
use App\Models\Hr\Custody\Item\CustodyItem;
use App\Models\Hr\Employees\Employees;

class CustodyItemDataTable extends ArabicSearchDataTable
{
    private $route = "hr.custody.items";
    private $employees;
    private $returnStatus;


    public function __construct()
    {
        $this->employees = Employees::active()->select(['id', 'name', 'nickname'])
            ->orderBy('id', 'desc')
            ->get();

        $this->returnStatus = CustodyReturnStatus::options();
    }

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "item-table";
    }

    protected function resource()
    {
        return CustodyItem::query();
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

            ['data' => 'name',                  'name' => 'name',                   'title' => 'الاصل',                'width' => '150px'],
            ['data' => 'serial_number',         'name' => 'serial_number',          'title' => 'الرقم التسلسلي',      'width' => '150px'],

            ['data' => 'asset_category_id',     'name' => 'asset_category_id',      'title' => 'التصنيف',             'width' => '150px'],
            ['data' => 'storage_location_id',   'name' => 'storage_location_id',    'title' => 'مرجعية الاصل',         'width' => '150px'],

            ['data' => 'use_status',            'name' => 'use_status',             'title' => 'حالة الاستخدام',       'width' => '150px'],
            ['data' => 'custody_status',        'name' => 'custody_status',         'title' => 'حالة الاصل',            'width' => '150px'],
            ['data' => 'assign',                'name' => 'assign',                 'title' => 'مسندة لدى',            'width' => '150px',   'orderable' => false,],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '150px'],
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

            ->editColumn('name', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->name);
            })

            ->editColumn('asset_category_id', function ($row) {
                return $row->assetCategory->name;
            })

            ->editColumn('storage_location_id', function ($row) {
                return $row->storageLocation->name;
            })

            ->editColumn('use_status', function ($row) {
                return $this->UseStatusBadge($row);
            })

            ->editColumn('custody_status', function ($row) {
                return $this->CustodyStatusBadge($row);
            })

            ->addColumn('assign', function ($row) {
                return $row->use_status == CustodyUseStatus::Available ? "-" : $row->logs->first()->request->employee->name ?? '-';
            })

            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل اصل') || auth()->user()->can('حذف اصل')) ? view('hr.custody.items.action', ['row' => $row, 'route' => $this->route, 'employees' => $this->employees, 'returnStatus' => $this->returnStatus])->render()  : ' ليس لديك صلاحيات';
            });
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('use_status', $request->status);
        }

        if ($request->filled('location')) {
            $query->where('storage_location_id', $request->location);
        }

        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }

        return $query;
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'name',  'use_status', 'custody_status', 'actions'];
    }
    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */
    // الحقول المسموح البحث فيها
    // protected function getSearchableColumns(): array
    // {
    //     return [
    //         'reward_number',
    //         'employee.name',
    //         'employee.nickname',
    //         'reward_type',
    //         'amount',
    //         'use_status',
    //         'reward_date',
    //     ];
    // }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة اصل')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة اصل',
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
    private function CustodyStatusBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->custody_status->color(),
            $row->custody_status->label()
        );
    }

    private function UseStatusBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->use_status->color(),
            $row->use_status->label()
        );
    }
}
