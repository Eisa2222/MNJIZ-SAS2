<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * Phase C — runtime application of central system_settings to Laravel's
 * Config bag.
 *
 * Mounted on the central web group (admin + super-admin + landing). Pulls
 * every key from system_settings (already cached + decrypted by the
 * `SystemSetting::allAsKeyValue()` static API) and overrides
 * `config('mail.*')` and `config('services.moyasar.*')` for the lifetime
 * of the request — so a Super Admin saving an SMTP password in the UI
 * takes effect on the very next outgoing email without a redeploy.
 *
 * Tenant routes are NOT touched — per-tenant overrides live in
 * `tenant_settings` and are applied through SettingsRepository (Phase 5).
 *
 * Failure mode: if `system_settings` table is missing (fresh install,
 * migrations not run) or the cache is misconfigured, this middleware
 * silently passes through. Boot-time stability is non-negotiable.
 */
final class ApplySystemSettings
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $map = SystemSetting::allAsKeyValue();
        } catch (\Throwable $e) {
            // Table missing or driver unreachable — never block the request.
            return $next($request);
        }

        if (! empty($map)) {
            $this->applyMail($map);
            $this->applyMoyasar($map);
        }

        return $next($request);
    }

    /**
     * Map Super-Admin Mail tab → Laravel mail config keys. Each `Config::set`
     * call only fires when a non-empty value is configured, so the existing
     * env-driven defaults remain in place when the operator hasn't filled
     * the field.
     *
     * @param  array<string, mixed> $map
     */
    private function applyMail(array $map): void
    {
        $mailMap = [
            'mail_driver'        => 'mail.default',
            'mail_host'          => 'mail.mailers.smtp.host',
            'mail_port'          => 'mail.mailers.smtp.port',
            'mail_username'      => 'mail.mailers.smtp.username',
            'mail_password'      => 'mail.mailers.smtp.password',
            'mail_encryption'    => 'mail.mailers.smtp.encryption',
            'mail_from_address'  => 'mail.from.address',
            'mail_from_name'     => 'mail.from.name',
        ];

        foreach ($mailMap as $sysKey => $configKey) {
            $value = $map[$sysKey] ?? null;
            if ($value !== null && $value !== '') {
                // mail_port must be an int — Laravel's SMTP transport rejects
                // string ports in some PHP/Symfony combos. setMany() preserves
                // the storage cast which may round-trip as string for missing
                // metadata, so we coerce defensively here.
                if ($sysKey === 'mail_port') {
                    $value = (int) $value;
                }
                Config::set($configKey, $value);
            }
        }
    }

    /**
     * @param  array<string, mixed> $map
     */
    private function applyMoyasar(array $map): void
    {
        $moyasarMap = [
            'moyasar_publishable_key' => 'services.moyasar.publishable_key',
            'moyasar_secret_key'      => 'services.moyasar.secret_key',
            'moyasar_webhook_secret'  => 'services.moyasar.webhook_secret',
            'moyasar_test_mode'       => 'services.moyasar.test_mode',
            'moyasar_enabled_methods' => 'services.moyasar.enabled_methods',
        ];

        foreach ($moyasarMap as $sysKey => $configKey) {
            $value = $map[$sysKey] ?? null;
            if ($value !== null && $value !== '') {
                Config::set($configKey, $value);
            }
        }
    }
}
