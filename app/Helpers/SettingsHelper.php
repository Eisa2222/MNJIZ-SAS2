<?php

namespace App\Helpers;

use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * الحصول على إعدادات التطبيق — tenant-scoped cache.
     *
     * IMPORTANT: prior version used a GLOBAL cache key 'app_settings' which
     * was a critical cross-tenant leak — tenant A's cached settings were
     * served to tenant B. Now keyed per tenant_id.
     *
     * @return \App\Models\GeneralSetting\SystemSetting\Settings
     */
    public static function getSettings()
    {
        $tenantId = TenantContext::currentId() ?? 0;

        return Cache::remember("tenant_{$tenantId}_app_settings", 60, function () {
            return Settings::current();
        });
    }

    /**
     * الحصول على قيمة إعداد معين.
     */
    public static function get($key, $default = null)
    {
        $settings = self::getSettings();

        if ($settings && isset($settings->$key)) {
            return $settings->$key;
        }

        return $default;
    }

    /**
     * Flush the current-tenant settings cache. Call after any Settings edit.
     */
    public static function flush(?int $tenantId = null): void
    {
        $tenantId ??= TenantContext::currentId() ?? 0;
        Cache::forget("tenant_{$tenantId}_app_settings");
    }
}
