<?php

namespace App\DataTables\Qoyod\Inventories;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use Illuminate\Support\Collection;


class QInventoriesDataTable extends AbstractResourceDataTable
{
    public function __construct(private InventoryResourceInterface $inventories) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): InventoryResourceInterface
    {
        return $this->inventories;
    }

    protected function resourceKey(): string
    {
        return 'inventories';
    }

    protected function tableId(): string
    {
        return 'inventories-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'ar_name',                       'name' => 'ar_name',                'title' => 'الاسم العربي',      'className' => 'table-ellipsis'],
            ['data' => 'name',                          'name' => 'name',                   'title' => 'الاسم الانجليزي',    'className' => 'table-ellipsis'],
            ['data' => 'address.shipping_address',      'name' => 'shipping_address',       'title' => 'العنوان',           'className' => 'table-ellipsis'],
            ['data' => 'address.shipping_city',         'name' => 'shipping_city',          'title' => 'المدينة',           'className' => 'table-ellipsis'],

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
        return ['ar_name', 'name', 'address.shipping_address', 'address.shipping_city'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(fn($row) => [
            ...$row,
            'address.shipping_address' => data_get($row, 'address.shipping_address') ?: $this->emptyDataMessage(),
            'address.shipping_city'    => data_get($row, 'address.shipping_city')    ?: $this->emptyDataMessage(),

            'actions'                  => view('qoyod.inventories.actions', compact('row'))->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['actions', 'address.shipping_address', 'address.shipping_city'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة موقع',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.inventories.create') . "'; }",
            ],
        ];
    }
}
