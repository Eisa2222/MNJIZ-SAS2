<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 Module 4 — Notifications tenant-awareness.
 *
 * Two mixed-traffic tables:
 *   - notifications   : Laravel's default DatabaseNotification rows
 *                       (morphMany on Notifiable). Shared across tenants
 *                       in single-tenant days → must carry tenant_id now.
 *   - message_logs    : SMS / WhatsApp / email message log.
 *
 * Rationale:
 *   * Without tenant_id, Super Admin dashboards, export scripts, and
 *     unscrambling of notifiable morphs all risk leaking cross-tenant data.
 *   * With tenant_id + a global auto-fill hook on DatabaseNotification,
 *     every new row carries its owning tenant — so a simple
 *     `notifications.where('tenant_id', ?)` is always correct.
 *
 * Backfill: existing rows are attributed to the Default Tenant (env-driven)
 * so the column can become NOT NULL at the FK step.
 */
return new class extends Migration {
    private array $tables = [
        'notifications',
        'message_logs',
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
                    // `notifications.id` is a UUID char(36) — we position
                    // tenant_id after it without assuming column order.
                    $table->foreignId('tenant_id')
                        ->nullable()
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
