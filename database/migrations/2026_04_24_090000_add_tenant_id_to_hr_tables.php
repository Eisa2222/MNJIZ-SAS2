<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 — HR Module tenant-awareness.
 *
 * Adds tenant_id to every primary HR aggregate-root table. Child/pivot tables
 * (wps_payroll_details, violation_appeals, etc.) are NOT touched — FK to their
 * tenant-scoped parent provides sufficient isolation.
 *
 *   Strategy:
 *     1. Add nullable tenant_id + FK + composite index
 *     2. Backfill historical rows to the Default Tenant (id from env)
 *     3. Keep nullable for Phase 6; flip to NOT NULL in Phase 7 after QA
 *
 * Idempotent — safe to re-run.
 */
return new class extends Migration {
    /** Primary HR tables — each gets tenant_id. */
    private array $tables = [
        'advances',
        'alerts',
        'attendances',
        'attendance_logs',
        'company_policies',
        'custody_items',
        'custody_requests',
        'custody_logs',
        'deductions',
        'employee_edit_requests',
        'employee_salary_histories',
        'invoices',                 // HR purchase invoices (distinct from billing_invoices)
        'leave_balances',
        'leave_balance_logs',
        'leave_requests',
        'opening_balance_adjustments',
        'purchase_requests',
        'purchases',
        'rewards',
        'violations',
        'wps_payrolls',
    ];

    public function up(): void
    {
        $defaultId = (int) env('TENANCY_DEFAULT_TENANT_ID', 1);

        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->foreignId('tenant_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('tenants')
                        ->nullOnDelete();

                    // Index name capped at 64 chars — let Laravel auto-name
                    // but keep the table prefix short enough for long names.
                    $table->index(['tenant_id'], substr("{$tableName}_tid_idx", 0, 60));
                });
            }

            // Backfill existing rows to Default Tenant. Idempotent — only touches NULL rows.
            DB::table($tableName)
                ->whereNull('tenant_id')
                ->update(['tenant_id' => $defaultId]);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                try { $table->dropIndex(substr("{$tableName}_tid_idx", 0, 60)); } catch (\Throwable $e) {}
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
