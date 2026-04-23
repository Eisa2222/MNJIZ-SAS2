<?php

namespace App\DataTables\Qoyod\Products;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\ProductResourceInterface;
use App\Services\Qoyod\Presenters\Products\ProductPresenter;
use Illuminate\Support\Collection;


class QProductsDataTable extends AbstractResourceDataTable
{
    public function __construct(private ProductResourceInterface $products,  private ProductPresenter $productPresenter) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): ProductResourceInterface
    {
        return $this->products;
    }

    protected function resourceKey(): string
    {
        return 'products';
    }

    protected function tableId(): string
    {
        return 'products-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'sku',               'name' => 'sku',            'title' => 'الرقم التسلسلي',       'className' => 'table-ellipsis'],
            ['data' => 'name_ar',           'name' => 'name_ar',        'title' => 'الاسم',                  'className' => 'table-ellipsis'],
            ['data' => 'category_name',     'name' => 'category_name',  'title' => 'صنف المنتج',           'className' => 'table-ellipsis'],
            ['data' => 'type',              'name' => 'type',           'title' => 'نوع المنتج',            'className' => 'table-ellipsis'],
            ['data' => 'buying_price',      'name' => 'buying_price',   'title' => 'سعر الشراء',            'className' => 'table-ellipsis'],
            ['data' => 'selling_price',     'name' => 'selling_price',  'title' => 'سعر البيع',             'className' => 'table-ellipsis'],

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
        return ['sku', 'category_name','name_ar', 'type', 'buying_price', 'selling_price'];
    }

    protected function formatRows(Collection $rows): Collection
    {
        $presented = collect(
            $this->productPresenter->getProductsWithCategoryName($rows->toArray())
        );

        return $presented->map(fn(array $row) => [
            ...$row,
            'name_ar'    => $this->routeName(route('qoyod.products.show', $row['id']), $row['name_ar']),
            'type'       => $this->type($row['type']),
            'actions'    => view('qoyod.products.actions', compact('row'))->render(),
        ]);
    }


    protected function rawColumns(): array
    {
        return ['name_ar','type', 'actions'];
    }


    protected function getButtons(): array
    {
        return [
            [
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة منتج',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.products.create') . "'; }",
            ],
        ];
    }


    /*
    |============================================================================
    |============================================================================
    |                         private methods
    |============================================================================
    |============================================================================
    */
    private function type($type): string
    {
        return match ($type) {
            'Service' => 'خدمة',
            'Expense' => 'مصروف',
            default   => 'غير محدد',
        };
    }
}
