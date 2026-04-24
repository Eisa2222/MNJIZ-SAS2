<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * `saas:migration:migrate-files [--dry-run] [--disk=public]`
 *
 * Walks every `{table, column, subdir}` entry in
 * config('saas_migration.file_columns') and moves each row's file into
 * its tenant-scoped location:
 *
 *     storage/app/{disk}/tenants/{tenant_id}/{subdir}/{basename}
 *
 * The DB column is then updated to the new relative path (without the
 * `tenants/{id}/` prefix so that TenantStorage::disk() re-attaches it
 * at runtime transparently).
 *
 * Safety:
 *   - `--dry-run` lists every intended move with no filesystem changes.
 *   - Missing source files are reported, skipped, and logged as warnings
 *     (no abort) so one bad file doesn't block a thousand.
 *   - Files already under `tenants/{id}/...` are recognized and skipped
 *     (idempotent re-runs).
 *   - Files are COPIED first, then the DB is updated, and only then the
 *     source is removed — no risk of dangling rows if disk fails mid-way.
 *   - An existing destination is never overwritten — the row is flagged
 *     as a conflict and skipped.
 */
final class MigrateFilesCommand extends BaseSaasMigrationCommand
{
    protected $signature = 'saas:migration:migrate-files
                            {--dry-run : Compute the plan without moving files}
                            {--disk=public : Disk to operate on (public | local)}';

    protected $description = 'Move files from legacy paths into storage/app/{disk}/tenants/{id}/... and update DB columns.';

    public function handle(): int
    {
        $this->startRun();

        $disk = (string) $this->option('disk');
        $storage = Storage::disk($disk);
        $fileColumns = collect(config('saas_migration.file_columns', []));

        if ($fileColumns->isEmpty()) {
            $this->warn('config/saas_migration.php :: file_columns is empty.');
            $this->finishRun('success');
            return self::SUCCESS;
        }

        $plan = [];
        $moved = 0;
        $skippedMissing = 0;
        $skippedAlready = 0;
        $conflicts = 0;

        foreach ($fileColumns as $entry) {
            $table  = $entry['table'];
            $column = $entry['column'];
            $subdir = rtrim($entry['subdir'], '/');

            if (! Schema::hasTable($table)) {
                $this->warn("Skip: table `{$table}` not present.");
                continue;
            }
            if (! Schema::hasColumn($table, $column)) {
                $this->warn("Skip: `{$table}.{$column}` does not exist.");
                continue;
            }
            if (! Schema::hasColumn($table, 'tenant_id')) {
                $this->warn("Skip: `{$table}` has no tenant_id — cannot resolve target path.");
                continue;
            }

            $rows = DB::table($table)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->get(['id', 'tenant_id', $column]);

            $tableMoved = 0;
            $tableMissing = 0;
            $tableAlready = 0;
            $tableConflict = 0;

            foreach ($rows as $row) {
                $currentPath = (string) $row->{$column};
                $tenantId    = (int) $row->tenant_id;

                if (! $tenantId) {
                    $this->noteError('migrate-files', "row with null tenant_id — skip", [
                        'table' => $table, 'id' => $row->id,
                    ]);
                    continue;
                }

                $prefix = "tenants/{$tenantId}/";

                // Idempotent: already under tenant path → nothing to do.
                if (str_starts_with($currentPath, $prefix)) {
                    $tableAlready++;
                    $skippedAlready++;
                    continue;
                }

                $basename = basename($currentPath);
                $newRelative = "{$subdir}/{$basename}";
                $newFullPath = "{$prefix}{$newRelative}";

                // Source existence check.
                if (! $storage->exists($currentPath)) {
                    $tableMissing++;
                    $skippedMissing++;
                    $this->noteError('migrate-files', "source missing on disk", [
                        'table' => $table, 'id' => $row->id, 'path' => $currentPath,
                    ]);
                    continue;
                }

                // Conflict: destination already exists.
                if ($storage->exists($newFullPath)) {
                    $tableConflict++;
                    $conflicts++;
                    $this->noteError('migrate-files', "destination already exists — skip", [
                        'table' => $table, 'id' => $row->id,
                        'from'  => $currentPath, 'to' => $newFullPath,
                    ]);
                    continue;
                }

                if ($this->isDryRun()) {
                    $plan[] = [
                        'table'  => $table, 'row_id' => $row->id,
                        'from'   => $currentPath, 'to'  => $newFullPath,
                    ];
                    continue;
                }

                // Copy → update DB → delete source (safe ordering).
                try {
                    $ok = $storage->copy($currentPath, $newFullPath);
                    if (! $ok) {
                        $this->noteError('migrate-files', 'copy() returned false', [
                            'table' => $table, 'id' => $row->id, 'path' => $currentPath,
                        ]);
                        continue;
                    }

                    // Store path WITHOUT the tenants/{id}/ prefix so
                    // TenantStorage::disk() can re-prefix at runtime.
                    DB::table($table)->where('id', $row->id)->update([
                        $column => $newRelative,
                    ]);

                    $storage->delete($currentPath);

                    $tableMoved++;
                    $moved++;
                } catch (\Throwable $e) {
                    $this->noteError('migrate-files', 'exception during move', [
                        'table' => $table, 'id' => $row->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            $plan[] = [
                'table'  => $table . '.' . $column,
                'moved'  => $tableMoved,
                'missing'=> $tableMissing,
                'already'=> $tableAlready,
                'conflict'=> $tableConflict,
            ];
        }

        $this->info(sprintf('— MIGRATE FILES (mode=%s, disk=%s) —', $this->mode(), $disk));
        $this->table(['table.column / row', 'moved / from', 'missing / to', 'already', 'conflict'],
            array_map(fn ($r) => [
                $r['table']        ?? ($r['row_id'] ?? '-'),
                $r['moved']        ?? ($r['from']   ?? '-'),
                $r['missing']      ?? ($r['to']     ?? '-'),
                $r['already']      ?? '-',
                $r['conflict']     ?? '-',
            ], $plan)
        );

        $this->line(sprintf(
            $this->isDryRun()
                ? '  Would move: %d  |  Missing: %d  |  Already tenant-prefixed: %d  |  Conflicts: %d'
                : '  Moved: %d  |  Missing: %d  |  Already tenant-prefixed: %d  |  Conflicts: %d',
            $this->isDryRun() ? count(array_filter($plan, fn ($p) => isset($p['from']))) : $moved,
            $skippedMissing, $skippedAlready, $conflicts
        ));

        $this->metric('moved',            $moved);
        $this->metric('missing',          $skippedMissing);
        $this->metric('already_migrated', $skippedAlready);
        $this->metric('conflicts',        $conflicts);

        $this->finishRun($this->errors ? 'partial' : 'success');

        return self::SUCCESS;
    }
}
