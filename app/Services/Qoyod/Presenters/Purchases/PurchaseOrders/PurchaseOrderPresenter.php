<?php

namespace App\Services\Qoyod\Presenters\Purchases\PurchaseOrders;

use App\Services\Qoyod\Contracts\Resources\InventoryResourceInterface;
use Illuminate\Support\Facades\Cache;

class PurchaseOrderPresenter
{
    protected int $ttlMinutes;

    public function __construct(protected InventoryResourceInterface $inventory)
    {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }


    /*
    |============================================================================
    |============================================================================
    |                 Get Purchase Order with inventory name
    |============================================================================
    |============================================================================
    */
    public function getPurchaseOrdersWithInventoryName($row)
    {
        $parentMap = $this->buildRelationMap(
            $this->inventory,
            'inventories',
            'ar_name'
        );

        $inventoryName = '-';

        if (isset($row['order']['line_items']) && is_array($row['order']['line_items']) && count($row['order']['line_items']) > 0) {
            $firstItem = $row['order']['line_items'][0];
            $pid       = $firstItem['inventory_id'] ?? null;

            if ($pid && isset($parentMap[$pid])) {
                $inventoryName = $parentMap[$pid];
            }
        }

        $row['order']['inventory_name_from_relation'] = $inventoryName;

        return $row;
    }





    /*
    |============================================================================
    |============================================================================
    |                          Private methods
    |============================================================================
    |============================================================================
    */

    /*
    |--------------------------------------------------------------------------
    | دالة مساعدة 
    |--------------------------------------------------------------------------
    | لجلب العلاقات ومنع التكرار في الجلب 
    */
    private function buildRelationMap(
        $resourceInstance,
        string $resourceKey,
        string $nameField
    ): array {
        $cacheKey = "qoyod:{$resourceKey}_map";

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($this->ttlMinutes),
            function () use ($resourceInstance, $resourceKey, $nameField) {

                $map          = [];
                $page         = 1;
                $perPage      = 100;
                $pageParam    = 'page';   // قد يتبدّل إلى 'pages' لاحقًا
                $maxPages     = 100;      // حدّ أمان لمنع الحلقات اللانهائية
                $supportsMeta = null;     // null => لم نتحقق بعد

                do {
                    // تجهيز الاستعلام مع الوسيط الصحيح
                    $query = [
                        $pageParam => $page,
                        'per_page' => $perPage,
                    ];

                    $resp  = $resourceInstance->all($query);
                    $items = $resp[$resourceKey] ?? [];

                    /*--------------------------------------------------
                 | (1) تجميع العناصر المسترجعة في الخريطة
                 *-------------------------------------------------*/
                    foreach ($items as $row) {
                        if (isset($row['id'], $row[$nameField])) {
                            $map[$row['id']] = $row[$nameField] ?: '-';
                        }
                    }

                    /*--------------------------------------------------
                 | (2) تحديد ما إذا كانت الاستجابة تحتوي على meta
                 *-------------------------------------------------*/
                    if ($supportsMeta === null) {
                        $supportsMeta = isset($resp['meta']['last_page']);

                        // لا يوجد meta ولا عناصر في أول محاولة: جرّب 'pages'
                        if (!$supportsMeta && $page === 1 && empty($items)) {
                            $pageParam    = 'pages';
                            $supportsMeta = false; // سنُعيد التحقق في الدورة القادمة
                            continue;             // أعد المحاولة بالوسيط الجديد
                        }
                    }

                    /*--------------------------------------------------
                 | (3) تحديد شرط الإنهاء
                 *-------------------------------------------------*/
                    if ($supportsMeta) {
                        // لدينا meta ⇒ نتوقف عند آخر صفحة مصرح بها
                        $lastPage = (int) ($resp['meta']['last_page'] ?? $page);
                        if ($page >= $lastPage) {
                            break;
                        }
                    } else {
                        // لا meta ⇒ نتوقف إذا الصفحة فارغة أو المعرّفات مكررة
                        if (empty($items)) {
                            break;
                        }
                        $ids    = array_column($items, 'id');
                        $newIds = array_diff($ids, array_keys($map));
                        if (empty($newIds)) {
                            break;  // تكرار تام ⇒ لا صفحات جديدة
                        }
                    }

                    /*--------------------------------------------------
                 | (4) تحريك العدّاد والحماية من الدوران المفرط
                 *-------------------------------------------------*/
                    $page++;
                    if ($page > $maxPages) {
                        break;      // حماية إضافية
                    }
                } while (true);

                return $map;
            }
        );
    }
}
