<?php

namespace App\DataTables\Qoyod\Purchases\PurchaseOrders;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\PurchaseOrdersResourceInterface;
use Illuminate\Support\Collection;


class QPurchaseOrdersDataTable extends AbstractResourceDataTable
{
    public function __construct(private PurchaseOrdersResourceInterface $purchase_orders) {}


    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): PurchaseOrdersResourceInterface
    {
        return $this->purchase_orders;
    }

    protected function resourceKey(): string
    {
        return 'orders';
    }

    protected function tableId(): string
    {
        return 'orders-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'reference',         'name' => 'reference',       'title' => 'المرجع',            'className' => 'table-ellipsis'],
            ['data' => 'issue_date',        'name' => 'issue_date',      'title' => 'تاريخ الإصدار',      'className' => 'table-ellipsis'],
            ['data' => 'expiry_date',       'name' => 'expiry_date',     'title' => 'تاريخ الانتهاء',     'className' => 'table-ellipsis'],
            ['data' => 'total_amount',      'name' => 'total_amount',    'title' => 'القيمة الاجمالية',   'className' => 'table-ellipsis'],
            ['data' => 'status',            'name' => 'status',          'title' => 'الحالة',             'className' => 'table-ellipsis'],
            ['data' => 'created_by',        'name' => 'created_by',      'title' => 'بواسطة',             'className' => 'table-ellipsis'],

            // Actions
            ['data' => 'actions', 'name' => 'actions', 'title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '50px'],
        ];
    }


    /*
    |============================================================================
    |============================================================================
    |                               Optional Methods
    |============================================================================
    |============================================================================
    */
    protected function filterableColumns(): array
    {
        return ['reference', 'issue_date', 'expiry_date', 'total_amount', 'status', 'created_by'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(fn($row) => [
            ...$row,
            'reference'  => $this->routeName(route('qoyod.purchase-orders.show', $row['id']), $row['reference']),
            'status'     => $this->getStatus($row['status']),
            'actions'    => view('qoyod.purchases.purchase_orders.actions')->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['reference', 'status', 'actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة أمر شراء',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.purchase-orders.create') . "'; }",
            ],
        ];
    }

    private function getStatus($status): string
    {
        return match ($status) {
            'Approved'  => '<span class="badge bg-success">موافق عليه</span>',
            'Draft'     => '<span class="badge bg-secondary">مسودة</span>',
            default     => '<span class="badge bg-secondary">غير محدد</span>',
        };
    }
}
