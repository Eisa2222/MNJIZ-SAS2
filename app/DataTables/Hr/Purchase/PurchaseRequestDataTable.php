<?php

namespace App\DataTables\Hr\Purchase;


use App\DataTables\ArabicSearchDataTable;
use App\Enums\ElectronicServices\PurchaseRequests\PurchaseRequestsStatus;
use App\Models\ElectronicServices\PurchaseRequests\PurchaseRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseRequestDataTable extends ArabicSearchDataTable
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
        return PurchaseRequest::query();
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
            ['data' => 'item_description',          'name' => 'item_description',           'title' => 'ملاحظات',             'width' => '150px'],


            // Actions
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
                if (auth()->user()->can('إضافة مشتريات')) {
                    return '<a href="' . route('purchasing-center.purchase-requests.invoices.createByItem', $row->id) . '" class="text-primary">' . e($row->item_name) . '</a>';
                }
                return e($row->item_name);
            })

            ->editColumn('purchase_category_id', function ($row) {
                return $row->category->name;
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })



            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
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



    /*
    |============================================================================
    |                            Private Helper Methods
    |============================================================================
    */
    private function statusBadge($row): string
    {
        // الحل الأول: استخدام sprintf مع تحسينات
        return sprintf(
            '<span class="badge bg-%s %s" style="%s" data-id="%s" data-current="%s">%s</span>',
            $row->status->color(),
            auth()->user()->can('تغيير حالة الطلب') ? 'change-status-btn' : '',
            auth()->user()->can('تغيير حالة الطلب') ? 'cursor: pointer' : '',
            $row->id,
            $row->status->value,  // أو $row->status->value إذا كان enum
            $row->status->label()
        );
    }
}
