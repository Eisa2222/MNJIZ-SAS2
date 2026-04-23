<?php

namespace App\DataTables\ElectronicServices\PurchaseRequests;

use App\DataTables\ArabicSearchDataTable;
use App\Enums\ElectronicServices\PurchaseRequests\PurchaseRequestsStatus;
use App\Models\ElectronicServices\PurchaseRequests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;

class EmployeePurchaseRequestDataTable extends ArabicSearchDataTable
{
    private $route = "account.electronic-services.purchase-requests";
    private $page   = "electronic_services.purchase_requests";



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
        return PurchaseRequest::where('employee_id', Auth::user()->employee->id);
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

            ['data' => 'item_name',                 'name' => 'item_name',                  'title' => 'الطلب',             'width' => '150px'],
            ['data' => 'purchase_category_id',      'name' => 'purchase_category_id',       'title' => 'التصنيف',           'width' => '150px'],
            ['data' => 'item_quantity',             'name' => 'item_quantity',              'title' => 'الكمية',            'width' => '150px', 'defaultContent' => '-',],
            ['data' => 'status',                    'name' => 'status',                     'title' => 'الحالة',            'width' => '150px'],
            ['data' => 'created_at',                'name' => 'created_at',                 'title' => 'تاريخ الطلب',       'width' => '150px'],


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

            ->editColumn('item_name', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->item_name);
            })

            ->editColumn('purchase_category_id', function ($row) {
                return $row->category->name;
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })

            ->addColumn('actions', function ($row) {
                return $row->status != PurchaseRequestsStatus::Pending
                    ? "لا يمكن التعديل و الحذف"
                    : view($this->page . '.action', ['row' => $row, 'route' => $this->route])->render();
            });
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('purchase_category_id', $request->category);
        }

        return $query;
    }


    protected function rawColumns(): array
    {
        return ['checkbox', 'item_name', 'status', 'actions'];
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
            'item_name',
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
                'text'      => '<i class="ti ti-send me-1"></i> طلب مشتريات',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route($this->route .  '.create') . "'; }",
            ],
        ];
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
}
