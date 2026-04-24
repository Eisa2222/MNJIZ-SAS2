<?php

declare(strict_types=1);

namespace App\Tenancy\Concerns;

use App\Tenancy\Jobs\Middleware\RestoreTenantContext;
use App\Tenancy\TenantContext;

/**
 * Attach to any Job that touches tenant-scoped data (which is almost all
 * user-initiated work in this SaaS).
 *
 * What it does:
 *   - Constructor captures the CURRENT tenant id via captureTenant()
 *   - Value is serialized into the job payload automatically
 *   - On worker pickup, RestoreTenantContext middleware re-seats the
 *     context before handle() runs — so BelongsToTenant scope filters
 *     correctly even in a different PHP process
 *   - Context is forgotten after the job finishes (success OR fail)
 *
 * Usage:
 *
 *     class SyncStepsTaskJob implements ShouldQueue
 *     {
 *         use Dispatchable, InteractsWithQueue, Queueable, TenantAwareJob;
 *
 *         public function __construct(public TaskStep $step)
 *         {
 *             $this->captureTenant();   // ← call once in constructor
 *         }
 *
 *         public function handle() { ... }
 *     }
 */
trait TenantAwareJob
{
    /** Tenant id at the time the job was dispatched. */
    public ?int $tenantId = null;

    /**
     * Call from the constructor AFTER parent/constructor-injected deps so the
     * dispatcher's current tenant is recorded into the serialized job.
     */
    protected function captureTenant(): void
    {
        if ($this->tenantId === null) {
            $this->tenantId = TenantContext::currentId();
        }
    }

    /**
     * Auto-registered — Laravel calls this to build the middleware pipeline
     * around handle(). RestoreTenantContext reads $this->tenantId.
     */
    public function middleware(): array
    {
        return [
            new RestoreTenantContext(),
        ];
    }
}
