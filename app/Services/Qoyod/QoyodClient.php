<?php

namespace App\Services\Qoyod;

use App\Models\GeneralSetting\SystemSetting\Settings;
use App\Services\Qoyod\Contracts\QoyodClientInterface;
use App\Services\Qoyod\Exceptions\QoyodConfigurationException;
use App\Services\Qoyod\Exceptions\QoyodRequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use RuntimeException;

class QoyodClient implements QoyodClientInterface
{
    private const CACHE_TTL_MIN     = 30;
    private const REQUEST_TIMEOUT_S = 60;
    private const CONNECT_TIMEOUT_S = 5;

    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = Cache::remember(
            'qoyod_api_key',
            now()->addMinutes(self::CACHE_TTL_MIN),
            fn() => Settings::value('qoyod_api_key')
                ?? throw new RuntimeException('Qoyod API key missing')
        );

        $this->baseUrl = Cache::remember(
            'qoyod_base_url',
            now()->addMinutes(self::CACHE_TTL_MIN),
            fn() => Settings::value('qoyod_base_url')
                ?: 'https://www.qoyod.com/api/2.0'
        );
    }

    /**
     *
     * @return PendingRequest
     */
    public function http(): PendingRequest
    {

        return Http::baseUrl($this->baseUrl)
            ->timeout(self::REQUEST_TIMEOUT_S)
            ->connectTimeout(self::CONNECT_TIMEOUT_S)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'API-KEY'      => $this->apiKey,
                'User-Agent'   => $this->getUserAgent(),
            ]);
    }


    /**
     *
     * @param string $uri
     * @return string
     */
    public function url(string $uri): string
    {
        return '/' . ltrim($uri, '/');
    }


    private function getUserAgent(): string
    {
        $name    = config('app.name', 'LaravelApp');
        $version = config('app.version', '1.0');
        return "{$name}/{$version}";
    }
}
