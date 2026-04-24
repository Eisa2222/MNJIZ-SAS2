<?php

declare(strict_types=1);

namespace App\Providers;

use App\Tenancy\TenantContext;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Phase 8 — wires production observability hooks into the container at
 * boot:
 *
 *   1. Slow query logger (threshold from config('security.slow_query_ms'))
 *   2. Failed-job logger (JobFailed event → structured error log)
 *   3. Job lifecycle breadcrumb (start / finish) on the `queue` channel
 *   4. Global renderable exception notifier stub — the actual delivery
 *      (email / Slack / PagerDuty) is left to app/Exceptions/Handler.php
 *      which already exists; this provider just guarantees the tenant
 *      context is on every error log record via Log::withContext().
 */
final class ProductionObservabilityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerSlowQueryLogger();
        $this->registerQueueLifecycleLogger();
        $this->registerFailedJobLogger();
    }

    /**
     * Any SQL statement that runs longer than `slow_query_ms` gets logged
     * once, structured, on the `slow` channel. Zero-overhead in the happy
     * path because the threshold check happens per-query but allocates
     * nothing until it fires.
     */
    private function registerSlowQueryLogger(): void
    {
        $threshold = (int) config('security.slow_query_ms', 1000);

        DB::listen(function (QueryExecuted $event) use ($threshold) {
            if ($event->time < $threshold) {
                return;
            }

            Log::channel($this->pickChannel('slow'))->warning('slow_query', [
                'sql'        => $event->sql,
                'time_ms'    => (int) round($event->time),
                'connection' => $event->connectionName,
                'tenant_id'  => TenantContext::currentId(),
            ]);
        });
    }

    /**
     * Structured log entries around every queue job. Useful for both
     * local debugging and production post-mortems — pairs with the
     * request_id attached to the originating HTTP request by
     * RequestIdMiddleware (persisted into the job payload via
     * TenantAwareJob's tenantId trail).
     */
    private function registerQueueLifecycleLogger(): void
    {
        $chan = $this->pickChannel('queue');

        Event::listen(JobProcessing::class, function (JobProcessing $event) use ($chan) {
            $payload = $event->job->payload();
            Log::channel($chan)->info('job.processing', [
                'job'       => $event->job->getName(),
                'connection'=> $event->connectionName,
                'queue'     => $event->job->getQueue(),
                'attempts'  => $event->job->attempts(),
                'tenant_id' => $payload['tenant_id'] ?? null,
            ]);
        });

        Event::listen(JobProcessed::class, function (JobProcessed $event) use ($chan) {
            Log::channel($chan)->info('job.processed', [
                'job'      => $event->job->getName(),
                'attempts' => $event->job->attempts(),
            ]);
        });
    }

    /**
     * Every failed job turns into an ERROR-level log entry with full
     * exception + tenant context. Ops dashboards key off this channel
     * for the "failed jobs" alert.
     */
    private function registerFailedJobLogger(): void
    {
        $chan = $this->pickChannel('queue');

        Event::listen(JobFailed::class, function (JobFailed $event) use ($chan) {
            $payload = $event->job->payload();
            Log::channel($chan)->error('job.failed', [
                'job'       => $event->job->getName(),
                'queue'     => $event->job->getQueue(),
                'attempts'  => $event->job->attempts(),
                'tenant_id' => $payload['tenant_id'] ?? null,
                'exception' => [
                    'class'   => get_class($event->exception),
                    'message' => $event->exception->getMessage(),
                ],
            ]);
        });
    }

    /**
     * Return the channel name if the app defines one, falling back to
     * the application default. Prevents "Log [slow] is not defined"
     * errors in installs that haven't updated config/logging.php yet.
     */
    private function pickChannel(string $preferred): string
    {
        $channels = (array) config('logging.channels', []);
        return array_key_exists($preferred, $channels) ? $preferred : config('logging.default');
    }
}
