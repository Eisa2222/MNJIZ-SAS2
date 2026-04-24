<?php

declare(strict_types=1);

namespace Tests\Feature\SaasMigration;

use App\Models\SaasMigrationRun;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Services\Settings\SettingsRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 7 — data-migration & cutover command suite.
 *
 *   1. inventory detects tenant-owned tables
 *   2. backfill dry-run does not mutate data
 *   3. backfill fills null tenant_id
 *   4. migrate-settings writes encrypted tenant_settings
 *   5. migrate-files dry-run does not move files
 *   6. migrate-files moves files to tenant path + updates DB
 *   7. validate FAILS when null tenant_id exists
 *   8. validate PASSES after backfill
 *   9. commands are idempotent (re-run is a no-op)
 *  10. every migration run is logged to saas_migration_runs
 */
final class SaasMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Some of the backfill targets have FK-heavy schemas; disable FKs
        // in tests so we can insert minimal rows for the migration logic
        // to exercise.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_inventory_command_detects_tenant_owned_tables(): void
    {
        $exit = Artisan::call('saas:migration:inventory');

        $this->assertSame(0, $exit, 'inventory exits successfully');

        // Audit log written with tenant_owned_tables > 0.
        $run = SaasMigrationRun::where('command', 'saas:migration:inventory')->latest('id')->first();
        $this->assertNotNull($run, 'inventory records an audit row');
        $this->assertSame('success', $run->status);
        $this->assertGreaterThan(0, $run->summary['tenant_owned_tables']);
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_backfill_dry_run_does_not_mutate_data(): void
    {
        $default = $this->ensureDefaultTenant();

        // Insert a task row with NULL tenant_id (raw — bypasses the hook).
        $id = $this->insertTask(['task_name' => 'pre-backfill', 'tenant_id' => null]);

        $beforeNull = (int) DB::table('tasks')->whereNull('tenant_id')->count();

        $exit = Artisan::call('saas:migration:backfill-default-tenant', ['--dry-run' => true]);
        $this->assertSame(0, $exit);

        $afterNull = (int) DB::table('tasks')->whereNull('tenant_id')->count();
        $this->assertSame($beforeNull, $afterNull, 'dry-run must NOT mutate rows');

        // The row is still null.
        $this->assertNull(DB::table('tasks')->where('id', $id)->value('tenant_id'));
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_backfill_fills_null_tenant_id(): void
    {
        $default = $this->ensureDefaultTenant();

        $this->insertTask(['task_name' => 'A', 'tenant_id' => null]);
        $this->insertTask(['task_name' => 'B', 'tenant_id' => null]);

        $this->assertSame(2, (int) DB::table('tasks')->whereNull('tenant_id')->count());

        $exit = Artisan::call('saas:migration:backfill-default-tenant');
        $this->assertSame(0, $exit);

        $this->assertSame(0, (int) DB::table('tasks')->whereNull('tenant_id')->count(), 'no rows should be left null');
        $this->assertSame(2, (int) DB::table('tasks')->where('tenant_id', $default->id)->count());
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_migrate_settings_writes_encrypted_tenant_settings(): void
    {
        $default = $this->ensureDefaultTenant();

        // Insert a legacy settings row for the default tenant with one sensitive
        // and one plaintext value. sms_api_key is in settings_map as is_encrypted=true.
        DB::table('settings')->insert([
            'tenant_id'         => $default->id,
            'sms_api_key'       => 'SECRET_SMS_KEY_123',
            'office_name'       => 'Firm Default',
            'created_at'        => now(),
            'updated_at'        => now(),
        ] + $this->legacySettingsDefaults());

        $exit = Artisan::call('saas:migration:migrate-settings');
        $this->assertSame(0, $exit);

        // The tenant_settings row for sms_api_key should exist, be encrypted
        // at rest, and decrypt back to the original value via the repo.
        $row = DB::table('tenant_settings')
            ->where('tenant_id', $default->id)
            ->where('key', 'sms_api_key')
            ->first();

        $this->assertNotNull($row, 'sms_api_key must have been migrated');
        $this->assertTrue((bool) $row->is_encrypted, 'sms_api_key must be flagged encrypted');
        $this->assertNotSame('SECRET_SMS_KEY_123', $row->value, 'stored value must be ciphertext');
        $this->assertSame('SECRET_SMS_KEY_123', Crypt::decryptString($row->value));

        // Plaintext key migrated correctly too.
        $office = DB::table('tenant_settings')
            ->where('tenant_id', $default->id)
            ->where('key', 'office_name')
            ->first();
        $this->assertNotNull($office);
        $this->assertSame('Firm Default', $office->value);
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_migrate_files_dry_run_does_not_move_files(): void
    {
        Storage::fake('public');
        $default = $this->ensureDefaultTenant();

        Storage::disk('public')->put('attachments/foo.pdf', 'hello');

        DB::table('lawsuit_attachments')->insert([
            'tenant_id'       => $default->id,
            'lawsuit_id'      => 1,
            'attachment_name' => 'foo',
            'file_path'       => 'attachments/foo.pdf',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $exit = Artisan::call('saas:migration:migrate-files', ['--dry-run' => true]);
        $this->assertSame(0, $exit);

        // Source still exists.
        Storage::disk('public')->assertExists('attachments/foo.pdf');
        // Destination does NOT.
        Storage::disk('public')->assertMissing("tenants/{$default->id}/legal-affair/lawsuits/attachments/foo.pdf");
        // DB column unchanged.
        $this->assertSame('attachments/foo.pdf',
            DB::table('lawsuit_attachments')->value('file_path')
        );
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_migrate_files_moves_files_to_tenant_path(): void
    {
        Storage::fake('public');
        $default = $this->ensureDefaultTenant();

        Storage::disk('public')->put('attachments/bar.pdf', 'hello-bar');

        DB::table('lawsuit_attachments')->insert([
            'tenant_id'       => $default->id,
            'lawsuit_id'      => 1,
            'attachment_name' => 'bar',
            'file_path'       => 'attachments/bar.pdf',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $exit = Artisan::call('saas:migration:migrate-files');
        $this->assertSame(0, $exit);

        // Source removed.
        Storage::disk('public')->assertMissing('attachments/bar.pdf');
        // Destination present under tenant path.
        Storage::disk('public')->assertExists("tenants/{$default->id}/legal-affair/lawsuits/attachments/bar.pdf");
        // DB column updated to the *relative* path without the tenant prefix
        // (TenantStorage re-attaches at runtime).
        $this->assertSame(
            'legal-affair/lawsuits/attachments/bar.pdf',
            DB::table('lawsuit_attachments')->value('file_path')
        );
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_validate_fails_when_null_tenant_id_exists(): void
    {
        $this->ensureDefaultTenant();
        $this->insertTask(['task_name' => 'N', 'tenant_id' => null]);

        $exit = Artisan::call('saas:migration:validate');

        $this->assertSame(1, $exit, 'validate must return non-zero on broken invariant');

        $run = SaasMigrationRun::where('command', 'saas:migration:validate')->latest('id')->first();
        $this->assertSame('failed', $run->status);
        $this->assertGreaterThan(0, $run->summary['null_tenant_id']['total']);
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_validate_passes_after_backfill(): void
    {
        $this->ensureDefaultTenant();
        $this->insertTask(['task_name' => 'N', 'tenant_id' => null]);

        Artisan::call('saas:migration:backfill-default-tenant');

        $exit = Artisan::call('saas:migration:validate');
        $this->assertSame(0, $exit, 'validate should PASS after backfill');
    }

    // 9 ─────────────────────────────────────────────────────────────────
    public function test_commands_are_idempotent(): void
    {
        $default = $this->ensureDefaultTenant();

        $this->insertTask(['task_name' => 'Idem', 'tenant_id' => null]);

        Artisan::call('saas:migration:backfill-default-tenant');
        // First pass filled in tenant_id.
        $this->assertSame(0, (int) DB::table('tasks')->whereNull('tenant_id')->count());

        // Second pass is a no-op — row count and tenant_id stay the same.
        Artisan::call('saas:migration:backfill-default-tenant');
        $this->assertSame(0, (int) DB::table('tasks')->whereNull('tenant_id')->count());
        $this->assertSame(
            $default->id,
            (int) DB::table('tasks')->where('task_name', 'Idem')->value('tenant_id')
        );
    }

    // 10 ────────────────────────────────────────────────────────────────
    public function test_migration_runs_are_logged(): void
    {
        $this->ensureDefaultTenant();

        Artisan::call('saas:migration:inventory');
        Artisan::call('saas:migration:backfill-default-tenant', ['--dry-run' => true]);
        Artisan::call('saas:migration:backfill-default-tenant');
        Artisan::call('saas:migration:validate');

        $commands = SaasMigrationRun::query()->pluck('command')->toArray();
        $modes    = SaasMigrationRun::query()->pluck('mode')->toArray();

        $this->assertContains('saas:migration:inventory', $commands);
        $this->assertContains('saas:migration:backfill-default-tenant', $commands);
        $this->assertContains('saas:migration:validate', $commands);
        $this->assertContains('dry-run', $modes);
        $this->assertContains('real', $modes);

        // Every successful run has a finished_at timestamp.
        $this->assertEquals(
            0,
            SaasMigrationRun::whereIn('status', ['success', 'partial', 'failed'])
                ->whereNull('finished_at')->count(),
            'every closed run should have finished_at set'
        );
    }

    // ──────────────────────────────────────────────────────── helpers

    private function ensureDefaultTenant(): Tenant
    {
        return Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default', 'status' => 'active']
        );
    }

    /**
     * Raw insert into `tasks` that satisfies the table's NOT-NULL
     * constraints without the test having to care about them.
     */
    private function insertTask(array $override = []): int
    {
        $row = array_merge([
            'task_name'  => 'Test',
            'due_date'   => now()->toDateString(),
            'due_time'   => now()->format('H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ], $override);

        return (int) DB::table('tasks')->insertGetId($row);
    }

    /**
     * The legacy `settings` table has many NOT-NULL columns without
     * defaults. Fill them with zero/empty values so the test can insert
     * a row without caring about unrelated schema noise.
     */
    private function legacySettingsDefaults(): array
    {
        $cols = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE
              FROM information_schema.COLUMNS
             WHERE TABLE_NAME = 'settings'
               AND IS_NULLABLE = 'NO'
               AND COLUMN_DEFAULT IS NULL
               AND EXTRA NOT LIKE '%auto_increment%'
        ");

        $out = [];
        foreach ($cols as $c) {
            $out[$c->COLUMN_NAME] = in_array($c->DATA_TYPE, ['int','bigint','tinyint','smallint','decimal','float','double'])
                ? 0
                : '';
        }
        // Do not overwrite the values we explicitly pass in the test.
        unset($out['tenant_id'], $out['sms_api_key'], $out['office_name']);
        return $out;
    }
}
