<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Jobs\Mail\SendEmailNotificationJob;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\Concerns\TenantAwareJob;
use App\Tenancy\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Minimal stand-in for any real ShouldQueue notification. We use this
 * instead of TaskCreatedNotification so tests don't need a full Task FK
 * graph to prove tenant-context serialization behavior.
 */
class TestTenantAwareNotification extends LaravelNotification implements ShouldQueue
{
    use Queueable, TenantAwareJob;

    public function __construct(public string $msg = 'hello')
    {
        $this->captureTenant();
    }

    public function via($notifiable): array { return ['database']; }
    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage(['message' => $this->msg]);
    }
}

/**
 * Phase 6 Module 4 — Notifications HIGH-risk isolation suite.
 *
 * Notifications cross the queue boundary; tenant context is lost on worker
 * pickup unless explicitly restored. Email jobs sent for firm A must never
 * land in firm B's staff inbox. Every test below encodes one specific
 * failure mode we must never ship.
 *
 *   1. notifications table row isolated per tenant
 *   2. cross-tenant notification reassignment blocked
 *   3. queued notification keeps tenant context (captureTenant works)
 *   4. message_logs rows isolated per tenant
 *   5. queued email Job keeps tenant context after serialize/unserialize
 *   6. Notification::send receives only current tenant users
 *   7. super admin withoutTenancy() sees all notifications
 *   8. RestoreTenantContext middleware picks up notification wrapper
 */
final class NotificationIsolationTest extends TestCase
{
    use RefreshDatabase;

    // 1 ─────────────────────────────────────────────────────────────────
    public function test_database_notification_is_stamped_with_current_tenant(): void
    {
        [$a, $b] = $this->twoTenants();
        $userA = $this->makeUser();
        $userB = $this->makeUser();

        TenantContext::runAs($a, function () use ($userA) {
            DatabaseNotification::create([
                'id'              => (string) \Illuminate\Support\Str::uuid(),
                'type'            => 'App\\Notifications\\TaskCreatedNotification',
                'notifiable_type' => User::class,
                'notifiable_id'   => $userA->id,
                'data'            => json_encode(['tenant' => 'A', 'msg' => 'a']),
            ]);
        });

        TenantContext::runAs($b, function () use ($userB) {
            DatabaseNotification::create([
                'id'              => (string) \Illuminate\Support\Str::uuid(),
                'type'            => 'App\\Notifications\\TaskCreatedNotification',
                'notifiable_type' => User::class,
                'notifiable_id'   => $userB->id,
                'data'            => json_encode(['tenant' => 'B', 'msg' => 'b']),
            ]);
        });

        $this->assertSame(1, DB::table('notifications')->where('tenant_id', $a->id)->count());
        $this->assertSame(1, DB::table('notifications')->where('tenant_id', $b->id)->count());

        // Sanity: both rows exist, but no row carries a null tenant_id.
        $this->assertSame(0, DB::table('notifications')->whereNull('tenant_id')->count());
    }

    // 2 ─────────────────────────────────────────────────────────────────
    public function test_cross_tenant_notification_reassignment_is_blocked(): void
    {
        [$a, $b] = $this->twoTenants();
        $user    = $this->makeUser();

        $notif = TenantContext::runAs($a, fn () => DatabaseNotification::create([
            'id'              => (string) \Illuminate\Support\Str::uuid(),
            'type'            => 'TaskCreatedNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['msg' => 'x']),
        ]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant reassignment/i');

        $notif->tenant_id = $b->id;
        $notif->save();
    }

    // 3 ─────────────────────────────────────────────────────────────────
    public function test_queued_notification_captures_tenant_id(): void
    {
        $tenant = Tenant::create(['name' => 'Cap', 'slug' => 'cap-'.uniqid(), 'status' => 'active']);

        // The notification captures tenantId in its constructor via
        // TenantAwareJob::captureTenant(). If we're under tenant A when
        // instantiating, the serialized payload must carry A's id.
        $captured = TenantContext::runAs($tenant, function () {
            $notif = new TestTenantAwareNotification('x');
            return $notif->tenantId;
        });

        $this->assertSame(
            $tenant->id,
            $captured,
            'Notification constructor must snapshot TenantContext::currentId() so the worker can restore it.'
        );
    }

    // 4 ─────────────────────────────────────────────────────────────────
    public function test_message_logs_are_isolated_per_tenant(): void
    {
        [$a, $b] = $this->twoTenants();
        $userA = $this->makeUser();
        $userB = $this->makeUser();

        TenantContext::runAs($a, function () use ($userA) {
            \App\Models\MessageLog::create([
                'sender_id'    => $userA->id,
                'message_text' => 'A-only message',
                'platform'     => 'sms',
                'recipients'   => ['+9665111'],
            ]);
        });

        TenantContext::runAs($b, function () use ($userB) {
            \App\Models\MessageLog::create([
                'sender_id'    => $userB->id,
                'message_text' => 'B-only message',
                'platform'     => 'sms',
                'recipients'   => ['+9665222'],
            ]);
            \App\Models\MessageLog::create([
                'sender_id'    => $userB->id,
                'message_text' => 'B second message',
                'platform'     => 'whatsapp',
                'recipients'   => ['+9665333'],
            ]);
        });

        TenantContext::runAs($a, function () {
            $this->assertSame(1, \App\Models\MessageLog::count());
            $this->assertTrue(\App\Models\MessageLog::where('message_text', 'A-only message')->exists());
            $this->assertFalse(\App\Models\MessageLog::where('message_text', 'B-only message')->exists());
        });

        TenantContext::runAs($b, fn () => $this->assertSame(2, \App\Models\MessageLog::count()));
    }

    // 5 ─────────────────────────────────────────────────────────────────
    public function test_queued_email_job_restores_tenant_after_serialize(): void
    {
        $tenant = Tenant::create(['name' => 'Qj', 'slug' => 'qj-'.uniqid(), 'status' => 'active']);

        // 1. Dispatcher is under tenant → captureTenant stamps tenantId.
        $job = TenantContext::runAs($tenant, fn () => new SendEmailNotificationJob(
            ['email' => 'alice@a.test'],
            ['user_name' => 'Alice', 'title' => 't', 'description' => 'd', 'office_name' => 'LA-A'],
        ));

        $this->assertSame($tenant->id, $job->tenantId, 'Dispatcher tenant must be captured on construct.');

        // 2. Simulate worker pickup: serialize → unserialize across process.
        $hydrated = unserialize(serialize($job));
        $this->assertSame($tenant->id, $hydrated->tenantId, 'tenantId must survive queue payload round-trip.');

        // 3. Run through RestoreTenantContext middleware to prove context re-entry.
        $middleware = new \App\Tenancy\Jobs\Middleware\RestoreTenantContext();
        TenantContext::forget();

        $observedInsideHandle = null;
        $middleware->handle($hydrated, function () use (&$observedInsideHandle) {
            $observedInsideHandle = TenantContext::currentId();
        });

        $this->assertSame(
            $tenant->id,
            $observedInsideHandle,
            'RestoreTenantContext must set TenantContext before the job runs.'
        );

        // 4. Context cleared after job (prevents leak to next worker cycle).
        // `currentId()` may reactivate the fallback default tenant if one
        // exists in the DB; what we need to prove is that $current was
        // forgotten (hasTenant=false before any lazy re-resolve).
        $this->assertFalse(
            TenantContext::hasTenant(),
            'Middleware must forget() the concrete current tenant after the job.'
        );
    }

    // 6 ─────────────────────────────────────────────────────────────────
    public function test_notification_send_only_targets_current_tenant_users(): void
    {
        [$a, $b] = $this->twoTenants();

        // Users created inside each tenant context get tenant-scoped.
        $aliceA = TenantContext::runAs($a, fn () => $this->makeUser('a-in-'.uniqid()));
        $bobA   = TenantContext::runAs($a, fn () => $this->makeUser('a-in-'.uniqid()));
        $evaB   = TenantContext::runAs($b, fn () => $this->makeUser('b-in-'.uniqid()));

        Notification::fake();

        // Simulate "send to all users of tenant A". A leaky query would
        // pick up evaB too → fake assertion would catch it.
        TenantContext::runAs($a, function () {
            $tenantUsers = User::query()->get();  // TenantScope applied
            Notification::send($tenantUsers, new TestTenantAwareNotification('to-A-only'));
        });

        Notification::assertSentTo($aliceA, TestTenantAwareNotification::class);
        Notification::assertSentTo($bobA,   TestTenantAwareNotification::class);
        Notification::assertNotSentTo($evaB, TestTenantAwareNotification::class,
            'Tenant B user MUST NOT receive tenant A notification.');
    }

    // 7 ─────────────────────────────────────────────────────────────────
    public function test_super_admin_without_tenancy_sees_all_notifications(): void
    {
        [$a, $b] = $this->twoTenants();
        $user    = $this->makeUser();

        TenantContext::runAs($a, fn () => DatabaseNotification::create([
            'id'              => (string) \Illuminate\Support\Str::uuid(),
            'type'            => 'X',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['a' => 1]),
        ]));

        TenantContext::runAs($b, fn () => DatabaseNotification::create([
            'id'              => (string) \Illuminate\Support\Str::uuid(),
            'type'            => 'X',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['b' => 1]),
        ]));

        TenantContext::forget();

        // Super admin view — raw DB query aggregates across all tenants.
        $this->assertSame(
            2,
            DB::table('notifications')->count(),
            'Super admin explicit cross-tenant view must be possible.'
        );
    }

    // 8 ─────────────────────────────────────────────────────────────────
    public function test_restore_tenant_context_picks_up_notification_wrapper(): void
    {
        $tenant = Tenant::create(['name' => 'Wrap', 'slug' => 'wrap-'.uniqid(), 'status' => 'active']);

        // Laravel wraps queued notifications in SendQueuedNotifications;
        // our middleware must read $job->notification->tenantId.
        $notif = TenantContext::runAs($tenant, fn () => new TestTenantAwareNotification('w'));

        // Shape: an object exposing a public $notification property (like
        // SendQueuedNotifications). tenantId is NOT on the wrapper itself.
        $wrapper = new class ($notif) {
            public $notification;
            public function __construct($n) { $this->notification = $n; }
        };

        $middleware = new \App\Tenancy\Jobs\Middleware\RestoreTenantContext();
        TenantContext::forget();

        $observed = null;
        $middleware->handle($wrapper, function () use (&$observed) {
            $observed = TenantContext::currentId();
        });

        $this->assertSame(
            $tenant->id,
            $observed,
            'Middleware must unwrap SendQueuedNotifications and pick tenantId from $job->notification.'
        );
    }

    // ────────────────────────────────────────────────────────── helpers

    /** @return array{0: Tenant, 1: Tenant} */
    private function twoTenants(): array
    {
        return [
            Tenant::create(['name' => 'NT-A', 'slug' => 'nt-a-'.uniqid(), 'status' => 'active']),
            Tenant::create(['name' => 'NT-B', 'slug' => 'nt-b-'.uniqid(), 'status' => 'active']),
        ];
    }

    private function makeUser(?string $suffix = null): User
    {
        $suffix ??= uniqid();
        return User::create([
            'name'                => 'U-'.$suffix,
            'email'               => 'u-'.$suffix.'-'.uniqid().'@test.sa',
            'password'            => Hash::make('secret123'),
            'nationality'         => 'SA',
            'tour_completed'      => 1,
            'tour_task_completed' => 1,
        ]);
    }
}
