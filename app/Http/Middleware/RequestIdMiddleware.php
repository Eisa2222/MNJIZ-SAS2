<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Attach a unique request id to every request and echo it back in the
 * `X-Request-Id` response header. Also seeds a Log context scope
 * (tenant_id + user_id + request_id) so every log line in the request
 * lifecycle carries the full tenant/user/correlation breadcrumb — the
 * single most important production-debugging primitive.
 *
 * Downstream code can read the id via `request()->attributes->get('request_id')`.
 *
 * Incoming requests MAY supply their own `X-Request-Id` (useful when a
 * reverse proxy or load balancer already attaches one). In that case we
 * reuse it — enabling trace correlation across layers.
 */
final class RequestIdMiddleware
{
    /** UUIDv4 format; we accept this shape only for safety. */
    private const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public function handle(Request $request, Closure $next): Response
    {
        $incoming = (string) $request->headers->get('X-Request-Id', '');
        $requestId = preg_match(self::UUID_REGEX, $incoming) === 1
            ? $incoming
            : (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);

        // Seed log context. Every Log::info(), Log::error() etc. in this
        // request now gets tenant_id / user_id / request_id automatically.
        Log::withContext([
            'request_id' => $requestId,
            'tenant_id'  => TenantContext::currentId(),
            'user_id'    => optional($request->user())->getAuthIdentifier(),
            'path'       => $request->path(),
            'method'     => $request->method(),
        ]);

        $response = $next($request);

        // Emit the id so clients / proxies / log-correlation systems can tie
        // server-side logs back to this HTTP request.
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
