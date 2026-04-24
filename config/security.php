<?php

declare(strict_types=1);

/**
 * Phase 8 — browser security + rate-limiting configuration.
 *
 * Consumed by SecureHeadersMiddleware and the RateLimiter bindings
 * declared in RouteServiceProvider. Every value is env-overridable so
 * that staging / production can tighten without a code change.
 */
return [

    'headers' => [
        'x_frame_options'        => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection'       => env('SECURITY_X_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy'        => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy'     => env('SECURITY_PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=()'),

        // Intentionally null by default — CSP must be deliberately
        // authored per environment to avoid breaking production the
        // first time it's deployed.
        'content_security_policy'  => env('SECURITY_CSP', null),
        'strict_transport_security' => env('SECURITY_HSTS', 'max-age=31536000; includeSubDomains; preload'),
    ],

    'rate_limits' => [
        // Default API throttle (per authenticated user, or per IP for guests).
        'api' => [
            'max_attempts' => (int) env('RL_API_MAX', 60),
            'decay_minutes' => (int) env('RL_API_DECAY_MIN', 1),
        ],
        // Per-tenant API throttle — tighter to protect noisy tenants from
        // starving the queue for everyone else.
        'tenant_api' => [
            'max_attempts' => (int) env('RL_TENANT_API_MAX', 600),
            'decay_minutes' => (int) env('RL_TENANT_API_DECAY_MIN', 1),
        ],
        // Login brute-force guard. Applied by name to login endpoints.
        'login' => [
            'max_attempts' => (int) env('RL_LOGIN_MAX', 5),
            'decay_minutes' => (int) env('RL_LOGIN_DECAY_MIN', 15),
        ],
    ],

    // Thresholds for the slow-query logger registered in AppServiceProvider.
    'slow_query_ms' => (int) env('SLOW_QUERY_MS', 1000),

];
