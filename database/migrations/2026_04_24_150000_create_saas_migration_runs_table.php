<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 7 — data-migration audit log.
 *
 * Every `saas:migration:*` command writes a row here at start and updates
 * it at finish. This gives operations a single source of truth for:
 *   - what ran, in what mode (dry-run vs real)
 *   - start/end timestamps
 *   - outcome status (success | partial | failed)
 *   - summary JSON (per-table row counts, file move counts, …)
 *   - errors JSON (per-row diagnostics when something throws)
 *
 * Central-only table — no tenant_id. Migration rows are platform-level
 * events, not tenant events.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('saas_migration_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command', 120);          // e.g. "saas:migration:backfill-default-tenant"
            $table->string('mode', 16);              // "dry-run" | "real"
            $table->enum('status', ['running', 'success', 'partial', 'failed'])
                  ->default('running');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->json('summary')->nullable();
            $table->json('errors')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();   // admin user id (if invoked from UI)
            $table->timestamps();

            $table->index(['command', 'mode']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_migration_runs');
    }
};
