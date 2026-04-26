<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Billing\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase H — gates tenant routes by current-subscription state.
 *
 *   ALLOW (next $request):
 *     • status = Trialing
 *     • status = Active
 *
 *   REDIRECT:
 *     • status = PastDue            → tenant.billing.index (pay outstanding)
 *     • status = Expired/Canceled/Paused → marketing.pricing (upgrade)
 *     • no subscription at all      → marketing.pricing (pick a plan)
 *
 *   RENDER (HTTP 403, never 500):
 *     • tenant.status = suspended   → tenant.suspended view
 *
 * Pass-through (never gates):
 *   • Anything where TenantContext::current() is null (= central routes)
 *   • Routes that the user must be able to reach to FIX the situation:
 *     billing portal, checkout, password setup, login/logout, webhooks,
 *     health, the suspended page itself.
 *
 * This middleware ALWAYS runs AFTER Phase 5's `InitializeTenantMiddleware`
 * — which already throws 403 for suspended tenants in the canonical
 * tenant routing path. CheckSubscription's suspended branch is
 * defence-in-depth for paths that bypass that middleware (e.g. a future
 * console-triggered request).
 *
 * Never aborts with 500. Every code path returns either next($request)
 * or an explicit redirect / view response.
 */
final class CheckSubscription
{
    /**
     * Routes that MUST stay reachable regardless of subscription state.
     * Match against `$request->routeIs(...)` patterns.
     */
    private const EXEMPT_ROUTE_PATTERNS = [
        // The billing portal itself — past-due / no-sub users land here.
        'tenant.billing.*',
        'host.tenant.billing.*',
        'admin.subscriptions.*',

        // The page we redirect TO when a tenant is suspended.
        'tenant.suspended',
        'host.tenant.suspended',

        // Public checkout flow — paying their way back to an Active sub.
        'checkout.*',

        // Phase F password-setup link — pre-auth.
        'tenant.password.setup',
        'tenant.password.setup.store',

        // Auth flows.
        'login', 'logout',
        'admin.login', 'admin.login.attempt', 'admin.logout',
        'super-admin.login', 'super-admin.login.attempt', 'super-admin.logout',

        // Webhooks (gateway / external systems).
        'webhooks.*',

        // Health checks.
        'health.*',

        // Marketing surface (the upgrade target).
        'marketing.*',
        'register', 'register.store',
        'tenant.ping', 'host.tenant.ping',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Central / no-tenant request — never gate.
        $tenant = TenantContext::current();
        if (! $tenant instanceof Tenant) {
            return $next($request);
        }

        // 2. Exempt routes — billing/checkout/auth/etc.
        if ($this->isExempt($request)) {
            return $next($request);
        }

        // 3. Suspended tenant — render the dedicated suspended view (403).
        //    Phase 5's InitializeTenantMiddleware fires first in the
        //    normal request lifecycle, returning a plain 403 — but this
        //    branch survives any future path that bypasses that
        //    middleware (e.g. a tenant context set programmatically).
        if (! $tenant->isActive()) {
            return $this->renderSuspended($tenant);
        }

        // 4. Look up the most-recent subscription for this tenant.
        //    `withoutTenancy()` because the BelongsToTenant scope on
        //    Subscription would normally restrict to the resolved tenant
        //    anyway — but we want explicit, scope-independent semantics.
        $subscription = Subscription::query()->withoutTenancy()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('id')
            ->first();

        // 5. No subscription → send the operator to the pricing page.
        if (! $subscription) {
            return $this->redirectToUpgrade($request, 'no_subscription');
        }

        // 6. Branch on status. Active + Trialing pass through; everything
        //    else routes the operator somewhere actionable.
        //
        // Phase H+ collaborative-audit fix: added `default` arm so a future
        // SubscriptionStatus case added without updating this middleware
        // can never produce an UnhandledMatchError (HTTP 500). The default
        // falls back to the safest visible state — the upgrade page —
        // because an unrecognised state is, by definition, not entitling.
        return match ($subscription->status) {
            SubscriptionStatus::Active, SubscriptionStatus::Trialing
                => $next($request),

            SubscriptionStatus::PastDue
                => $this->redirectToBillingPortal($request, 'past_due'),

            SubscriptionStatus::Expired,
            SubscriptionStatus::Canceled,
            SubscriptionStatus::Paused
                => $this->redirectToUpgrade($request, 'expired'),

            default
                => $this->redirectToUpgrade($request, 'expired'),
        };
    }

    private function isExempt(Request $request): bool
    {
        foreach (self::EXEMPT_ROUTE_PATTERNS as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        // Also exempt by URL prefix as a belt-and-braces guard for any
        // future controller-less routes that don't have route names.
        $path = '/'.ltrim($request->path(), '/');
        $prefixes = ['/checkout/', '/password/setup', '/webhooks/', '/health'];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function renderSuspended(Tenant $tenant): Response
    {
        return response()->view('tenant.suspended', [
            'tenant'        => $tenant,
            'support_email' => (string) (\App\Models\SystemSetting::get('support_email') ?? 'support@mnjiz.sa'),
            'app_name'      => (string) (\App\Models\SystemSetting::get('app_name')      ?? 'MNJIZ'),
        ], 403);
    }

    private function redirectToBillingPortal(Request $request, string $reason): RedirectResponse
    {
        $tenantSlug = TenantContext::current()?->slug ?? '';
        $url = route('tenant.billing.index', ['tenant' => $tenantSlug]);

        return redirect()->guest($url)
            ->with('subscription_block', __("subscription.{$reason}"));
    }

    private function redirectToUpgrade(Request $request, string $reason): RedirectResponse
    {
        // The marketing pricing page is the de-facto "plans" surface.
        // We pass the reason through the session flash so the page can
        // render a context-aware banner (Phase H+ enhancement).
        return redirect()->guest(route('marketing.pricing'))
            ->with('subscription_block', __("subscription.{$reason}"));
    }
}
