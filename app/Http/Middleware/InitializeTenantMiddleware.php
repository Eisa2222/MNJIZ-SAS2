<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Reads the tenant slug from the request (path param, segment, header, or
 * fallback), looks it up, validates status, and pushes it into the global
 * TenantContext for the duration of the request.
 *
 * Registered under the alias `tenant.init` — applied to the `tenant` route
 * group and (during Phase 2) also to the legacy `web` group as a fallback
 * resolver so existing /employees/* URLs keep working.
 */
final class InitializeTenantMiddleware
{
    public function __construct(private TenantResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->resolver->isCentralPath($request)) {
            return $next($request);
        }

        $tenant = $this->resolver->resolveFromRequest($request);

        if (! $tenant) {
            if (config('tenancy.fallback_enabled', true)) {
                return $next($request);
            }

            throw new NotFoundHttpException('Unknown tenant.');
        }

        if (! $tenant->isActive()) {
            throw new HttpException(403, "Tenant '{$tenant->slug}' is suspended.");
        }

        TenantContext::set($tenant);

        $this->applyCachePrefix($tenant);

        return $next($request);
    }

    private function applyCachePrefix(Tenant $tenant): void
    {
        $format = config('tenancy.isolation.cache_prefix_format', 'tenant_%d_');
        $basePrefix = (string) config('cache.prefix', '');
        $tenantPrefix = sprintf($format, $tenant->getKey());

        // Bypass double-prefix on re-entry (nested middleware or internal subrequests).
        if (str_contains($basePrefix, $tenantPrefix)) {
            return;
        }

        config(['cache.prefix' => $basePrefix.$tenantPrefix]);
    }
}
