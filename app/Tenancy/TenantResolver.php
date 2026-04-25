<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * Turns an incoming HTTP request into a Tenant instance.
 *
 * Resolution order (first non-null wins):
 *   1. Custom domain (exact host match in `domains` table)
 *   2. Subdomain     ({slug}.{app_base_domain}) when subdomain mode is enabled
 *   3. Path segment  /t/{slug}/...
 *   4. Route binding via {tenant} parameter
 *   5. Header        X-Tenant-Slug: {slug}
 *   6. Configured default (only if fallback is enabled — compat layer)
 *
 * Phase A added cases 1 + 2 in front of the existing path/header chain.
 * Legacy `/t/{slug}` traffic continues to resolve unchanged.
 */
final class TenantResolver
{
    public function resolveFromRequest(Request $request): ?Tenant
    {
        // 1 + 2. Try host first — strongest signal when present.
        $tenant = $this->resolveByHost($request);
        if ($tenant) {
            return $tenant;
        }

        // 3-5. Existing slug-based chain (path / route param / header).
        $slug = $this->extractSlug($request);

        if ($slug !== null) {
            return Tenant::query()->where('slug', $slug)->first();
        }

        // 6. Default-tenant fallback (compat for /employees/* legacy URLs).
        if (! config('tenancy.fallback_enabled', true)) {
            return null;
        }

        return Tenant::query()
            ->where('slug', config('tenancy.default_tenant_slug', 'default'))
            ->first();
    }

    /**
     * Resolve a Tenant from the HTTP host. Tries:
     *   1. Exact host match in `domains` (custom domain or stored subdomain)
     *   2. Stripped subdomain match: `{slug}.{app_base_domain}` → tenants.slug = {slug}
     *
     * Returns null when neither matches OR when the host is configured as a
     * central domain (those should never resolve to a tenant).
     */
    public function resolveByHost(Request $request): ?Tenant
    {
        $host = Domain::normalize($request->getHost());

        if ($host === '' || $this->isCentralHost($host)) {
            return null;
        }

        // (1) Exact match — covers both verified custom domains and stored
        //     subdomain rows (e.g. acme.mnjiz.sa inserted at signup time).
        if (config('tenancy.identification.custom_domain.enabled', true)) {
            $tenantId = Domain::query()->where('domain', $host)->value('tenant_id');
            if ($tenantId) {
                $tenant = Tenant::query()->find($tenantId);
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        // (2) Subdomain pattern — `{slug}.{app_base_domain}`.
        if (config('tenancy.identification.subdomain.enabled', true)) {
            $slug = $this->extractSubdomainSlug($host);
            if ($slug !== null) {
                $tenant = Tenant::query()->where('slug', $slug)->first();
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        return null;
    }

    /**
     * Pull `acme` out of `acme.mnjiz.sa`. Returns null when the host does
     * not end in the configured base domain or has no subdomain segment.
     */
    public function extractSubdomainSlug(string $host): ?string
    {
        $base = (string) config('tenancy.identification.subdomain.app_base_domain', '');
        if ($base === '') {
            return null;
        }
        $base = Domain::normalize($base);
        $host = Domain::normalize($host);

        if ($host === $base) {
            return null;   // bare base domain — not a tenant subdomain
        }

        $suffix = '.' . $base;
        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $slug = substr($host, 0, -strlen($suffix));

        // Disallow nested subdomains (e.g. `www.acme.mnjiz.sa` — only the
        // single-segment `acme` counts in this model). Returning null lets
        // the next resolver in the chain try.
        if ($slug === '' || str_contains($slug, '.')) {
            return null;
        }

        return $slug;
    }

    public function extractSlug(Request $request): ?string
    {
        // Path-based: /t/{slug}/... (also handled via route param {tenant})
        $routeParam = $request->route('tenant');
        if ($routeParam instanceof Tenant) {
            return $routeParam->slug;
        }
        if (is_string($routeParam) && $routeParam !== '') {
            return $routeParam;
        }

        $prefix = config('tenancy.identification.path.prefix', 't');
        $segments = $request->segments();
        if (count($segments) >= 2 && $segments[0] === $prefix) {
            return $segments[1];
        }

        // Header-based (for API calls that don't use URL paths)
        $header = $request->header(config('tenancy.identification.header', 'X-Tenant-Slug'));
        if (is_string($header) && $header !== '') {
            return $header;
        }

        return null;
    }

    public function isCentralPath(Request $request): bool
    {
        $centralPaths = (array) config('tenancy.central_paths', []);
        $first = $request->segments()[0] ?? '';

        return in_array($first, $centralPaths, true);
    }

    /**
     * Phase A — host-level central guard. Any host listed in
     * config('tenancy.central_domains') NEVER resolves to a tenant, even if
     * a `domains` row accidentally points at one.
     */
    public function isCentralHost(string $host): bool
    {
        $host = Domain::normalize($host);
        $list = array_map(
            [Domain::class, 'normalize'],
            (array) config('tenancy.central_domains', [])
        );
        return in_array($host, $list, true);
    }
}
