<?php

declare(strict_types=1);

namespace App\Console\Concerns;

use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Throwable;

/**
 * Mixin for Artisan commands that must run their logic ONCE PER TENANT.
 *
 * Before Phase 6, scheduled commands like `payroll:generate-wps` operated on
 * global data (single-tenant assumption). Post-SaaS they must iterate every
 * active tenant, run the same logic inside that tenant's context, and isolate
 * failures so one bad tenant doesn't block the others.
 *
 * Usage:
 *
 *     class GeneratePayrollCommand extends Command
 *     {
 *         use IteratesTenants;
 *
 *         public function handle(): int
 *         {
 *             return $this->perTenant(function (Tenant $tenant) {
 *                 // original logic, unchanged — runs under tenant context
 *                 $this->info("Generating payroll for {$tenant->slug}");
 *                 (new PayrollsService())->generateMonthly();
 *             });
 *         }
 *     }
 *
 * Returns 0 on overall success, 1 if ANY tenant threw. Per-tenant failures
 * are logged + stored in $this->failedTenants for follow-up.
 */
trait IteratesTenants
{
    /** @var array<int, array{tenant: Tenant, error: string}> */
    protected array $failedTenants = [];

    /**
     * @param callable(Tenant):void $callback
     */
    protected function perTenant(callable $callback, ?callable $filter = null): int
    {
        $query = Tenant::query()->where('status', Tenant::STATUS_ACTIVE);

        if ($filter) {
            $filter($query);
        }

        $total     = $query->count();
        $succeeded = 0;
        $failed    = 0;

        $this->info("Running across {$total} active tenant(s).");

        $query->chunkById(50, function ($tenants) use ($callback, &$succeeded, &$failed) {
            foreach ($tenants as $tenant) {
                try {
                    TenantContext::runAs($tenant, fn () => $callback($tenant));
                    $succeeded++;
                } catch (Throwable $e) {
                    $failed++;
                    $this->failedTenants[] = [
                        'tenant' => $tenant,
                        'error'  => $e->getMessage(),
                    ];

                    if (method_exists($this, 'error')) {
                        $this->error("  tenant={$tenant->slug} failed: {$e->getMessage()}");
                    }

                    report($e); // let the global exception handler log it too
                }
            }
        });

        $this->info("Done. succeeded={$succeeded}  failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
