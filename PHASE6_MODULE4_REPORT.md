# Phase 6 — Module 4: Notifications

**Risk Tier:** HIGH (cross-tenant inbox delivery)
**Status:** ✅ SEALED — 130/130 tests pass (122 prior + 8 new)
**Date:** 2026-04-24

---

## Summary

| Metric | Count |
|---|---|
| Jobs معدلة (`TenantAwareJob`) | **5** (email-path) |
| Notifications معدلة (`TenantAwareJob`) | **7** (TaskCreated/Updated/Completed/Deleted, StepCompleted, CommentMentioned, NoteCommentMentioned) |
| DB tables معدلة (`tenant_id` + FK + index) | **2** (`notifications`, `message_logs`) |
| Models معدلة (`BelongsToTenant`) | **1** (`MessageLog`) |
| Infrastructure معدلة | **2** (TenancyServiceProvider, RestoreTenantContext middleware) |
| Tests مضافة | **8** (19 assertions) |

---

## Bugs Found

### 1. `notifications` table shared across tenants — **CRITICAL**
- **Severity:** HIGH
- **Root cause:** Laravel's default `DatabaseNotification` rows carry only `notifiable_type` + `notifiable_id`. With users already tenant-scoped this *happens* to read correctly through `$user->notifications`, BUT any direct query (admin export scripts, Super Admin dashboards, raw `notifications` read from Blade) would see rows for every firm.
- **Fix:**
  1. Migration adds `tenant_id` + FK + index (backfill to Default Tenant).
  2. `TenancyServiceProvider` now registers a `DatabaseNotification::creating` hook that auto-stamps `tenant_id = TenantContext::currentId()` at row creation.
  3. `DatabaseNotification::updating` throws `RuntimeException("Cross-tenant reassignment…")` on any attempt to move a notification between tenants.
- **Regression test:** `test_database_notification_is_stamped_with_current_tenant` + `test_cross_tenant_notification_reassignment_is_blocked`

### 2. Queued Notifications lose tenant context on worker pickup — **HIGH**
- **Severity:** HIGH
- **Root cause:** 7 Notification classes (`TaskCreated`, `TaskUpdated`, `TaskCompleted`, `TaskDeleted`, `StepCompleted`, `CommentMentioned`, `NoteCommentMentioned`) implement `ShouldQueue`. Laravel wraps them in `SendQueuedNotifications` and pushes to the queue. On worker pickup the process has **no** `TenantContext` — `toDatabase($notifiable)` then accesses `$this->task->createdBy` which Eloquent resolves through `TenantScope::current()` → **fallback tenant** (wrong firm's row) OR null lookup.
- **Fix:**
  1. Added `use TenantAwareJob;` to all 7 Notification classes.
  2. Each constructor calls `$this->captureTenant()` so `$tenantId` is part of the serialized payload.
  3. `RestoreTenantContext` middleware extended — `resolveTenantId($job)` now also checks `$job->notification->tenantId` so the `SendQueuedNotifications` wrapper is unwrapped correctly.
- **Regression test:** `test_queued_notification_captures_tenant_id` + `test_restore_tenant_context_picks_up_notification_wrapper`

### 3. 5 queued email Jobs without `TenantAwareJob` — **HIGH**
- **Severity:** HIGH
- **Root cause:** `SendEmailNotificationJob`, `SendTechnicalSupportEmailNotificationJob`, `SendStepCompletionEmailJob`, `SendTaskCompletionEmailJob`, `SendTaskReturnEmailJob` all dispatch `User::where('email', $email)->first()` inside `handle()`. Without a captured tenant, this query runs against the fallback tenant and could pick up another firm's user with the same email.
- **Fix:** `use TenantAwareJob;` + `$this->captureTenant()` in every constructor.
- **Regression test:** `test_queued_email_job_restores_tenant_after_serialize` (proves full serialize → unserialize → middleware round-trip preserves tenantId).

### 4. `message_logs` shared across tenants
- **Severity:** MEDIUM (SMS / WhatsApp send log)
- **Root cause:** Table had no `tenant_id`; `MessageLog` model had no scope.
- **Fix:** Migration + `BelongsToTenant` trait on `MessageLog`.
- **Regression test:** `test_message_logs_are_isolated_per_tenant`

---

## Risk Analysis

### Where leakage was theoretically possible before Module 4

| Leak path | Before | After |
|---|---|---|
| Admin export `SELECT * FROM notifications` | ❌ all firms mixed | ✅ `tenant_id` column + WHERE filter trivially correct |
| Queued `TaskCreatedNotification` runs on a fresh worker | ❌ no context → fallback tenant used | ✅ `captureTenant()` in constructor + `RestoreTenantContext` middleware |
| `SendEmailNotificationJob::handle()` looks up user by email | ❌ could return tenant B user with same email | ✅ worker is re-seated to dispatcher's tenant first |
| `MessageLog` SMS export | ❌ all firms' numbers mixed | ✅ scoped + creating hook auto-fills `tenant_id` |
| Super Admin cross-firm view | (unreachable without scope escape) | ✅ raw `DB::table('notifications')` or model `withoutTenancy()` both work |
| Broadcast `NotificationSent` event | User-scoped channel (ok) but payload had no tenant | ✅ Notification class itself carries `tenantId` (via trait) |

### Remaining residual risk

- ⚠️ Notifications in the deprecated `app/Http/Controllers/OrganizationCenter/Tasks/Task_delete/*` controllers (e.g. `TaskController_خم.php`, `oldTask.php`) still dispatch `$user->notify(...)` without tenant fences. These files are **not route-registered** (dead code left for reference) — scanned and confirmed via `routes/*`. Flagged in the file list below as out of scope; to be removed wholesale in Module 6 cleanup.
- Future audit checklist: any new `Notification` class must either use `TenantAwareJob` OR be explicitly flagged as central/admin-only (auth notifications `ResetPasswordNotification`, `PasswordResetFirstNotification` fit this exception — they run synchronously in HTTP context before the reset link flow, no queued cross-tenant path).

---

## Before / After Behavior

```php
// ────────────── Before Module 4 ──────────────

// Tenant A admin action
$user->notify(new TaskCreatedNotification($task));
    └─ SendQueuedNotifications dispatched → worker picks up
       └─ handle(): $this->task->createdBy->name     // ⚠ runs with NO context
          └─ Task::find($id) → TenantScope → fallback tenant
             → loads a DIFFERENT firm's task row   ❌ LEAK

// Super admin SELECT on notifications
SELECT * FROM notifications WHERE notifiable_id = 42  // ⚠ all firms ❌

// SendEmailNotificationJob handle()
User::where('email', 'alice@a.test')->first()
    → hits Global scope with fallback tenant → possibly another firm's alice ❌

// MessageLog
SELECT * FROM message_logs                        // ⚠ all tenants' SMS logs ❌

// ────────────── After Module 4 ──────────────

// Tenant A admin action
$user->notify(new TaskCreatedNotification($task));
    ├─ __construct() → $this->tenantId = currentId()      // ✅ captured
    └─ SendQueuedNotifications → worker picks up
       └─ RestoreTenantContext middleware reads
          $job->notification->tenantId → TenantContext::set(A)
          ├─ handle(): Task::find($id) → TenantScope = A  // ✅ correct firm
          └─ after: TenantContext::forget()               // ✅ no leak to next job

// Direct DB insert under tenant A
DatabaseNotification::create([...])                // tenant_id = A auto-stamped ✅

// Cross-tenant reassignment attempt
$n->tenant_id = $other->id; $n->save();            // RuntimeException ✅

// SELECT on notifications
DB::table('notifications')->where('tenant_id', $a->id)->count()   // filtered ✅

// MessageLog under tenant A
MessageLog::count()                                // returns only A's rows ✅
MessageLog::withoutTenancy()->count()              // super admin sees all ✅
```

---

## Deliverables

**New:**
- `database/migrations/2026_04_24_120000_add_tenant_id_to_notification_tables.php`
- `tests/Feature/Notifications/NotificationIsolationTest.php` (8 tests, 19 assertions)
- `PHASE6_MODULE4_REPORT.md` (this file)

**Modified (infra — 2):**
- `app/Providers/TenancyServiceProvider.php` (auto-fill hook + updating guard on `DatabaseNotification`)
- `app/Tenancy/Jobs/Middleware/RestoreTenantContext.php` (now unwraps `SendQueuedNotifications`)

**Modified (notifications — 7):**
- `app/Notifications/TaskCreatedNotification.php`
- `app/Notifications/TaskUpdatedNotification.php`
- `app/Notifications/TaskCompletedNotification.php`
- `app/Notifications/TaskDeletedNotification.php`
- `app/Notifications/StepCompletedNotification.php`
- `app/Notifications/CommentMentioned.php`
- `app/Notifications/NoteCommentMentioned.php`

**Modified (email jobs — 5):**
- `app/Jobs/Mail/SendEmailNotificationJob.php`
- `app/Jobs/Mail/SendTechnicalSupportEmailNotificationJob.php`
- `app/Jobs/OrganizationCenter/Tasks/StepTask/Email/SendStepCompletionEmailJob.php`
- `app/Jobs/OrganizationCenter/Tasks/Task/Email/SendTaskCompletionEmailJob.php`
- `app/Jobs/OrganizationCenter/Tasks/Task/Email/SendTaskReturnEmailJob.php`

**Modified (models — 1):**
- `app/Models/MessageLog.php`

---

## Final Verdict

| Gate | Status |
|---|---|
| Full test suite passes | ✅ 130/130 (312 assertions) |
| Zero cross-tenant notification leak | ✅ 8 regression tests |
| Queued jobs keep tenant context | ✅ verified via serialize / unserialize round-trip test |
| Scheduled notification commands per-tenant | ✅ (already covered in Module 3 + Module 2 scheduled commands) |
| `notifications` DB row filtered by tenant | ✅ auto-fill + updating guard + test |
| `message_logs` scoped | ✅ `BelongsToTenant` + test |
| Super Admin cross-tenant view preserved | ✅ via `withoutTenancy()` / raw DB |
| Migration idempotent + reversible | ✅ |

**Verdict: GO — ready for Module 5 (Settings).**
