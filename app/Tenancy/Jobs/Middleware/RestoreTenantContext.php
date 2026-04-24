<?php

declare(strict_types=1);

namespace App\Tenancy\Jobs\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantContext;

/**
 * Queue-job middleware that rehydrates TenantContext on a worker process.
 *
 * Lifecycle:
 *   1. Dispatcher in HTTP request: Job::__construct() → captureTenant()
 *      persists $this->tenantId into the serialized payload.
 *   2. Worker (different PHP process, empty context) pulls the payload,
 *      deserializes the Job. middleware() returns this class.
 *   3. Laravel invokes our handle(): we load the tenant, set context,
 *      call $next($job) (runs the job's handle()), and always clear
 *      context afterwards — even on failure.
 *
 * If tenantId is NULL (Job dispatched from central/admin context), we
 * still proceed but without context, letting the handle() decide what
 * "no tenant" means for that domain.
 */
final class RestoreTenantContext
{
    public function handle(object $job, \Closure $next): mixed
    {
        $tenantId = $job->tenantId ?? null;
        $tenant   = null;

        if ($tenantId) {
            $tenant = Tenant::query()->find($tenantId);
            if ($tenant) {
                TenantContext::set($tenant);
            }
        }

        try {
            return $next($job);
        } finally {
            TenantContext::forget();
        }
    }
}
