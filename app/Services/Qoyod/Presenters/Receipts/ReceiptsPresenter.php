<?php

namespace App\Services\Qoyod\Presenters\Receipts;

use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;
use App\Services\Qoyod\Contracts\Resources\BillResourceInterface;
use App\Services\Qoyod\Contracts\Resources\CustomerResourceInterface;
use App\Services\Qoyod\Contracts\Resources\InvoiceResourceInterface;
use App\Services\Qoyod\Contracts\Resources\ReceiptResourceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReceiptsPresenter
{
    protected int $ttlMinutes;

    public function __construct(
        protected ReceiptResourceInterface  $receipts,
        protected CustomerResourceInterface $customers,
        protected AccountResourceInterface  $accounts,
        protected InvoiceResourceInterface  $invoices,
        protected BillResourceInterface     $bills,

    ) {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }

    public function getReceiptsByVendorId($filterValue)
    {
        $cacheKey = "qoyod:receipts_vendor_{$filterValue}";

        return Cache::remember($cacheKey, now()->addMinutes($this->ttlMinutes), function () use ($filterValue) {
            $allItems = collect();
            $page        = 1;
            $perPage     = 100000;
            do {
                try {
                    $resp = $this->receipts->all([
                        'page'     => $page,
                        'per_page' => $perPage,
                    ]);
                } catch (\Exception $e) {
                    break;
                }

                $items = collect($resp['receipts'] ?? []);



                // 2. التحقق من تكرار السجلات
                if ($this->isDuplicatePage($items, $allItems)) {
                    break;
                }

                // 3. دمج السجلات بدون تكرار
                $allItems = $allItems->merge($items)->unique('id')->values();



                if ($items->isEmpty()) {
                    break;
                }

                $page++;
            } while (true);

            return $allItems->filter(function ($receipt) use ($filterValue) {
                return isset($receipt['contact_id']) && (string)$receipt['contact_id']=== (string)$filterValue;
            })->values();
        });
    }

    public function getReceiptWithAccountAndCustomer($rows)
    {
        try {
            $customerMap = $this->buildRelationMap(
                $this->customers,
                'customers',
                'name',
                'id'
            );

            $accountMap = $this->buildRelationMap(
                $this->accounts,
                'accounts',
                'name_ar',
                'id'
            );

            // account
            $accountId = $rows['account_id'] ?? null;
            $rows['account_name'] = $accountId && isset($accountMap[$accountId])
                ? $accountMap[$accountId]
                : '-';

            // contact 
            $contactId = $rows['contact_id'] ?? null;
            $rows['contact_name'] = $contactId && isset($customerMap[$contactId])
                ? $customerMap[$contactId]
                : '-';


            $invoiceMap = $this->buildRelationMap(
                $this->invoices,
                'invoices',
                'reference',
                'id'
            );


            $billsMap = $this->buildRelationMap(
                $this->bills,
                'bills',
                'reference',
                'id'
            );

            foreach ($rows['allocations'] as &$item) {
                if ($item['allocatee_type'] == "Invoice") {
                    $allocation = $item['allocatee_id'] ?? null;
                    $item['allocatee_name'] = $allocation && isset($invoiceMap[$allocation])
                        ? $invoiceMap[$allocation]
                        : '-';
                } elseif ($item['allocatee_type'] == "Bill") {
                    $allocation = $item['allocatee_id'] ?? null;
                    $item['allocatee_name'] = $allocation && isset($billsMap[$allocation])
                        ? $billsMap[$allocation]
                        : '-';
                }
            }

            return $rows;
        } catch (\Throwable $e) {

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
