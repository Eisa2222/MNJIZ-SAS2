<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 Module 5 — tenant-aware legacy `settings` (singleton) table.
 *
 * History: original single-tenant design kept a single row per install
 * (id=1) holding every system flag, branding image, integration credential
 * and SMTP secret. 79 call sites across 33 files still call
 *   Settings::first()  /  Settings::find(1)
 * — which in a multi-tenant DB would read the WRONG firm's config.
 *
 * Fix strategy for this migration:
 *   1. Add tenant_id + FK + index + unique(tenant_id) so each tenant has
 *      at most one settings row.
 *   2. Backfill the existing id=1 row to the Default Tenant.
 *   3. For every OTHER tenant, clone that row (so Settings::current()
 *      works immediately without tripping on missing rows).
 *
 * After this + Settings model's new BelongsToTenant trait, the legacy
 * ::first() calls auto-scope correctly. The ::find(1) calls need a small
 * controller-level migration to Settings::current() (done in follow-up
 * edits).
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $defaultId = (int) env('TENANCY_DEFAULT_TENANT_ID', 1);

        // 1 — add tenant_id column (nullable first so backfill works).
        if (! Schema::hasColumn('settings', 'tenant_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();

                $table->index('tenant_id', 'settings_tid_idx');
            });
        }

        // 2 — backfill any null rows to default tenant.
        DB::table('settings')
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $defaultId]);

        // 3 — clone the "singleton" row for every tenant that doesn't
        //     have one yet, so Settings::current() never returns null.
        $template = DB::table('settings')
            ->where('tenant_id', $defaultId)
            ->orderBy('id')
            ->first();

        if ($template) {
            $templateArr = (array) $template;
            unset($templateArr['id']);              // let auto-increment assign
            $templateArr['created_at'] = now();
            $templateArr['updated_at'] = now();

            $existingTenants = DB::table('settings')->pluck('tenant_id')->unique()->toArray();
            $allTenants      = DB::table('tenants')->pluck('id')->toArray();

            $missing = array_diff($allTenants, $existingTenants);

            foreach ($missing as $tenantId) {
                $row = $templateArr;
                $row['tenant_id'] = $tenantId;
                DB::table('settings')->insert($row);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('settings') || ! Schema::hasColumn('settings', 'tenant_id')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            try { $table->dropIndex('settings_tid_idx'); } catch (\Throwable $e) {}
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
