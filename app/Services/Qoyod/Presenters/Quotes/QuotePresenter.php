<?php

namespace App\Services\Qoyod\Presenters\Quotes;

use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class QuotePresenter
{
    protected int $ttlMinutes;

    public function __construct(protected CustomerResourceInterface $customers, protected InventoryResourceInterface $inventory)
    {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }


    public function getQuotesWithRelatedNames(array $rows): array
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
}
