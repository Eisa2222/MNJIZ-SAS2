<?php

use Illuminate\Support\Facades\Cache;

class RelationMap
{
    protected int $ttlMinutes;

    public function __construct()
    {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }

    public function buildRelationMap(
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
