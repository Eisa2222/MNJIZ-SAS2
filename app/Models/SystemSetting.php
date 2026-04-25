<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Phase C — central key/value SaaS configuration store.
 *
 * Spec-compliant API:
 *   SystemSetting::get($key, $default = null)
 *   SystemSetting::set($key, $value, $meta = [])
 *   SystemSetting::setMany([...], $meta = [])
 *   SystemSetting::forgetCache()
 *   SystemSetting::allAsKeyValue()
 *
 * Encryption: values flagged `is_encrypted = true` are stored as Crypt
 * cipher-text and transparently decrypted on read. Lookup-by-key reads
 * are cached as a single hash under `system_settings` (24h TTL); any
 * write invalidates the cache via `forgetCache()`.
 *
 * Casts (per row):
 *   string | bool | int | float | json | array
 */
class SystemSetting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'system_settings';
    public const CACHE_TTL_SECONDS = 86400; // 24 h

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
        'is_encrypted',
        'cast',
    ];

    protected $casts = [
        'is_encrypted' => 'bool',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors / Mutators — transparent encryption + cast
    |--------------------------------------------------------------------------
    */

    public function getValueAttribute(?string $raw): mixed
    {
        if ($raw === null) {
            return null;
        }

        $plain = $this->is_encrypted ? $this->tryDecrypt($raw) : $raw;

        return $this->castOut($plain);
    }

    public function setValueAttribute(mixed $value): void
    {
        if ($value === null) {
            $this->attributes['value'] = null;
            return;
        }

        $serialized = $this->castIn($value);

        $this->attributes['value'] = $this->is_encrypted
            ? Crypt::encryptString((string) $serialized)
            : $serialized;
    }

    public function getRawValue(): ?string
    {
        return $this->attributes['value'] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Spec-mandated static API — get / set / setMany / forgetCache / allAsKeyValue
    |--------------------------------------------------------------------------
    */

    public static function get(string $key, mixed $default = null): mixed
    {
        $map = self::cachedMap();

        if (! array_key_exists($key, $map)) {
            return $default;
        }

        // Map carries already-decrypted, already-cast values.
        return $map[$key];
    }

    /**
     * Persist one key/value pair.
     *
     * @param  array{group?: string, label?: ?string, is_encrypted?: bool, cast?: ?string} $meta
     */
    public static function set(string $key, mixed $value, array $meta = []): self
    {
        /** @var self $row */
        $row = self::query()->firstOrNew(['key' => $key]);

        // Locked-in metadata wins over the existing row only if explicitly
        // provided — preserves a previously-flagged `is_encrypted=true` key
        // when a UI form just sends a new value.
        $row->group        = $meta['group']        ?? $row->group ?? 'general';
        $row->label        = $meta['label']        ?? $row->label;
        $row->is_encrypted = $meta['is_encrypted'] ?? $row->is_encrypted ?? false;
        $row->cast         = $meta['cast']         ?? $row->cast;

        $row->value = $value;
        $row->save();

        self::forgetCache();

        return $row;
    }

    /**
     * Persist many key/value pairs in one call. The optional `$meta` array is
     * applied to every key (typical use: a tab-form save where every field
     * shares the same `group`).
     *
     * @param  array<string, mixed> $values
     * @param  array{group?: string, label?: ?string, is_encrypted?: bool, cast?: ?string} $meta
     */
    public static function setMany(array $values, array $meta = []): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value, $meta);
        }
        // Single cache flush instead of N — set() already flushes per-call,
        // but a final flush guards against partial-write races.
        self::forgetCache();
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Returns every system setting as a `[key => decoded_value]` hash. Used
     * by `ApplySystemSettings` middleware for runtime Config::set() and by
     * the Super Admin settings tabs for prefilling forms.
     *
     * @return array<string, mixed>
     */
    public static function allAsKeyValue(): array
    {
        return self::cachedMap();
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>
     */
    private static function cachedMap(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            $out = [];
            foreach (self::query()->get() as $row) {
                /** @var self $row */
                // value accessor handles decrypt + cast.
                $out[$row->key] = $row->value;
            }
            return $out;
        });
    }

    private function castIn(mixed $value): string
    {
        return match ($this->cast) {
            'json', 'array' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'bool', 'boolean' => $value ? '1' : '0',
            'int', 'integer'  => (string) (int) $value,
            'float', 'double' => (string) (float) $value,
            default           => (string) $value,
        };
    }

    private function castOut(string $value): mixed
    {
        return match ($this->cast) {
            'json', 'array' => json_decode($value, true),
            'bool', 'boolean' => (bool) $value && $value !== '0' && $value !== 'false',
            'int', 'integer'  => (int) $value,
            'float', 'double' => (float) $value,
            default           => $value,
        };
    }

    private function tryDecrypt(string $raw): string
    {
        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable $e) {
            // Defensive — never crash a request because a setting is broken.
            // Operators see the cipher-text in logs and can fix it.
            report($e);
            return '';
        }
    }
}
