<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Models\CentralSetting;
use App\Models\TenantSetting;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Single gateway for reading/writing both CentralSetting and TenantSetting.
 * Caches aggressively (24h TTL) with keys:
 *
 *   central_settings            ← one hash of all central settings
 *   tenant_{id}_settings        ← one hash of all settings for a tenant
 *
 * Invalidated automatically on every write.
 *
 * NEVER call CentralSetting::... or TenantSetting::... directly in business
 * logic — always go through this class so the cache stays consistent.
 */
final class SettingsRepository
{
    public const CACHE_TTL_SECONDS = 86400; // 24h

    // ------------------------------------------------------------------ CENTRAL

    public function getCentral(string $key, mixed $default = null): mixed
    {
        $map = $this->allCentral();

        return $map[$key]['value'] ?? $default;
    }

    public function setCentral(string $key, mixed $value, array $meta = []): CentralSetting
    {
        $setting = CentralSetting::query()->firstOrNew(['key' => $key]);

        $setting->group        = $meta['group']        ?? $setting->group ?? 'general';
        $setting->is_encrypted = $meta['is_encrypted'] ?? $setting->is_encrypted ?? false;
        $setting->cast         = $meta['cast']         ?? $setting->cast ?? 'string';
        $setting->description  = $meta['description']  ?? $setting->description;
        $setting->value        = $value;
        $setting->save();

        $this->flushCentralCache();

        return $setting;
    }

    public function forgetCentral(string $key): void
    {
        CentralSetting::query()->where('key', $key)->delete();
        $this->flushCentralCache();
    }

    public function allCentral(): array
    {
        return Cache::remember(
            $this->centralCacheKey(),
            self::CACHE_TTL_SECONDS,
            fn () => CentralSetting::query()
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->key => [
                        'value'        => $row->value,
                        'group'        => $row->group,
                        'is_encrypted' => $row->is_encrypted,
                        'cast'         => $row->cast,
                    ],
                ])
                ->toArray()
        );
    }

    // ------------------------------------------------------------------- TENANT

    public function get(string $key, mixed $default = null, ?int $tenantId = null): mixed
    {
        $map = $this->all($tenantId);

        return $map[$key]['value'] ?? $default;
    }

    public function set(string $key, mixed $value, array $meta = [], ?int $tenantId = null): TenantSetting
    {
        $tenantId = $this->resolveTenantId($tenantId);

        // BYPASS TenantScope — targeting a specific tenant_id explicitly.
        // Super Admin may be running under Default-Tenant fallback context;
        // relying on the scope would silently return no-match.
        $setting = TenantSetting::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        if (! $setting) {
            $setting = new TenantSetting();
            $setting->tenant_id = $tenantId;
            $setting->key       = $key;
        }

        $setting->group        = $meta['group']        ?? $setting->group ?? 'general';
        $setting->is_encrypted = $meta['is_encrypted'] ?? $setting->is_encrypted ?? false;
        $setting->cast         = $meta['cast']         ?? $setting->cast ?? 'string';
        $setting->description  = $meta['description']  ?? $setting->description;
        $setting->value        = $value;
        $setting->save();

        $this->flushTenantCache($tenantId);

        return $setting;
    }

    public function forget(string $key, ?int $tenantId = null): void
    {
        $tenantId = $this->resolveTenantId($tenantId);

        TenantSetting::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->where('key', $key)
            ->delete();

        $this->flushTenantCache($tenantId);
    }

    public function all(?int $tenantId = null): array
    {
        $tenantId = $this->resolveTenantId($tenantId);

        return Cache::remember(
            $this->tenantCacheKey($tenantId),
            self::CACHE_TTL_SECONDS,
            fn () => TenantSetting::withoutTenancy()
                ->where('tenant_id', $tenantId)
                ->get()
                ->mapWithKeys(fn ($row) => [
                    $row->key => [
                        'value'        => $row->value,
                        'group'        => $row->group,
                        'is_encrypted' => $row->is_encrypted,
                        'cast'         => $row->cast,
                    ],
                ])
                ->toArray()
        );
    }

    public function flushTenantCache(int $tenantId): void
    {
        Cache::forget($this->tenantCacheKey($tenantId));
    }

    public function flushCentralCache(): void
    {
        Cache::forget($this->centralCacheKey());
    }

    // ------------------------------------------------------------------- HELPERS

    private function centralCacheKey(): string
    {
        return 'central_settings';
    }

    private function tenantCacheKey(int $tenantId): string
    {
        return "tenant_{$tenantId}_settings";
    }

    private function resolveTenantId(?int $tenantId): int
    {
        $tenantId ??= TenantContext::currentId();

        if ($tenantId === null) {
            throw new RuntimeException(
                'SettingsRepository: no tenant resolved. Pass $tenantId explicitly or '
                .'call from within a tenant-aware request / TenantContext::runAs().'
            );
        }

        return $tenantId;
    }
}
