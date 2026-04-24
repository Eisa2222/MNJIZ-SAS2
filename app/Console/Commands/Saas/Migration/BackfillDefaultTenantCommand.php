<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Migration;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `saas:migration:backfill-default-tenant [--dry-run]`
 *
 * For every tenant-owned table, sets tenant_id = default tenant id on rows
 * where it is currently NULL. Idempotent: re-runs are no-ops once clean.
 *
 * Dry-run mode: reports exactly what WOULD be updated per table, without
 * mutating anything. Use to generate a production runbook before cutover.
 *
 * Central / lookup tables are skipped (see config/saas_migration.php).
 */
final class BackfillDefaultTenantCommand extends BaseSaasMigrationCommand
{
    protected $signature = 'saas:migration:backfill-default-tenant
                            {--dry-run : Compute the plan without writing}';

    protected $description = 'Fill NULL tenant_id rows with the default tenant. Dry-run supported.';

    public function handle(): int
    {
        $this->startRun();

        $tenant = $this->resolveDefaultTenant();
        if (! $tenant) {
            $this->error('No default tenant resolvable. Create one with slug="default" first.');
            $this->finishRun('failed');
            return self::FAILURE;
        }

        $central = collect(config('saas_migration.central_tables', []));
        $lookup  = collect(config('saas_migration.lookup_tables', []));
        $skip    = $central->merge($lookup);

        $totalRowsToUpdate = 0;
        $perTable = [];

        foreach ($this->tenantOwnedTables() as $table) {
            if ($skip->contains($table)) {
                continue;
            }

            $nullCount = (int) DB::table($table)->whereNull('tenant_id')->count();

            if ($nullCount === 0) {
                $perTable[] = ['table' => $table, 'before_null' => 0, 'updated' => 0, 'status' => 'clean'];
                continue;
            }

            $totalRowsToUpdate += $nullCount;

            if ($this->isDryRun()) {
                $perTable[] = ['table' => $table, 'before_null' => $nullCount, 'updated' => 0, 'status' => 'would-update'];
                continue;
            }

            $updated = DB::table($table)
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $tenant->id]);

            $afterNull = (int) DB::table($table)->whereNull('tenant_id')->count();

            $perTable[] = [
                'table'       => $table,
                'before_null' => $nullCount,
                'updated'     => $updated,
                'status'      => $afterNull === 0 ? 'ok' : "FAILED ({$afterNull} still null)",
            ];

            if ($afterNull !== 0) {
                $this->noteError('backfill', "Residual null tenant_id rows after update", [
                    'table'       => $table,
                    'remaining'   => $afterNull,
                ]);
            }
        }

        $this->info(sprintf(
            '— BACKFILL TO TENANT #%d (%s) — mode=%s —',
            $tenant->id, $tenant->slug, $this->mode()
        ));

        $this->table(['table', 'before_null', 'updated', 'status'], $perTable);

        $this->line(sprintf(
            '  Would-update rows : %d',
            $this->isDryRun() ? $totalRowsToUpdate : 0
        ));
        $this->line(sprintf(
            '  Updated rows      : %d',
            $this->isDryRun() ? 0 : $totalRowsToUpdate
        ));

        $this->metric('default_tenant_id', $tenant->id);
        $this->metric('default_tenant_slug', $tenant->slug);
        $this->metric('tables_touched',   count($perTable));
        $this->metric('total_null_rows',  $totalRowsToUpdate);
        $this->metric('per_table',        $perTable);

        $status = $this->errors ? 'partial' : 'success';
        $this->finishRun($status);

        return $this->errors ? self::FAILURE : self::SUCCESS;
    }

    private function resolveDefaultTenant(): ?Tenant
    {
        $slug = config('saas_migration.default_tenant_slug', 'default');
        $id   = (int) config('saas_migration.default_tenant_id', 1);

        $tenant = Tenant::query()->where('slug', $slug)->first();

        if ($tenant) {
            return $tenant;
        }

        // Slug didn't match; try by id as last-resort fallback.
        $tenant = Tenant::query()->find($id);

        if ($tenant) {
            $this->warn("Default tenant slug '{$slug}' not found; falling back to tenant id={$id} (slug={$tenant->slug}).");
        }

        return $tenant;
    }

    /** @return array<string> */
    private function tenantOwnedTables(): array
    {
        $rows = DB::select("
            SELECT TABLE_NAME
              FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND COLUMN_NAME = 'tenant_id'
          ORDER BY TABLE_NAME
        ");
        return array_map(fn ($r) => $r->TABLE_NAME, $rows);
    }
}
