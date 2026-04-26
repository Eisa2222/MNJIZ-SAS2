<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;
use Symfony\Component\HttpFoundation\Response;

/**
 * V2 — CheckSubscription middleware. Spec lines 206-210.
 *
 * Runs INSIDE tenant context (after InitializeTenancyByDomainOrSubdomain).
 * Reads the central subscriptions table for the current tenant and:
 *   - tenant.status = 'suspended'  → render errors.suspended (HTTP 403)
 *   - subscription expired         → render errors.subscription-expired
 *   - no subscription              → render errors.subscription-expired
 *   - active / trialing            → next($request)
 */
final class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();   // resolved by tenancy middleware
        if (! $tenant) {
            return $next($request);
        }

        if ($tenant->status === 'suspended') {
            return response()->view('errors.suspended', ['tenant' => $tenant], 403);
        }

        // Subscription lives in CENTRAL DB, so we have to query without
        // tenancy context. Easiest: end tenancy briefly for this query.
        $sub = Tenancy::central(fn () =>
            Subscription::where('tenant_id', $tenant->id)
                ->whereIn('status', ['active', 'trialing'])
                ->latest('id')
                ->first()
        );

        if (! $sub || $sub->isExpired()) {
            return response()->view('errors.subscription-expired', [
                'tenant'       => $tenant,
                'subscription' => $sub,
            ], 402);
        }

        return $next($request);
    }
}
