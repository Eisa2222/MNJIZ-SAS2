<?php

declare(strict_types=1);

namespace Tests\Feature\Module6;

use App\Models\ApprovalSystem\ApprovalRequest;
use App\Models\chat\Message;
use App\Models\LegalAI\AiChat;
use App\Models\Marketing\CampaignManagement\CampaignManagement;
use App\Models\Marketing\ContentManagement\ContentManagement;
use App\Models\MeetingRoom\MeetingRoom;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use App\Models\Survey\Survey;
use App\Models\Survey\SurveyResponse;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Settings\SettingsRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 6 Module 6 — final tenant-awareness sweep regression suite.
 *
 * Every remaining module (Marketing, Tasks, Approvals, Chat, AI, Surveys,
 * Meeting Rooms) now carries tenant_id and a BelongsToTenant scope.
 * These tests prove the isolation holds end-to-end for each subsystem and
 * for the cross-cutting concerns (queue jobs, external API keys, cache,
 * file storage).
 *
 *   1. Marketing data isolation (Campaigns + Content)
 *   2. Task isolation
 *   3. Approval isolation
 *   4. Chat isolation
 *   5. AI logs isolation
 *   6. Survey isolation
 *   7. External API keys isolation (already sealed in Module 5; re-asserted)
 *   8. File storage isolation (already sealed in Phase 2; re-asserted)
 *   9. Queued jobs keep tenant context (already sealed in Module 4)
 *  10. Cache isolation
 *  11. Cross-tenant reassignment prevented on tenant-scoped Module 6 models
 *  12. Super-admin withoutTenancy() can see everything
 */
final class Module6IsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Module 6 tables have heavy FK footprint (campaigns → sections,
        // tasks → creators, surveys → creators, etc). Disable FK checks
        // so tests can insert minimal rows via DB::table() while still
        // exercising the TenantScope on the Eloquent side.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_marketing_campaigns_and_content_are_isolated(): void
    {
        [$a, $b] = $this->twoTenants();

        $this->seedRow('campaign_management', $a, ['campaign_name' => 'A Camp 1', 'created_by' => 0]);
        $this->seedRow('campaign_management', $a, ['campaign_name' => 'A Camp 2', 'created_by' => 0]);
        $this->seedRow('campaign_management', $b, ['campaign_name' => 'B Camp',   'created_by' => 0]);

        $this->seedRow('content_management', $a, ['created_by' => 0]);
        $this->seedRow('content_management', $b, ['created_by' => 0]);
        $this->seedRow('content_management', $b, ['created_by' => 0]);

        TenantContext::runAs($a, function () {
            $this->assertSame(2, CampaignManagement::count());
            $this->assertSame(1, ContentManagement::count());
            $this->assertFalse(CampaignManagement::where('campaign_name', 'B Camp')->exists());
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(1, CampaignManagement::count());
            $this->assertSame(2, ContentManagement::count());
        });
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_task_records_and_steps_are_isolated(): void
    {
        [$a, $b] = $this->twoTenants();

        $aTaskId = $this->seedRow('tasks', $a, ['task_name' => 'A task 1']);
        $this->seedRow('tasks', $a, ['task_name' => 'A task 2']);
        $bTaskId = $this->seedRow('tasks', $b, ['task_name' => 'B task']);

        // Steps attached to each tenant's tasks.
        $this->seedRow('task_steps', $a, ['task_id' => $aTaskId, 'name' => 'A-step-1']);
        $this->seedRow('task_steps', $a, ['task_id' => $aTaskId, 'name' => 'A-step-2']);
        $this->seedRow('task_steps', $b, ['task_id' => $bTaskId, 'name' => 'B-step']);

        TenantContext::runAs($a, function () {
            $this->assertSame(2, Task::count());
            $this->assertSame(2, TaskStep::count());
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(1, Task::count());
            $this->assertSame(1, TaskStep::count());
        });
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_approval_requests_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        $this->seedRow('approval_requests', $a, [
            'approvable_type' => 'App\\Models\\Fake', 'approvable_id' => 1,
            'current_level'   => 1, 'status' => 'pending',
        ]);
        $this->seedRow('approval_requests', $b, [
            'approvable_type' => 'App\\Models\\Fake', 'approvable_id' => 2,
            'current_level'   => 1, 'status' => 'pending',
        ]);
        $this->seedRow('approval_requests', $b, [
            'approvable_type' => 'App\\Models\\Fake', 'approvable_id' => 3,
            'current_level'   => 1, 'status' => 'pending',
        ]);

        TenantContext::runAs($a, fn () => $this->assertSame(1, ApprovalRequest::count()));
        TenantContext::runAs($b, fn () => $this->assertSame(2, ApprovalRequest::count()));
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_chat_messages_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        $this->seedRow('messages', $a, ['sender_id' => 1, 'receiver_id' => 2, 'message' => 'A-private']);
        $this->seedRow('messages', $b, ['sender_id' => 1, 'receiver_id' => 2, 'message' => 'B-private']);

        TenantContext::runAs($a, function () {
            $this->assertSame(1, Message::count());
            $this->assertTrue(Message::where('message', 'A-private')->exists());
            $this->assertFalse(
                Message::where('message', 'B-private')->exists(),
                'CRITICAL: tenant A must NOT see tenant B chat messages.'
            );
        });

        TenantContext::runAs($b, fn () => $this->assertSame(1, Message::count()));
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_ai_chat_logs_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        // AiChat uses UUID — we generate our own.
        $aChatId = (string) \Illuminate\Support\Str::uuid();
        $bChatId = (string) \Illuminate\Support\Str::uuid();

        DB::table('ai_chats')->insert([
            ['id' => $aChatId, 'tenant_id' => $a->id, 'user_id' => 1, 'title' => 'A legal Q', 'type' => 'chat', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $bChatId, 'tenant_id' => $b->id, 'user_id' => 2, 'title' => 'B legal Q', 'type' => 'chat', 'created_at' => now(), 'updated_at' => now()],
        ]);

        TenantContext::runAs($a, function () {
            $this->assertSame(1, AiChat::count());
            $this->assertTrue(AiChat::where('title', 'A legal Q')->exists());
            $this->assertFalse(
                AiChat::where('title', 'B legal Q')->exists(),
                'CRITICAL: tenant A must NOT see tenant B AI chats (privileged conversations).'
            );
        });
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_surveys_and_responses_are_isolated(): void
    {
        [$a, $b] = $this->twoTenants();

        $aSurveyId = $this->seedRow('surveys', $a, [
            'title' => 'A survey', 'type' => 'session', 'status' => 'active',
            'message_template' => 'hello {name}',
        ]);
        $bSurveyId = $this->seedRow('surveys', $b, [
            'title' => 'B survey', 'type' => 'lawsuit', 'status' => 'active',
            'message_template' => 'hi {name}',
        ]);

        $this->seedRow('survey_responses', $a, [
            'token' => \Illuminate\Support\Str::uuid(), 'survey_id' => $aSurveyId, 'status' => 'pending',
        ]);
        $this->seedRow('survey_responses', $b, [
            'token' => \Illuminate\Support\Str::uuid(), 'survey_id' => $bSurveyId, 'status' => 'pending',
        ]);
        $this->seedRow('survey_responses', $b, [
            'token' => \Illuminate\Support\Str::uuid(), 'survey_id' => $bSurveyId, 'status' => 'completed',
        ]);

        TenantContext::runAs($a, function () {
            $this->assertSame(1, Survey::count());
            $this->assertSame(1, SurveyResponse::count());
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(1, Survey::count());
            $this->assertSame(2, SurveyResponse::count());
        });
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_external_api_keys_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();
        $repo = app(SettingsRepository::class);

        TenantContext::runAs($a, fn () => $repo->set('linkedin_access_token', 'token-A', ['is_encrypted' => true]));
        TenantContext::runAs($b, fn () => $repo->set('linkedin_access_token', 'token-B', ['is_encrypted' => true]));

        $aKey = TenantContext::runAs($a, fn () => $repo->get('linkedin_access_token'));
        $bKey = TenantContext::runAs($b, fn () => $repo->get('linkedin_access_token'));

        $this->assertSame('token-A', $aKey);
        $this->assertSame('token-B', $bKey);
        $this->assertNotSame(
            $aKey,
            $bKey,
            'Marketing/Social API tokens MUST differ per tenant — posting under wrong firm = brand damage.'
        );
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_file_storage_is_tenant_prefixed(): void
    {
        $tenant = Tenant::create(['name' => 'FS', 'slug' => 'fs-'.uniqid(), 'status' => 'active']);

        TenantContext::runAs($tenant, function () use ($tenant) {
            $paths = [
                \App\Tenancy\Support\TenantStorage::path('marketing/content/assets'),
                \App\Tenancy\Support\TenantStorage::path('tasks/attachments'),
                \App\Tenancy\Support\TenantStorage::path('surveys/responses'),
                \App\Tenancy\Support\TenantStorage::path('chat/uploads'),
            ];

            foreach ($paths as $p) {
                $this->assertStringStartsWith(
                    "tenants/{$tenant->id}/",
                    $p,
                    'Every Module 6 upload path must be prefixed with tenants/{id}/'
                );
            }
        });
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_queued_job_restores_tenant_context(): void
    {
        // Sanity-check round-trip on a Module 6 job (CreateTaskJob now uses TenantAwareJob).
        $tenant = Tenant::create(['name' => 'Qj', 'slug' => 'qj-'.uniqid(), 'status' => 'active']);

        $job = TenantContext::runAs($tenant, fn () => new \App\Jobs\Tasks\CreateTaskJob([
            'task_name' => 'sample',
        ]));

        $this->assertSame($tenant->id, $job->tenantId, 'CreateTaskJob must capture dispatcher tenantId.');

        // Serialize / unserialize round-trip (simulates queue payload).
        $hydrated = unserialize(serialize($job));
        $this->assertSame($tenant->id, $hydrated->tenantId);

        // Middleware re-enters context.
        $observed = null;
        (new \App\Tenancy\Jobs\Middleware\RestoreTenantContext())
            ->handle($hydrated, function () use (&$observed) {
                $observed = TenantContext::currentId();
            });

        $this->assertSame($tenant->id, $observed);
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_cache_keys_are_tenant_scoped(): void
    {
        [$a, $b] = $this->twoTenants();
        $repo = app(SettingsRepository::class);

        TenantContext::runAs($a, fn () => $repo->set('mkt_flag', 'on'));
        TenantContext::runAs($b, fn () => $repo->set('mkt_flag', 'off'));

        // Warm both caches through the scoped reader.
        TenantContext::runAs($a, fn () => $repo->get('mkt_flag'));
        TenantContext::runAs($b, fn () => $repo->get('mkt_flag'));

        $this->assertNotNull(\Illuminate\Support\Facades\Cache::get("tenant_{$a->id}_settings"));
        $this->assertNotNull(\Illuminate\Support\Facades\Cache::get("tenant_{$b->id}_settings"));
        $this->assertNotSame(
            \Illuminate\Support\Facades\Cache::get("tenant_{$a->id}_settings"),
            \Illuminate\Support\Facades\Cache::get("tenant_{$b->id}_settings"),
            'Per-tenant cache values must be distinct.'
        );
    }

    // 11 ────────────────────────────────────────────────────────────────
    public function test_cross_tenant_reassignment_blocked_on_module_6_model(): void
    {
        [$a, $b] = $this->twoTenants();
        $taskId  = $this->seedRow('tasks', $a, ['task_name' => 'Immutable']);

        $task = TenantContext::runAs($a, fn () => Task::findOrFail($taskId));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant reassignment/i');

        $task->tenant_id = $b->id;
        $task->save();
    }

    // 12 ────────────────────────────────────────────────────────────────
    public function test_super_admin_without_tenancy_sees_everything(): void
    {
        [$a, $b] = $this->twoTenants();

        $this->seedRow('tasks', $a, ['task_name' => 'A1']);
        $this->seedRow('tasks', $a, ['task_name' => 'A2']);
        $this->seedRow('tasks', $b, ['task_name' => 'B1']);

        $this->seedRow('meeting_rooms', $a, ['title' => 'A Room', 'hall' => 'big']);
        $this->seedRow('meeting_rooms', $b, ['title' => 'B Room', 'hall' => 'small']);

        TenantContext::forget();

        $this->assertSame(3, Task::withoutTenancy()->count());
        $this->assertSame(2, MeetingRoom::withoutTenancy()->count());
    }

    // ──────────────────────────────────────────────────────────  helpers

    /** @return array{0: Tenant, 1: Tenant} */
    private function twoTenants(): array
    {
        return [
            Tenant::create(['name' => 'M6-A', 'slug' => 'm6-a-'.uniqid(), 'status' => 'active']),
            Tenant::create(['name' => 'M6-B', 'slug' => 'm6-b-'.uniqid(), 'status' => 'active']),
        ];
    }

    /**
     * Insert a minimal row directly via DB::table() — bypasses FK constraints
     * (disabled in setUp) and auto-fills any NOT NULL columns without a
     * default to a type-appropriate zero value. This keeps tests focused on
     * tenant isolation behavior, not on schema completeness.
     */
    private function seedRow(string $table, Tenant $tenant, array $extra = []): int
    {
        $row = array_merge([
            'tenant_id'  => $tenant->id,
            'created_at' => now(),
            'updated_at' => now(),
        ], $extra);

        // Fill every remaining NOT NULL column that has no default.
        $missing = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE
              FROM information_schema.COLUMNS
             WHERE TABLE_NAME = ?
               AND IS_NULLABLE = 'NO'
               AND COLUMN_DEFAULT IS NULL
               AND EXTRA NOT LIKE '%auto_increment%'
        ", [$table]);

        foreach ($missing as $col) {
            $name = $col->COLUMN_NAME;
            if (array_key_exists($name, $row)) {
                continue;
            }
            $row[$name] = in_array($col->DATA_TYPE, ['int','bigint','smallint','tinyint','decimal','float','double'])
                ? 0
                : (in_array($col->DATA_TYPE, ['date','datetime','timestamp']) ? now() : '');
        }

        return (int) DB::table($table)->insertGetId($row);
    }
}
