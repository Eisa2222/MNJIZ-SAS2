<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 Module 3 — LegalAffair tenant-awareness.
 *
 * HIGH-risk module: legal records (lawsuits, sessions, opponents, memos,
 * attachments). A leak here would expose one firm's privileged/litigated
 * matters to another firm — privilege, confidentiality & licensing hazard.
 *
 * Scope selection:
 *   - Core aggregate roots (Lawsuit, Session, Opponent, PowerOfAttorney,
 *     Memo, LawsuitNote, LawsuitAttachment, Project, Document, …)
 *   - Pivot tables queried directly via raw DB::table() calls
 *     (lawsuit_plaintiffs, lawsuit_defendants) — would leak without tenant_id
 *   - Reference data owned per-firm (judges, courts, courtrooms) — these
 *     are user-maintained per tenant, not global seeds
 *
 * Child tables not listed here stay FK-scoped via their parent
 * (e.g. lawsuit_power_of_attorneys pivot, assigned_lawsuits, project_employee).
 *
 * Backfill: existing rows go to TENANCY_DEFAULT_TENANT_ID (env-driven).
 */
return new class extends Migration {
    private array $tables = [
        // Core parent rows
        'lawsuits',
        'sessions',
        'opponents',
        'power_of_attorneys',
        'memos',
        'lawsuit_notes',
        'lawsuit_note_replies',
        'lawsuit_attachments',
        'session_comments',
        'session_comment_mentions',
        'opponent_authorizations',

        // Pivots touched by raw DB::table() queries
        'lawsuit_plaintiffs',
        'lawsuit_defendants',

        // Judicial affairs (same tenant boundary)
        'projects',
        'documents',
        'project_attachments',
        'judges',
        'courts',
        'courtrooms',
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
