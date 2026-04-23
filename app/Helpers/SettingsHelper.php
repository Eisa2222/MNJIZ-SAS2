<?php

namespace App\Helpers;

use App\Models\GeneralSetting\SystemSetting\Settings;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    /**
     * الحصول على إعدادات التطبيق.
     *
     * @return \App\Models\GeneralSetting\SystemSetting\Settings|null
     */
    public static function getSettings()
    {
        // استخدام التخزين المؤقت لتحسين الأداء
        return Cache::remember('app_settings', 60, function () {
            return Settings::first();
        });
    }

    /**
     * الحصول على قيمة إعداد معين.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $settings = self::getSettings();

        if ($settings && isset($settings->$key)) {
            return $settings->$key;
        }

        return $default;
    }
}
