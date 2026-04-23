<?php

namespace App\Services\Qoyod\Presenters\Accounts;

use App\Services\Qoyod\Contracts\Resources\AccountResourceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AccountsPresenter
{
    protected int $ttlMinutes;

    public function __construct(protected AccountResourceInterface $accounts)
    {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }

    public function getFilterAccounts(string $filterKey, string $filterValue): Collection
    {
        $cacheKey = "qoyod:accounts_{$filterKey}_{$filterValue}";

        return Cache::remember($cacheKey, now()->addMinutes($this->ttlMinutes), function () use ($filterKey, $filterValue) {
            $allAccounts = collect();
            $page        = 1;
            $perPage     = 100;
            $useMeta     = false;
            $lastPage    = null;

            do {
                try {
                    $resp = $this->accounts->all([
                        'page'     => $page,
                        'per_page' => $perPage,
                    ]);
                } catch (\Exception $e) {
                    break;
                }

                $items = collect($resp['accounts'] ?? []);

                // 1. استخدام metadata إذا كانت موجودة
                if (isset($resp['meta']['last_page'])) {
                    $useMeta  = true;
                    $lastPage = (int) $resp['meta']['last_page'];
                }

                // 2. التحقق من تكرار السجلات
                if ($this->isDuplicatePage($items, $allAccounts)) {
                    break;
                }

                // 3. دمج السجلات بدون تكرار
                $allAccounts = $allAccounts->merge($items)->unique('id')->values();

                // 4. إذا وصلنا لنهاية الصفحات حسب metadata، نخرج
                if ($useMeta && $page >= $lastPage) {
                    break;
                }

                if ($items->isEmpty()) {
                    break;
                }

                $page++;
            } while (true);

            return $allAccounts->filter(function ($account) use ($filterKey, $filterValue) {
                return isset($account[$filterKey]) && (string)$account[$filterKey]  === (string)$filterValue &&  (string)$account['status']=="Active";
            })->values();
        });
    }


    protected function isDuplicatePage(Collection $currentItems, Collection $existingItems): bool
    {
        $newIds      = $currentItems->pluck('id')->filter()->unique()->values();
        $existingIds = $existingItems->pluck('id')->unique();

        return $newIds->isNotEmpty() && $newIds->intersect($existingIds)->count() === $newIds->count();
    }
}
