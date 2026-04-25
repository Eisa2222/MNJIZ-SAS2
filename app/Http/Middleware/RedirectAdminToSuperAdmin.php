<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase B — env-gated 301 from /admin/* to /super-admin/*.
 *
 * Default: DISABLED (`ADMIN_LEGACY_REDIRECT=false`). With the flag off,
 * /admin keeps responding normally — preserving every existing
 * controller, route name, bookmark and 173+ tests written for it.
 *
 * Flip to true only after operations confirm:
 *   - all admin users have logged into /super-admin at least once
 *   - external links / docs have been updated
 *   - no automated job is hitting /admin/api/* directly
 *
 * Wired by TenancyServiceProvider on the legacy /admin route group.
 */
final class RedirectAdminToSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('tenancy.admin_legacy_redirect', false)) {
            return $next($request);
        }

        $path = $request->path();

        // path() never starts with a leading slash; first segment is "admin".
        if (! str_starts_with($path, 'admin')) {
            return $next($request);
        }

        // Replace the first "admin" segment with "super-admin", preserve the rest.
        $newPath = 'super-admin' . substr($path, strlen('admin'));
        $query   = $request->getQueryString();
        $target  = '/' . $newPath . ($query ? '?' . $query : '');

        return redirect($target, 301);
    }
}
