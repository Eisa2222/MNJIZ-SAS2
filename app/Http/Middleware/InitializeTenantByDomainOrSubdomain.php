<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Phase A — host-aware tenant initializer.
 *
 * Sister middleware to the existing `InitializeTenantMiddleware`
 * (`tenant.init`). Difference:
 *
 *   - InitializeTenantMiddleware: was the original Phase 2 entry point;
 *     resolves by path/header and pushes the tenant into context.
 *   - InitializeTenantByDomainOrSubdomain (this one): explicitly tries the
 *     HOST first (custom domain → subdomain) and ONLY then falls back to
 *     the path/header resolver. Designed to be the new default for the
 *     subdomain/custom-domain world while keeping the path-based legacy
 *     wholly intact.
 *
 * Both middleware delegate to the same `TenantResolver` so behavior stays
 * consistent. We keep two classes (instead of replacing the old one)
 * because Phase 2-9 routes/middleware are wired against `tenant.init`;
 * silently broadening their behavior would risk breaking 173 existing
 * isolation tests.
 *
 * Registered alias: `tenant.init.host`
 */
final class InitializeTenantByDomainOrSubdomain
{
    public function __construct(private TenantResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Central paths (admin, super-admin, webhooks, billing) bypass
        // tenant resolution entirely — same rule as Phase 2.
        if ($this->resolver->isCentralPath($request)) {
            return $next($request);
        }

        $tenant = $this->resolver->resolveFromRequest($request);

        if (! $tenant) {
            // Compat: fallback flag preserved (Phase 2 behavior).
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
        $this->applyUrlDefaults($tenant);

        return $next($request);
    }

    private function applyUrlDefaults(\App\Models\Tenant $tenant): void
    {
        URL::defaults(['tenant' => $tenant->slug]);
    }

    private function applyCachePrefix(\App\Models\Tenant $tenant): void
    {
        $format = config('tenancy.isolation.cache_prefix_format', 'tenant_%d_');
        $basePrefix = (string) config('cache.prefix', '');
        $tenantPrefix = sprintf($format, $tenant->getKey());

        if (str_contains($basePrefix, $tenantPrefix)) {
            return;
        }

        config(['cache.prefix' => $basePrefix . $tenantPrefix]);
    }
}
