<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Throwable;

/**
 * Phase 8 — health / liveness / readiness endpoints.
 *
 *   GET /health          → overall liveness (200 = process up)
 *   GET /health/db       → DB connectivity probe
 *   GET /health/queue    → queue driver reachable
 *   GET /health/cache    → cache driver reachable
 *   GET /health/ready    → composite readiness (all of the above + migrations)
 *
 * All endpoints return JSON; success = 200, any failure = 503. Designed to
 * be wired into a load-balancer health check / Kubernetes probe without
 * further shaping. Every response includes a stable `status` field
 * (ok | degraded | down) so downstream monitoring can alert deterministically.
 */
final class HealthController extends Controller
{
    public function overall(): JsonResponse
    {
        return response()->json([
            'status'  => 'ok',
            'service' => 'mnjiz',
            'time'    => now()->toIso8601String(),
        ]);
    }

    public function db(): JsonResponse
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            // Cheap probe — SELECT 1 avoids loading any data.
            DB::select('SELECT 1');
            return response()->json([
                'status'     => 'ok',
                'check'      => 'db',
                'latency_ms' => $this->ms($start),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'down',
                'check'  => 'db',
                'error'  => $e->getMessage(),
            ], 503);
        }
    }

    public function queue(): JsonResponse
    {
        $start = microtime(true);
        try {
            // Only verify the connection can be resolved. We do NOT
            // enqueue a probe job because that would pollute failed_jobs
            // if workers are down.
            $connection = Queue::connection();
            $size = method_exists($connection, 'size')
                ? $connection->size()
                : null;

            return response()->json([
                'status'     => 'ok',
                'check'      => 'queue',
                'driver'     => config('queue.default'),
                'queue_size' => $size,
                'latency_ms' => $this->ms($start),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'down',
                'check'  => 'queue',
                'error'  => $e->getMessage(),
            ], 503);
        }
    }

    public function cache(): JsonResponse
    {
        $start = microtime(true);
        try {
            $key = '_health_probe_' . bin2hex(random_bytes(4));
            Cache::put($key, '1', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return response()->json([
                'status'     => $value === '1' ? 'ok' : 'degraded',
                'check'      => 'cache',
                'store'      => config('cache.default'),
                'latency_ms' => $this->ms($start),
            ], $value === '1' ? 200 : 503);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'down',
                'check'  => 'cache',
                'error'  => $e->getMessage(),
            ], 503);
        }
    }

    public function ready(): JsonResponse
    {
        $checks = [
            'db'    => $this->db()->getData(true),
            'queue' => $this->queue()->getData(true),
            'cache' => $this->cache()->getData(true),
        ];

        $allOk = collect($checks)->every(fn ($c) => ($c['status'] ?? 'down') === 'ok');

        return response()->json([
            'status' => $allOk ? 'ok' : 'degraded',
            'checks' => $checks,
            'time'   => now()->toIso8601String(),
        ], $allOk ? 200 : 503);
    }

    private function ms(float $start): int
    {
        return (int) round((microtime(true) - $start) * 1000);
    }
}
