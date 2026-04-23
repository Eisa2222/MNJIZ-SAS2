<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * Turns an incoming HTTP request into a Tenant instance.
 *
 * Resolution order (first non-null wins):
 *   1. Path segment     /t/{slug}/...
 *   2. Header           X-Tenant-Slug: {slug}
 *   3. Configured default (only if fallback is enabled — Phase 2/3 only)
 */
final class TenantResolver
{
    public function resolveFromRequest(Request $request): ?Tenant
    {
        $slug = $this->extractSlug($request);

        if ($slug !== null) {
            $tenant = Tenant::query()->where('slug', $slug)->first();

            return $tenant;
        }

        if (! config('tenancy.fallback_enabled', true)) {
            return null;
        }

        return Tenant::query()
            ->where('slug', config('tenancy.default_tenant_slug', 'default'))
            ->first();
    }

    public function extractSlug(Request $request): ?string
    {
        // 1. Path-based: /t/{slug}/... (also handled via route param {tenant})
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

        // 2. Header-based (for API calls that don't use URL paths)
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
}
