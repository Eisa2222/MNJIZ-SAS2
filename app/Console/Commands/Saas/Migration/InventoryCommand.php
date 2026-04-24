<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `saas:migration:inventory`
 *
 * Read-only scan of the current database. Produces a table-by-table
 * classification so operations know exactly what is about to be touched:
 *
 *   - Tenant-owned : has tenant_id column
 *   - Central      : explicitly listed in config('saas_migration.central_tables')
 *   - Lookup       : listed in config('saas_migration.lookup_tables')
 *   - Other        : unclassified (usually = low-risk extras)
 *
 * Per tenant-owned table it also reports:
 *   - total row count
 *   - count where tenant_id IS NULL (= work for the backfill command)
 *   - which file columns live on that table (from config)
 *   - flags risk level based on keyword match
 */
final class InventoryCommand extends BaseSaasMigrationCommand
{
    protected $signature   = 'saas:migration:inventory';
    protected $description = 'Scan DB schema and classify tables for tenant-migration purposes (read-only).';

    public function handle(): int
    {
        $this->startRun();

        $central = collect(config('saas_migration.central_tables', []));
        $lookup  = collect(config('saas_migration.lookup_tables', []));
        $fileColumns = collect(config('saas_migration.file_columns', []));

        $allTables = $this->listTables();

        $tenantOwned = [];
        $centralHit  = [];
        $lookupHit   = [];
        $other       = [];

        foreach ($allTables as $t) {
            if ($central->contains($t)) {
                $centralHit[] = $t;
                continue;
            }
            if ($lookup->contains($t)) {
                $lookupHit[] = $t;
                continue;
            }
            if (Schema::hasColumn($t, 'tenant_id')) {
                $tenantOwned[] = $t;
                continue;
            }
            $other[] = $t;
        }

        // Build the tenant-owned dataset (counts + nullable + files).
        $rows = [];
        $totalRows     = 0;
        $totalNull     = 0;
        $riskyTables   = [];

        foreach ($tenantOwned as $t) {
            $count  = (int) DB::table($t)->count();
            $nulls  = (int) DB::table($t)->whereNull('tenant_id')->count();
            $files  = $fileColumns->where('table', $t)->pluck('column')->values()->all();
            $isRisky = $this->isRisky($t, $files);

            $totalRows += $count;
            $totalNull += $nulls;
            if ($isRisky) $riskyTables[] = $t;

            $rows[] = [
                'table'        => $t,
                'rows'         => $count,
                'null_tenant'  => $nulls,
                'file_columns' => $files ? implode(',', $files) : '-',
                'risky'        => $isRisky ? 'YES' : '-',
            ];
        }

        // Render the main table.
        $this->info('— TENANT-OWNED TABLES —');
        $this->table(
            ['table', 'rows', 'null_tenant', 'file_columns', 'risky'],
            $rows
        );

        // Summary line.
        $this->info('— SUMMARY —');
        $this->line(sprintf('  Tenant-owned tables : %d', count($tenantOwned)));
        $this->line(sprintf('  Central tables      : %d', count($centralHit)));
        $this->line(sprintf('  Lookup tables       : %d', count($lookupHit)));
        $this->line(sprintf('  Other / unclassified: %d', count($other)));
        $this->line(sprintf('  Total tenant rows   : %d', $totalRows));
        $this->line(sprintf('  Rows with null tenant_id : %d', $totalNull));
        $this->line(sprintf('  Risky tables        : %d  [%s]', count($riskyTables), implode(', ', $riskyTables)));

        if ($other) {
            $this->warn('Unclassified tables (check whether they should be central / lookup):');
            foreach (array_chunk($other, 5) as $chunk) {
                $this->line('    ' . implode(', ', $chunk));
            }
        }

        // Persist to audit log.
        $this->metric('tenant_owned_tables', count($tenantOwned));
        $this->metric('central_tables',     count($centralHit));
        $this->metric('lookup_tables',      count($lookupHit));
        $this->metric('other_tables',       count($other));
        $this->metric('total_tenant_rows',  $totalRows);
        $this->metric('rows_with_null_tenant_id', $totalNull);
        $this->metric('risky_tables',       $riskyTables);

        $this->finishRun('success');

        return self::SUCCESS;
    }

    /** @return array<string> */
    private function listTables(): array
    {
        $rows = DB::select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME');
        return array_map(fn ($r) => $r->TABLE_NAME, $rows);
    }

    /**
     * Flag tables that hold files, credentials, PII, financial or legal data.
     * Pure heuristic based on table name + file-column list — informational
     * only; the migration does not treat these tables differently.
     */
    private function isRisky(string $table, array $files): bool
    {
        if (! empty($files)) {
            return true;
        }
        $riskyKeywords = ['settings', 'payments', 'invoice', 'webhook', 'contract', 'subscription', 'billing', 'lawsuit', 'session', 'notification', 'message', 'chat'];
        foreach ($riskyKeywords as $kw) {
            if (str_contains($table, $kw)) {
                return true;
            }
        }
        return false;
    }
}
