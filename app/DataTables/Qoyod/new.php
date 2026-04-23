<?php

namespace App\DataTables\Qoyod;

use Illuminate\Support\Collection;
use Yajra\DataTables\Services\DataTable;
use App\Services\Qoyod\Contracts\CrudResourceInterface;

abstract class wwww extends DataTable
{
    protected int $recordsTotal    = 0;
    protected int $recordsFiltered = 0;

    /*
    |============================================================================
    |  تحسين للعمل مع الـ Cache الموجود لديك
    |============================================================================
    */

    public function dataTable($query)
    {
        $rows = $this->formatRows($query);

        return datatables()
            ->collection($rows)
            ->addIndexColumn()
            ->skipPaging() // نحتاجها لأننا نقوم بالـ pagination يدوياً
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
        $length  = (int)$request->get('length', 10);

        $length = max(1, $length);

        try {
            // الاعتماد على الـ Cache الموجود لديك في الـ Service Layer
            $allData = $this->getAllDataFromService();

            // تطبيق الفلاتر والبحث
            $filteredData = $this->applyFilters($allData, $request);

            // حساب الأعداد الإجمالية
            $this->recordsTotal = count($allData);
            $this->recordsFiltered = count($filteredData);

            // تطبيق الـ pagination يدوياً
            $paginatedData = $filteredData->slice($start, $length)->values();

            return $paginatedData;
        } catch (\Exception $e) {
            $this->recordsTotal = 0;
            $this->recordsFiltered = 0;
            return collect([]);
        }
    }

    /**
     * جلب جميع البيانات من الـ Service (يستخدم الـ Cache الموجود لديك)
     */
    protected function getAllDataFromService(): Collection
    {
        // استخدام الـ Service مع Cache للحصول على جميع البيانات
        // بدلاً من تحديد per_page، نحاول جلب عدد كبير أو جميع البيانات

        $allItems = collect([]);
        $page = 1;
        $perPage = 100; // أو حسب ما يدعمه الـ API

        do {
            $apiQuery = [
                'page'     => $page,
                'per_page' => $perPage,
            ];

            try {
                $resp = $this->resource()->all($apiQuery);
                $items = collect($resp[$this->resourceKey()] ?? []);

                if ($items->isEmpty()) {
                    break; // لا توجد المزيد من البيانات
                }

                $allItems = $allItems->concat($items);
                $page++;

                // توقف إذا كان عدد العناصر أقل من المطلوب (آخر صفحة)
            } catch (\Exception $e) {
                break;
            }
        } while ($items->count() >= $perPage);

        return $allItems;
    }

    /**
     * تطبيق الفلاتر والبحث على البيانات
     */
    protected function applyFilters(Collection $data, $request): Collection
    {
        $filteredData = $data;

        // تطبيق فلاتر الأعمدة المخصصة
        $filters = $request->only($this->filterableColumns());
        foreach ($filters as $column => $value) {
            if (!empty($value)) {
                $filteredData = $filteredData->filter(function ($item) use ($column, $value) {
                    $itemArray = is_array($item) ? $item : (is_object($item) ? (array)$item : []);
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
                $itemArray = is_array($item) ? $item : (is_object($item) ? (array)$item : []);

                // البحث في الأعمدة المحددة
                foreach ($searchableColumns as $column) {
                    $columnValue = strtolower((string)($itemArray[$column] ?? ''));
                    if (stripos($columnValue, $searchValue) !== false) {
                        return true;
                    }
                }

                // إذا لم نجد في الأعمدة المحددة، ابحث في جميع الأعمدة
                if (empty($searchableColumns)) {
                    foreach ($itemArray as $value) {
                        $columnValue = strtolower((string)$value);
                        if (stripos($columnValue, $searchValue) !== false) {
                            return true;
                        }
                    }
                }

                return false;
            });
        }

        // تطبيق الترتيب إذا كان مطلوباً
        if ($request->has('order')) {
            $orderData = $request->get('order')[0] ?? [];
            $columnIndex = $orderData['column'] ?? 0;
            $direction = $orderData['dir'] ?? 'asc';

            $columns = $this->getColumns();
            if (isset($columns[$columnIndex])) {
                $columnName = $columns[$columnIndex]['data'] ?? $columns[$columnIndex]['name'] ?? null;

                if ($columnName) {
                    $filteredData = $filteredData->sortBy(function ($item) use ($columnName) {
                        $itemArray = is_array($item) ? $item : (is_object($item) ? (array)$item : []);
                        return $itemArray[$columnName] ?? '';
                    }, SORT_REGULAR, $direction === 'desc');
                }
            }
        }

        return $filteredData->values(); // إعادة ترقيم المؤشرات
    }

    /**
     * الحصول على الأعمدة القابلة للبحث
     * يمكن تخصيصها في كل DataTable
     */
    protected function getSearchableColumns(): array
    {
        // افتراضياً، البحث في جميع الأعمدة القابلة للفلترة
        return $this->filterableColumns();
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

    // Abstract methods
    abstract protected function resource(): CrudResourceInterface;
    abstract protected function resourceKey(): string;
    abstract protected function tableId(): string;
    abstract protected function getColumns(): array;

    // Optional methods
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
            'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
        ];
    }

    protected function emptyDataMessage($message = null): string
    {
        return '<span class="text-danger">'
            . ($message ?? 'غير متوفر')
            . '</span>';
    }

    protected function routeName($route) {}
}
