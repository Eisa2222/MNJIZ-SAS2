<?php

namespace App\DataTables\Hr\Custody\Request;

use App\DataTables\ArabicSearchDataTable;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;

class CustodyRequestDataTable extends ArabicSearchDataTable
{
    private $route = "hr.custody.requests";
    private $employeeRoute = "account.employee.profile";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "request-table";
    }

    protected function resource()
    {
        return CustodyRequest::with([
            'item',
            'item.assetCategory',
            'item.storageLocation',
            'employee',
        ]);
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

            ['data' => 'custody_item_id',       'name' => 'custody_item_id',        'title' => 'الاصل',             'width' => '150px'],
            ['data' => 'serial_number',         'name' => 'serial_number',          'title' => 'الرقم التسلسلي',   'width' => '150px'],
            ['data' => 'asset_category_id',     'name' => 'asset_category_id',      'title' => 'التصنيف',           'width' => '150px'],
            ['data' => 'storage_location_id',   'name' => 'storage_location_id',    'title' => 'مرجعية الاصل',      'width' => '150px'],
            ['data' => 'employee_id',           'name' => 'employee_id',            'title' => 'الموظف',            'width' => '150px'],
            ['data' => 'request_type',          'name' => 'request_type',           'title' => 'نوع الطلب',         'width' => '100px'],
            ['data' => 'status',                'name' => 'status',                 'title' => 'الحالة',            'width' => '100px'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '180px'],
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

            ->editColumn('custody_item_id', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->item->name);
            })

            ->addColumn('asset_category_id', function ($row) {
                return $row->item->assetCategory->name ?? 'غير محدد';
            })

            ->addColumn('storage_location_id', function ($row) {
                return $row->item->storageLocation->name ?? 'غير محدد';
            })

            ->addColumn('serial_number', function ($row) {
                return $row->item->serial_number ?? 'غير متوفر';
            })

            ->editColumn('employee_id', function ($row) {
                return $this->routeName(route($this->employeeRoute, $row->employee_id), $row->employee->name);
            })

            ->editColumn('request_type', function ($row) {
                return $this->requestTypeBadge($row);
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at->format("Y-m-d H:i:s");
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->editColumn('created_by', function ($row) {
                return $this->routeName(route($this->employeeRoute, $row->created_by), $row->createdBy->name);
            })

            ->addColumn('actions', function ($row) {
                if ($row->status != CustodyRequestStatus::Pending) {
                    return "لا يمكن تعديل أو حذف الطلب المعتمد";
                }
                return (auth()->user()->can('تعديل عهدة') || auth()->user()->can('حذف عهدة')) ? view('hr.custody.requests.action', ['row' => $row, 'route' => $this->route])->render()  : ' ليس لديك صلاحيات';
            });
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('employee')) {
            $query->where('employee_id', $request->employee);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('request_type', $request->type);
        }

        if ($request->filled('item')) {
            $query->where('custody_item_id', $request->item);
        }

        return $query;
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'custody_item_id', 'employee_id', 'request_type', 'status', 'created_by', 'actions'];
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
    //         'status',
    //         'reward_date',
    //     ];
    // }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }


    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة عهدة')) {
            return [
                [
                    'text'      => '<i class="fas fa-box-open me-1"></i> طلب عهدة لموظف',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route .  '.assign.create') . "'; }",
                ],
                [
                    'text'      => '<i class="fas fa-undo-alt me-1"></i> طلب إرجاع عهدة لموظف',
                    'className' => 'btn btn-primary btn-add',
                    'action'    => "function(){ window.location.href='" . route($this->route .  '.return.create') . "'; }",
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

    private function requestTypeBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->request_type->color(),
            $row->request_type->label()
        );
    }
}
