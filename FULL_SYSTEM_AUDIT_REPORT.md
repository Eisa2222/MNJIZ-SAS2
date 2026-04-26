# MNJIZ SaaS — Full System Audit Report

**Audit Date:** 2026-04-26
**Audit Mode:** Read-only — no code modifications, no refactors, no deletions
**Auditor Role:** Principal QA Architect + Laravel Auditor + Code Compliance Inspector
**Path:** Path C Hybrid — Compliance Gap Closure (Phases A → H)
**Reference Sources:** `saas-prompt (2).md`, `MY_CODING_STYLE (1).md`

---

## 1. Summary

The MNJIZ SaaS codebase has completed 8 sealed compliance phases (A → H)
on top of the pre-existing single-tenant → multi-tenant SaaS conversion
(Phases 0 → 9). All work was committed and pushed to the `saas-initial`
branch on `saas/MNJIZ-SAS2`.

This audit verifies:
- ✅ The full PHPUnit suite passes (**280/280**, 762 assertions, 0 failures, 0 errors).
- ✅ All 8 Phase A → H artefacts are present, registered, and reachable.
- ✅ The codebase honours both reference documents (`saas-prompt (2).md` for compliance, `MY_CODING_STYLE (1).md` for engineering style).
- ✅ Security posture is sound — no plaintext secrets in logs, mails, or HTML; signed URLs with hashed tokens; PCI-safe payments.
- ✅ Tenancy isolation is preserved — `BelongsToTenant` correctly applied; central-only models correctly excluded; scope-bypass calls only in commands/jobs/webhooks.
- ✅ Billing is atomic and idempotent — coupon counter via `DB::increment`, checkout idempotent on `gateway_payment_id`, trial commands re-runnable.
- 🟡 One **non-blocking** performance finding: the public landing pricing grid lacks `with(['features'])` eager-load on the `Plan` model (acceptable for low cardinality but flagged for future).

**Final verdict: 🟢 PRODUCTION READY** — with one yellow advisory.

---

## 2. Test Results

### Full suite

```bash
$ docker exec mnjiz-app php artisan config:clear && view:clear && route:clear && php artisan test
…
Tests:    280 passed (762 assertions)
Duration: 590.95s
```

| Metric | Value |
|--------|-------|
| Total tests | **280** |
| Total assertions | **762** |
| Failures | **0** |
| Errors | **0** |
| Skipped | **0** |
| Risky | **0** |
| Duration | ~9.85 min |

### Test growth across Path C phases

| Phase | New tests file(s) | New tests | Cumulative |
|-------|-------------------|----------:|-----------:|
| Pre-Path-C baseline | (Phase 0–9) | — | 183 |
| Phase A | `DomainResolutionTest` | 10 | 193 |
| Phase B | `SuperAdminCompatibilityTest` | 8 | 201 |
| Phase C | `SystemSettingsBridgeTest` | 12 | 213 |
| Phase D | `LandingContentTest` | 11 | 224 |
| Phase E | `CouponServiceComplianceTest` (11) + `CheckoutComplianceTest` (10) | 21 | 245 |
| Phase F | `CreateTenantJobTest` (10) + `TenantPasswordSetupTest` (6) | 16 | 261 |
| Phase G | `TrialLifecycleTest` | 10 | 271 |
| Phase H | `CheckSubscriptionTest` | 9 | **280** |

**Net Path C delta:** +97 regression tests (53 % growth from 183 to 280).
Pre-existing Phase 5 billing and Phase 6 isolation tests all still pass.

---

## 3. Phase Compliance

| Phase | Goal | Status | Evidence |
|-------|------|:------:|----------|
| **A** | Subdomain + custom-domain resolver | ✅ | `Domain` model + `domains` table; `TenantResolver` (5 methods); `InitializeTenantByDomainOrSubdomain`; `PreventAccessFromCentralDomains`; 10 tests; commit `4e213a0` |
| **B** | Super Admin compatibility layer | ✅ | `SuperAdmin` extends `Admin`; `super_admin` guard + provider + broker in `config/auth.php`; `routes/super-admin.php` mirroring `routes/admin.php`; `RedirectIfAuthenticatedSuperAdmin` + `RedirectAdminToSuperAdmin` middleware; 8 tests; commit `6c3d0a0` |
| **C** | System Settings Bridge | ✅ | `system_settings` migration; `SystemSetting` model with cache; `SystemSettingsService` bridge to `central_settings`; `ApplySystemSettings` middleware overriding `mail.*` + `services.moyasar.*`; 6-tab Super Admin UI; 12 tests; commit `f206219` |
| **D** | Landing content dynamic | ✅ | `landing_features` + `landing_faqs` tables + models with `active`/`ordered` scopes; CRUD with SortableJS; updated `Marketing\LandingController` injecting features/faqs/hero/footer; ar/en translations; 11 tests; commit `f6afe49` |
| **E** | Checkout + Coupon compliance | ✅ | `CheckoutController` (show/applyCoupon/callback/success/failure/accountPending); `MoyasarService` façade; `CouponService` (array-returning validate, atomic apply); `coupon_uses` table; admin coupon hardening; 21 tests; commit `57a7c35` |
| **F** | Secure tenant onboarding | ✅ | `CreateTenantJob` (queue, idempotent); `TenantPasswordSetupService` (signed URL + sha256 hashed tokens, 48h expiry); `TenantWelcomeMail`; password setup controller + view; feature flag `tenancy.signup.use_setup_link`; 16 tests; commit `a19ab75` |
| **G** | Trial lifecycle automation | ✅ | `saas:check-trial-expiry` (00:00 daily); `saas:send-trial-warnings` (08:00 daily); 2 mailables + views; 2 tracking columns + meta-tracker for per-milestone idempotency; 10 tests; commit `36f2c87` |
| **H** | CheckSubscription middleware | ✅ | `CheckSubscription` middleware with exhaustive `match`; `tenant.suspended` view + route; `check.subscription` alias; 9 tests; commit `3dc2744` |

**Phase coverage verdict:** 🟢 8/8 phases sealed, all with regression
tests, all with sealed reports (`PHASE_*_REPORT.md`).

---

## 4. SaaS Prompt Compliance

| Spec requirement | Status | Notes |
|------------------|:------:|-------|
| `system_settings` central table | ✅ | Migration `2026_04_25_110000`; static `get/set/setMany/forgetCache/allAsKeyValue` API; 24h cache |
| Sensitive keys encrypted at rest | ✅ | `mail_password`, `moyasar_secret_key`, `moyasar_webhook_secret` round-trip via `Crypt` |
| Runtime override of `mail.*` + `services.moyasar.*` | ✅ | `ApplySystemSettings` middleware mounted on admin/super-admin/checkout groups |
| `CheckoutController` with show/applyCoupon/callback/success | ✅ | All four methods + companion `failure`/`accountPending` |
| `MoyasarService` createPayment/getPayment/refundPayment | ✅ | Façade over Phase 5 `MoyasarClient`; never logs payload |
| Moyasar.js Credit Card + Apple Pay + STC Pay | ✅ | View exposes `data-methods="creditcard,applepay,stcpay"`; widget mount point provided |
| `CouponService::validate` returns array, no exceptions | ✅ | 9 failure paths each return `['valid' => false, ...]` array |
| Coupon uses `DB::increment` for atomic counter | ✅ | `CouponService::apply()` line 122; `ApplyCouponAction` line 42 |
| Checkout metadata: plan_id + billing_cycle + company + coupon_code | ✅ | Both `Subscription.meta.checkout` and `Payment.meta` carry the full set |
| `CreateTenantJob` runs via Queue | ✅ | Implements `ShouldQueue`, `tries=3`, `backoff=30` |
| No plaintext temp passwords | ✅ | Job sets bcrypt(random_64) placeholder; mail contains only signed setup URL |
| 48h signed setup URL via `URL::temporarySignedRoute` | ✅ | `TenantPasswordSetupService` const `VALID_HOURS=48`, `ROUTE_NAME=tenant.password.setup` |
| `TenantWelcomeMail` Mailable | ✅ | Branded RTL view; pulls hero/footer/support email from SystemSetting |
| Trial settings honoured | ✅ | `trial_enabled`, `trial_days`, `trial_requires_payment`, `trial_suspend_after_expiry`, `trial_warning_days`, `notify_trial_expiring` all read by Phase G commands |
| `saas:check-trial-expiry` command | ✅ | Daily 00:00; suspends tenant per setting; one-shot mail |
| `saas:send-trial-warnings` command | ✅ | Daily 08:00; per-milestone idempotency via `meta.trial_warning_days_sent` |
| Super Admin notified on new subscription if enabled | ✅ | `NewTenantSubscriptionNotification` routed to `admin_notification_email` |
| `CheckSubscription` middleware | ✅ | Active/Trialing → pass; PastDue → billing portal; Expired/Canceled/Paused → pricing; suspended → 403 view; never `abort(500)` |

**SaaS prompt verdict:** 🟢 100 % of compliance bullets satisfied across the 8 phases.

---

## 5. Coding Style Compliance

| `MY_CODING_STYLE (1).md` rule | Status | Evidence |
|--------------------------------|:------:|----------|
| ❌ No Tailwind | ✅ | `grep` of 616 Blade files for Tailwind utility patterns (`flex `, `bg-blue-`, `hover:bg-`, `p-4 `, etc.) returned **zero matches** |
| ❌ No React/Vue/SPA | ✅ | `grep` for `Vue.createApp`, `new Vue(`, `<template>`, `useState`, `ReactDOM` returned **zero matches** |
| ✅ Blade only | ✅ | All views are `*.blade.php`; admin/checkout/email views use plain Blade + Bootstrap CDN where needed |
| ✅ Bootstrap / Vuexy | ✅ | Public surfaces (`checkout/*`, `auth/tenant-password-setup`, `tenant/suspended`) use Bootstrap 5 RTL via CDN; admin views extend `admin.layout` (Phase 3 inline-CSS Vuexy-style) |
| ✅ jQuery + AJAX | ✅ | Checkout coupon-apply uses jQuery AJAX (`resources/views/checkout/show.blade.php`); SortableJS via CDN for landing-content reordering |
| ✅ Form Requests | ✅ | All Phase A→H POST/PUT routes validated by Form Requests: `ApplyCouponRequest`, `TenantPasswordSetupRequest`, `Store/UpdateLandingFeatureRequest`, `Store/UpdateLandingFaqRequest`, `UpdateSystemSettingsRequest` |
| ✅ Actions / Services | ✅ | `app/Services/CouponService.php`, `app/Services/MoyasarService.php`, `app/Services/Auth/TenantPasswordSetupService.php`, `app/Services/Settings/SystemSettingsService.php`; Phase 5 `app/Actions/Billing/Subscription/*` all preserved |
| ✅ Translations ar/en | ✅ | 12/12 translation files present (6 namespaces × 2 languages): `admin`, `landing`, `checkout`, `auth`, `emails`, `subscription` |
| ✅ Same admin module patterns | ✅ | New CRUD modules (LandingFeature, LandingFaq, SystemSetting, Coupon show/toggle) follow the established Phase 3 controller signature + redirect-with-status convention |

**Coding-style verdict:** 🟢 Full conformance to `MY_CODING_STYLE (1).md`.

---

## 6. Security Audit 🔐

### 6.1 Secrets in logs / responses

| Surface | Discipline | Evidence |
|---------|-----------|----------|
| `MoyasarService::createPayment` | Logs `'moyasar.create_payment_failed' + message only` — explicit comment "may contain a token" | `app/Services/MoyasarService.php:58` |
| `CreateTenantJob` | Logs `tenant_id` only; never token, URL, password | `app/Jobs/CreateTenantJob.php:82,92,150,189` |
| `TenantPasswordSetupController` | Logs only `email_domain` (post-`@`) for missing-user warnings — no full email leakage | `app/Http/Controllers/Auth/TenantPasswordSetupController.php:83` |
| `CheckoutController::callback` | Logs `gateway_payment_id` + message; never the `$payment` array | `app/Http/Controllers/CheckoutController.php:137,280` |
| `TenantPasswordSetupService` | Zero `Log::*` calls — pure data layer | n/a |
| `CouponService` | Zero `Log::*` calls | n/a |
| `testMail` / `testMoyasar` JSON responses | Tests #10, #11 in `SystemSettingsBridgeTest` confirm secrets never appear in JSON | `tests/Feature/Admin/SystemSettingsBridgeTest.php` |
| `checkout/show.blade.php` | Test `#9 no_secret_appears_in_checkout_view` confirms `moyasar_secret_key` never reaches HTML; only `publishable_key` is rendered | `tests/Feature/Checkout/CheckoutComplianceTest.php` |
| `emails/tenant/welcome.blade.php` | Test `#4 welcome_mail_does_not_contain_plaintext_password` greps for "temporary password", "your password is" in render() | `tests/Feature/Jobs/CreateTenantJobTest.php` |

### 6.2 Password setup token chain

| Property | Implementation | Evidence |
|----------|----------------|----------|
| Token hashed at rest | `hash('sha256', $plainToken)` stored in `tenant_password_setup_tokens.token` | `app/Services/Auth/TenantPasswordSetupService.php:50` |
| Signed URL | `URL::temporarySignedRoute(ROUTE_NAME, addHours(48), [...])` | line 58 |
| Constant-time hash compare | `hash_equals()` in `verify()` | line 83 |
| One-shot consumption | `consume()` deletes the row; re-use returns 403 (Test #5) | line 101 |
| Throttling | `POST /password/setup` has `throttle:6,1` | `routes/central.php:111` |

### 6.3 Payments — PCI safety

| Concern | Status |
|---------|:------:|
| No PAN / CVV / expiry stored | ✅ — `payments` migration has only `card_last4` + `card_brand` |
| Card data never reaches our server | ✅ — Moyasar.js tokenises in browser; backend receives `source.token` |
| Secret key never logged or echoed | ✅ — confirmed in §6.1 |

### 6.4 Access control

| Layer | Mechanism |
|-------|-----------|
| Admin guard | `Authenticate('admin')` middleware via `auth:admin` |
| Super Admin guard | Phase B `auth:super_admin` + `RequireAdminRole` accepts both guards |
| Tenant isolation | `BelongsToTenant` global scope; `withoutTenancy()` only in commands/jobs/webhooks |
| Subscription gate | Phase H `check.subscription` middleware (currently empty group, ready for migration) |

**Security verdict:** 🟢 No secrets leaked anywhere in scope. Token, payment, and access controls all sound.

---

## 7. Tenancy Isolation Audit

| Concern | Status | Evidence |
|---------|:------:|----------|
| Tenant-aware models use `BelongsToTenant` | ✅ | Confirmed on `User`, `Subscription`, `Payment`, `Invoice`, plus pre-existing HR/legal/operations models |
| Central-only models EXCLUDED from `BelongsToTenant` | ✅ | `Coupon`, `CouponUse`, `Plan`, `Feature`, `LandingFeature`, `LandingFaq`, `Domain`, `Tenant`, `Admin`, `SuperAdmin`, `SystemSetting`, `CentralSetting`, `WebhookEvent`, `Refund` |
| `withoutTenancy()` calls only in safe scopes | ✅ | Found in `app/Actions/Admin/*`, `app/Actions/Billing/*`, `app/Console/Commands/Billing/*`, `app/Http/Controllers/Webhooks/*` — never in tenant-facing controllers |
| `withoutGlobalScopes()` in `CheckoutController` | ✅ | Line 169 (idempotency lookup) and lines 301/306 (post-commit re-resolution outside tenant context) — both legitimate |
| `TenantContext::set()/runAs()/forget()` discipline | ✅ | All set/forget pairs are bracketed; `CreateTenantJob` uses `runAs()` (closure-scoped); `CheckoutController` uses `set` + `forget` in `try/finally` |
| Mailables are tenant-aware | ✅ | `TenantWelcomeMail`, `TrialExpiredMail`, `TrialExpiryWarningMail` all use `TenantAwareJob` trait + call `captureTenant()` in constructor |
| Tenant settings remain isolated | ✅ | Phase C `SystemSetting` is central-only; per-tenant `tenant_settings` (Phase 3) untouched |

**Tenancy verdict:** 🟢 Isolation invariants hold across all 8 phases.

---

## 8. Billing & Financial Integrity

| Property | Implementation | Evidence |
|----------|----------------|----------|
| Coupon redemption is atomic | `CouponService::apply()` wraps `DB::transaction` and uses `DB::table('coupons')->where('id', …)->increment('redemptions_count')` — no fetch-modify-save race | `app/Services/CouponService.php:115-137` |
| Phase 5 `ApplyCouponAction` also atomic | `Coupon::query()->where('id', …)->increment('redemptions_count')` inside transaction | `app/Actions/Billing/ApplyCouponAction.php:42` |
| Per-redemption audit row written same transaction | `CouponUse::create([...])` inside the same `DB::transaction` as the increment | `CouponService.php:128-135` |
| Checkout double-spend protection | Idempotency check on `gateway_payment_id` BEFORE the transaction returns success on duplicate | `CheckoutController.php:168-175` |
| Checkout metadata captured | Both `Subscription.meta.checkout` and `Payment.meta` carry `plan_id`, `billing_cycle`, `company_name`, `owner_email`, `owner_phone`, `coupon_code` | `CheckoutController.php:220-228, 267-273` |
| Trial expiry idempotent | `whereNull('trial_expired_notified_at')` filter + `event(SubscriptionExpired)` fires AFTER commit | `CheckTrialExpiryCommand.php:63, 110` |
| Trial suspend opt-in | Tenant suspended only when `trial_suspend_after_expiry=true` AND tenant currently `STATUS_ACTIVE` | `CheckTrialExpiryCommand.php:95` |
| Trial warning per-milestone idempotency | `meta.trial_warning_days_sent` array consulted before mail dispatch | `SendTrialWarningsCommand.php:76` |
| Refund / chargeback safety | `Payment::isRefundable()` requires `status=Captured && amount_refunded < amount`; `remainingRefundable()` clamps to `max(0, …)` | `app/Models/Payment.php:41-50` |

**Billing verdict:** 🟢 Atomic, idempotent, audit-trailed. No double-spend or over-refund vector identified in scope.

---

## 9. Performance & Scalability

| Concern | Status | Evidence |
|---------|:------:|----------|
| `system_settings` cached | ✅ | `Cache::remember('system_settings', 86400, ...)` (24h TTL); `forgetCache()` flushes on every set/setMany |
| Trial commands fetch settings ONCE before loop | ✅ | `CheckTrialExpiryCommand:56-57`, `SendTrialWarningsCommand:51-52` — no per-row settings round-trip |
| Mailables queued | ✅ | `TenantWelcomeMail`, `TrialExpiredMail`, `TrialExpiryWarningMail` all `implements ShouldQueue` |
| Job retry policy | ✅ | `CreateTenantJob` declares `tries=3`, `backoff=30` |
| Notification queued | ✅ | `NewTenantSubscriptionNotification implements ShouldQueue` |
| Trial commands chunk by id | ✅ | `chunkById(100, ...)` in both Phase G commands — no full-table load |
| Public landing pricing eager-load | 🟡 | `Marketing\LandingController::sellablePlans()` returns `Plan::where(...)->get()` without `with(['features'])`. The pricing-grid view iterates `$plan->features` per plan — N+1 risk. **Acceptable today** because the catalogue is ≤4 plans, but flagged for Phase I+ optimisation. |
| Admin landing CRUD index | ✅ | `LandingFeatureController::index()` uses simple `paginate(50)` on a flat table — no relations to N+1 |
| Queue connection | ✅ | `.env` has `QUEUE_CONNECTION=database` (production); test env uses `sync` (deterministic) |

**Performance verdict:** 🟡 GREEN with one **non-blocking** advisory (Plan→features eager-load on landing).

---

## 10. Dead Code Detection 🧹

| Component | Verdict | Rationale |
|-----------|:-------:|-----------|
| `App\Http\Controllers\Auth\TenantPasswordSetupController` | **USED** | Routes `tenant.password.setup` + `.store` in `routes/central.php` |
| `App\Http\Controllers\CheckoutController` | **USED** | 6 routes in `routes/central.php` (`checkout.*`) |
| `App\Http\Controllers\Admin\LandingFeatureController` + `LandingFaqController` | **USED** | Routes in `routes/admin.php` AND `routes/super-admin.php` (mirror) |
| `App\Http\Controllers\Admin\SystemSettingController` | **USED** | Routes in `routes/admin.php` lines 80–96 |
| `App\Http\Controllers\SuperAdmin\Auth\LoginController` | **USED** | `routes/super-admin.php` lines 41–49 |
| `App\Services\MoyasarService` | **USED** | `CheckoutController::__construct` |
| `App\Services\CouponService` | **USED** | `CheckoutController::__construct` |
| `App\Services\Auth\TenantPasswordSetupService` | **USED** | `TenantPasswordSetupController::__construct` + `CreateTenantJob::handle` |
| `App\Services\Settings\SystemSettingsService` | **USED** | `SystemSettingController::update` (and Phase C bridge fallback) |
| `App\Models\CouponUse` | **USED** | `CouponService::apply` writes rows; `Coupon::uses()` relation |
| `App\Models\LandingFeature` + `LandingFaq` | **USED** | CRUD controllers + landing view |
| `App\Models\Domain` | **USED** | `TenantResolver::resolveByHost` |
| `App\Models\SuperAdmin` | **USED** | `super_admin` guard provider |
| `App\Models\SystemSetting` | **USED** | 50+ call sites across phases |
| `App\Mail\TrialExpiredMail` + `TrialExpiryWarningMail` | **USED** | Phase G commands queue them |
| `App\Mail\TenantWelcomeMail` | **USED** | `CreateTenantJob` queues it |
| `App\Mail\Marketing\WelcomeMail` (Phase 9 legacy) | **USED — preserved** | `PublicSignupController` (legacy mode when feature flag is off) |
| `App\Notifications\NewTenantSubscriptionNotification` | **USED** | `CreateTenantJob::maybeNotifySuperAdmin` |
| `App\Jobs\CreateTenantJob` | **USED** | `PublicSignupController` (flag on) + `CheckoutController::callback` (flag on) |
| Phase G migration `add_trial_tracking_to_subscriptions` | **APPLIED** | `migrate:status` shows DONE |
| Phase H `tenant.suspended` route + view | **USED** | Reachable via middleware-rendered response AND named route |

**Safe-to-delete candidates:** **0**.
**Needs verification:** **0**.
**Used:** **All 8 phases × ~12 artefacts each.**

No dead code introduced by Phases A → H. The pre-Path-C codebase (171
controllers, 130+ models, 183 migrations) is out of audit scope per the
Path C contract ("don't delete legacy code without proof of non-use").

---

## 11. Risks

### 🟢 Production-acceptable, no action required

| # | Risk | Mitigation |
|---|------|------------|
| R1 | Phase H subscription-gated container is empty | Intentional — real tenant features still in `routes/web.php` (Phase 2 compatibility); migrating them is Phase 6/I scope. The middleware is wired and tested. |
| R2 | `tenancy.signup.use_setup_link` defaults to `false` | Intentional opt-in. Production deployments flip the env flag after operator readiness review. Legacy auto-login signup keeps Phase 9 + Auth/RegistrationTest expectations. |
| R3 | `Schedule::command(...)` lives in `app/Console/Kernel.php` not `routes/console.php` | Laravel 10 doesn't ship the standalone `Schedule` facade (Laravel 11+). Documented in `routes/console.php`. |

### 🟡 Non-blocking advisories

| # | Risk | Recommended Phase to address |
|---|------|------------------------------|
| Y1 | `Marketing\LandingController::sellablePlans()` lacks `->with(['features'])` — N+1 if catalogue grows past ~4 plans | I+ |
| Y2 | `tenant_password_setup_tokens` rows for unclicked links accumulate forever | I+ (add `php artisan tokens:prune` scheduled command) |
| Y3 | Pre-existing Phase 5 Qoyod boot bug breaks `php artisan route:list` (unrelated to Path C; documented earlier) | Pre-Path-C debt |
| Y4 | `Canceled` subscriptions are treated as upgrade-needed by Phase H middleware (stricter than `SubscriptionStatus::isEntitling()`) | Acceptable per spec; revisit if business product decision changes |
| Y5 | Adding a new `SubscriptionStatus` enum case without updating `CheckSubscription::handle()` would surface as 500 via PHP `UnhandledMatchError` | Test #9 (`no_500_errors_in_any_state`) catches it; reviewer-checklist item |

### 🔴 Blockers

**None.**

---

## 12. Final Verdict

```
SYSTEM STATUS: 🟢 Production Ready
```

| Pillar | Verdict |
|--------|---------|
| Functional correctness | 🟢 |
| Security | 🟢 |
| Tenancy isolation | 🟢 |
| Billing integrity | 🟢 |
| Coding style compliance | 🟢 |
| Performance | 🟡 (1 advisory) |
| Dead code | 🟢 |
| Routes & config | 🟢 |
| Error handling | 🟢 (no `abort(500)`) |
| Test coverage | 🟢 (280/280) |

**Cumulative artefact count introduced by Phases A → H:**

| Surface | Count |
|---------|------:|
| New migrations | ~10 |
| New models | 8 (Domain, SuperAdmin, SystemSetting, LandingFeature, LandingFaq, CouponUse + 2 supporting) |
| New controllers | 7 (CheckoutController, TenantPasswordSetupController, SystemSettingController, LandingFeatureController, LandingFaqController, SuperAdmin\Auth\LoginController, plus +1 Marketing extension) |
| New middleware | 5 (`InitializeTenantByDomainOrSubdomain`, `PreventAccessFromCentralDomains`, `RedirectIfAuthenticatedSuperAdmin`, `RedirectAdminToSuperAdmin`, `ApplySystemSettings`, `CheckSubscription`) |
| New services | 4 (`SystemSettingsService`, `CouponService`, `MoyasarService`, `TenantPasswordSetupService`) |
| New jobs | 1 (`CreateTenantJob`) |
| New mailables | 3 (`TenantWelcomeMail`, `TrialExpiryWarningMail`, `TrialExpiredMail`) |
| New notifications | 1 (`NewTenantSubscriptionNotification`) |
| New console commands | 2 (`CheckTrialExpiryCommand`, `SendTrialWarningsCommand`) |
| New form requests | 8 |
| New Blade views | ~25 |
| New translation files | 12 (6 namespaces × 2 langs) |
| Net regression tests | +97 |

---

## 13. Recommended Next Steps

These are **suggestions only** — no Phase I work is being initiated by
this audit.

### Immediate (no code changes needed; documentation/operator)

1. **Flip `TENANT_SIGNUP_USE_SETUP_LINK=true`** in production once the
   operator has reviewed the welcome-mail template and confirmed
   email-deliverability warm-up. Phase F is opt-in by design.
2. **Schedule cron**: ensure the host's cron runs `php artisan schedule:run`
   every minute so the four billing schedule entries (Phase 5) and the
   two trial-lifecycle entries (Phase G) actually fire.
3. **Operator handover doc**: write a one-pager covering the 6
   `SystemSetting` Trial-tab keys and what each does — most operator
   confusion will land here.

### Phase I+ candidates (technical)

1. **N+1 fix on landing pricing** (Y1): add `->with(['features'])` to
   `Marketing\LandingController::sellablePlans()`. ~3 lines of code.
2. **`tokens:prune` scheduled command** (Y2): delete
   `tenant_password_setup_tokens` rows older than 48h. Trivial cron
   addition.
3. **Move legacy `/employees/*` routes** from `routes/web.php` into the
   Phase H gated container in `routes/tenant.php` — migrating real
   tenant features under `check.subscription`. This is a multi-week
   migration; Phase 6 was its original landing slot.
4. **Standalone `Coupon::code` immutability** as a model-event guard
   (currently enforced only at controller level — defensive depth).
5. **Resend setup-link UI** on the login page so a user who lost the
   welcome email can self-serve.
6. **Self-test for `notify_subscription_expiring`** parallel to the
   trial flow, for paid subscriptions approaching renewal.

### Pre-existing technical debt (out of Path C scope)

1. The Phase 5 Qoyod boot bug breaking `php artisan route:list` —
   pre-existing, doesn't affect tests, requests, or runtime. Worth
   tackling because it impedes ops visibility.
2. Legacy Breeze tests under `tests/Feature/Auth/` are excluded from
   the suite via `phpunit.xml`. They could be ported to MNJIZ's
   `/employees/*` routes (or removed) — out of Path C scope.

---

**End of audit. No code was modified. No files were deleted.**
