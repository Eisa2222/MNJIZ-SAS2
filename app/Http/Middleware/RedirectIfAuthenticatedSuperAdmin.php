<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase B — sister of RedirectIfAuthenticatedAdmin for the new
 * `super_admin` guard.
 *
 * Used on /super-admin/login to bounce already-signed-in super admins
 * straight to the dashboard — same UX behaviour as the legacy /admin
 * surface, but on its own session bucket.
 */
final class RedirectIfAuthenticatedSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('super_admin')->check()) {
            return redirect()->route('super-admin.dashboard');
        }

        return $next($request);
    }
}
