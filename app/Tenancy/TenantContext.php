<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Tenant;
use App\Tenancy\Events\TenantSwitched;
use RuntimeException;

/**
 * Global per-request registry of the currently-resolved tenant.
 *
 * Everything tenant-aware (BelongsToTenant trait, TenantScope, jobs, cache
 * keys, storage paths, logging) reads from here. Middleware writes to here
 * once per request; jobs/commands set it manually.
 */
final class TenantContext
{
    private static ?Tenant $current = null;
    private static bool $resolvedFromFallback = false;

    public static function set(Tenant $tenant): void
    {
        $previous       = self::$current;
        self::$current  = $tenant;
        self::$resolvedFromFallback = false;

        if (! $previous || $previous->getKey() !== $tenant->getKey()) {
            event(new TenantSwitched($tenant, $previous));
        }
    }

    public static function forget(): void
    {
        $previous      = self::$current;
        self::$current = null;
        self::$resolvedFromFallback = false;

        if ($previous) {
            event(new TenantSwitched(null, $previous));
        }
    }

    public static function current(): ?Tenant
    {
        if (self::$current !== null) {
            return self::$current;
        }

        if (config('tenancy.fallback_enabled', true)) {
            $tenant = self::resolveDefault();

            if ($tenant) {
                // Go through set() so TenantSwitched fires (Spatie Teams
                // bridge listens for this to call setPermissionsTeamId()).
                // Without the event, Spatie writes tenant_id=NULL into
                // model_has_roles and model_has_permissions — FK violation.
                self::set($tenant);
                self::$resolvedFromFallback = true;
            }
        }

        return self::$current;
    }

    public static function currentId(): ?int
    {
        $tenant = self::current();

        return $tenant?->getKey();
    }

    public static function hasTenant(): bool
    {
        return self::$current !== null;
    }

    public static function resolvedFromFallback(): bool
    {
        return self::$resolvedFromFallback;
    }

    /**
     * Run a callback with a different tenant in context, restoring previous after.
     * Safe for nested calls.
     */
    public static function runAs(Tenant $tenant, callable $callback): mixed
    {
        $previous = self::$current;

        try {
            self::set($tenant);

            return $callback($tenant);
        } finally {
            if ($previous) {
                self::set($previous);
            } else {
                self::forget();
            }
        }
    }

    public static function requireCurrent(): Tenant
    {
        $tenant = self::current();

        if (! $tenant) {
            throw new RuntimeException(
                'No tenant resolved. Ensure the request passed through InitializeTenantMiddleware '
                .'or set TENANCY_FALLBACK_ENABLED=true during the migration period.'
            );
        }

        return $tenant;
    }

    private static function resolveDefault(): ?Tenant
    {
        $slug = config('tenancy.default_tenant_slug', 'default');

        return Tenant::query()->where('slug', $slug)->first();
    }
}
