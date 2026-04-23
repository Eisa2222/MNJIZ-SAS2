<?php

namespace App\DataTables\Qoyod\Products\Categories;

use App\DataTables\Qoyod\AbstractResourceDataTable;
use App\Services\Qoyod\Contracts\Resources\CategoryResourceInterface;
use App\Services\Qoyod\Presenters\Products\Categories\CategoryPresenter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class QCategoriesDataTable extends AbstractResourceDataTable
{
    public function __construct(private CategoryResourceInterface $categories, private CategoryPresenter $categoryPresenter) {}

    /*
    |============================================================================
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    |============================================================================
    */
    protected function resource(): CategoryResourceInterface
    {
        return $this->categories;
    }

    protected function resourceKey(): string
    {
        return 'categories';
    }

    protected function tableId(): string
    {
        return 'categories-table';
    }

    protected function getColumns(): array
    {
        return [
            // ID
            ['data' => 'DT_RowIndex', 'title' => 'م', 'orderable' => false, 'searchable' => false, 'width' => '10px'],

            ['data' => 'name',           'name' => 'name',          'title' => 'الاسم',          'className' => 'table-ellipsis'],
            ['data' => 'parent_name',    'name' => 'parent_name',   'title' => 'الصنف الاساس',   'className' => 'table-ellipsis'],
            ['data' => 'description',    'name' => 'description',   'title' => 'الوصف',         'className' => 'table-ellipsis'],

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
        return ['name','parent_name','description'];
    }


    protected function formatRows(Collection $rows): Collection
    {
        $presented = collect(
            $this->categoryPresenter->presentCollection($rows->toArray())
        );

        return $presented->map(fn(array $row) => [
            ...$row,
            'actions' => view('qoyod.products.categories.actions', compact('row'))->render(),
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
                'text'      => '<i class="fas fa-plus-circle me-1"></i> إضافة تصنيف',
                'className' => 'btn btn-primary btn-add',
                'action'    => "function(){ window.location.href='" . route('qoyod.categories.create') . "'; }",
            ],
        ];
    }
}
