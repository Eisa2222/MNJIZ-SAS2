<?php

namespace App\DataTables\Qoyod;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Services\DataTable;
use App\Services\Qoyod\Contracts\CrudResourceInterface;

abstract class AbstractResourceDataTable extends DataTable
{
    protected int $recordsTotal    = 0;
    protected int $recordsFiltered = 0;

    /*
    |--------------------------------------------------------------------------
    | DataTable Methods
    |--------------------------------------------------------------------------
    */
    public function dataTable($query)
    {
        Log::info('[AbstractResourceDataTable][dataTable] بدء تجميع البيانات قبل الإرسال إلى DataTables', [
            'query_count' => is_array($query) ? count($query) : ($query instanceof Collection ? $query->count() : 'غير معروف')
        ]);

        try {
            // تنسيق الصفوف قبل المرور على DataTables
            $rows = $this->formatRows($query);

            Log::info('[AbstractResourceDataTable][dataTable] بعد formatRows() عدد الصفوف:', [
                'formatted_count' => is_array($rows) ? count($rows) : ($rows instanceof Collection ? $rows->count() : 'غير معروف')
            ]);

            return datatables()
                ->collection($rows)
                ->addIndexColumn()
                ->skipPaging() // إلغاء التحجيم من جانب العميل (لأننا نتحكم بهذه النقطة يدويًا)
                ->with([
                    'recordsTotal'    => $this->recordsTotal,
                    'recordsFiltered' => $this->recordsFiltered,
                ])
                ->rawColumns($this->rawColumns());
        } catch (\Throwable $e) {
            Log::error('[AbstractResourceDataTable][dataTable] خطأ أثناء تجهيز DataTable: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            // إعادة رمي الاستثناء لكي يظهر في الاستجابة أو يتم معالجته في مكانٍ آخر
            throw $e;
        }
    }

    public function query(): Collection
    {
        Log::info('[AbstractResourceDataTable][query] بداية تنفيذ query()');

        $request = $this->request;
        $start   = (int)$request->get('start', 0);
        $length  = (int)$request->get('length', 10);
        $length  = max(1, $length);

        Log::info('[AbstractResourceDataTable][query] قيم start و length', [
            'start'  => $start,
            'length' => $length,
        ]);

        try {
            // 1. استدعاء الخدمة لجلب كل البيانات (مع Pagination داخليّ في الـ Service)
            Log::info('[AbstractResourceDataTable][query] استدعاء getAllDataFromService()');
            $allData = $this->getAllDataFromService();
            Log::info('[AbstractResourceDataTable][query] عدد العناصر المسترجعة من الخدمة (allData): ' . $allData->count());

            // 2. تطبيق الفلاتر والبحث على البيانات
            Log::info('[AbstractResourceDataTable][query] تطبيق applyFilters() على allData');
            $filteredData = $this->applyFilters($allData, $request);
            Log::info('[AbstractResourceDataTable][query] عدد العناصر بعد applyFilters (filteredData): ' . $filteredData->count());

            // 3. حساب الأعداد الإجمالية (قبل وبعد الفلترة)
            $this->recordsTotal    = $allData->count();
            $this->recordsFiltered = $filteredData->count();

            Log::info('[AbstractResourceDataTable][query] تعيين recordsTotal و recordsFiltered', [
                'recordsTotal'    => $this->recordsTotal,
                'recordsFiltered' => $this->recordsFiltered,
            ]);

            // 4. تطبيق Pagination يدويًّا (slice و values)
            Log::info('[AbstractResourceDataTable][query] تطبيق pagination يدويّ على filteredData');
            $paginatedData = $filteredData->slice($start, $length)->values();
            Log::info('[AbstractResourceDataTable][query] عدد العناصر بعد pagination (paginatedData): ' . $paginatedData->count());

            return $paginatedData;
        } catch (\Throwable $e) {
            Log::error('[AbstractResourceDataTable][query] خطأ أثناء تنفيذ query(): ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            $this->recordsTotal = 0;
            $this->recordsFiltered = 0;
            return collect([]);
        }
    }

    public function html()
    {
        Log::info('[AbstractResourceDataTable][html] بناء تعريف جدول DataTable في html()');

        try {
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
        } catch (\Throwable $e) {
            Log::error('[AbstractResourceDataTable][html] خطأ أثناء بناء html(): ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Abstract Methods — يجب على الكلاس المشتقّ أن يعيد هذه القيم
    |--------------------------------------------------------------------------
    */
    abstract protected function resource(): CrudResourceInterface;
    abstract protected function resourceKey(): string;
    abstract protected function tableId(): string;
    abstract protected function getColumns(): array;

    /*
    |--------------------------------------------------------------------------
    | Optional Methods — يمكن للكلاس المشتقّ أن يخصّص أو يعتمد على الافتراضي
    |--------------------------------------------------------------------------
    */
    protected function getAllDataFromService(): Collection
    {
        Log::info('[AbstractResourceDataTable][getAllDataFromService] بدء جلب البيانات من الخدمة (Resource) صفحياً');
        $allItems = collect([]);
        $page     = 1;
        $perPage  = 100; // العدد الافتراضي للدعوات في كل صفحة (يمكن تخصيصه)

        do {
            Log::info("[AbstractResourceDataTable][getAllDataFromService] طلب صفحة رقم $page من المورد");
            $apiQuery = [
                'page'     => $page,
                'per_page' => $perPage,
            ];

            try {
                // الاستدعاء الفعلي للواجهة عبر الـ Service
                $resp  = $this->resource()->all($apiQuery);
                $items = collect($resp[$this->resourceKey()] ?? []);

                Log::info("[AbstractResourceDataTable][getAllDataFromService] استجابة الصفحة $page تحتوي على عدد عناصر: " . $items->count());

                if ($items->isEmpty()) {
                    Log::info('[AbstractResourceDataTable][getAllDataFromService] لا توجد بيانات إضافية، انتهى الاسترجاع');
                    break;
                }

                $allItems = $allItems->concat($items);
                $page++;
            } catch (\Throwable $e) {
                Log::error('[AbstractResourceDataTable][getAllDataFromService] خطأ أثناء جلب الصفحة ' . $page . ': ' . $e->getMessage(), [
                    'stack' => $e->getTraceAsString(),
                ]);
                break;
            }
        } while ($items->count() >= $perPage);

        Log::info('[AbstractResourceDataTable][getAllDataFromService] انتهى جلب البيانات، المجموع الكلي للعناصر: ' . $allItems->count());
        return $allItems;
    }

    /**
     * تطبيق الفلاتر والبحث على البيانات
     */
    protected function applyFilters(Collection $data, $request): Collection
    {
        Log::info('[AbstractResourceDataTable][applyFilters] عدد البيانات الواردة قبل الفلترة: ' . $data->count());

        $filteredData = $data;

        // 1. فلاتر الأعمدة المخصصة (إن وجدت)
        $filters = $request->only($this->filterableColumns());
        Log::info('[AbstractResourceDataTable][applyFilters] الفلاتر الواردة من الطلب', ['filters' => $filters]);

        foreach ($filters as $column => $value) {
            if (!empty($value)) {
                Log::info("[AbstractResourceDataTable][applyFilters] تطبيق فلتر على العمود '$column' بالقيمة '$value'");
                $filteredData = $filteredData->filter(function ($item) use ($column, $value) {
                    $itemArray   = is_array($item) ? $item : (is_object($item) ? (array)$item : []);
                    $columnValue = $itemArray[$column] ?? '';
                    return stripos((string)$columnValue, (string)$value) !== false;
                });
                Log::info("[AbstractResourceDataTable][applyFilters] عدد العناصر بعد فلترة العمود '$column': " . $filteredData->count());
            }
        }

        // 2. بحث عام (Global Search) من DataTables
        if ($request->has('search') && !empty($request->get('search')['value'])) {
            $searchValue = strtolower(trim($request->get('search')['value']));
            $searchCols  = $this->getSearchableColumns();
            Log::info("[AbstractResourceDataTable][applyFilters] بدء البحث العام بالقيمة '$searchValue' في الأعمدة: ", $searchCols);

            $filteredData = $filteredData->filter(function ($item) use ($searchValue, $searchCols) {
                $itemArray = is_array($item) ? $item : (is_object($item) ? (array)$item : []);
                // البحث في الأعمدة المحددة أولاً
                foreach ($searchCols as $column) {
                    $columnValue = strtolower((string)($itemArray[$column] ?? ''));
                    if (stripos($columnValue, $searchValue) !== false) {
                        return true;
                    }
                }
                // إذا لم يُعثر على شيء في الأعمدة القابلة للبحث، ابحث في كل القيم
                if (empty($searchCols)) {
                    foreach ($itemArray as $val) {
                        $valStr = strtolower((string)$val);
                        if (stripos($valStr, $searchValue) !== false) {
                            return true;
                        }
                    }
                }
                return false;
            });

            Log::info('[AbstractResourceDataTable][applyFilters] عدد العناصر بعد البحث العام: ' . $filteredData->count());
        }

        // 3. تطبيق الترتيب (Ordering) إن وُجد
        if ($request->has('order')) {
            $orderData   = $request->get('order')[0] ?? [];
            $columnIndex = $orderData['column'] ?? 0;
            $direction   = $orderData['dir'] ?? 'asc';
            Log::info('[AbstractResourceDataTable][applyFilters] بيانات الترتيب الواردة: ', [
                'columnIndex' => $columnIndex,
                'direction'   => $direction,
            ]);

            $columns = $this->getColumns();
            if (isset($columns[$columnIndex])) {
                $columnName = $columns[$columnIndex]['data'] ?? $columns[$columnIndex]['name'] ?? null;
                Log::info("[AbstractResourceDataTable][applyFilters] سنرتب على العمود '$columnName' باتجاه '$direction'");

                if ($columnName) {
                    $filteredData = $filteredData->sortBy(function ($item) use ($columnName) {
                        $itemArray = is_array($item) ? $item : (is_object($item) ? (array)$item : []);
                        return $itemArray[$columnName] ?? '';
                    }, SORT_REGULAR, $direction === 'desc');

                    Log::info('[AbstractResourceDataTable][applyFilters] انتهى الترتيب');
                }
            }
        }

        $countAfter = $filteredData->count();
        Log::info("[AbstractResourceDataTable][applyFilters] المجموع النهائي للعناصر بعد جميع الفلاتر والترتيب: $countAfter");

        return $filteredData->values(); // إعادة ترقيم المفاتيح (0,1,2,…)
    }

    /**
     * جلب الأعمدة القابلة للبحث (يمكن للكلابسات المشتقة تعديلها)
     */
    protected function getSearchableColumns(): array
    {
        $cols = $this->filterableColumns();
        Log::info('[AbstractResourceDataTable][getSearchableColumns] الأعمدة القابلة للبحث الافتراضية: ', $cols);
        return $cols;
    }

    /**
     * الأعمدة القابلة للفلترة — يمكن تغييرها في الكلاس المشتق
     */
    protected function filterableColumns(): array
    {
        return [];
    }

    /**
     * تنسيق الصفوف قبل الإرسال لواجهة DataTables — يمكن للكلاس المشتقّ تعديلها
     */
    protected function formatRows(Collection $rows): Collection
    {
        Log::info('[AbstractResourceDataTable][formatRows] تنسيق الصفوف الواردة، عددها: ' . $rows->count());

        // في حال أردت إضافة أعمدة إضافية (مثلاً عمود إجراءات “actions”)، قم بتعديل الطريقة في الكلاس المشتق
        return $rows;
    }

    /**
     * تحديد الأعمدة التي يجب اعتبارها خام HTML (Raw Columns)
     */
    protected function rawColumns(): array
    {
        return [];
    }

    /**
     * أزرار إضافية تظهر في واجهة DataTables
     */
    protected function getButtons(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Default Settings Methods
    |--------------------------------------------------------------------------
    */
    protected function dom(): string
    {
        return  '<"dt-toolbar"'
            . '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center" l >'
            . '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between" f B >'
            . '>'
            . 'rt'
            . '<"row mt-3"'
            . '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100" i <"pagination-wrapper overflow-auto w-100" p >'
            . '>'
            . '';
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

    /*
    |--------------------------------------------------------------------------
    | Additional Helper Methods
    |--------------------------------------------------------------------------
    */
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
