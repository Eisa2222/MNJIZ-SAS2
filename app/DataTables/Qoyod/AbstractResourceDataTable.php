<?php

namespace App\DataTables\Qoyod;

use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;
use App\Services\Qoyod\Contracts\CrudResourceInterface;
use Illuminate\Support\Facades\Log;

abstract class AbstractResourceDataTable extends DataTable
{
    protected int $recordsTotal    = 0;
    protected int $recordsFiltered = 0;

    //تخزين البيانات (سيتم جلبها مرة واحدة فقط)
    protected Collection $allItems;

    //  علامة لمعرفة إذا تم جلب البيانات
    protected bool $dataLoaded = false;

    public function dataTable($query)
    {
        $rows = $this->formatRows($query);

        return datatables()
            ->collection($rows)
            ->addIndexColumn()
            ->skipPaging()
            ->with([
                'recordsTotal'    => $this->recordsTotal,
                'recordsFiltered' => $this->recordsFiltered,
            ])
            ->rawColumns($this->rawColumns());
    }

    public function query(): Collection
    {
        $request = $this->request;
        $start   = (int)$request->get('start', 0);
        $length  = max(1, (int)$request->get('length', 10));

        try {
            // جلب البيانات مرة واحدة فقط
            if (!$this->dataLoaded) {
                $this->loadAllData();
            }

            // تطبيق الفلاتر والبحث
            $filtered = $this->applyFilters($this->allItems, $request);

            $this->recordsTotal = $this->allItems->count();
            $this->recordsFiltered = $filtered->count();



            // إرجاع الشريحة المطلوبة
            return $filtered->slice($start, $length)->values();
        } catch (\Throwable $e) {


            $this->recordsTotal = $this->recordsFiltered = 0;
            return collect([]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | جلب كل البيانات 
    |--------------------------------------------------------------------------
    */
    protected function loadAllData(): void
    {
        try {

            $response = $this->resource()->all();
            $this->allItems = collect($response[$this->resourceKey()] ?? []);
            $this->dataLoaded = true;
        } catch (\Throwable $e) {

            $this->allItems = collect([]);
            $this->dataLoaded = true;
        }
    }



    /*
    |--------------------------------------------------------------------------
    | تطبيق الفلاتر والبحث على البيانات 
    |--------------------------------------------------------------------------
    */
    protected function applyFilters(Collection $data, $request): Collection
    {
        $filteredData = $data;

        // تطبيق فلاتر الأعمدة المخصصة
        $filters = $request->only($this->filterableColumns());
        foreach ($filters as $column => $value) {
            if (!empty($value)) {
                $filteredData = $filteredData->filter(function ($item) use ($column, $value) {
                    $itemArray = $this->normalizeItem($item);
                    $columnValue = $itemArray[$column] ?? '';
                    return stripos((string)$columnValue, (string)$value) !== false;
                });
            }
        }

        // تطبيق البحث العام من DataTables
        if ($request->has('search') && !empty($request->get('search')['value'])) {
            $searchValue = strtolower(trim($request->get('search')['value']));
            $searchableColumns = $this->getSearchableColumns();

            $filteredData = $filteredData->filter(function ($item) use ($searchValue, $searchableColumns) {
                $itemArray = $this->normalizeItem($item);

                $columnsToSearch = !empty($searchableColumns) ? $searchableColumns : array_keys($itemArray);

                foreach ($columnsToSearch as $column) {
                    $columnValue = strtolower((string)($itemArray[$column] ?? ''));
                    if (stripos($columnValue, $searchValue) !== false) {
                        return true;
                    }
                }

                return false;
            });
        }

        // تطبيق الترتيب
        if ($request->has('order')) {
            $orderData = $request->get('order')[0] ?? [];
            $columnIndex = $orderData['column'] ?? 0;
            $direction = $orderData['dir'] ?? 'asc';

            $columns = $this->getColumns();
            if (isset($columns[$columnIndex])) {
                $columnName = $columns[$columnIndex]['data'] ?? $columns[$columnIndex]['name'] ?? null;

                if ($columnName) {
                    $filteredData = $filteredData->sortBy(function ($item) use ($columnName) {
                        $itemArray = $this->normalizeItem($item);
                        return $itemArray[$columnName] ?? '';
                    }, SORT_REGULAR, $direction === 'desc');
                }
            }
        }

        return $filteredData->values();
    }



    /*
    |--------------------------------------------------------------------------
    | تحويل العنصر إلى مصفوفة للمعالجة الموحدة
    |--------------------------------------------------------------------------
    */
    protected function normalizeItem($item): array
    {
        if (is_array($item)) {
            return $item;
        }

        if (is_object($item)) {
            return method_exists($item, 'toArray') ? $item->toArray() : (array)$item;
        }

        return [];
    }




    public function html()
    {
        return $this->builder()
            ->setTableId($this->tableId())
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->processing(true)
            ->serverSide(true)
            ->orderBy(1)
            ->dom($this->dom())
            ->buttons($this->buttons())
            ->parameters($this->parameters());
    }

    /*
    |============================================================================
    |                              Abstract Methods
    |============================================================================
    */
    abstract protected function resource(): CrudResourceInterface;
    abstract protected function resourceKey(): string;
    abstract protected function tableId(): string;
    abstract protected function getColumns(): array;

    /*
    |============================================================================
    |                               Optional Methods
    |============================================================================
    */

    protected function getSearchableColumns(): array
    {
        return $this->filterableColumns();
    }

    protected function filterableColumns(): array
    {
        return [];
    }

    protected function formatRows(Collection $rows): Collection
    {
        return $rows;
    }

    protected function rawColumns(): array
    {
        return [];
    }

    protected function getButtons(): array
    {
        return [];
    }

    protected function dom(): string
    {
        return  '<"dt-toolbar"' .
            '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center" l >' .
            '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between" f B >' .
            '>' .
            'rt' .
            '<"row mt-3"' .
            '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100" i <"pagination-wrapper overflow-auto w-100" p >' .
            '>' .
            '>';
    }

    protected function buttons(): array
    {
        return [
            [
                'extend'    => 'collection',
                'className' => 'btn btn-export btn',
                'text'      => 'الإجراءات',
                'buttons'   => [
                    [
                        'extend'        => 'copy',
                        'text'          => 'نسخ',
                        'exportOptions' => [
                            'columns' => ':not(:last-child)',
                        ],
                    ],
                    [
                        'extend'        => 'excel',
                        'text'          => 'إكسل',
                        'exportOptions' => [
                            'columns' => ':not(:last-child)',
                        ],
                    ],
                ],
            ],
            ...$this->getButtons()
        ];
    }

    protected function parameters(): array
    {
        return [
            'responsive' => true,
            'processing' => true,
            'serverSide' => true,
            'language'   => ['url' => asset('assets/json/ar.json')],
            'pageLength' => 10,
        ];
    }

    protected function emptyDataMessage($message = null): string
    {
        return '<span class="text-danger">'
            . ($message ?? 'غير متوفر')
            . '</span>';
    }

    protected function routeName($route, $name)
    {
        return '
        <a href="' . $route . '" >
            ' . $name . '
        </a>
        ';
    }
}
