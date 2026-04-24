<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 Module 6 — FINAL tenant-awareness sweep.
 *
 * Adds tenant_id + FK + index to every remaining aggregate-root table
 * across Marketing, Tasks, Approvals, Chat, AI, Surveys and Meeting Rooms.
 * After this migration every user-owned record in the system carries a
 * tenant_id — the last columns that would otherwise let cross-tenant data
 * leak via a direct DB query, an unscoped aggregate, or a Super Admin
 * export script.
 *
 * Backfill: historical rows go to TENANCY_DEFAULT_TENANT_ID so the
 * constraint remains satisfiable without making columns nullable
 * long-term.
 */
return new class extends Migration {
    private array $tables = [
        // Marketing
        'campaign_management',
        'campaign_results',
        'content_management',
        'content_management_socials',
        'social_publications',

        // Tasks + Workflow
        'tasks',
        'task_steps',
        'task_events',
        'task_step_events',
        'task_attachments',
        'task_routings',

        // Approval System
        'approval_requests',
        'approval_logs',
        'approval_request_levels',

        // Chat / Messaging
        'messages',

        // AI (Legal AI)
        'ai_chats',
        'ai_chat_messages',

        // Surveys
        'surveys',
        'survey_questions',
        'survey_question_options',
        'survey_responses',
        'survey_answers',

        // Meeting Rooms
        'meeting_rooms',
        'participants',
        'meeting_notes',
        'attendee_meetings',
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
