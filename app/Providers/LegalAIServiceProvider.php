<?php

namespace App\Providers;

use App\Contracts\LegalAI\AIProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class LegalAIServiceProvider extends ServiceProvider implements DeferrableProvider
{

    /*
    |--------------------------------------------------------------------------
    | تسجيل أي خدمات للتطبيق.
    |--------------------------------------------------------------------------
    */
    public function register(): void
    {
        $this->app->singleton(AIProvider::class, function ($app) {

            $providerKey = config('legal_ai.default_provider', 'openai');
            $providerClass = config("legal_ai.providers.{$providerKey}.class");

            if (!$providerClass || !class_exists($providerClass)) {
                throw new \Exception("AI Provider '{$providerKey}' is not configured correctly in config/legal_ai.php.");
            }

            return new $providerClass();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | الحصول على الخدمات التي يوفرها مقدم الخدمة.
    |--------------------------------------------------------------------------
    | هذا يحسن الأداء عن طريق عدم تحميل الخدمة إلا عند الحاجة إليها فعليًا.
    */
    public function provides(): array
    {
        return [AIProvider::class];
    }
}
