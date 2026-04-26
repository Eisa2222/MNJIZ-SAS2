<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hotfix — fixes post-login tenant binding for the multi-tenant flow.
 *
 *  Problem (root cause)
 *  --------------------
 *  Tenant-aware models (User, Subscription, …) carry the
 *  `BelongsToTenant` global scope. The scope reads the *current*
 *  tenant from `TenantContext`. The Phase A/Phase 2 resolver picks
 *  that tenant from the URL — but the bulk of the legacy lawyer UI
 *  still lives at `/employees/*` (which has NO tenant slug in the
 *  path) so the resolver always falls back to the default tenant
 *  (id=1). A user whose `tenant_id != 1` would log in and then see
 *  the DEFAULT tenant's data — or be blocked by the global scope on
 *  every query.
 *
 *  Fix (this middleware)
 *  ---------------------
 *  After authentication, this middleware verifies that the resolved
 *  tenant context matches the authenticated user's `tenant_id`. If
 *  it doesn't:
 *
 *    • For URL-scoped tenant routes (`/t/{slug}/*`, host-based) we
 *      KEEP the URL-resolved tenant and let the existing
 *      authorisation layers reject mismatches (404 / 403 / scope).
 *      We do NOT silently override URL-driven scoping — that would
 *      be a data-leak vector.
 *
 *    • For path-less routes (`/employees/*`, `/onboarding/*`,
 *      `/dashboard`) we override the fallback default tenant with
 *      the user's actual tenant. The URL didn't specify a tenant,
 *      so the user's own `tenant_id` is the correct source of truth.
 *
 *  Skip rules
 *  ----------
 *    • No authenticated user → no-op (resolver fallback governs).
 *    • Authenticated as `admin` / `super_admin` guard → no-op
 *      (admins are platform-wide; their tenant context is set by
 *      whichever tenant they're inspecting via the admin panel).
 *    • User has no `tenant_id` → no-op (legacy data; defensive).
 *    • URL is path-scoped (`/t/...`) or admin / central / health →
 *      no-op (URL is authoritative).
 *
 *  Logging
 *  -------
 *  None. This middleware is on the hot path of every web request;
 *  silent operation is intentional. Failures (e.g. user has a
 *  tenant_id pointing to a deleted tenant) become a no-op so the
 *  resolver fallback continues to govern — never an HTTP 500.
 *
 *  Cancellation
 *  ------------
 *  Removing this middleware from the `web` group restores the
 *  pre-hotfix behaviour. Nothing else depends on it.
 */
final class ResolveTenantFromAuthenticatedUser
{
    /**
     * URL-prefix patterns where the URL itself is authoritative for
     * tenant resolution and we MUST NOT override based on user.
     *
     * @var array<int, string>
     */
    private const URL_AUTHORITATIVE_PREFIXES = [
        't/',           // /t/{slug}/* — Phase 2 path-based tenant
        'admin/',       // /admin/* — central admin panel
        'super-admin/', // /super-admin/* — Phase B central panel
        'webhooks/',    // gateway callbacks
        'health',       // load-balancer probes
        'checkout/',    // public-checkout flow
        'password/',    // forgot/reset/setup links
        'register',     // signup
        'login',        // login screen
        'logout',       // logout
        'pricing',      // marketing
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip when not authenticated under the web (tenant-user) guard.
        // We don't touch admin/super_admin guards — those are platform-wide.
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // Web-guard tenant users carry `tenant_id`. Admin / SuperAdmin
        // models inherit BelongsToTenant=NO and don't carry the column.
        if (! property_exists($user, 'tenant_id') && ! isset($user->tenant_id)) {
            return $next($request);
        }

        $userTenantId = (int) ($user->tenant_id ?? 0);
        if ($userTenantId === 0) {
            return $next($request);
        }

        // Don't override when URL itself is authoritative for tenant.
        if ($this->urlIsAuthoritative($request)) {
            return $next($request);
        }

        $current = TenantContext::current();
        if ($current && (int) $current->id === $userTenantId) {
            return $next($request);   // already correct
        }

        $tenant = Tenant::find($userTenantId);
        if ($tenant) {
            TenantContext::set($tenant);
        }

        return $next($request);
    }

    private function urlIsAuthoritative(Request $request): bool
    {
        $path = ltrim($request->path(), '/');

        foreach (self::URL_AUTHORITATIVE_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix) || $path === rtrim($prefix, '/')) {
                return true;
            }
        }

        return false;
    }
}
