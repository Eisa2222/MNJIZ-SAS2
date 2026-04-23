<?php

namespace App\Services\Qoyod\Presenters\Products\Categories;

use App\Services\Qoyod\Contracts\Resources\CategoryResourceInterface;
use App\Services\Qoyod\Contracts\Resources\UserResourceInterface;
use Illuminate\Support\Facades\Cache;

class CategoryPresenter
{
    protected int $ttlMinutes;

    public function __construct(protected CategoryResourceInterface $categories)
    {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }


    public function presentCollection(array $rows): array
    {
        $parentMap = $this->buildRelationMap(
            $this->categories,
            'categories',
            'name'
        );


        foreach ($rows as &$item) {

            $pid = $item['parent_id'] ?? null;
            $item['parent_name'] = $pid ? ($parentMap[$pid] ?? '-') : '-';
        }

        return $rows;
    }

    /**
     *
     * @param mixed  $resourceInstance  واجهة استدعاء الـ API
     * @param string $resourceKey       المفتاح في الاستجابة (مثلاً "categories")
     * @param string $nameField         اسم الحقل المطلوب استخدامه كـ "name"
     *
     * @return array   خارطة من id => name
     */
    protected function buildRelationMap($resourceInstance, string $resourceKey, string $nameField): array
    {
        $cacheKey = "qoyod:{$resourceKey}_map";

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($this->ttlMinutes),
            function () use ($resourceInstance, $resourceKey, $nameField) {
                $map       = [];
                $page      = 1;
                $perPage   = 100;
                $useMeta   = false;
                $lastPage  = null;

                do {
                    // 1. نجلب الصفحة الحالية
                    $resp = $resourceInstance->all([
                        'page'     => $page,
                        'per_page' => $perPage,
                    ]);

                    // 2. نحاول استخراج عناصر هذه الصفحة
                    $items = $resp[$resourceKey] ?? [];

                    // 3. إذا كانت الاستجابة تحتوي على metadata (current_page, last_page)، نفعّل استخدامه
                    if (isset($resp['meta']['last_page'])) {
                        $useMeta  = true;
                        $lastPage = (int) $resp['meta']['last_page'];
                    }

                    // 4. إذا كان لا يوجد أي عنصر في هذه الصفحة، نخرج فورًا
                    if (empty($items)) {
                        break;
                    }

                    // 5. إذا لم نستعمل metadata، فنسجّل قائمة المعرفات القديمة ونقارن
                    if (!$useMeta) {
                        // نحصل على المعرفات الجديدة
                        $newIds      = array_map(fn($i) => $i['id'] ?? null, $items);
                        $newIds      = array_filter($newIds, fn($id) => $id !== null);
                        $newIds      = array_unique($newIds);

                        // المعرفات الموجودة في خارطتنا مسبقًا
                        $existingIds = array_keys($map);

                        // إذا كل المعرفات الجديدة موجودة مسبقًا، إذاً خرجنا من الحلقة
                        if (count($newIds) > 0 && count(array_intersect($newIds, $existingIds)) === count($newIds)) {
                            break;
                        }
                    }

                    // 6. نضيف أو نحدّث الخارطة بناءً على العناصر المستخلصة
                    foreach ($items as $i) {
                        if (isset($i['id']) && array_key_exists($nameField, $i)) {
                            $map[$i['id']] = $i[$nameField] ?? '-';
                        }
                    }

                    // 7. إذا كنا نستخدم metadata ووصلنا إلى الصفحة الأخيرة، نخرج
                    if ($useMeta && $page >= $lastPage) {
                        break;
                    }

                    $page++;
                } while (true);

                return $map;
            }
        );
    }
}
