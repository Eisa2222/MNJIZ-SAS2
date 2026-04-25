<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Models\CentralSetting;
use App\Models\SystemSetting;

/**
 * Phase C — bridge service over `SystemSetting` (new) + `CentralSetting`
 * (Phase 3 legacy).
 *
 * Read order:
 *   1. system_settings.{key}     — preferred
 *   2. central_settings.{key}    — fallback when key not in system_settings
 *   3. $default                  — last resort
 *
 * Write behavior (`set` / `setMany`):
 *   - Always writes the new `system_settings` row.
 *   - If a `central_settings` row already exists for the same key, it is
 *     ALSO updated (dual-write). That keeps any existing call-site that
 *     reads via `SettingsRepository::getCentral()` working unchanged.
 *   - We never CREATE new central_settings rows — only update existing
 *     ones. The bridge is forward-compatible (everything new lives in
 *     system_settings), not retroactive.
 *
 * Masking helper for sensitive values surfaces dotted patterns
 * (e.g. `••••••••1234`) when the Super Admin UI renders an existing
 * encrypted secret. Plaintext value never leaves the server.
 */
final class SystemSettingsService
{
    /** Keys that should appear masked in the UI / logs / responses. */
    private const SENSITIVE_KEYS = [
        'mail_password',
        'moyasar_secret_key',
        'moyasar_webhook_secret',
    ];

    /**
     * @param  string $key
     * @param  mixed  $default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = SystemSetting::get($key);

        if ($value !== null) {
            return $value;
        }

        // Fallback to the Phase 3 central_settings row (decrypted via its
        // own model accessor). Safe even when CentralSetting has no row —
        // returns null in that case, and we fall through to $default.
        $legacy = CentralSetting::query()->where('key', $key)->first();
        if ($legacy) {
            return $legacy->value;   // accessor handles decrypt + cast
        }

        return $default;
    }

    /**
     * Persist a single key. Mirrors to central_settings if a matching row
     * exists (dual-write).
     *
     * @param  array{group?: string, label?: ?string, is_encrypted?: bool, cast?: ?string} $meta
     */
    public function set(string $key, mixed $value, array $meta = []): SystemSetting
    {
        $row = SystemSetting::set($key, $value, $meta);

        // Mirror to central_settings only if a row already exists there —
        // we don't want to leak the new bridge into the legacy table on
        // every key.
        $legacy = CentralSetting::query()->where('key', $key)->first();
        if ($legacy) {
            $legacy->is_encrypted = $row->is_encrypted;
            if (isset($meta['cast']))  { $legacy->cast  = $meta['cast']; }
            if (isset($meta['group'])) { $legacy->group = $meta['group']; }
            $legacy->value = $value;
            $legacy->save();
        }

        return $row;
    }

    /**
     * Persist a batch of key/value pairs that share the same group + meta.
     * Used by the Super Admin Settings tab forms.
     *
     * @param  array<string, mixed> $values
     */
    public function setMany(array $values, string $group, array $perKeyMeta = []): void
    {
        foreach ($values as $key => $value) {
            $meta = $perKeyMeta[$key] ?? [];
            $meta['group'] = $meta['group'] ?? $group;

            // Sensitive keys auto-flagged regardless of caller intent.
            if (in_array($key, self::SENSITIVE_KEYS, true)) {
                $meta['is_encrypted'] = true;
            }

            $this->set($key, $value, $meta);
        }
    }

    /**
     * Returns a redacted preview for sensitive keys: empty string when the
     * value is null/empty, otherwise a fixed-width dotted mask. Used by
     * the Settings UI to indicate "a secret is configured" without ever
     * sending the plaintext to the browser.
     */
    public function maskedValue(string $key): ?string
    {
        $raw = $this->get($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        if (in_array($key, self::SENSITIVE_KEYS, true)) {
            return '••••••••';
        }

        return is_string($raw) ? $raw : json_encode($raw);
    }

    /** @return array<int, string> */
    public static function sensitiveKeys(): array
    {
        return self::SENSITIVE_KEYS;
    }
}
