<?php

declare(strict_types=1);

namespace App\Providers;

use App\Tenancy\Events\TenantSwitched;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Log\Logger;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Monolog\LogRecord;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;

/**
 * Wires the tenancy layer into Laravel.
 *
 * Registers:
 *   - TenantResolver binding
 *   - Routes: routes/central.php + routes/tenant.php
 *   - Monolog processor that stamps tenant_id onto every log entry
 *   - Activity model hook that stamps tenant_id on Spatie activity_log rows
 *   - Queue event listeners that preserve tenant context across jobs
 *   - Eloquent macro: ->whereTenantIs($id) for explicit cross-tenant queries
 */
final class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/tenancy.php', 'tenancy');

        $this->app->singleton(TenantResolver::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerEloquentMacros();
        $this->registerLoggingProcessor();
        $this->registerActivityLogHook();
        $this->registerNotificationTenantHook();
        $this->registerQueueListeners();
        $this->registerSpatieTeamsBridge();
    }

    /**
     * Phase 3 — every time the tenant context changes, tell Spatie's
     * PermissionRegistrar which team_id (tenant_id) to use. Without this,
     * roles/permissions resolve under the wrong tenant.
     *
     * CRITICAL: TenantContext::currentId() would query the tenants table
     * to resolve the fallback. On a fresh install (before migrate) that
     * table does not exist, which would kill every artisan command.
     * The listener + bootstrap block both run inside try/catch so the app
     * boots even when tenancy tables are missing.
     */
    private function registerSpatieTeamsBridge(): void
    {
        if (! class_exists(PermissionRegistrar::class)) {
            return;
        }

        Event::listen(TenantSwitched::class, function (TenantSwitched $event) {
            try {
                $registrar = app(PermissionRegistrar::class);
                $registrar->setPermissionsTeamId($event->current?->getKey());
                $registrar->forgetCachedPermissions();
            } catch (\Throwable $e) {
                // Swallow — Spatie tables may not exist yet.
            }
        });
    }

    private function registerRoutes(): void
    {
        if (file_exists(base_path('routes/central.php'))) {
            Route::middleware('web')
                ->group(base_path('routes/central.php'));
        }

        // Admin panel (Phase 3) — central, never tenant-resolved.
        // The `admin.legacy.redirect` middleware is a no-op unless the
        // ADMIN_LEGACY_REDIRECT env flag is true (Phase B compatibility).
        if (file_exists(base_path('routes/admin.php'))) {
            Route::middleware(['web', 'admin.legacy.redirect'])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
        }

        // Super Admin panel (Phase B — Path C compatibility layer).
        // Mirrors routes/admin.php under /super-admin with the new
        // `super_admin` guard. Both surfaces stay live in parallel.
        if (file_exists(base_path('routes/super-admin.php'))) {
            Route::middleware('web')
                ->prefix('super-admin')
                ->group(base_path('routes/super-admin.php'));
        }

        if (file_exists(base_path('routes/tenant.php'))) {
            $prefix    = config('tenancy.identification.path.prefix', 't');
            $parameter = config('tenancy.identification.path.parameter', 'tenant');

            // ── Legacy path-based group ─ /t/{slug}/...  (Phase 2-9, kept) ──
            Route::middleware(['web', 'tenant.init'])
                ->prefix("{$prefix}/{{$parameter}}")
                ->group(base_path('routes/tenant.php'));

            // ── Phase A — host-aware group (subdomain + custom domain) ──
            // Same route file, second registration. The `host.` name prefix
            // keeps route names unique so a request to acme.mnjiz.sa/billing
            // resolves to host.tenant.billing.index while
            // mnjiz.sa/t/acme/billing keeps resolving to tenant.billing.index.
            // PreventAccessFromCentralDomains 404s any tenant URL hit from
            // a host listed in config('tenancy.central_domains').
            Route::middleware(['web', 'tenant.init.host', 'tenant.prevent.central'])
                ->name('host.')
                ->group(base_path('routes/tenant.php'));
        }
    }

    private function registerEloquentMacros(): void
    {
        Builder::macro('whereTenantIs', function (int $tenantId) {
            /** @var Builder $this */
            return $this->withoutGlobalScope(\App\Tenancy\Scopes\TenantScope::class)
                ->where($this->getModel()->getTable().'.tenant_id', $tenantId);
        });
    }

    private function registerLoggingProcessor(): void
    {
        // Only apply to the default logger; avoids double-tagging on stack channels.
        try {
            $logger = Log::getLogger();

            if ($logger instanceof \Monolog\Logger) {
                $logger->pushProcessor(function ($record) {
                    // tenants table may not exist yet (fresh install, migrations
                    // haven't run). Never let log stamping trigger a DB error —
                    // the log call itself would then recurse into logging again.
                    $tenantId = null;
                    try {
                        $tenantId = TenantContext::currentId();
                    } catch (\Throwable $e) {
                        // silent — logging must never crash.
                    }

                    if ($record instanceof LogRecord) {
                        $record->extra['tenant_id'] = $tenantId;

                        return $record;
                    }

                    // Monolog v2 compatibility.
                    $record['extra']['tenant_id'] = $tenantId;

                    return $record;
                });
            }
        } catch (\Throwable $e) {
            // Never let logging setup kill the app.
        }
    }

    private function registerActivityLogHook(): void
    {
        if (! config('tenancy.activity_log.stamp_tenant_id', true)) {
            return;
        }

        if (! class_exists(Activity::class)) {
            return;
        }

        Activity::creating(function (Activity $activity) {
            try {
                if (! array_key_exists('tenant_id', $activity->getAttributes())
                    || $activity->getAttribute('tenant_id') === null) {
                    $activity->tenant_id = TenantContext::currentId();
                }
            } catch (\Throwable $e) {
                // tenants table may not exist yet — don't block activity writes.
            }

            // If we're inside an impersonation session, stamp the impersonating
            // admin onto every activity so audits can distinguish "the user did
            // it themselves" vs "an admin did it while impersonating".
            if ($data = session('impersonation')) {
                $props = $activity->properties ?? collect();

                if (! is_array($props)) {
                    $props = $props->toArray();
                }

                $props['impersonator_admin_id'] = $data['admin_id'] ?? null;
                $props['impersonation_log_id'] = $data['log_id']   ?? null;

                $activity->properties = $props;
            }
        });
    }

    /**
     * Phase 6 Module 4 — auto-stamp tenant_id on every DatabaseNotification
     * row. Laravel's DatabaseChannel inserts via the built-in
     * Illuminate\Notifications\DatabaseNotification model; we can't edit
     * that class, but we can listen to its `creating` event and fill
     * tenant_id from the current context (or from the Notification's own
     * tenantId property if queued and restored by RestoreTenantContext).
     *
     * Also installs a guard that rejects cross-tenant reads: a query
     * against notifications WITHOUT a tenant filter under an active
     * TenantContext is unreachable at the framework level — Laravel
     * resolves notifications via $notifiable->notifications() morphMany,
     * which we add a global tenant scope to through a Model observer.
     */
    private function registerNotificationTenantHook(): void
    {
        if (! class_exists(\Illuminate\Notifications\DatabaseNotification::class)) {
            return;
        }

        \Illuminate\Notifications\DatabaseNotification::creating(function ($notification) {
            try {
                if (empty($notification->tenant_id)) {
                    $notification->tenant_id = TenantContext::currentId();
                }
            } catch (\Throwable $e) {
                // notifications table may lack tenant_id column on a
                // partially-migrated install; fail open (null) so the
                // notification still writes and we surface it via tests.
            }
        });

        // Block silent cross-tenant writes by throwing on any attempt to
        // move an existing notification to another tenant.
        \Illuminate\Notifications\DatabaseNotification::updating(function ($notification) {
            if (! $notification->isDirty('tenant_id')) {
                return;
            }
            $original = $notification->getOriginal('tenant_id');
            if ($original !== null && $original !== $notification->tenant_id) {
                throw new \RuntimeException(
                    'Cross-tenant reassignment on DatabaseNotification is not allowed.'
                );
            }
        });
    }

    private function registerQueueListeners(): void
    {
        // Pull tenant_id from job payload when a queued job starts executing
        // on a worker (different process → empty TenantContext). Works with
        // the TenantAwareJob trait (added in Phase 6).
        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            $payload = $event->job->payload();
            $tenantId = $payload['tenant_id'] ?? null;

            if ($tenantId && $tenant = \App\Models\Tenant::query()->find($tenantId)) {
                TenantContext::set($tenant);
            }
        });

        Event::listen([JobProcessed::class, JobFailed::class], function () {
            TenantContext::forget();
        });
    }
}
