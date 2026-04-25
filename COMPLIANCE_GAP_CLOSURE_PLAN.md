# Compliance Gap Closure Plan — Path C Hybrid

**Date:** 2026-04-25
**Author:** Principal Laravel SaaS Architect
**Status:** 🟡 Plan only — awaiting explicit go-ahead before any code change
**Baseline tests:** ✅ 183/183 passing on PHP 8.3.30

---

## 1. Baseline (read-only snapshot)

### 1.1 Environment

| Component | Version | Notes |
|---|---|---|
| **PHP** | **8.3.30** | upgraded today; `webdevops/php:8.3` Docker image |
| Composer | 2.9.7 | latest stable |
| **Laravel** | **10.48.29** | will stay on 10.x for this phase (per Path C — defer Laravel 13) |
| **OS image** | webdevops/php:8.3 | replaces previous webdevops/php:8.2 |
| MySQL | 8.0 (mnjiz-mysql container, untouched) | persistent volume preserved |

### 1.2 Composer constraints (current `composer.json` — UNCHANGED)

```json
"php":                          "^8.1",     // works under 8.3
"laravel/framework":            "^10.0",
"spatie/laravel-permission":    "^6.9",
"stancl/tenancy":               "^3.8",     // ⚠️ DECLARED but vendor/stancl/ MISSING
"yajra/laravel-datatables":     "^9.0",
"phpunit/phpunit":              "^10.5"
```

**Critical finding:** `vendor/stancl/` does not exist on disk. The package is declared in `composer.json` but was never installed (or was removed manually). The project's tenancy is **100 % custom** — written from scratch in Phase 2.

### 1.3 Test count
- **183 passing / 482 assertions / 0 failures** on PHP 8.3.30 (full suite)

### 1.4 Tenancy mode (current)

| Aspect | Current implementation |
|---|---|
| Mode | `single_database` (`config/tenancy.php`) — shared DB + `tenant_id` column |
| Tenant Model | `App\Models\Tenant extends Eloquent\Model` (custom, not stancl) |
| Resolution | Path-based via `App\Http\Middleware\InitializeTenantMiddleware` — `/t/{tenant}/...` |
| Scope | `App\Tenancy\Scopes\TenantScope` global scope on every BelongsToTenant model |
| Trait | `App\Tenancy\Concerns\BelongsToTenant` on 56 models |
| Static registry | `App\Tenancy\TenantContext::current()` |
| Storage isolation | `App\Tenancy\Support\TenantStorage` — `tenants/{id}/...` paths |
| Queue safety | `App\Tenancy\Concerns\TenantAwareJob` + `RestoreTenantContext` middleware on every queued Job (Phase 6 + 7 + 9) |
| Domains table | ❌ DOES NOT EXIST |
| Subdomain support | ❌ NOT IMPLEMENTED |
| Custom domain support | ❌ NOT IMPLEMENTED |
| Tenant migrations dir | ❌ DOES NOT EXIST (single dir for all migrations) |

### 1.5 Auth guards

| Guard | Provider | Model | Status |
|---|---|---|---|
| `web` | `users` | `App\Models\User` | ✅ tenant-aware (BelongsToTenant) |
| `admin` | `admins` | `App\Models\Admin` | ✅ central-only (no tenant_id) |
| `super_admin` | — | — | ❌ MISSING (spec asks for this name) |

`Admin` model already has the constants `ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_SUPPORT` and `RequireAdminRole` middleware enforces them. The semantic super-admin already exists; only the **guard name and route prefix** differ from the spec.

### 1.6 Settings architecture (current)

| System | Status | Notes |
|---|---|---|
| **legacy `settings`** table | EXISTS | wide-column singleton (one row per tenant after Module 5). 49 active call-sites use `Settings::current()`. |
| **`central_settings`** | EXISTS | platform-wide key/value (Phase 3). Encryption support. |
| **`tenant_settings`** | EXISTS | per-tenant key/value (Phase 3). Encryption support. |
| **`system_settings`** (spec name) | ❌ MISSING | spec asks for a single key/value/group/label table. |

### 1.7 Landing / checkout state

| Surface | Current state |
|---|---|
| `GET /` | ✅ exists (`marketing/landing.blade.php`) but **uses CUSTOM Tailwind-ish styling** — does NOT match the project's Vuexy + Bootstrap 5 + jQuery + SweetAlert2 + Toastr convention. |
| `GET /pricing` | ✅ exists (same deviation as above) |
| `GET /register` | ✅ exists (same deviation) |
| Hero / features / FAQs | ❌ HARDCODED in Blade. Spec requires `system_settings` for hero/footer + `landing_features` + `landing_faqs`. |
| `CheckoutController` | ❌ MISSING. Signup goes straight to a trial — no Moyasar.js checkout flow. |
| `MoyasarPaymentService` | ✅ EXISTS (Phase 5 — `App\Services\Billing\Gateway\MoyasarPaymentService`). createPayment / refund / webhook verification all present. |
| Coupons | ⚠️ `Coupon` model has `isRedeemable()` + `computeDiscount()`. **MISSING** the spec methods: `isValid`, `isApplicableToPlan`, `isApplicableToBillingCycle`, `hasReachedMaxUses`, `isExpired`, `getRemainingUses`, `scopeActive`, `calculateDiscount` (named differently). |
| `CouponService` | ⚠️ `ApplyCouponAction` exists (Phase 5). Throws `CouponRedemptionException` on invalid — spec wants array-return, never throw. |

### 1.8 UI stack (project convention — `package.json`)

```
Theme:        Vuexy ("name": "vuexy")
CSS:          Bootstrap 5.3.3
Build:        Vite 5
JS:           jQuery 3.7 + Alpine.js 3.4 + Tailwind 3.1 (used sparingly inside Vuexy)
DataTables:   Yajra Laravel DataTables (73 DataTable classes already in app/DataTables/)
Modals UX:    SweetAlert2 11 + Toastr 2 + bootstrap-select / bootstrap-datepicker
Translations: lang/ar/* + lang/en/* + ar.json + en.json
Layouts:      resources/views/layouts/{app,layoutFront,layoutFrontPublic,…}
```

**My Phase 9 landing page deviates from this** by using a custom Tailwind-only design. The plan below explicitly fixes this.

---

## 2. Compliance Matrix

| # | Requirement (spec) | Current state | Gap | Decision | Action | Risk |
|---:|---|---|---|---|---|---|
| 1 | PHP ^8.3 | ✅ 8.3.30 | none | done | — | none |
| 2 | Laravel ^13.0 | 10.48 | 3 majors behind | **DEFER** (Path C) | document deferral; stay on 10.x | breaking 183 tests + dependencies |
| 3 | stancl/tenancy ^3.10 multi-DB | declared 3.8, **not installed**, custom impl | huge gap | **DEFER full multi-DB**; build subdomain on existing custom resolver | extend `InitializeTenantMiddleware` | none beyond minor refactor |
| 4 | spatie/laravel-permission ^7.3 | 6.9 | 1 major behind | **DEFER** | — | breaking change |
| 5 | phpunit/phpunit ^12.0 | 10.5 | 2 majors behind | **DEFER** | — | breaking change |
| 6 | Tailwind v4 | Tailwind 3.1 (within Vuexy) | 1 major behind | **DEFER** | keep Bootstrap-first per project style | breaking Vuexy |
| 7 | Multi-DB tenancy | shared DB + tenant_id (90 tables) | fundamental | **DEFER** (write `MIGRATION_PLAN_MULTIDB.md`) | none in this pass | 6-10 weeks of rewrite |
| 8 | Subdomain routing (`tenant.app.com`) | ❌ none | core | **IMPLEMENT NOW** | new `domains` table + `DomainTenantResolver` + `InitializeTenancyByDomainOrSubdomain` middleware | medium — must coexist with `/t/{slug}` legacy |
| 9 | Custom-domain routing | ❌ none | core | **IMPLEMENT NOW** | same `domains` table; one tenant → many domain rows | medium |
| 10 | `domains` table | ❌ MISSING | required | **IMPLEMENT NOW** | new migration | low |
| 11 | `super_admin` guard | `admin` exists, role const present | naming | **COMPATIBILITY LAYER** — keep `admin` working, alias `super_admin` to same provider | new guard binding in `config/auth.php` | low |
| 12 | `/super-admin` prefix | `/admin` exists | naming | **COMPATIBILITY LAYER** — register `/super-admin` route group sharing same controllers; `/admin/*` redirects to `/super-admin/*` | route additions + redirect middleware | low (back-compat preserved) |
| 13 | `system_settings` table | three parallel systems exist | divergent | **BRIDGE** — add `system_settings` + a `SystemSetting` facade that reads from existing `central_settings` and writes to both for forward-compat | new model + migration + bridge service | medium — must not break 49 legacy callsites |
| 14 | `landing_features` table | ❌ MISSING | required | **IMPLEMENT NOW** | new table + model + Super Admin CRUD + DataTable + Form Requests | low |
| 15 | `landing_faqs` table | ❌ MISSING | required | **IMPLEMENT NOW** | same | low |
| 16 | Landing page dynamic content | hardcoded | required | **IMPLEMENT NOW** — re-skin into Vuexy `layoutFrontPublic` + read from `system_settings` / `landing_features` / `landing_faqs` | rewrite `marketing/landing.blade.php` using project conventions | medium — touches existing views |
| 17 | `CheckoutController @show /applyCoupon /callback /success` | ❌ MISSING | required | **IMPLEMENT NOW** | new controller + 4 routes + Moyasar.js Blade snippet | medium |
| 18 | Moyasar.js (Card / Apple Pay / STC Pay) | server-side only (Phase 5) | required | **IMPLEMENT NOW** | embed Moyasar.js in checkout view | low |
| 19 | Coupon spec methods (`isValid`, `isApplicableToPlan`, …) | only `isRedeemable` + `computeDiscount` | naming | **EXTEND** the model with spec-named methods (delegate to existing logic). Do not remove old methods. | model additions | low |
| 20 | `CouponService::validate()` returns array, never throws | `ApplyCouponAction` throws `CouponRedemptionException` | behavioral | **ADD** new `CouponService` next to existing Action; service returns array; Action stays for back-compat | new service class | low |
| 21 | `DB::increment('uses_count')` race-safe | `ApplyCouponAction` already uses `Coupon::where(id)->increment('uses_count')` | ✅ already complies | none | — | none |
| 22 | `CreateTenantJob` queued | signup is synchronous | required | **IMPLEMENT NOW** — extract logic from `PublicSignupController` into a queued job | new job (TenantAwareJob) | medium — changes signup UX |
| 23 | Password setup link (48h) | auto-generated password + auto-login | required | **IMPLEMENT NOW** — `URL::temporarySignedRoute('password.setup', 48h)` + `TenantWelcomeMail` carries link, no auto-login | new mail + new controller + 2 routes | medium |
| 24 | `TenantWelcomeMail` | `WelcomeMail` exists with dashboard CTA | rename + payload | **EXTEND** existing `WelcomeMail` to include the setup link; rename Mailable class to `TenantWelcomeMail` (alias for back-compat) | edit Mailable | low |
| 25 | `saas:check-trial-expiry` command | ❌ name missing (`billing:send-renewal-reminders` exists) | naming | **IMPLEMENT** as a thin wrapper that calls existing logic | new command + register in `routes/console.php` | low |
| 26 | `saas:send-trial-warnings` command | ❌ same | naming | **IMPLEMENT** wrapper | same | low |
| 27 | `CheckSubscription` middleware | ❌ MISSING | required | **IMPLEMENT NOW** — `tenant.subscription.active` middleware | new middleware + 2 error views | medium — must whitelist some routes (impersonation stop, password setup, billing portal) |
| 28 | `errors.suspended` view | ❌ MISSING | required | **IMPLEMENT NOW** | new Blade in Vuexy style | low |
| 29 | `errors.subscription-expired` view | ❌ MISSING | required | **IMPLEMENT NOW** | same | low |
| 30 | `ApplySystemSettings` middleware | ❌ MISSING | required | **IMPLEMENT NOW** — middleware reads `SystemSetting::all()` and `Config::set()`s them at runtime (mail/moyasar/landing groups) | new middleware | low |
| 31 | `PreventAccessFromCentralDomains` middleware | ❌ MISSING | required | **IMPLEMENT NOW** | new middleware applied to tenant route group | low |
| 32 | Cleanup pass (dead controllers / models) | dead files identified in Phase-9 + GAP report (8 candidates) | required | **CLEANUP** after each phase passes; never bulk-delete | per-file: `rg` proof + run tests | low if methodical |

**Total items:** 32. **Implement now:** 18. **Defer (Path C):** 6 (Laravel 13, stancl 3.10, spatie 7.3, phpunit 12, Tailwind 4, full multi-DB). **Compatibility layer:** 4. **Already complies:** 4.

---

## 3. Path C Hybrid — Decisions (binding)

These decisions are explicit and final unless you override them. They drive every Action in the matrix above.

1. **Tenancy mode stays SHARED DB + `tenant_id` for now.** Phase 6 + 7 architecture (90 tables, 56 models, 173 isolation tests) is preserved verbatim.

2. **Subdomain + Custom Domain support is added on top of the existing custom resolver.** I will extend `InitializeTenantMiddleware` to look up tenant by:
   - Host = `{slug}.{central_domain}` → resolve by `tenants.slug`
   - Host listed in new `domains` table → resolve by `domains.tenant_id`
   - Path `/t/{slug}/...` → resolve as today (legacy preserved)

3. **No `composer require` / `composer update` in this phase.** stancl/tenancy 3.8 stays declared-but-uninstalled. We do NOT integrate the package — we mirror its routing concepts using the project's own resolver. (Avoids touching `vendor/` and risking the 183 baseline.)

4. **`super_admin` guard is added as an alias** (same provider, same `App\Models\Admin` class). `admin` continues to work. New `/super-admin` route group is registered alongside `/admin`. `/admin/*` HTTP-redirects to `/super-admin/*` over a 1-month deprecation window (configurable via env).

5. **`system_settings` consolidation is a BRIDGE, not a replacement.** I add the new table + `SystemSetting` model + service. The bridge service writes to both `system_settings` AND the legacy table on every `set()` call so existing readers (Phase 5 `Settings::current()`, `SettingsHelper`, etc.) continue working untouched. Future cutover is one config flip.

6. **Full Multi-DB migration is deferred.** I will write `MULTIDB_MIGRATION_PLAN.md` (separate document) outlining the work but not executing it. The architecture won't fight a future stancl migration — adding `domains` and a domain-aware resolver maps cleanly to stancl 3.10's `Stancl\Tenancy\Database\Models\Domain` later.

7. **UI stays Vuexy + Bootstrap.** I will rewrite the Phase 9 landing page (`marketing/*.blade.php`) and signup form to use:
   - `layouts/layoutFrontPublic.blade.php` master
   - Bootstrap 5 components (cards, navs, modals)
   - jQuery + SweetAlert2 + Toastr for client interactions
   - Yajra DataTables for any new admin lists
   - `lang/ar/*` + `lang/en/*` translations
   - Form Requests for every POST
   - Actions for every business mutation (project's existing pattern)

8. **No tests are deleted.** New tests are added alongside existing ones. Goal: end this work with 220+ tests passing (183 baseline + ~40 new for spec compliance).

---

## 4. Implementation Phases (sequenced)

Each phase ends with `php artisan test` returning 0 failures before moving on. Each phase produces a single feature commit.

### Phase A — Subdomain + Custom Domain Resolver
- New migration: `domains` (id, tenant_id, host UNIQUE, is_primary, verified_at, timestamps)
- New model: `App\Models\Domain` (central, no tenant scope)
- New middleware: `App\Http\Middleware\InitializeTenancyByDomainOrSubdomain` — wraps existing `InitializeTenantMiddleware` logic; adds host-based + subdomain-based resolution before falling back to path-based
- New middleware: `App\Http\Middleware\PreventAccessFromCentralDomains` — 404s tenant routes if Host matches `config('tenancy.central_domains')`
- `config/tenancy.php`: add `central_domains[]`, `subdomain_pattern`, `central_domain` keys
- Routes: legacy `/t/{tenant}/...` retained; new tenant route group resolves by Host
- Tests (5+): subdomain resolves; custom domain resolves; central domain rejected on tenant routes; legacy path still works; unknown host → 404

### Phase B — Super Admin Compatibility Layer
- `config/auth.php`: add `super_admin` guard pointing at `admins` provider
- `routes/super-admin.php`: new route group mirroring `routes/admin.php` (same controllers, different prefix + middleware)
- `app/Http/Middleware/RedirectAdminToSuperAdmin`: optional 301 on `/admin/*` to `/super-admin/*` (env-gated)
- Tests (3+): super_admin login works; old admin route still responds; admin sessions independent of tenant `web` sessions

### Phase C — SystemSettings Bridge
- Migration: `system_settings (key UNIQUE, value, group, label, is_encrypted, cast, timestamps)`
- Model: `App\Models\SystemSetting` with `get($key, $default = null)`, `set($key, $value, $meta = [])`, `setMany(array)`
- Service: `App\Services\Settings\SystemSettingService` — bridges to existing `SettingsRepository` for reads + dual-writes
- Cache key: `system_settings`
- Middleware: `ApplySystemSettings` — reads `mail/*`, `moyasar/*`, `landing/*` groups and `Config::set` at boot
- Tests (4+): get/set/setMany; cache invalidation; encrypted moyasar key roundtrip; ApplySystemSettings flips runtime config

### Phase D — Landing Content Dynamic
- Migrations: `landing_features` (id, icon, title_ar, title_en, body_ar, body_en, sort_order, is_active) + `landing_faqs` (id, question_ar, question_en, answer_ar, answer_en, sort_order, is_active)
- Models: `LandingFeature`, `LandingFaq` (central; never scoped)
- Re-skin `marketing/landing.blade.php` and `marketing/pricing.blade.php` using `layouts/layoutFrontPublic`
- New Super Admin controllers: `LandingFeatureController` + `LandingFaqController` (DataTables-backed CRUD with create/edit modals + SortableJS reorder)
- Form Requests + DataTable classes per project pattern
- `lang/ar/landing.php` + `lang/en/landing.php`
- Tests (4+): hero from settings; features rendered; sortable persists order; super-admin CRUD works

### Phase E — Checkout + Coupon Compliance
- New `CheckoutController @show / applyCoupon / callback / success`
- New routes (4) under `/checkout/*`
- New view `checkout/show.blade.php` embedding Moyasar.js (configured for Card / Apple Pay / STC Pay)
- Extend `Coupon` model with spec-named methods (`isValid`, `isApplicableToPlan`, `isApplicableToBillingCycle`, `hasReachedMaxUses`, `isExpired`, `calculateDiscount`, `getRemainingUses`, `scopeActive`) — delegate to existing logic; keep `isRedeemable` for back-compat
- New `App\Services\Billing\CouponService` returning array (never throws)
- Tests (6+): coupon invalid/expired/max-uses/cycle-mismatch/plan-mismatch returned as array; race-safe increment; checkout callback creates subscription + payment

### Phase F — CreateTenantJob + Password Setup Link
- New `App\Jobs\Tenant\CreateTenantJob` (queued, TenantAwareJob): extracts the DB-transaction block from `PublicSignupController::store`
- Refactor `PublicSignupController::store` to dispatch the job; remove auto-login
- New `App\Mail\Marketing\TenantWelcomeMail` (alias keeps `WelcomeMail` working) — body contains password-setup signed URL valid 48 h
- New `App\Http\Controllers\Auth\PasswordSetupController @show / store` for `/setup-password/{token}` (URL::temporarySignedRoute)
- Tests (4+): mail contains setup link, no plaintext password; expired link rejected; setup → user can log in; queued job runs end-to-end

### Phase G — Trial Command Aliases
- New `App\Console\Commands\Saas\Trial\CheckTrialExpiryCommand` → `saas:check-trial-expiry` (calls existing `MarkPastDueSubscriptionsCommand` + `ExpireGracePeriodSubscriptionsCommand`)
- New `App\Console\Commands\Saas\Trial\SendTrialWarningsCommand` → `saas:send-trial-warnings` (delegates to existing `SendRenewalRemindersCommand`)
- Register in `routes/console.php`
- Tests (3+): commands registered; trial-warning sends correct mailable; expiry suspends tenant when `auto_suspend_on_expiry` setting on

### Phase H — CheckSubscription + ApplySystemSettings + Error Views
- New `App\Http\Middleware\CheckSubscription` — kicks tenant requests to `errors.suspended` or `errors.subscription-expired`
- Whitelist: impersonation-stop, password-setup, billing-portal endpoints
- New views in `resources/views/errors/{suspended,subscription-expired}.blade.php` (Vuexy guest layout)
- Wire `ApplySystemSettings` (from Phase C) + `CheckSubscription` into the tenant route group
- Tests (4+): suspended tenant blocked with 403 + view; expired subscription view rendered; whitelisted routes still reachable; central admin not affected

### Phase I — Cleanup (safe pass)
- Per-file proof of non-use via `rg` + `route:list` + `phpstan` (if available)
- Candidates listed in §5 below; nothing deleted without proof
- All deletions logged in `REMOVED_CODE_REPORT.md`
- Tests must still be green after each batch of removals

### Phase J — Final Validation
- `php artisan test` (0 failures)
- `php artisan route:list` (no broken routes)
- `php artisan migrate:fresh --seed` (clean schema seeds correctly)
- `php artisan config:cache && php artisan route:cache && php artisan view:cache` (all cacheable)
- Final report: `COMPLIANCE_FINAL_REPORT.md`

**Estimated effort:** 4-6 working days assuming sequential execution and no surprises.

---

## 5. Cleanup Candidates (NO deletions yet)

Each item below is **suspected** to be unused. Proof before delete.

### Suspect dead files

| Path | Why suspect | Verification needed |
|---|---|---|
| `app/Http/Controllers/OperationsCenter/Contract/old.php` | filename `old.php` | rg references + route:list |
| `app/Http/Controllers/LegalAffair/Lawsuit/LawsuitController-old.php` | suffix `-old.php` | same |
| `app/Http/Controllers/LegalAffair/Session/old.php` | filename `old.php` | same |
| `app/Http/Controllers/OrganizationCenter/Tasks/Task_delete/TaskController_خم.php` | parent dir `Task_delete/` (Arabic suffix in filename) | same |
| `app/Http/Controllers/OrganizationCenter/Tasks/Task_delete/oldTask.php` | filename `oldTask.php` | same |
| `app/Services/__MicrosoftGraphService.php` | underscore prefix = backup convention | same |
| `app/Console/Commands/CloseExpiredLawsuits.php` | entire class commented out | review + delete or restore |
| `app/Models/ContentManagementSocial.php` (root namespace) | empty scaffold; real model is at `Marketing/.../ContentManagementSocial/ContentManagementSocial.php` | rg `App\Models\ContentManagementSocial` (root) |

### Models possibly redundant

| Model | Why suspect |
|---|---|
| `App\Models\LegalCase` | superseded by `LegalAffair\Lawsuit\Lawsuit` since Phase 6 |
| `App\Models\LawsuitPlaintiff` | controllers now use raw `DB::table('lawsuit_plaintiffs')` |
| `App\Models\PowerAttorneyAgent` / `PowerAttorneyCustomer` | superseded by `LegalAffair\PowerOfAttorney\PowerOfAttorney` |
| `App\Models\LitigationStage` | unclear ownership |

### Phase 9 deviations (rewrite, not delete)

| Path | Action |
|---|---|
| `resources/views/marketing/landing.blade.php` | **rewrite** in Vuexy/Bootstrap style |
| `resources/views/marketing/pricing.blade.php` | same |
| `resources/views/auth/signup.blade.php` | same |
| `resources/views/onboarding/welcome.blade.php` | same |
| `resources/views/emails/marketing/welcome.blade.php` | edit to include password-setup link |

---

## 6. Risks

| # | Risk | Likelihood | Impact | Mitigation |
|---:|---|---|---|---|
| 1 | Subdomain middleware breaks legacy `/t/{slug}` traffic | medium | high | new resolver runs *before* path resolver; comprehensive tests on both paths; `php artisan route:list` diff before merge |
| 2 | `system_settings` bridge double-writes get out of sync | low | medium | unit tests assert dual write; periodic reconciliation command; bridge is read-from-legacy by default |
| 3 | New `super_admin` guard accidentally shares session with `web` | low | high | guard is bound to its own session driver; explicit isolation test |
| 4 | `CheckSubscription` blocks impersonation/setup paths | medium | high | explicit whitelist + tests |
| 5 | Vuexy view rewrite breaks public landing CSS | low | medium | preview before commit; existing tests verify HTTP 200 + key text only |
| 6 | Custom domain resolution leaks to wrong tenant | low | critical | new isolation test: configure domain X for tenant A; assert request never reaches tenant B's data |
| 7 | Removing dead files breaks an obscure dependency | medium | high | per-file `rg` + `route:list` proof; bisect via tests after each removal batch |
| 8 | Future Laravel 13 / stancl 3.10 / Tailwind 4 deferral creates tech debt | high | low (planned) | `MULTIDB_MIGRATION_PLAN.md` + a `DEFERRED_UPGRADES.md` ledger |
| 9 | `composer install` on first deploy may pull stancl 3.8 unexpectedly | medium | medium | confirm it doesn't conflict; consider `composer remove stancl/tenancy` if we won't integrate it (decide in Phase A) |
| 10 | Migration cycle on a single dir (`migrations/`) accumulates 220+ files | low | low | acceptable; multi-DB phase will split |

---

## 7. Go / No-Go Criteria

A phase **may merge** only if ALL of:

- ✅ `docker exec mnjiz-app php artisan test` returns **0 failures, 0 errors**
- ✅ `php artisan route:list` exits cleanly (no exceptions during route resolution)
- ✅ No new file in commit references plaintext secrets / API keys
- ✅ No deleted file proven to be referenced in `app/`, `routes/`, `resources/`, `tests/`
- ✅ Each new feature has **at least one regression test** added in the same commit
- ✅ Every new view extends a project layout (`layouts/layoutFront`, `layoutFrontPublic`, `app`, etc.) — no orphaned styling
- ✅ Every new POST is backed by a Form Request
- ✅ Every new business mutation goes through an Action class

A phase is **rolled back** if ANY criterion fails.

---

## 8. Top-3 Gaps (executive summary)

1. **No subdomain / custom-domain routing.** Spec wants `tenant.app.com` + `custom-domain.com`; today everything is path-based `/t/{slug}/...`. → **Phase A** adds the `domains` table + domain-aware resolver while keeping the legacy path live.

2. **No `system_settings` table — three parallel settings systems exist.** Legacy `settings` (singleton wide-column), `central_settings` (k/v platform), `tenant_settings` (k/v per-tenant). Spec wants one `system_settings`. → **Phase C** adds the new table + bridge service that writes to both — zero existing callsite breaks.

3. **Tenant signup is synchronous + auto-login + plaintext password.** Spec wants a queued `CreateTenantJob` + a 48-hour password-setup link in the welcome mail + no auto-login. → **Phase F** refactors signup into the queued job + setup-link flow without breaking the public `/register` URL.

## Recommended first execution phase

**Phase A — Subdomain + Custom Domain Resolver.**
Reasons:
- Lowest risk (new code, additive — legacy `/t/{slug}` keeps working)
- Unblocks the spec's Routing chapter without touching the data model
- Single migration + one model + one middleware + 5 tests
- Establishes the `domains` table that Phase F (`CreateTenantJob`) will need

---

## 9. What This Plan Will NOT Do

To honor your "don't break the system" rule, this plan **explicitly will not**:

- ❌ Run `composer update` or `composer require`
- ❌ Touch the `vendor/` directory
- ❌ Drop or rename existing tables (`admins`, `central_settings`, `tenant_settings`, `settings`)
- ❌ Remove the legacy `/t/{slug}` routes
- ❌ Delete the `BelongsToTenant` trait or `TenantScope`
- ❌ Migrate to Multi-DB tenancy
- ❌ Upgrade Laravel, spatie/permission, phpunit, Tailwind, stancl
- ❌ Delete any file before grep-verified proof of non-use

---

## ⏸️ Awaiting Approval

I will not execute Phase A (or any other) until you reply with one of:

- **"Approve plan, start Phase A"** — I begin with the subdomain + custom-domain resolver.
- **"Approve plan, but reorder: start with X"** — I respect your sequencing.
- **"Modify plan: …"** — I revise this document and re-submit.

Until then, the codebase is untouched and 183/183 tests remain green.
