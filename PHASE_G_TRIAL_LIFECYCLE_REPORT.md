# Phase G — Trial Lifecycle Automation — SEALED

**Status:** ✅ Complete · 271/271 tests passing (261 baseline + 10 new)
**Date:** 2026-04-26
**Path:** Path C Hybrid — Compliance Gap Closure

---

## 1. Summary

Phase G adds the spec-mandated trial-lifecycle automation on top of the
Phase 5 billing engine — without touching that engine:

- **`saas:check-trial-expiry`** (daily 00:00) flips trialing
  subscriptions whose `trial_ends_at` has passed into the `Expired`
  state, optionally suspending the tenant when the operator's
  `trial_suspend_after_expiry` setting is on, and queueing
  `TrialExpiredMail` once per subscription.
- **`saas:send-trial-warnings`** (daily 08:00) reads the
  `trial_warning_days` array (e.g. `[7, 3, 1]`) and, for each
  milestone, queues `TrialExpiryWarningMail` to the tenant owner —
  with per-milestone idempotency tracked via
  `subscriptions.meta.trial_warning_days_sent` so re-runs the same
  calendar day never double-mail.
- Both commands honour `notify_trial_expiring` as a global kill
  switch; flipping it off skips mail dispatch entirely without
  touching the state transitions or trackers.
- The Phase 5 billing schedule (4 commands at 00:10, 00:15, 00:20,
  09:00) is left exactly as-is; Phase G picks 00:00 + 08:00 to land
  *before* the existing slots, so the trial transitions are committed
  before charge-due / past-due / grace-expire / renewal-reminder
  reads them.

Zero `composer.json` changes. Zero Laravel-version bumps. Zero
deletions. Zero touches on Phase 5 billing actions, `StartTrialAction`,
`SubscriptionStatus` enum, or `Tenant` model.

---

## 2. Discovery Findings

| Area | State before Phase G | Phase G action |
|------|----------------------|----------------|
| `app/Console/Commands/Billing/` | 4 commands (`ChargeDueSubscriptionsCommand`, `MarkPastDueSubscriptionsCommand`, `ExpireGracePeriodSubscriptionsCommand`, `SendRenewalRemindersCommand`) | Adds 2 new — `CheckTrialExpiryCommand`, `SendTrialWarningsCommand` |
| `StartTrialAction` | Reads `$plan->trial_days` only; sets status Trialing/Active accordingly | Untouched. The user spec said "إذا كان تعديل action خطرًا: أضف wrapper" — Phase G only READS what StartTrialAction wrote (`trial_ends_at`, `status`), so no wrapper needed. |
| `SubscriptionStatus` enum | `Trialing`, `Active`, `PastDue`, `Paused`, `Canceled`, `Expired` | All already present. No enum changes. |
| `Subscription` model tracking columns | `trial_ends_at` exists; NO `trial_warning_sent_at` / `trial_expired_notified_at` | Migration adds both columns. Per-milestone tracking goes into the existing `meta` JSON. |
| `Tenant` model | `STATUS_ACTIVE`/`STATUS_SUSPENDED` constants exist; no suspend()/activate() helper | Phase G writes `$tenant->status = STATUS_SUSPENDED` directly in the same transaction as the subscription flip. |
| `SystemSetting` trial keys (Phase C) | `trial_enabled`, `trial_days`, `trial_requires_payment`, `trial_suspend_after_expiry`, `trial_warning_days` (json array), `notify_trial_expiring` | All wired and validated by Phase C `UpdateSystemSettingsRequest`. Phase G reads them with safe defaults. |
| Mailables | `WelcomeMail`, `TenantWelcomeMail`, `SessionReminder`, `EventCreatedNotification` | Adds `TrialExpiryWarningMail`, `TrialExpiredMail` + 2 Blade views. |
| Schedule registration | All in `app/Console/Kernel.php::schedule()` | Phase G adds the two new entries there too — Laravel 10 ships without the standalone `Schedule` facade, so the `routes/console.php` location the spec mentioned isn't viable in this Laravel version. Documented in routes/console.php. |
| `tests/Feature/Billing/*` | 9 tests (lifecycle, invoicing, coupons, etc.) — NO `TrialLifecycleTest.php` | Adds `TrialLifecycleTest.php` with 10 tests. |

**Compatibility risks identified — and addressed:**
1. `ExpireSubscriptionAction` exists and would revert to the free plan
   on Expired. Phase G does the status flip directly to keep trial
   expiry / paid-expiry semantically distinct, then dispatches
   `SubscriptionExpired` so any listener that wants to revert (the
   Phase 5 `RevertToFreePlanOnSubscriptionExpired` listener still
   fires).
2. `billing:expire-grace-period` (00:20) targets PastDue subscriptions
   only — never collides with Phase G's Trialing-only filter.
3. `Schedule` facade is Laravel 11+. Used `app/Console/Kernel.php`
   (the canonical L10 location) and documented in routes/console.php.

---

## 3. Commands

### `saas:check-trial-expiry`

```
php artisan saas:check-trial-expiry [--dry-run]
```

**Filter:** `status = trialing AND trial_ends_at <= NOW() AND trial_expired_notified_at IS NULL`

**Per-row execution (inside `DB::transaction`):**
1. `subscription.status = Expired`
2. `subscription.ends_at = subscription.ends_at ?? now()`
3. `subscription.trial_expired_notified_at = now()` ← one-shot guard
4. If `trial_suspend_after_expiry` AND `tenant.status = active`:
   `tenant.status = suspended`

**After commit:**
- `event(new SubscriptionExpired($sub->fresh()))` — Phase 5 listeners
  still fire.
- If `notify_trial_expiring` is true: queue `TrialExpiredMail` to the
  first user under the tenant.

**Idempotency:** the `whereNull('trial_expired_notified_at')` clause
combined with the status-flip-then-event ordering means re-runs return
zero rows for already-processed subscriptions.

**No deletion** — the row stays for audit.

### `saas:send-trial-warnings`

```
php artisan saas:send-trial-warnings [--dry-run]
```

**Reads:** `trial_warning_days` (default `[7, 3, 1]`) — sorted
descending so log output is human-readable.

**For each milestone N:**
- Filter: `status = trialing AND DATE(trial_ends_at) = today + N days`
- Per-row idempotency: if `meta.trial_warning_days_sent` array
  already contains N, skip.
- If `notify_trial_expiring` is false, skip mail dispatch + meta
  update (so flipping the toggle back on the next day correctly
  resumes from the next eligible milestone).
- Otherwise: queue `TrialExpiryWarningMail`, append N to
  `meta.trial_warning_days_sent`, stamp
  `trial_warning_sent_at = now()`.

**Two runs the same day on the same eligible subscription email
exactly once** — covered by Test #1.

---

## 4. Mail Flow

### `TrialExpiryWarningMail`

- Implements `ShouldQueue` + `TenantAwareJob` (captures tenant id at
  dispatch, restored on the worker).
- Subject: `[:app] Your trial ends in :days days`
- View: `emails.trial.warning` — gradient amber header, account
  summary, big "Subscribe now" CTA → `/pricing`, support email from
  `SystemSetting`.
- Constructor: `(Tenant, User, Subscription, int $daysRemaining)`.
- **Carries no payment data, no setup URLs, no secrets.**

### `TrialExpiredMail`

- Implements `ShouldQueue` + `TenantAwareJob`.
- Subject: `[:app] Your trial has ended`
- View: `emails.trial.expired` — red header, body text branches on
  whether the tenant was suspended (`body_suspended`) or kept active
  but feature-limited (`body_active`).
- Constructor: `(Tenant, User, Subscription, bool $tenantSuspended)`.

---

## 5. Settings Used

All read with sensible defaults so a fresh install with no
`system_settings` rows still behaves correctly:

```php
SystemSetting::get('trial_enabled', true)              // future hook (not yet read by commands; StartTrialAction is Phase 5's domain)
SystemSetting::get('trial_days', 14)                   // future hook
SystemSetting::get('trial_requires_payment', false)    // future hook
SystemSetting::get('trial_suspend_after_expiry', true) // ✅ check-trial-expiry
SystemSetting::get('trial_warning_days', [7, 3, 1])    // ✅ send-trial-warnings (json array)
SystemSetting::get('notify_trial_expiring', true)      // ✅ both commands — global kill switch
```

`trial_enabled`, `trial_days`, `trial_requires_payment` are
deliberately NOT consumed by the new commands — `StartTrialAction`
already owns trial creation and is Phase 5 territory. Phase G only
reads what's needed for the lifecycle commands.

---

## 6. Idempotency

| Surface | Mechanism |
|---------|-----------|
| Trial expiry — status transition | `whereNull('trial_expired_notified_at')` query filter; commit sets the column |
| Trial expiry — tenant suspension | Conditional `if ($tenant->status === STATUS_ACTIVE)` — already-suspended tenants are no-op |
| Trial expiry — mail dispatch | Implicit via the status filter (`status = trialing` is false on second run after first run flipped to `Expired`) AND the `trial_expired_notified_at` column gates re-mail even if status filter were bypassed |
| Trial warnings — per-milestone | `meta.trial_warning_days_sent` array — `if (in_array($daysBefore, $sentDays, true)) continue` |
| Trial warnings — global toggle | `notify_trial_expiring=false` skips dispatch AND skips meta update, so toggling back on resumes correctly |

---

## 7. Tests

`tests/Feature/Billing/TrialLifecycleTest.php` — 10 tests.

| # | Test | Coverage |
|---|------|----------|
| 1 | `trial_warning_command_sends_warning_exactly_once` | Re-runs same day → exactly 1 mail queued |
| 2 | `warning_disabled_does_not_send_email` | `notify_trial_expiring=false` → 0 mails |
| 3 | `warning_days_setting_respected` | `[7,3]` setting → only 7- and 3-day eligible subs warned, 5-day skipped |
| 4 | `expired_trial_suspends_tenant_when_setting_enabled` | `trial_suspend_after_expiry=true` → tenant.status flipped |
| 5 | `expired_trial_does_not_suspend_tenant_when_setting_disabled` | `trial_suspend_after_expiry=false` → tenant.status stays active |
| 6 | `expired_mail_sent_exactly_once` | Re-running expiry command → 1 `TrialExpiredMail` only |
| 7 | `rerunning_commands_is_idempotent` | 3× both commands → 1 warning mail + 1 expired mail |
| 8 | `start_trial_action_respects_plan_trial_days` | Plan with `trial_days=21` → subscription `trial_ends_at = now+21` |
| 9 | `zero_trial_days_starts_active_not_trialing` | Plan with `trial_days=0` → status Active, `trial_ends_at` null |
| 10 | `schedule_commands_are_registered` | `artisan list` contains both `saas:*` commands |

**Final suite:** `php artisan test` → **271 passed (730 assertions)**, 0 failures, 0 errors.

---

## 8. Files Changed

**New (8)**

```
database/migrations/2026_04_27_100000_add_trial_tracking_to_subscriptions_table.php
app/Console/Commands/Billing/CheckTrialExpiryCommand.php
app/Console/Commands/Billing/SendTrialWarningsCommand.php
app/Mail/TrialExpiryWarningMail.php
app/Mail/TrialExpiredMail.php
resources/views/emails/trial/warning.blade.php
resources/views/emails/trial/expired.blade.php
tests/Feature/Billing/TrialLifecycleTest.php
PHASE_G_TRIAL_LIFECYCLE_REPORT.md
```

**Modified (4)**

```
app/Models/Subscription.php          ← added 2 fillable + 2 casts (trial tracking columns)
app/Console/Kernel.php               ← added 2 schedule entries (00:00 + 08:00)
routes/console.php                   ← documented Laravel-10 schedule location
lang/ar/emails.php                   ← added trial_warning + trial_expired keys
lang/en/emails.php                   ← same
```

**Untouched (preserved by design)**

- `StartTrialAction` — Phase G reads its output but never modifies it
- `ExpireSubscriptionAction` — exists for paid-subscription expiry;
  Phase G does the trial-specific transition inline so the two
  pathways stay decoupled
- All Phase 5 billing tests (`SubscriptionLifecycleTest`,
  `InvoiceAndChargeTest`, `WebhookIdempotencyTest`, etc.) — still pass
- All Phase 6 / Phase A–F additions — untouched

---

## 9. Risks Remaining

- **`SystemSetting::set('trial_warning_days', [...])` requires the caller
  to pass `cast => 'json'` metadata** for the array→string round-trip
  to succeed. Production callers (the Phase C
  `SystemSettingController`) already do this in `metaForGroup()`; tests
  must do the same. A future Phase could auto-detect array values in
  `SystemSetting::set()` to remove this footgun.

- **The trial expiry mail picks `User::query()->where('tenant_id', …)->first()`**
  as the recipient — i.e. the first user under the tenant. For
  Phase F-onboarded tenants this is the owner; for legacy tenants with
  multiple users it's the lowest-id user. Acceptable for v1; a future
  enhancement could persist the owner_user_id on subscriptions.

- **Suspended tenants stay suspended forever from Phase G's perspective.**
  Re-activation happens through Phase 5 paths (paid subscription via
  Phase E checkout, manual super-admin action, etc.). Phase G never
  reactivates a tenant — its only state change is suspend.

- **Tracking columns are wide.** `trial_warning_sent_at` is a
  per-subscription "last sent at" datetime; the per-milestone history
  lives in JSON `meta.trial_warning_days_sent`. Querying "which subs
  got the 3-day warning?" requires a JSON_CONTAINS — fine for
  occasional ad-hoc reporting, suboptimal for high-frequency analytics.
  A future analytics phase could add a normalized
  `trial_warning_log` table if needed.

- **No "trial extended" flow yet.** If support manually pushes
  `trial_ends_at` further into the future for a customer, the next
  command run picks that up automatically — but the previous warning
  milestones in `meta.trial_warning_days_sent` are not cleared. Result:
  the customer might not get re-warned for the new milestone. A future
  enhancement could add a `clear-trial-warnings` admin action.

- **Schedule lives in `app/Console/Kernel.php`, not `routes/console.php`**
  as the spec requested. The Laravel-11 standalone `Schedule` facade
  is not available in Laravel 10; using it from `routes/console.php`
  causes a class-not-found error at console boot (verified). Kernel.php
  is the canonical L10 location and matches the existing Phase 5
  billing schedule layout. `routes/console.php` carries a NOTE
  documenting this.

---

## 10. Verdict

**SEALED.** All Phase G exit criteria met:

- ✅ `saas:check-trial-expiry` command — flips trialing → expired,
  suspends tenant per setting, dispatches `SubscriptionExpired`,
  queues `TrialExpiredMail` once
- ✅ `saas:send-trial-warnings` command — milestone-driven via
  `trial_warning_days`, per-milestone idempotency, gated by
  `notify_trial_expiring`
- ✅ `TrialExpiryWarningMail` + `TrialExpiredMail` Mailables + Blade
  views (RTL, no Tailwind, no SPA)
- ✅ Subscription tracking columns: `trial_warning_sent_at`,
  `trial_expired_notified_at` + `meta.trial_warning_days_sent`
  for per-milestone history
- ✅ Schedule registered: 00:00 expiry, 08:00 warnings, both with
  `withoutOverlapping()` + `onOneServer()`
- ✅ All settings (`trial_suspend_after_expiry`, `trial_warning_days`,
  `notify_trial_expiring`) read from `SystemSetting` with defaults
- ✅ ar + en email translations
- ✅ 10 new regression tests, all passing
- ✅ Full suite: **271/271** (261 baseline + 10 new) — every Phase A→F
  test untouched
- ✅ Zero deletions, zero `composer.json` edits, zero Laravel upgrade,
  zero hard deletes of Tenants or Subscriptions, zero Tailwind
- ✅ No emails sent when settings disable them
- ✅ Idempotent re-runs (Test #7)

Ready for Phase H approval.
