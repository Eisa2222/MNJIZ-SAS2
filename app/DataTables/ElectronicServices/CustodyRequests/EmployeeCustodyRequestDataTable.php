<?php

namespace App\DataTables\ElectronicServices\CustodyRequests;

use App\DataTables\ArabicSearchDataTable;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use Illuminate\Support\Facades\Auth;

class EmployeeCustodyRequestDataTable extends ArabicSearchDataTable
{
    private $route = "account.electronic-services.custody-requests";

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
        return CustodyRequest::where('employee_id', Auth::user()->employee->id);
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
            ['data' => 'request_type',          'name' => 'request_type',       'title' => 'نوع الطلب',     'width' => '150px'],
            ['data' => 'status',                'name' => 'status',             'title' => 'الحالة',        'width' => '150px'],

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

            ->editColumn('custody_item_id', function ($row) {
                return $this->routeName(route($this->route . '.show', $row->id), $row->item->name);
            })
            ->addColumn('serial_number', function ($row) {
                return $row->item->serial_number ?? 'غير متوفر';
            })

            ->editColumn('request_type', function ($row) {
                return $this->requestTypeBadge($row);
            })
            ->addColumn('asset_category_id', function ($row) {
                return $row->item->assetCategory->name ?? 'غير محدد';
            })

            ->addColumn('storage_location_id', function ($row) {
                return $row->item->storageLocation->name ?? 'غير محدد';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return $row->status != CustodyRequestStatus::Pending
                    ? "لا يمكن التعديل و الحذف"
                    : view('electronic_services.custody_requests.action', ['row' => $row, 'route' => $this->route])->render();
            });
    }


    protected function applyCustomFilters($query)
    {
        $request = request();

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
        return ['checkbox', 'custody_item_id', 'request_type',  'status', 'actions'];
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
        return [
            [
                'text'      => '<i class="fas fa-box-open me-1"></i> طلب عهدة',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route($this->route .  '.assign.create') . "'; }",
            ],
            [
                'text'      => '<i class="fas fa-undo-alt me-1"></i> طلب إرجاع عهدة',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route($this->route .  '.return.create') . "'; }",
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

    private function requestTypeBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->request_type->color(),
            $row->request_type->label()
        );
    }
}
