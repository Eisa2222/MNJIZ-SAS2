<?php

namespace App\Helpers;

use App\Models\GeneralSetting\SystemSetting\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OpenAISettings
{
    /**
     * الحصول على إعدادات OpenAI
     */
    public static function get()
    {
        // تجنب الكاش مؤقتاً للتأكد من القيم
        $settings = Settings::current();

        $data = [
            'api_key' => $settings->openai_api_key,
            'model' => $settings->openai_model,
            'enabled' => $settings->chatgpt_enabled == 1  // مقارنة مباشرة مع 1
        ];

        Log::info('Settings Retrieved', [
            'raw_value' => $settings->chatgpt_enabled,
            'is_enabled' => $data['enabled']
        ]);

        return $data;
    }

    /**
     * التحقق من تفعيل الخدمة
     */
    public static function isEnabled()
    {
        $settings = Settings::current();
        return $settings ? $settings->chatgpt_enabled == 1 : false;
    }

    /**
     * الحصول على API Key
     */
    public static function getApiKey()
    {
        $settings = self::get();
        return $settings['api_key'];
    }

    /**
     * الحصول على Model
     */
    public static function getModel()
    {
        $settings = self::get();
        return $settings['model'];
    }

    /**
     * تحديث الكاش
     */
    public static function clearCache()
    {
        Cache::forget('openai_settings');
    }
}