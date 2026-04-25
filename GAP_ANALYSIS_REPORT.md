# Gap Analysis — Current SaaS vs Reference Specification

**Date:** 2026-04-25
**Status:** 🚨 **BLOCKED on PHP version + scope ambiguity — no code modified**
**Baseline tests:** ✅ **183/183 passing (482 assertions)**

---

## 0. Hard Blocker — PHP Version

Per your explicit instruction:

> إذا كانت PHP أقل من 8.3:
> * أوقف التنفيذ
> * أعطني تقريرًا واضحًا

| Component | Current | Required | Status |
|---|---|---|---|
| **PHP** | **8.2.30** | **^8.3** | 🛑 **BLOCKER** |
| Laravel | 10.48.29 | ^13.0 | ❌ Mismatch |
| stancl/tenancy | 3.8 | ^3.10 | ❌ Mismatch |
| spatie/laravel-permission | 6.9 | ^7.3 | ❌ Mismatch |
| phpunit/phpunit | 10.5 | ^12.0 | ❌ Mismatch |
| tailwindcss | 3.1.0 | v4 | ❌ Mismatch |

**The Docker container ships PHP 8.2.30** (`webdevops/php:8.2`). Upgrading requires:
1. Switching the Docker base image to `webdevops/php:8.3` (or 8.4)
2. `composer.json` `"php": "^8.3"` and Laravel 13 + dependency upgrades
3. Re-installing all vendor packages
4. Re-running 183 tests against the new stack
5. Possibly fixing breaking changes (Laravel 10 → 13 spans 3 major versions)

**Per your rule, I have not run any composer or code change.** Decision required.

---

## 1. Compliance Snapshot

### What MATCHES the spec ✅

| Area | Spec | Current Implementation |
|---|---|---|
| Moyasar SAR billing gateway | required | ✅ Phase 5 `MoyasarPaymentService` + halalas conversion + webhook signature verification |
| Coupons | model + service | ✅ `App\Models\Coupon` + `ApplyCouponAction` (acts as service) |
| Subscriptions / Plans / Features | core SaaS | ✅ Phase 4 schema |
| Trial system | required | ⚠️ Partial — `SendRenewalRemindersCommand` exists but command names differ |
| Admin panel | tenants/plans/coupons/subscriptions | ✅ Mostly present, different naming |
| Routes split (central / tenant / admin) | required | ✅ `routes/central.php` + `routes/tenant.php` + `routes/admin.php` |
| Multi-tenant isolation | required | ✅ 173 isolation tests, zero cross-tenant leak proven |
| Production hardening | observability + security | ✅ Phase 8 — health endpoints, secure headers, request-id, rate limiting |
| Public landing | `/` route | ✅ Phase 9 — Blade landing page |
| Public signup | `/register` | ✅ Phase 9 — atomic signup pipeline |

### What DOES NOT MATCH ❌

#### A. Tenancy architecture — **fundamental mismatch**

| Aspect | Spec | Current |
|---|---|---|
| Tenancy mode | **Multi Database** (one DB per tenant) | **Shared DB** (`tenant_id` column on every table) |
| Routing | **Subdomain** (`acme.app.com`) + **Custom Domain** | **Path-based** (`/t/{slug}/...`) |
| Tenant model | extends `Stancl\Tenancy\Database\Models\Tenant` (or `BaseTenant`) implementing `TenantWithDatabase` | extends `Illuminate\Database\Eloquent\Model` (custom impl) |
| Domains table | required (`domains`) | ❌ MISSING |
| Tenant migrations dir | `database/migrations/tenant/` | ❌ MISSING (single dir for all tables) |
| Initialization middleware | `InitializeTenancyByDomainOrSubdomain` (stancl) | `InitializeTenantMiddleware` (custom path-based) |
| Central-domain protection | `PreventAccessFromCentralDomains` | ❌ MISSING |
| stancl/tenancy package | actively used | ✅ installed but ❌ NOT integrated (zero `use Stancl\Tenancy\…` imports in `app/`) |

**Impact:** Switching to stancl multi-DB unwinds Phases 2-7 architecture entirely:
- 26 `add_tenant_id_to_*` migrations would need rollback
- `BelongsToTenant` trait + `TenantScope` would be removed
- 56 models that currently use `BelongsToTenant` would change inheritance
- 100+ controllers/services/jobs that reference `TenantContext` would change
- Per-tenant DB connection wiring + tenant migrations directory created
- `tenant.init` middleware → `InitializeTenancyByDomainOrSubdomain`
- All 173 tests rewritten (current tests assert shared-DB scope behavior)

This is a near-total rewrite of the tenancy layer, not a "compliance closure".

#### B. Auth guard naming

| Aspect | Spec | Current |
|---|---|---|
| Model | `SuperAdmin` | `App\Models\Admin` |
| Guard | `super_admin` | `admin` |
| Route prefix | `/super-admin` | `/admin` |
| Tables | `super_admins` | `admins` |

#### C. Settings architecture

| Aspect | Spec | Current |
|---|---|---|
| Tables | single `system_settings` | three parallel: `settings` (legacy singleton) + `central_settings` (Phase 3) + `tenant_settings` (Phase 3) |
| Model | `SystemSetting` with `get/set/setMany` | `CentralSetting` + `TenantSetting` + legacy `Settings` |
| Service | unified | `SettingsRepository` (Phase 3) + 49 legacy call sites migrated to `Settings::current()` (Phase 5) |
| Tabs | general / trial / moyasar / mail / landing / notifications | ❌ NOT structured by groups |

#### D. Landing page dynamic content

| Aspect | Spec | Current |
|---|---|---|
| Hero from settings | ✅ required | ❌ hardcoded in Blade |
| Features from `landing_features` | ✅ required | ❌ hardcoded in Blade (6 items in template) |
| FAQs from `landing_faqs` | ✅ required | ❌ hardcoded in Blade (6 items) |
| Footer from settings | ✅ required | ❌ hardcoded |
| Plans from `plans` | ✅ required | ✅ already dynamic |
| Sortable Super Admin CRUD for features/FAQs | ✅ required | ❌ MISSING |

#### E. Checkout + Moyasar.js

| Aspect | Spec | Current |
|---|---|---|
| `CheckoutController@show/applyCoupon/callback/success` | required | ❌ MISSING — signup goes straight to trial |
| Moyasar.js (Card / Apple Pay / STC Pay) | required | ❌ MISSING — no JS gateway integration |
| `metadata` (plan_id / billing_cycle / company / coupon) | required | ⚠️ Partially set in Phase 5 webhook handler |

#### F. Tenant creation flow

| Aspect | Spec | Current |
|---|---|---|
| `CreateTenantJob` (queued) | required | ❌ Synchronous in `PublicSignupController::store` |
| Tenant DB provisioning | required (multi-DB) | ❌ Single DB shared |
| Tenant migrations on creation | required | ❌ N/A (shared DB) |
| First admin user inside tenant DB | required | ⚠️ Created in shared `users` table |
| Password setup link (48h) | required, no temp password | ❌ Auto-generates password from form + auto-logs in |
| `TenantWelcomeMail` with setup link | required | ⚠️ `WelcomeMail` exists but uses dashboard CTA, not setup link |

#### G. Trial commands (naming)

| Spec command | Current command | Status |
|---|---|---|
| `saas:check-trial-expiry` | — | ❌ MISSING |
| `saas:send-trial-warnings` | `billing:send-renewal-reminders` (Phase 5) | ⚠️ different name + scope |

#### H. Middleware

| Spec | Current | Status |
|---|---|---|
| `CheckSubscription` | — | ❌ MISSING |
| `ApplySystemSettings` | — | ❌ MISSING |
| `InitializeTenancyByDomainOrSubdomain` | `InitializeTenantMiddleware` (custom) | ⚠️ Different |
| `PreventAccessFromCentralDomains` | — | ❌ MISSING |
| `errors.suspended` view | — | ❌ MISSING |
| `errors.subscription-expired` view | — | ❌ MISSING |

---

## 2. Tables Reconciliation

### Central DB (per spec)

| Spec table | Current equivalent | Status |
|---|---|---|
| `super_admins` | `admins` | ⚠️ Renamed needed |
| `plans` | `plans` | ✅ Match |
| `plan_features` | `plan_features` | ✅ Match |
| `tenants` | `tenants` | ✅ Match (but model class differs — see A above) |
| `domains` | — | ❌ MISSING |
| `subscriptions` | `subscriptions` | ✅ Match |
| `payments` | `payments` | ✅ Match |
| `coupons` | `coupons` | ✅ Match |
| `coupon_uses` | — (only `coupon_plan` pivot) | ❌ MISSING (uses tracked in `coupons.uses_count` integer column) |
| `landing_features` | — | ❌ MISSING |
| `landing_faqs` | — | ❌ MISSING |
| `system_settings` | `central_settings` (close fit) + `tenant_settings` + legacy `settings` | ⚠️ Three parallel systems |

### Tenant DB (per spec — multi-DB)

In a true multi-DB world, every business table (lawsuits, contracts, employees, etc.) lives in the **per-tenant** DB. Currently they all live in the shared central DB with `tenant_id` columns.

**90 tables currently carry `tenant_id`.** Migrating all to per-tenant DBs is a data move, not just a schema change.

---

## 3. Cleanup Candidates (Pre-existing dead code)

These have been observed during prior phases but **not yet removed** (per the cautious-cleanup rule). Each is a candidate for removal **only after** confirming it's not referenced anywhere:

### Suspected dead controllers / files

| File | Why suspect |
|---|---|
| `app/Http/Controllers/OperationsCenter/Contract/old.php` | suffixed `old.php` |
| `app/Http/Controllers/LegalAffair/Lawsuit/LawsuitController-old.php` | suffixed `-old.php` |
| `app/Http/Controllers/LegalAffair/Session/old.php` | same |
| `app/Http/Controllers/OrganizationCenter/Tasks/Task_delete/TaskController_خم.php` | parent dir `Task_delete/` (Arabic suffix) |
| `app/Http/Controllers/OrganizationCenter/Tasks/Task_delete/oldTask.php` | same |
| `app/Services/__MicrosoftGraphService.php` | underscore prefix = backup file convention |
| `app/Console/Commands/CloseExpiredLawsuits.php` | entire class commented out |
| `app/Models/ContentManagementSocial.php` | empty scaffold (no fillable, no relations) — different from real `Marketing/.../ContentManagementSocial.php` |

**Verification step required before deletion:** `grep -r "<ClassName>" app/ resources/ routes/ tests/` plus `php artisan route:list` to prove zero references.

### Models that may be redundant

| Model | Why suspect |
|---|---|
| `App\Models\LegalCase` | very old root-level legal model; replaced by `LegalAffair\Lawsuit\Lawsuit` since Phase 6 |
| `App\Models\LawsuitPlaintiff` | pivot now accessed via raw `DB::table('lawsuit_plaintiffs')` in `LawsuitController` |
| `App\Models\PowerAttorneyAgent` / `PowerAttorneyCustomer` | superseded by `LegalAffair\PowerOfAttorney\PowerOfAttorney` |
| `App\Models\LitigationStage` | unclear ownership |

---

## 4. Scope & Effort Reality Check

The "compliance closure" you described is — based on these gaps — a **multi-week rewrite** rather than a cleanup pass:

| Work item | Estimated complexity |
|---|---|
| PHP 8.3+ + Laravel 13 upgrade | 3–5 days (breaking changes across 3 major versions) |
| stancl/tenancy multi-DB conversion | 2–3 weeks (un-do shared-DB pattern across 90 tables, 56 models, 173 tests) |
| Subdomain + custom domain routing | 3–5 days |
| `super_admin` guard renaming | 1–2 days (50+ middleware refs, route names) |
| `system_settings` consolidation | 3–5 days (49 legacy callsite migration was Phase 5 — would need third migration) |
| Landing page → dynamic CMS | 2–3 days |
| Checkout flow + Moyasar.js | 3–5 days |
| `CreateTenantJob` + password setup link | 1–2 days |
| Cleanup pass | 2–3 days |
| **Total** | **~6–10 weeks of focused engineering** |

For comparison, all of Phases 1–9 (which got us here) was the equivalent scope.

---

## 5. Recommendations

I see three rational paths. **None can begin until you decide which.**

### Path A — Honor the spec literally (full rewrite)
Pros: hits every checkbox in the reference file.
Cons: throws away Phases 2-7 architecture (~50% of the codebase work). 6–10 weeks. PHP upgrade required first.

### Path B — Honor the spec's INTENT, keep the architecture
Pros: preserves 183 passing tests + production-ready stack from Phases 1-9. Adds the missing pieces:
- `super_admin` guard rename (compatibility layer keeps `admin` working)
- `CheckSubscription` + `ApplySystemSettings` middleware
- `landing_features` + `landing_faqs` + Super Admin CRUD
- `CheckoutController` + Moyasar.js wiring
- `CreateTenantJob` + password setup link refactor
- Trial command renames

Cons: stays on shared-DB tenancy + path-based routing. Won't satisfy "Multi Database tenancy" + "Subdomain + Custom Domain" lines.

Effort: ~2–3 weeks.

### Path C — Hybrid (subdomain routing on shared DB)
Add subdomain routing on top of the existing shared-DB tenancy. stancl/tenancy supports this mode. Most of the user-visible spec items satisfied without unwinding Phase 2-7.

Effort: ~3–4 weeks.

---

## 6. What Was NOT Modified

To honor your rule (no code changes before approval):
- ✅ Zero files written, zero files deleted
- ✅ Zero `composer require/update` executed
- ✅ Zero migrations created
- ✅ Zero tests modified
- ✅ Baseline 183/183 still green

---

## 7. Decisions Required From You

1. **PHP upgrade approval** — switch Docker base image to `webdevops/php:8.3` (or 8.4) and run `composer update`?
2. **Path selection (A / B / C)** — which level of compliance to target?
3. **Cleanup authorization** — confirm I may delete the 8 suspected dead files in §3 after grep-verification?
4. **Auth guard rename** — `admin` → `super_admin` and `/admin` → `/super-admin`? This breaks all 50+ existing references and admin bookmarks.
5. **Settings consolidation** — collapse legacy `settings` + `central_settings` + `tenant_settings` into one `system_settings`? Phase 5 + 9 already migrated 49 callsites; another rewrite means revisiting all of them.

---

## Final Verdict

**Status: Partially Compliant.**
The current system is a fully-working, tested, production-ready SaaS that satisfies many of the spec's intentions through different concrete choices (shared-DB scope vs multi-DB; path-based routing vs subdomain; `admin` guard vs `super_admin`; etc.).

Treating the reference file as a literal blueprint requires a near-total rewrite of the tenancy foundation. **I will not start any modification until you confirm the path forward.**
