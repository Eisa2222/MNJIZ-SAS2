<?php

declare(strict_types=1);

namespace App\Console\Commands\Saas\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * `saas:migration:validate`
 *
 * Post-cutover sanity checker. Exits with code 0 only if ALL of:
 *
 *   - every tenant-owned table has zero rows with null tenant_id
 *   - every tenant_id value references a row that exists in `tenants`
 *     (no orphan tenant_ids pointing at deleted tenants)
 *   - no file column on disk exposes a path NOT under `tenants/{id}/...`
 *   - no cross-tenant parent/child mismatch in known parent-child FKs
 *     (tenant_id on child must equal tenant_id on parent)
 *
 * Informational only on row-count change — commits do NOT drop legacy
 * data, so row counts are monotonic over time.
 */
final class ValidateCommand extends BaseSaasMigrationCommand
{
    protected $signature   = 'saas:migration:validate';
    protected $description = 'Assert tenant isolation invariants are met across the database.';

    public function handle(): int
    {
        $this->startRun();

        $ok = true;

        // ── 1. null tenant_id scan ──────────────────────────────────
        $nullRows = $this->findNullTenantIdRows();
        $this->reportNullTenantIdRows($nullRows);
        if ($nullRows['total'] > 0) $ok = false;

        // ── 2. orphan tenant_ids ───────────────────────────────────
        $orphans = $this->findOrphanTenantIds();
        $this->reportOrphanTenantIds($orphans);
        if ($orphans['total'] > 0) $ok = false;

        // ── 3. parent/child tenant consistency ─────────────────────
        $mismatches = $this->findParentChildMismatches();
        $this->reportMismatches($mismatches);
        if ($mismatches['total'] > 0) $ok = false;

        // ── 4. file path isolation ─────────────────────────────────
        $badPaths = $this->findLeakyFilePaths();
        $this->reportLeakyFilePaths($badPaths);
        if ($badPaths['total'] > 0) $ok = false;

        $this->metric('null_tenant_id', $nullRows);
        $this->metric('orphan_tenant_ids', $orphans);
        $this->metric('parent_child_mismatches', $mismatches);
        $this->metric('leaky_file_paths', $badPaths);
        $this->metric('status', $ok ? 'pass' : 'fail');

        if ($ok) {
            $this->info('✅ VALIDATE: all invariants hold.');
            $this->finishRun('success');
            return self::SUCCESS;
        }

        $this->error('❌ VALIDATE: invariants BROKEN — see details above. Do NOT cut over to production.');
        $this->finishRun('failed');
        return self::FAILURE;
    }

    // ----------------------------------------------------------------

    private function tenantOwnedTables(): array
    {
        $central = config('saas_migration.central_tables', []);
        $lookup  = config('saas_migration.lookup_tables', []);
        $skip    = array_merge($central, $lookup);

        $rows = DB::select("
            SELECT TABLE_NAME
              FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND COLUMN_NAME = 'tenant_id'
          ORDER BY TABLE_NAME
        ");
        return array_values(array_filter(
            array_map(fn ($r) => $r->TABLE_NAME, $rows),
            fn ($t) => ! in_array($t, $skip, true)
        ));
    }

    private function findNullTenantIdRows(): array
    {
        $perTable = [];
        $total = 0;
        foreach ($this->tenantOwnedTables() as $t) {
            $n = (int) DB::table($t)->whereNull('tenant_id')->count();
            if ($n > 0) {
                $perTable[$t] = $n;
                $total += $n;
            }
        }
        return ['total' => $total, 'per_table' => $perTable];
    }

    private function reportNullTenantIdRows(array $data): void
    {
        $this->line('— CHECK 1: tenant_id IS NULL —');
        if ($data['total'] === 0) {
            $this->info('  OK (no rows)');
            return;
        }
        foreach ($data['per_table'] as $t => $n) {
            $this->error("  {$t}: {$n} rows");
        }
    }

    private function findOrphanTenantIds(): array
    {
        $tenantIds = DB::table('tenants')->pluck('id')->all();
        $validIds = implode(',', $tenantIds) ?: '0';

        $perTable = [];
        $total = 0;
        foreach ($this->tenantOwnedTables() as $t) {
            $n = (int) DB::table($t)
                ->whereNotNull('tenant_id')
                ->whereRaw("tenant_id NOT IN ({$validIds})")
                ->count();
            if ($n > 0) {
                $perTable[$t] = $n;
                $total += $n;
            }
        }
        return ['total' => $total, 'per_table' => $perTable];
    }

    private function reportOrphanTenantIds(array $data): void
    {
        $this->line('— CHECK 2: orphan tenant_ids (pointing at deleted tenant) —');
        if ($data['total'] === 0) {
            $this->info('  OK (no orphans)');
            return;
        }
        foreach ($data['per_table'] as $t => $n) {
            $this->error("  {$t}: {$n} orphaned rows");
        }
    }

    /**
     * A curated list of the most-important parent/child relationships.
     * Extend as needed; the goal is to catch obvious tenant-boundary
     * violations (child.tenant_id != parent.tenant_id).
     */
    private function parentChildEdges(): array
    {
        return [
            ['child' => 'contract_payments',     'fk' => 'contract_id',     'parent' => 'contracts'],
            ['child' => 'contract_attachments',  'fk' => 'contract_id',     'parent' => 'contracts'],
            ['child' => 'exceptional_contract_approvals', 'fk' => 'contract_id', 'parent' => 'exceptional_contracts'],
            ['child' => 'lawsuit_attachments',   'fk' => 'lawsuit_id',      'parent' => 'lawsuits'],
            ['child' => 'lawsuit_notes',         'fk' => 'lawsuit_id',      'parent' => 'lawsuits'],
            ['child' => 'lawsuit_note_replies',  'fk' => 'lawsuit_note_id', 'parent' => 'lawsuit_notes'],
            ['child' => 'sessions',              'fk' => 'lawsuit_id',      'parent' => 'lawsuits'],
            ['child' => 'session_comments',      'fk' => 'session_id',      'parent' => 'sessions'],
            ['child' => 'task_steps',            'fk' => 'task_id',         'parent' => 'tasks'],
            ['child' => 'task_events',           'fk' => 'task_id',         'parent' => 'tasks'],
            ['child' => 'task_step_events',      'fk' => 'task_step_id',    'parent' => 'task_steps'],
            ['child' => 'survey_questions',      'fk' => 'survey_id',       'parent' => 'surveys'],
            ['child' => 'survey_responses',      'fk' => 'survey_id',       'parent' => 'surveys'],
            ['child' => 'survey_answers',        'fk' => 'survey_response_id', 'parent' => 'survey_responses'],
            ['child' => 'ai_chat_messages',      'fk' => 'ai_chat_id',      'parent' => 'ai_chats'],
            ['child' => 'approval_logs',         'fk' => 'approval_request_id', 'parent' => 'approval_requests'],
            ['child' => 'approval_request_levels','fk' => 'approval_request_id', 'parent' => 'approval_requests'],
        ];
    }

    private function findParentChildMismatches(): array
    {
        $mismatches = [];
        $total = 0;

        foreach ($this->parentChildEdges() as $edge) {
            $c = $edge['child']; $p = $edge['parent']; $fk = $edge['fk'];
            if (! Schema::hasTable($c) || ! Schema::hasTable($p))       continue;
            if (! Schema::hasColumn($c, 'tenant_id'))                     continue;
            if (! Schema::hasColumn($p, 'tenant_id'))                     continue;
            if (! Schema::hasColumn($c, $fk))                             continue;

            $n = (int) DB::table("{$c} as c")
                ->join("{$p} as p", "c.{$fk}", '=', 'p.id')
                ->whereRaw('c.tenant_id <> p.tenant_id')
                ->count();

            if ($n > 0) {
                $mismatches["{$c}->{$p}"] = $n;
                $total += $n;
            }
        }

        return ['total' => $total, 'per_edge' => $mismatches];
    }

    private function reportMismatches(array $data): void
    {
        $this->line('— CHECK 3: parent/child tenant_id mismatch —');
        if ($data['total'] === 0) {
            $this->info('  OK (all edges consistent)');
            return;
        }
        foreach ($data['per_edge'] as $edge => $n) {
            $this->error("  {$edge}: {$n} mismatched rows");
        }
    }

    /**
     * For every file column, check that the stored path — once resolved
     * to its absolute tenant location — does not escape into another
     * tenant's namespace. Since TenantStorage prefixes with
     * tenants/{tenant_id}/, the stored column value SHOULD be a plain
     * relative path (no leading `tenants/` segment). If it IS prefixed,
     * it must match this row's tenant_id.
     */
    private function findLeakyFilePaths(): array
    {
        $bad = [];
        $total = 0;
        foreach (config('saas_migration.file_columns', []) as $entry) {
            $t = $entry['table']; $c = $entry['column'];
            if (! Schema::hasTable($t) || ! Schema::hasColumn($t, $c)) continue;
            if (! Schema::hasColumn($t, 'tenant_id')) continue;

            $rows = DB::table($t)
                ->whereNotNull($c)
                ->where($c, '!=', '')
                ->where($c, 'LIKE', 'tenants/%')
                ->get(['id', 'tenant_id', $c]);

            foreach ($rows as $row) {
                $path = (string) $row->{$c};
                // Extract tenant id from the stored path: "tenants/{id}/..."
                if (preg_match('#^tenants/(\d+)/#', $path, $m)) {
                    $pathTenantId = (int) $m[1];
                    if ($pathTenantId !== (int) $row->tenant_id) {
                        $bad[] = [
                            'table' => $t, 'column' => $c,
                            'row_id' => $row->id, 'row_tenant' => $row->tenant_id,
                            'path_tenant' => $pathTenantId, 'path' => $path,
                        ];
                        $total++;
                    }
                }
            }
        }
        return ['total' => $total, 'details' => $bad];
    }

    private function reportLeakyFilePaths(array $data): void
    {
        $this->line('— CHECK 4: cross-tenant file paths —');
        if ($data['total'] === 0) {
            $this->info('  OK (no mismatched file paths)');
            return;
        }
        foreach ($data['details'] as $d) {
            $this->error(sprintf(
                '  %s.%s id=%d  row_tenant=%d  path_tenant=%d  path=%s',
                $d['table'], $d['column'], $d['row_id'], $d['row_tenant'], $d['path_tenant'], $d['path']
            ));
        }
    }
}
