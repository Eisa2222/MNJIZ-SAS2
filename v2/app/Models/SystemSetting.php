<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * V2 — Operator-managed runtime settings (mail, Moyasar, hero, trial).
 *
 * Spec lines 240-248: get/set/setMany API + caching.
 *
 * Sensitive keys (auto-encrypted at write, auto-decrypted at read):
 *   moyasar_secret_key, moyasar_webhook_secret, mail_password
 */
final class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label'];

    private const CACHE_KEY = 'system_settings';
    private const CACHE_TTL = 3600;   // 1 hour per spec line 244

    private const SENSITIVE_KEYS = [
        'moyasar_secret_key',
        'moyasar_webhook_secret',
        'mail_password',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $map = self::cachedMap();
        return $map[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): self
    {
        $stored = self::isSensitive($key) && $value !== null && $value !== ''
            ? Crypt::encryptString((string) $value)
            : (is_array($value) || is_object($value) ? json_encode($value) : (string) $value);

        $row = self::updateOrCreate(['key' => $key], ['value' => $stored]);
        self::forgetCache();
        return $row;
    }

    /** @param  array<string,mixed> $data */
    public static function setMany(array $data): void
    {
        foreach ($data as $k => $v) {
            self::set($k, $v);
        }
        self::forgetCache();
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function cachedMap(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $map = [];
            foreach (self::all(['key', 'value']) as $row) {
                $map[$row->key] = self::decode($row->key, $row->value);
            }
            return $map;
        });
    }

    private static function decode(string $key, ?string $raw): mixed
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (self::isSensitive($key)) {
            try { return Crypt::decryptString($raw); }
            catch (\Throwable) { return null; }
        }
        // JSON heuristic: if it parses, return the array.
        $decoded = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
    }

    private static function isSensitive(string $key): bool
    {
        return in_array($key, self::SENSITIVE_KEYS, true);
    }
}
