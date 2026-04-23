<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Surfaces impersonation state to all views while a session is active.
 *
 * Shared with every Blade view:
 *   $isImpersonating : bool
 *   $impersonation   : array  (admin_id, tenant_id, user_id, started_at, …)
 *
 * Tenant layouts should render a banner + "Stop impersonating" button when
 * $isImpersonating is true.
 */
final class ImpersonationContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $data = session('impersonation');

        View::share('isImpersonating', (bool) $data);
        View::share('impersonation',   $data ?? []);

        return $next($request);
    }
}
