<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Apply the full set of recommended browser security headers to every
 * response.
 *
 * Each header is configurable via `config('security.headers.*')` so that
 * dev environments (where strict CSP would break hot-reload tooling) can
 * relax individual headers without forking the middleware.
 *
 * Defaults target the production profile — the configuration layer is
 * the gate, not a default "allow-all".
 */
final class SecureHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ($this->headers() as $name => $value) {
            if ($value === null || $value === false || $value === '') {
                continue;
            }
            // Never clobber an explicit header set further down the stack
            // (e.g. a controller that deliberately customized CSP).
            if (! $response->headers->has($name)) {
                $response->headers->set($name, (string) $value);
            }
        }

        return $response;
    }

    /** @return array<string, string|null> */
    private function headers(): array
    {
        $cfg = config('security.headers', []);
        $isSecure = request()?->isSecure() ?? false;

        return [
            // Clickjacking protection.
            'X-Frame-Options'         => $cfg['x_frame_options']         ?? 'SAMEORIGIN',
            // MIME-sniffing guard.
            'X-Content-Type-Options'  => $cfg['x_content_type_options']  ?? 'nosniff',
            // Legacy XSS filter.
            'X-XSS-Protection'        => $cfg['x_xss_protection']        ?? '1; mode=block',
            // Modern privacy / referrer control.
            'Referrer-Policy'         => $cfg['referrer_policy']         ?? 'strict-origin-when-cross-origin',
            // Disable the most commonly-abused browser APIs site-wide.
            'Permissions-Policy'      => $cfg['permissions_policy']      ?? 'geolocation=(), microphone=(), camera=()',
            // CSP — project-specific allowlist. Nullable so localhost
            // development can opt out via config until policies settle.
            'Content-Security-Policy' => $cfg['content_security_policy'] ?? null,
            // HSTS only over HTTPS. One year + preload + subdomains.
            'Strict-Transport-Security' => $isSecure
                ? ($cfg['strict_transport_security'] ?? 'max-age=31536000; includeSubDomains; preload')
                : null,
        ];
    }
}
