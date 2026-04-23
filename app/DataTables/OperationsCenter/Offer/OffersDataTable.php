<?php

namespace App\DataTables\OperationsCenter\Offer;

use App\DataTables\ArabicSearchDataTable;
use App\Models\OperationsCenter\Offer\Offers;

class OffersDataTable extends ArabicSearchDataTable
{
    private $route = "operations-center.offers";

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    protected function getTableId(): string
    {
        return "offers-table";
    }

    protected function resource()
    {
        $query = Offers::with([
            'customer:id,name',
            'relationshipManager:id,name,nickname'
        ])
            ->select([
                'offers.id as id',
                'offers.offer_name',
                'offers.offer_number',
                'offers.customer_id',
                'offers.relationship_manager_id',
                'offers.start_date',
                'offers.status',
                'offers.created_by'
            ]);

        // التحقق من صلاحيات المستخدم
        if (auth()->user()->can('العروض الخاصة بي') && !auth()->user()->can('كل العروض')) {
            $query->where('created_by',  auth()->user()->employee->id);
        }

        return $query;
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

            ['data' => 'offer_name',                'name' => 'offer_name',                 'title' => 'اسم العرض',             'width' => '400px'],
            ['data' => 'offer_number',              'name' => 'offer_number',               'title' => 'رقم العرض',             'width' => '150px'],
            ['data' => 'customer_id',               'name' => 'customer_id',                'title' => 'العميل',                'width' => '300px'],
            ['data' => 'relationship_manager_id',   'name' => 'relationship_manager_id',    'title' => 'مدير العلاقات',         'width' => '300px'],
            ['data' => 'start_date',                'name' => 'start_date',                 'title' => 'تاريخ بداية العرض',    'width' => '150px'],
            ['data' => 'status',                    'name' => 'status',                     'title' => 'الحالة',                'width' => '150px'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '150px',],
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

            ->editColumn('offer_name', function ($row) {
                return $this->routeName(route('operations-center.offers.show', $row->id), $row->offer_name);
            })

            ->editColumn('offer_number', function ($row) {
                return $row->offer_number ?? '-';
            })

            ->editColumn('customer_id', function ($row) {
                return $row->customer ? $row->customer->name : 'غير متوفر';
            })

            ->editColumn('relationship_manager_id', function ($row) {
                return $row->relationshipManager
                    ? $this->routeName(route('account.employee.profile', $row->relationshipManager->id), $row->relationshipManager->name)
                    : 'غير محدد';
            })

            ->editColumn('start_date', function ($row) {
                return $row->hijri_start_date ?? '-';
            })

            ->editColumn('status', function ($row) {
                return $this->statusBadge($row);
            })

            ->addColumn('actions', function ($row) {
                return (auth()->user()->can('تعديل عرض') || auth()->user()->can('حذف عرض')) ? view('operations_center.offers.action', ['row' => $row, 'route' => $this->route])->render() : ' ليس لديك صلاحيات';
            });
    }

    protected function applyCustomFilters($query)
    {
        $request = request();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer')) {
            $query->where('customer_id', $request->customer);
        }

        if ($request->filled('employee')) {
            $query->where('relationship_manager_id', $request->employee);
        }

        return $query;
    }

    protected function rawColumns(): array
    {
        return ['checkbox', 'offer_name', 'relationship_manager_id', 'status', 'actions'];
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
            'offer_name',
            'offer_number',
            'customer.name',
            'relationshipManager.name',
            'start_date',
        ];
    }

    protected function getCustomOrder(): array
    {
        return [1, 'desc'];
    }

    protected function getButtons(): array
    {
        if (auth()->user()->can('إضافة عرض')) {
            return [
                [
                    'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة عرض',
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
    private function statusBadge($row): string
    {
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $row->status->color(),
            $row->status->label()
        );
    }
}
