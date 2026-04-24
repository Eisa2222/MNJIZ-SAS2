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
        $tenantId = $this->resolveTenantId($job);
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

    /**
     * Resolve tenantId from whatever job shape was handed to us.
     *
     * Cases:
     *   1. Plain Job using TenantAwareJob → $job->tenantId
     *   2. Queued Notification wrapper (SendQueuedNotifications) →
     *      $job->notification->tenantId (set by Notification constructor)
     */
    private function resolveTenantId(object $job): ?int
    {
        // Case 1: direct Job (has TenantAwareJob trait).
        if (property_exists($job, 'tenantId') && $job->tenantId !== null) {
            return (int) $job->tenantId;
        }

        // Case 2: Laravel wraps queued notifications in SendQueuedNotifications.
        if (isset($job->notification) && is_object($job->notification)
            && property_exists($job->notification, 'tenantId')
            && $job->notification->tenantId !== null) {
            return (int) $job->notification->tenantId;
        }

        return null;
    }
}
