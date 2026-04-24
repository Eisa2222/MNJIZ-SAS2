<?php

declare(strict_types=1);

namespace Tests\Feature\LegalAffair;

use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\LegalAffair\Session\Session;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\Support\TenantStorage;
use App\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 6 Module 3 — LegalAffair HIGH-risk isolation suite.
 *
 * Legal records (lawsuits, sessions, opponents, POA) are privileged work
 * product; a cross-tenant leak here is a confidentiality / licensing hazard.
 * Every test below encodes one failure mode we must never ship:
 *
 *   1. Lawsuit records isolated per tenant
 *   2. Session records isolated per tenant
 *   3. Opponent records isolated per tenant
 *   4. tenant_id auto-filled on Eloquent create (hook works)
 *   5. Cross-tenant reassignment blocked (RuntimeException)
 *   6. Session reminder command does NOT cross tenants
 *   7. Legal attachment path is tenant-prefixed
 *   8. Super-admin withoutTenancy() sees all tenants
 *
 * Strategy: For tables with a heavy FK footprint (lawsuits, sessions),
 * tests disable FK checks in setUp so we can raw-insert minimal rows and
 * still prove the TenantScope filters correctly. Eloquent-only tests keep
 * FK checks enabled to prove the creating hook integrates cleanly.
 */
final class LegalAffairTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Lawsuit / Session have many required FK columns (project, court,
        // category, …) that are irrelevant to this suite's concern — tenant
        // isolation. Disable FK enforcement so we can insert minimal rows
        // via DB::table(). Each test still rolls back via RefreshDatabase.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_lawsuit_records_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        $this->insertLawsuit($a, 'LS-A-1');
        $this->insertLawsuit($a, 'LS-A-2');
        $this->insertLawsuit($b, 'LS-B-1');

        TenantContext::runAs($a, function () {
            $this->assertSame(2, Lawsuit::count());
            $this->assertTrue(Lawsuit::where('lawsuit_number', 'LS-A-1')->exists());
            $this->assertFalse(Lawsuit::where('lawsuit_number', 'LS-B-1')->exists());
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(1, Lawsuit::count());
            $this->assertTrue(Lawsuit::where('lawsuit_number', 'LS-B-1')->exists());
        });
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_session_records_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        $lsA = $this->insertLawsuit($a, 'SES-A');
        $lsB = $this->insertLawsuit($b, 'SES-B');

        $this->insertSession($a, $lsA, 'A-morning');
        $this->insertSession($a, $lsA, 'A-evening');
        $this->insertSession($b, $lsB, 'B-only');

        TenantContext::runAs($a, fn () => $this->assertSame(2, Session::count()));
        TenantContext::runAs($b, fn () => $this->assertSame(1, Session::count()));
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_opponent_records_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();

        $userA = $this->makeUser();
        $userB = $this->makeUser();

        TenantContext::runAs($a, function () use ($userA) {
            Opponent::create([
                'name'       => 'Opp-A-1',
                'type'       => 'individual',
                'created_by' => $userA->id,
            ]);
        });

        TenantContext::runAs($b, function () use ($userB) {
            Opponent::create([
                'name'       => 'Opp-B-1',
                'type'       => 'company',
                'created_by' => $userB->id,
            ]);
            Opponent::create([
                'name'       => 'Opp-B-2',
                'type'       => 'individual',
                'created_by' => $userB->id,
            ]);
        });

        TenantContext::runAs($a, function () {
            $this->assertSame(1, Opponent::count());
            $this->assertTrue(Opponent::where('name', 'Opp-A-1')->exists());
            $this->assertFalse(Opponent::where('name', 'Opp-B-1')->exists());
        });

        TenantContext::runAs($b, function () {
            $this->assertSame(2, Opponent::count());
        });
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_tenant_id_is_auto_filled_on_create(): void
    {
        $tenant = Tenant::create(['name' => 'Auto', 'slug' => 'auto-'.uniqid(), 'status' => 'active']);
        $user   = $this->makeUser();

        $opponent = TenantContext::runAs($tenant, fn () => Opponent::create([
            'name'       => 'AutoFill',
            'type'       => 'individual',
            'created_by' => $user->id,
        ]));

        $this->assertSame($tenant->id, $opponent->tenant_id, 'BelongsToTenant::creating hook must auto-fill tenant_id');
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_cross_tenant_reassignment_is_blocked_on_legal_record(): void
    {
        [$a, $b] = $this->twoTenants();
        $user    = $this->makeUser();

        $opponent = TenantContext::runAs($a, fn () => Opponent::create([
            'name'       => 'Immutable',
            'type'       => 'individual',
            'created_by' => $user->id,
        ]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant reassignment/i');

        $opponent->tenant_id = $b->id;
        $opponent->save();
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_session_reminder_command_does_not_cross_tenants(): void
    {
        // Freeze the clock so the command's "30-minute window" math is
        // reproducible across test runs regardless of how long setUp took.
        Carbon::setTestNow('2026-05-01 09:00:00');

        [$a, $b] = $this->twoTenants();

        $lsA = $this->insertLawsuit($a, 'CMD-A');
        $lsB = $this->insertLawsuit($b, 'CMD-B');

        // Fake session "due in 30 min" for tenant A — should be flagged as reminded.
        $when = now()->addMinutes(30);

        $sessionIdA = $this->insertSession($a, $lsA, 'CMD-A-due', [
            'session_date' => $when->format('Y-m-d'),
            'session_time' => $when->format('H:i:s'),
        ]);

        // Tenant B has its own due session at the same clock time — MUST NOT
        // be processed under tenant A's context.
        $sessionIdB = $this->insertSession($b, $lsB, 'CMD-B-due', [
            'session_date' => $when->format('Y-m-d'),
            'session_time' => $when->format('H:i:s'),
        ]);

        // Mock the reminder service so we can assert per-tenant call count
        // without hitting SMS/email gateways.
        $recordedTenants = [];
        $this->app->bind(
            \App\Services\SessionReminderService::class,
            fn () => new class ($recordedTenants) extends \App\Services\SessionReminderService {
                private array $recordedTenantsRef;
                public function __construct(array &$ref)
                {
                    // Skip parent constructor (Graph + Email service) — not needed here.
                    $this->recordedTenantsRef = &$ref;
                }
                public function sendAllReminders($session): array
                {
                    $this->recordedTenantsRef[] = $session->tenant_id;
                    return ['success' => true, 'sms' => ['recipients' => []], 'email' => ['recipients' => []]];
                }
                public function maybeSendObjectionReminder($session): void
                {
                    // noop
                }
            }
        );

        // Need real mock binding to capture array by reference.
        // Use service container singleton trick instead:
        $spy = new class extends \App\Services\SessionReminderService {
            public array $seen = [];
            public function __construct() {}
            public function sendAllReminders($session): array
            {
                $this->seen[] = $session->tenant_id;
                return ['success' => true, 'sms' => ['recipients' => []], 'email' => ['recipients' => []]];
            }
            public function maybeSendObjectionReminder($session): void {}
        };
        $this->app->instance(\App\Services\SessionReminderService::class, $spy);

        $this->artisan('sessions:send-reminders')->assertExitCode(0);

        // Every invocation must carry the row's own tenant_id (so perTenant
        // really entered the right context before the query).
        foreach ($spy->seen as $seenTenantId) {
            $this->assertContains(
                $seenTenantId,
                [$a->id, $b->id],
                'Reminder should have fired under a known tenant context.'
            );
        }

        // Both tenants' sessions were processed (one per tenant), not mixed.
        $this->assertSame(2, count($spy->seen), 'Exactly one reminder per tenant, not cross-tenant.');
        $this->assertContains($a->id, $spy->seen);
        $this->assertContains($b->id, $spy->seen);

        // Each session's reminder_sent_at is stamped; proving the update
        // happened under the correct tenant context.
        $this->assertNotNull(
            DB::table('sessions')->where('id', $sessionIdA)->value('reminder_sent_at'),
            'Tenant A session should be stamped'
        );
        $this->assertNotNull(
            DB::table('sessions')->where('id', $sessionIdB)->value('reminder_sent_at'),
            'Tenant B session should be stamped'
        );

        Carbon::setTestNow(); // unfreeze
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_legal_attachment_path_is_tenant_prefixed(): void
    {
        $tenant = Tenant::create(['name' => 'P', 'slug' => 'p-'.uniqid(), 'status' => 'active']);

        TenantContext::runAs($tenant, function () use ($tenant) {
            $paths = [
                TenantStorage::path('legal-affair/lawsuits/attachments'),
                TenantStorage::path('legal-affair/sessions/control'),
                TenantStorage::path('legal-affair/sessions/rules'),
                TenantStorage::path('legal-affair/power-of-attorneys'),
            ];

            foreach ($paths as $p) {
                $this->assertStringStartsWith(
                    "tenants/{$tenant->id}/legal-affair/",
                    $p,
                    "Legal attachment path must be prefixed with tenants/{id}/legal-affair/"
                );
            }
        });
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_without_tenancy_sees_all(): void
    {
        [$a, $b] = $this->twoTenants();
        $user    = $this->makeUser();

        TenantContext::runAs($a, fn () => Opponent::create(['name' => 'X', 'type' => 'individual', 'created_by' => $user->id]));
        TenantContext::runAs($b, fn () => Opponent::create(['name' => 'Y', 'type' => 'company', 'created_by' => $user->id]));

        TenantContext::forget();
        $this->assertSame(
            2,
            Opponent::withoutTenancy()->count(),
            'Super admin must be able to bypass scope for cross-tenant views.'
        );
    }

    // ────────────────────────────────────────────────────────── helpers

    /** @return array{0: Tenant, 1: Tenant} */
    private function twoTenants(): array
    {
        return [
            Tenant::create(['name' => 'LA-A', 'slug' => 'la-a-'.uniqid(), 'status' => 'active']),
            Tenant::create(['name' => 'LA-B', 'slug' => 'la-b-'.uniqid(), 'status' => 'active']),
        ];
    }

    private function makeUser(): User
    {
        return User::create([
            'name'                => 'User '.uniqid(),
            'email'               => 'u-'.uniqid().'@test.sa',
            'password'            => Hash::make('secret123'),
            'nationality'         => 'SA',
            'tour_completed'      => 1,
            'tour_task_completed' => 1,
        ]);
    }

    private function insertLawsuit(Tenant $t, string $num): int
    {
        return (int) DB::table('lawsuits')->insertGetId([
            'tenant_id'       => $t->id,
            'name'            => 'Lawsuit '.$num,
            'lawsuit_number'  => $num,
            'project_id'      => 0,
            'main_courts_id'  => 0,
            'regions_id'      => 0,
            'category_id'     => 0,
            'subcategory_id'  => 0,
            'lawsuit_type_id' => 0,
            'created_by'      => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    private function insertSession(Tenant $t, int $lawsuitId, string $name, array $override = []): int
    {
        $row = array_merge([
            'tenant_id'       => $t->id,
            'session_name'    => $name,
            'session_date'    => now()->toDateString(),
            'session_time'    => now()->format('H:i:s'),
            'project_id'      => 0,
            'lawsuit_id'      => $lawsuitId,
            'entity_ranks_id' => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $override);

        return (int) DB::table('sessions')->insertGetId($row);
    }
}
