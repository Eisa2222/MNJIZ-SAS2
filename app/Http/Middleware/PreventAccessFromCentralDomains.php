<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Phase A — host-level isolation guard.
 *
 * Apply this to any tenant route group that should NEVER be reachable from
 * a central domain (e.g. `mnjiz.sa` itself, the marketing landing host,
 * the super-admin host). When the request's HTTP host matches one of
 * `config('tenancy.central_domains')`, we 404 immediately — preventing a
 * tenant URL leak via the public domain.
 *
 * Symmetric to stancl/tenancy's middleware of the same name.
 *
 * Registered alias: `tenant.prevent.central`
 */
final class PreventAccessFromCentralDomains
{
    public function __construct(private TenantResolver $resolver) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->resolver->isCentralHost($request->getHost())) {
            throw new NotFoundHttpException(
                'Tenant routes are not reachable from a central domain.'
            );
        }

        return $next($request);
    }
}
