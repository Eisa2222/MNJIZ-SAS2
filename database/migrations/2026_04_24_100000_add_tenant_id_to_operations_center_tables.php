<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 Module 2 — OperationsCenter tenant-awareness.
 *
 * High-risk module: financial rows (contracts + payments). A query that
 * leaks across tenants would mix one firm's receivables into another's
 * dashboard. Every aggregate-root table gets tenant_id + FK + index;
 * historical rows backfill to Default Tenant.
 *
 *   Tables covered:
 *     contracts, offers, exceptional_contracts, exceptional_contract_approvals,
 *     contract_payments, contract_attachments, offer_approval_logs
 *
 *   Pivots / children (stay FK-scoped via parent):
 *     contract_offer, offer_technical_study (if present)
 */
return new class extends Migration {
    private array $tables = [
        'contracts',
        'offers',
        'exceptional_contracts',
        'exceptional_contract_approvals',
        'contract_payments',
        'contract_attachments',
        'offer_approval_logs',
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

                    $table->index(['tenant_id'], substr("{$tableName}_tid_idx", 0, 60));
                });
            }

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
