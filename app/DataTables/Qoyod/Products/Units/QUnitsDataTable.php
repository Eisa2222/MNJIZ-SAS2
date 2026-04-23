<?php

namespace App\DataTables\Qoyod\Products\Units;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\ProductUnitResourceInterface;
use Illuminate\Support\Collection;


class QUnitsDataTable extends AbstractResourceDataTable
{
    public function __construct(private ProductUnitResourceInterface $units) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): ProductUnitResourceInterface
    {
        return $this->units;
    }

    protected function resourceKey(): string
    {
        return 'product_unit_types';
    }

    protected function tableId(): string
    {
        return 'product_unit_types-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false,'searchable' => false,'width' => '10px'],

            ['data' => 'unit_name',               'name' => 'unit_name',                   'title' => 'الوحدة',       'className' => 'table-ellipsis'],
            ['data' => 'unit_representation',     'name' => 'unit_representation',         'title' => 'طريقة العرض',       'className' => 'table-ellipsis'],

            // Actions
            ['data' => 'actions','name' => 'actions','title' => 'الإجراءات', 'orderable' => false, 'searchable' => false, 'width' => '50px'],
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
        return ['unit_name'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows->map(fn($row) => [
            ...$row,
            'actions'      => view('qoyod.products.units.actions', compact('row'))->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة وحدة قياس',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.product-unit-types.create') . "'; }",
            ],
        ];
    }
}
