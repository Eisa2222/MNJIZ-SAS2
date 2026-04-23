<?php

namespace App\Services\General;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UrlShortenerService
{
    public function shorten(string $originalUrl): string
    {
        $cacheKey   = 'short_url_' . md5($originalUrl);
        $cachedUrl  = Cache::get($cacheKey);

        if ($cachedUrl) {
            return $cachedUrl;
        }

        try {
            $response = Http::timeout(10)->get('https://is.gd/create.php', [
                'format'    => 'simple',
                'url'       => $originalUrl
            ]);

            if ($response->successful()) {
                $shortUrl = trim($response->body());

                if ($this->isValidShortUrl($shortUrl)) {
                    Cache::put($cacheKey, $shortUrl, now()->addDay());

                    return $shortUrl;
                }
            }
        } catch (\Exception $e) {
            Log::error('خطأ في اختصار الرابط', [
                'url' => $originalUrl,
                'error' => $e->getMessage()
            ]);
        }

        return $originalUrl;
    }

    protected function isValidShortUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && strpos($url, 'https://is.gd/') === 0;
    }
}