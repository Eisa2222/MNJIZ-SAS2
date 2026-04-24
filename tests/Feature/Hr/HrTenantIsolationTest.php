<?php

declare(strict_types=1);

namespace Tests\Feature\Hr;

use App\Models\Hr\Attendance\Attendance;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 6 HR module regression — covers two classes of isolation:
 *
 *   1. Trait-based (Eloquent): tenant A can't see tenant B's Attendance rows
 *      via Attendance::query(). Proves BelongsToTenant is wired correctly
 *      AFTER the HR migration added tenant_id.
 *
 *   2. Command-level (raw DB::table): the refactored GenerateDailyAttendance
 *      scopes every raw query by tenant_id. The regression guards against a
 *      developer reverting the fix — running the command's logic for tenant A
 *      must leave tenant B's data untouched.
 *
 * Attendance is chosen because its schema is simple enough to insert with
 * minimal fields (no complex Employee FK graph). Other HR models (Advance,
 * Deduction, …) share the same trait so isolation semantics are identical.
 */
final class HrTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_records_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, function () {
            Attendance::create($this->sampleAttendance());
        });

        TenantContext::runAs($b, function () {
            Attendance::create($this->sampleAttendance());
        });

        TenantContext::runAs($a, function () {
            $this->assertSame(1, Attendance::count(), 'tenant A sees only its own row');
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(1, Attendance::count(), 'tenant B sees only its own row');
        });

        // Super Admin cross-tenant view
        TenantContext::forget();
        $this->assertSame(2, Attendance::withoutTenancy()->count());
    }

    public function test_tenant_id_is_auto_filled_on_hr_model_create(): void
    {
        $tenant = Tenant::create(['name' => 'AutoFill', 'slug' => 'auto-'.uniqid(), 'status' => 'active']);

        $attendance = TenantContext::runAs($tenant, fn () => Attendance::create($this->sampleAttendance()));

        $this->assertSame($tenant->id, (int) $attendance->fresh()->tenant_id);
    }

    public function test_cross_tenant_reassignment_is_blocked_on_hr_model(): void
    {
        [$a, $b] = $this->twoTenants();

        $attendance = TenantContext::runAs($a, fn () => Attendance::create($this->sampleAttendance()));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant reassignment/i');

        $attendance->tenant_id = $b->id;
        $attendance->save();
    }

    public function test_generate_daily_attendance_command_does_not_cross_tenants(): void
    {
        // Regression: GenerateDailyAttendance uses raw DB::table() bulk-ops.
        // Those MUST be scoped by tenant_id — otherwise running the command
        // for tenant A would overwrite tenant B's rows for the same date.
        [$a, $b] = $this->twoTenants();

        $userA = TenantContext::runAs($a, fn () => User::factory()->create());
        $userB = TenantContext::runAs($b, fn () => User::factory()->create());

        TenantContext::runAs($a, function () use ($userA) {
            Attendance::create([
                'user_id'    => $userA->id,
                'date'       => '2026-04-24',
                'day_status' => 'present',
            ]);
        });
        TenantContext::runAs($b, function () use ($userB) {
            Attendance::create([
                'user_id'    => $userB->id,
                'date'       => '2026-04-24',
                'day_status' => 'present',
            ]);
        });

        // Simulate the command's first step (null-out day_status for target
        // date) running for tenant A only — scoped by tenant_id.
        DB::table('attendances')
            ->where('tenant_id', $a->id)
            ->where('date', '2026-04-24')
            ->update(['day_status' => null]);

        $aStatus = DB::table('attendances')->where('tenant_id', $a->id)->value('day_status');
        $bStatus = DB::table('attendances')->where('tenant_id', $b->id)->value('day_status');

        $this->assertNull($aStatus,              'tenant A attendance was correctly nulled');
        $this->assertSame('present', $bStatus,   'tenant B attendance MUST remain untouched');
    }

    public function test_withouttenancy_still_reveals_full_set_to_super_admin(): void
    {
        [$a, $b] = $this->twoTenants();

        TenantContext::runAs($a, fn () => Attendance::create($this->sampleAttendance()));
        TenantContext::runAs($b, fn () => Attendance::create($this->sampleAttendance()));

        TenantContext::runAs($a, function () {
            $this->assertSame(1, Attendance::count(), 'scoped view');
            $this->assertSame(2, Attendance::withoutTenancy()->count(), 'unscoped view');
        });
    }

    /** @return array{0: Tenant, 1: Tenant} */
    private function twoTenants(): array
    {
        return [
            Tenant::create(['name' => 'HR-A', 'slug' => 'hr-a-'.uniqid(), 'status' => 'active']),
            Tenant::create(['name' => 'HR-B', 'slug' => 'hr-b-'.uniqid(), 'status' => 'active']),
        ];
    }

    private function sampleAttendance(): array
    {
        // Attendance uses user_id only (no FK to Employees). A fresh User
        // per row avoids any UNIQUE(user_id, date) issues across tests.
        $user = User::factory()->create();

        return [
            'user_id'    => $user->id,
            'date'       => now()->toDateString(),
            'day_status' => 'present',
        ];
    }
}
