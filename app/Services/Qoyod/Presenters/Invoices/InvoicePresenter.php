<?php

namespace App\Services\Qoyod\Presenters\Invoices;

use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;


class InvoicePresenter
{
    protected int $ttlMinutes;

    public function __construct(
        protected InvoiceResourceInterface $invoices,
        protected CustomerResourceInterface $customers,
        protected InventoryResourceInterface $inventory
    ) {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }

    // get Invoices by filter value
    public function getFilterInvoices(string $filterKey, string $filterValue): Collection
    {
        $cacheKey = "qoyod:invoices_{$filterKey}_{$filterValue}";

        return Cache::remember($cacheKey, now()->addMinutes($this->ttlMinutes), function () use ($filterKey, $filterValue) {
            $allItems = collect();
            $page        = 1;
            $perPage     = 100;
            $useMeta     = false;
            $lastPage    = null;

            do {
                try {
                    $resp = $this->invoices->all([
                        'page'     => $page,
                        'per_page' => $perPage,
                    ]);
                } catch (\Exception $e) {
                    break;
                }

                $items = collect($resp['invoices'] ?? []);

                // 1. استخدام metadata إذا كانت موجودة
                if (isset($resp['meta']['last_page'])) {
                    $useMeta  = true;
                    $lastPage = (int) $resp['meta']['last_page'];
                }

                // 2. التحقق من تكرار السجلات
                if ($this->isDuplicatePage($items, $allItems)) {
                    break;
                }

                // 3. دمج السجلات بدون تكرار
                $allItems = $allItems->merge($items)->unique('id')->values();

                // 4. إذا وصلنا لنهاية الصفحات حسب metadata، نخرج
                if ($useMeta && $page >= $lastPage) {
                    break;
                }

                if ($items->isEmpty()) {
                    break;
                }

                $page++;
            } while (true);

            return $allItems->filter(function ($invoice) use ($filterKey, $filterValue) {
                return isset($invoice[$filterKey]) && (string)$invoice[$filterKey]  === (string)$filterValue;
            })->values();
        });
    }


    // get all invoices with customer name and inventory name
    public function getInvoicesWithRelatedNames(array $rows): array
    {
        try {
            // جلب خرائط العلاقات مرة واحدة
            $customerMap = $this->buildRelationMap(
                $this->customers,
                'customers',
                'name',
                'id'
            );

            $inventoryMap = $this->buildRelationMap(
                $this->inventory,
                'inventories',
                'ar_name',
                'id'
            );

            // معالجة جميع الصفوف
            foreach ($rows as &$row) {
                // إضافة اسم العميل
                $contactId = $row['contact_id'] ?? null;
                $row['contact_name'] = $contactId && isset($customerMap[$contactId])
                    ? $customerMap[$contactId]
                    : '-';

                // إضافة اسم المخزون
                $inventoryName = '-';
                if (
                    isset($row['line_items']) &&
                    is_array($row['line_items']) &&
                    count($row['line_items']) > 0
                ) {

                    $firstItem = $row['line_items'][0];
                    $inventoryId = $firstItem['inventory_id'] ?? null;

                    if ($inventoryId && isset($inventoryMap[$inventoryId])) {
                        $inventoryName = $inventoryMap[$inventoryId];
                    }
                }

                $row['inventory_name_from_relation'] = $inventoryName;
            }

            return $rows;
        } catch (\Throwable $e) {
            Log::error('Error in getQuotesWithRelatedNames', [
                'error' => $e->getMessage(),
                'rows_count' => count($rows)
            ]);

            // في حالة الخطأ، إضافة قيم افتراضية
            foreach ($rows as &$row) {
                $row['contact_name'] = '-';
                if (!isset($row['quote']['inventory_name_from_relation'])) {
                    $row['quote']['inventory_name_from_relation'] = '-';
                }
            }

            return $rows;
        }
    }

    // get all invoices with customer name
    public function getInvoicesWithCustomerName(array $rows): array
    {
        try {
            $parentMap = $this->buildRelationMap(
                $this->customers,
                'customers',
                'name',
                'id'
            );


            foreach ($rows as &$item) {

                $pid = $item['contact_id'] ?? null;
                $item['contact_name'] = $pid ? ($parentMap[$pid] ?? '-') : '-';
            }

            return $rows;
        } catch (\Throwable $e) {
            foreach ($rows as &$item) {
                $item['contact_name'] = '-';
            }

            return $rows;
        }
    }


    private function buildRelationMap(
        $resourceInstance,
        string $resourceKey,
        string $nameField,
        string $idField = 'id'
    ): array {
        $cacheKey = "qoyod:{$resourceKey}_map";

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($this->ttlMinutes),
            function () use ($resourceInstance, $resourceKey, $nameField, $idField) {
                $map = [];
                $page = 1;
                $perPage = 200;
                $maxPages = 10000;
                $processedIds = [];
                $consecutiveEmptyPages = 0;
                $maxConsecutiveEmpty = 3;

                do {
                    try {
                        $resp = $resourceInstance->all([
                            'page' => $page,
                            'per_page' => $perPage,
                        ]);

                        if (!is_array($resp) || !isset($resp[$resourceKey])) {

                            break;
                        }

                        $items = $resp[$resourceKey];

                        // التحقق من الصفحة الفارغة
                        if (empty($items)) {
                            $consecutiveEmptyPages++;
                            if ($consecutiveEmptyPages >= $maxConsecutiveEmpty) {
                                break;
                            }
                            $page++;
                            continue;
                        }

                        $consecutiveEmptyPages = 0; // إعادة تعيين العداد

                        // معالجة العناصر مباشرة دون تخزينها
                        $currentIds = [];
                        $newItemsCount = 0;

                        foreach ($items as $item) {
                            $id = $item[$idField] ?? null;
                            $name = $item[$nameField] ?? null;

                            if ($id !== null) {
                                $currentIds[] = $id;

                                // إضافة للخريطة فقط إذا كان جديد وله اسم صالح
                                if ($name !== null && !isset($map[$id])) {
                                    $map[$id] = $name;
                                    $newItemsCount++;
                                }
                            }
                        }

                        // التحقق من التكرار باستخدام المعرفات فقط
                        if (!empty($currentIds)) {
                            $currentIds = array_unique($currentIds);
                            $duplicateIds = array_intersect($currentIds, $processedIds);

                            if (!empty($duplicateIds)) {

                                break;
                            }

                            // إضافة المعرفات الجديدة فقط
                            $processedIds = array_merge($processedIds, $currentIds);

                            // تحسين: إزالة المعرفات القديمة للحفاظ على حجم معقول
                            if (count($processedIds) > 50000) {
                                $processedIds = array_slice($processedIds, -30000); // الاحتفاظ بآخر 30k معرف
                            }
                        }

                        // التحقق من وجود صفحة تالية باستخدام meta
                        if (isset($resp['meta']['last_page'])) {
                            $lastPage = (int) $resp['meta']['last_page'];
                            if ($page >= $lastPage) {

                                break;
                            }
                        } elseif (isset($resp['meta']['current_page'], $resp['meta']['total'])) {
                            $totalItems = (int) $resp['meta']['total'];
                            $expectedPages = ceil($totalItems / $perPage);
                            if ($page >= $expectedPages) {

                                break;
                            }
                        }

                        $page++;

                        // حماية من الحلقات اللانهائية
                        if ($page > $maxPages) {

                            break;
                        }
                    } catch (\Throwable $e) {

                        break;
                    }

                    // تنظيف الذاكرة بشكل دوري
                    if ($page % 50 === 0) {
                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }
                    }
                } while (true);


                return $map;
            }
        );
    }

    protected function isDuplicatePage(Collection $currentItems, Collection $existingItems): bool
    {
        $newIds      = $currentItems->pluck('id')->filter()->unique()->values();
        $existingIds = $existingItems->pluck('id')->unique();

        return $newIds->isNotEmpty() && $newIds->intersect($existingIds)->count() === $newIds->count();
    }
}
