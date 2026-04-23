<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Hard gate. Apply to any route that MUST run under an EXPLICITLY resolved
 * tenant (never the fallback Default Tenant). Use on:
 *   - Tenant-specific API endpoints
 *   - Destructive operations that must never leak to Default
 *   - Routes meant for Phase 6+ once legacy /employees/* traffic migrates
 *
 * Reads hasTenant() (the strict check) — NOT current(), which would silently
 * succeed via fallback.
 */
final class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! TenantContext::hasTenant()) {
            throw new HttpException(
                statusCode: 403,
                message:    'This endpoint requires a resolved tenant (fallback is not allowed here).',
            );
        }

        return $next($request);
    }
}
