<?php

namespace App\Services\Qoyod\Resources;

use Illuminate\Support\Facades\Cache;
use App\Services\Qoyod\Contracts\CrudResourceInterface;

class CacheResourceDecorator implements CrudResourceInterface
{
    protected int $ttlMinutes;

    public function __construct(
        private CrudResourceInterface $resource,
        private string $cachePrefix = ''
    ) {
        $this->ttlMinutes = config('qoyod.cache_ttl', 10);
    }

    public function all(array $query = []): array
    {
        $fullKey = "{$this->cachePrefix}:full";


        return Cache::remember(
            $fullKey,
            now()->addMinutes($this->ttlMinutes),
            fn() => $this->resource->all($query)
        );
    }

    public function find(int|string $id): array
    {
        return $this->resource->find($id);
    }

    public function create(array $data): array
    {
        $this->flush();
        return $this->resource->create($data);
    }

    public function update(int|string $id, array $data): array
    {
        $this->flush();
        return $this->resource->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        $this->flush();
        return $this->resource->delete($id);
    }

    private function flush(): void
    {
        Cache::flush(); // أو Cache::forgetPattern("{$this->cachePrefix}*")
    }
}
