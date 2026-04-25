# Phase B — Super Admin Compatibility Layer

**Status:** ✅ SEALED — **201/201 tests pass** (193 baseline preserved + 8 new)
**Date:** 2026-04-25
**Branch:** `saas-initial`
**Path:** Path C Hybrid (no DB rename, no Laravel 13, no composer changes)

---

## Summary

The reference SaaS spec asks for a `super_admin` guard, a `SuperAdmin` model and a `/super-admin` route prefix. The legacy MNJIZ stack already has `admin` guard, `Admin` model and `/admin` prefix in active use across 173+ existing tests, controllers, route names and external integrations.

Phase B closes the gap **without renaming or breaking anything**:

- Both surfaces live in parallel: `/admin/login` AND `/super-admin/login` are valid endpoints
- A single `admins` table backs both — same row reachable via either guard
- Sessions stay isolated: signing into one does NOT authenticate the other
- An env-gated 301 redirect (`ADMIN_LEGACY_REDIRECT=true`) is available when ops decide to deprecate the legacy surface; OFF by default

### Why we did NOT rename `admins` → `super_admins`

1. 173+ existing tests reference `Auth::guard('admin')`, `route('admin.dashboard')`, `App\Models\Admin`. Renaming would require re-writing every one of them.
2. External tools (cron, monitoring, ops bookmarks, support docs) target `/admin/*`. Breaking those would require a coordinated migration window we haven't planned.
3. The `admins` table also feeds `admin_password_reset_tokens` and Spatie permissions tied to `model_has_roles.model_type = 'App\Models\Admin'`. A rename ripples into 4+ FK/morph constraints.
4. **The compatibility layer satisfies the spec contract** (a `super_admin` guard with a `SuperAdmin` Eloquent model and `/super-admin` URL space exists and functions correctly) at zero migration cost.

A future phase can flip `ADMIN_LEGACY_REDIRECT=true` to retire `/admin` once everyone has switched. No further code change required.

---

## Files Changed

### New files (5)

| File | Purpose |
|---|---|
| `app/Models/SuperAdmin.php` | spec-compliant marker class extending `Admin`, same `admins` table |
| `app/Http/Controllers/SuperAdmin/Auth/LoginController.php` | login/logout flow bound to `super_admin` guard, redirects to `super-admin.dashboard` |
| `app/Http/Requests/SuperAdmin/LoginRequest.php` | Form Request mirroring `Admin\LoginRequest` but calling `Auth::guard('super_admin')` |
| `app/Http/Middleware/RedirectIfAuthenticatedSuperAdmin.php` | bounces signed-in super-admins from `/super-admin/login` → dashboard |
| `app/Http/Middleware/RedirectAdminToSuperAdmin.php` | env-gated 301 from `/admin/*` to `/super-admin/*` (default OFF) |
| `routes/super-admin.php` | mirrors `routes/admin.php` route-for-route under `/super-admin` |
| `tests/Feature/Admin/SuperAdminCompatibilityTest.php` | 8 regression tests |

### Modified files (4)

| File | Change |
|---|---|
| `config/auth.php` | added `super_admin` guard + `super_admins` provider + `super_admins` password broker |
| `config/tenancy.php` | added `admin_legacy_redirect` boolean (env-overridable, default false) |
| `app/Http/Kernel.php` | registered aliases `super-admin.guest` + `admin.legacy.redirect` |
| `app/Providers/TenancyServiceProvider.php` | mounted `routes/super-admin.php` under `/super-admin`; wrapped legacy `/admin` group with the new (no-op-by-default) `admin.legacy.redirect` middleware |
| `resources/views/admin/auth/login.blade.php` | one-line change: form `action="..."` now reads `$loginAction ?? route('admin.login.attempt')` so the same Vuexy view serves both `/admin/login` and `/super-admin/login` |

### Files NOT touched (per Path C rules)

- ❌ `composer.json` — zero composer activity, zero `vendor/` changes
- ❌ `database/migrations/` — no schema change (admins table unchanged)
- ❌ `app/Models/Admin.php` — unchanged (`SuperAdmin` extends it cleanly)
- ❌ `routes/admin.php` — unchanged (every existing route, name and middleware preserved)
- ❌ Existing tests — none modified
- ❌ Spatie permission `model_has_roles` schema — `model_type` keeps storing `App\Models\Admin`; SuperAdmin permissions inherit transparently because `instanceof Admin` returns true

---

## Routes

### `/admin/*` — legacy surface (Phase 3, preserved verbatim)

```
GET  /admin/login                    → admin.login                       (admin.guest)
POST /admin/login                    → admin.login.attempt               (admin.guest)
POST /admin/logout                   → admin.logout                      (auth:admin)
GET  /admin                          → admin.dashboard                   (auth:admin)
GET  /admin/dashboard                → admin.dashboard.alt               (auth:admin)
GET  /admin/api/growth               → admin.growth.metrics              (auth:admin)
GET  /admin/tenants                  → admin.tenants.index               (auth:admin)
GET  /admin/tenants/{slug}           → admin.tenants.show                (auth:admin)
GET  /admin/tenants/create           → admin.tenants.create              (auth:admin + admin.role:super_admin)
POST /admin/tenants                  → admin.tenants.store               (...)
GET  /admin/tenants/{slug}/edit      → admin.tenants.edit                (...)
PUT  /admin/tenants/{slug}           → admin.tenants.update              (...)
POST /admin/tenants/{slug}/suspend   → admin.tenants.suspend             (...)
POST /admin/tenants/{slug}/activate  → admin.tenants.activate            (...)
POST /admin/tenants/{slug}/impersonate/{userId}  → admin.tenants.impersonate
GET  /admin/settings                 → admin.settings.index              (auth:admin + admin.role:super_admin)
PUT  /admin/settings/{key}           → admin.settings.update             (...)
GET  /admin/subscriptions            → admin.subscriptions.index         (auth:admin)
GET  /admin/subscriptions/{id}       → admin.subscriptions.show          (...)
POST /admin/subscriptions/{id}/cancel → admin.subscriptions.cancel       (admin.role:super_admin)
POST /admin/subscriptions/{id}/resume → admin.subscriptions.resume       (admin.role:super_admin)
GET  /admin/coupons                  → admin.coupons.index               (auth:admin)
GET  /admin/coupons/create           → admin.coupons.create              (admin.role:super_admin)
POST /admin/coupons                  → admin.coupons.store               (admin.role:super_admin)
GET  /admin/coupons/{coupon}/edit    → admin.coupons.edit                (admin.role:super_admin)
PUT  /admin/coupons/{coupon}         → admin.coupons.update              (admin.role:super_admin)
DEL  /admin/coupons/{coupon}         → admin.coupons.destroy             (admin.role:super_admin)
POST /admin/impersonation/stop       → admin.impersonation.stop          (no auth — used while impersonating)
```

The whole group is now wrapped by `admin.legacy.redirect` middleware, but it's a no-op when `ADMIN_LEGACY_REDIRECT=false` (default).

### `/super-admin/*` — new spec-compliant surface (Phase B)

Same controllers, same role gates, same view templates. Only differences:

- Authentication uses `auth:super_admin` instead of `auth:admin`
- Route names use `super-admin.*` prefix
- Login flow uses dedicated `App\Http\Controllers\SuperAdmin\Auth\LoginController` (only place where guard is hardcoded)

```
GET  /super-admin/login                  → super-admin.login              (super-admin.guest)
POST /super-admin/login                  → super-admin.login.attempt      (super-admin.guest)
POST /super-admin/logout                 → super-admin.logout             (auth:super_admin)
GET  /super-admin                        → super-admin.dashboard          (auth:super_admin)
GET  /super-admin/dashboard              → super-admin.dashboard.alt      (auth:super_admin)
GET  /super-admin/api/growth             → super-admin.growth.metrics     (auth:super_admin)
… [tenants / settings / subscriptions / coupons all mirrored, with super-admin.* names]
```

---

## Auth

```
GUARDS
  web          (driver=session, provider=users)        → App\Models\User
  admin        (driver=session, provider=admins)       → App\Models\Admin           ← legacy, preserved
  super_admin  (driver=session, provider=super_admins) → App\Models\SuperAdmin      ← new (Phase B)

PROVIDERS
  users         (eloquent, App\Models\User)
  admins        (eloquent, App\Models\Admin)            ← legacy
  super_admins  (eloquent, App\Models\SuperAdmin)       ← new

PASSWORD BROKERS
  users         → password_reset_tokens
  admins        → admin_password_reset_tokens           ← legacy
  super_admins  → admin_password_reset_tokens           ← shares the same table; distinct broker key
```

`super_admins.expire = 15` minutes, `throttle = 60` seconds — matches the legacy `admins` broker.

### Why three separate guards on the same table?

A single admin row at `id=1, email=ops@mnjiz.sa` can be authenticated through:
- `Auth::guard('admin')->attempt(...)` — produces a session bound to the legacy `admin` guard
- `Auth::guard('super_admin')->attempt(...)` — produces a session bound to the new `super_admin` guard

The two sessions live in different cookie buckets (Laravel's default per-guard session keys). Logging in via one does NOT authenticate the other — the user must explicitly choose which surface to enter through. This is intentional: it prevents accidental privilege escalation between the legacy `/admin` and the spec-compliant `/super-admin` URLs while still letting the same person operate either.

---

## Tests

### New tests (8) — `SuperAdminCompatibilityTest`

```
✔ Super admin login form renders
✔ Active super admin can log in
✔ Tenant user cannot log in via super admin guard
✔ Super admin dashboard requires authentication
✔ Legacy admin login still works
✔ Legacy admin responds directly when redirect off
✔ Legacy admin redirects to super admin when flag on
✔ Session buckets are isolated across three guards
```

### Full suite

```
Tests:    201 passed (530 assertions)
Duration: 147.77s
```

Phase B preserves every Phase 1-9 test verbatim. The 8 new tests prove the compatibility contract from every angle.

---

## Project-Style Compliance (per `MY_CODING_STYLE`)

| Convention | Honored? | How |
|---|---|---|
| Blade + Vuexy + Bootstrap | ✅ | reused `resources/views/admin/auth/login.blade.php` (one-line change adds `$loginAction` variable; existing styling preserved) |
| Form Requests | ✅ | new `App\Http\Requests\SuperAdmin\LoginRequest` follows the same shape as the legacy `Admin\LoginRequest` |
| Controller naming | ✅ | `SuperAdmin\Auth\LoginController` mirrors `Admin\Auth\LoginController` |
| Translations ar/en | ✅ | uses `__('auth.failed')` / `__('auth.throttle', …)` — same keys as legacy login |
| No new UI framework | ✅ | zero Tailwind code added; no React/Vue widget; no custom CSS file |
| jQuery / SweetAlert2 / Toastr | n/a | login flow is server-rendered like the legacy one — no JS additions |
| DataTables | n/a | login + dashboard use existing controllers; no new tables |
| Actions | n/a | auth is the only domain operation; LoginRequest already encapsulates the use-case (project pattern) |

---

## Risks Remaining

1. **`route:list` fails to render due to a pre-existing Phase 5 `QoyodClient` eager-bind bug** — *not introduced by Phase B*. Documented in `PHASE_A_DOMAIN_RESOLVER_REPORT.md` and the original `GAP_ANALYSIS_REPORT.md`. Tests resolve all routes correctly; only the artisan listing command fails. Out of scope for Phase B.

2. **Spatie permissions are stored against `model_type = 'App\Models\Admin'`** — `SuperAdmin extends Admin`, so `instanceof Admin` is true and `hasRole()` checks still pass. But if any downstream code does a strict `model_type === 'App\Models\Admin'` query, the new `SuperAdmin` instances would not match. Suggest auditing during Phase I cleanup.

3. **Two parallel route surfaces means `route:list` (when fixed) will show ~30 routes twice** — once with `admin.*` names and once with `super-admin.*` names. Cosmetic only.

4. **`URL::route('admin.dashboard')` from existing controllers/views/emails still produces `/admin/dashboard`** — i.e., they continue to point at the legacy surface. If the team wants emails or buttons to point at `/super-admin`, those callers must explicitly switch to the `super-admin.*` route name. Phase F (`TenantWelcomeMail`) will likely be the first to make that switch.

5. **Should we rename the table later?** Probably no. The `super_admin` name lives at the guard/route layer where the spec is read; the table name is an implementation detail. If a future spec audit specifically demands `super_admins` table name, we can do it via a non-destructive `RENAME TABLE` migration — but the tooling cost (FK + Spatie morph + Phase 3-9 callers) outweighs the symbolic benefit today. Defer until clearly needed.

6. **Should we remove `/admin` later?** Only after operations confirm:
   - all admins have logged into `/super-admin` at least once
   - external links/docs/dashboards have been updated
   - no automated job hits `/admin/api/*` directly
   When ready, flip `ADMIN_LEGACY_REDIRECT=true` and observe redirect logs for ~30 days. Removing the routes file is a follow-up after that observation window.

---

## Compliance Progress — After Phase B

| # | Spec requirement | Status |
|---|---|---|
| 11 | `super_admin` guard | ✅ added (legacy `admin` preserved as compat) |
| 12 | `/super-admin` prefix | ✅ added (legacy `/admin` preserved + optional 301 redirect) |
| — | `SuperAdmin` Eloquent model | ✅ added (extends `Admin`, same table) |
| — | `super_admins` provider + broker | ✅ added |
| — | Tests | ✅ 8 new + 193 baseline = 201 passing |

**7 of 32 spec items closed** (5 from Phase A + 2 from Phase B). 25 items remain for Phases C-J.

---

## Final Verdict

**GO for Phase C — SystemSettings Bridge.**

- ✅ 201/201 tests passing
- ✅ Zero regression in 193 baseline tests
- ✅ Zero composer activity
- ✅ Both `/admin` and `/super-admin` surfaces functional
- ✅ Three guards isolated (web / admin / super_admin)
- ✅ Spec-compliant naming achieved at the surface (guard / route / model) without DB rename
- ✅ Project conventions honored (Blade/Bootstrap/Form Request/Vuexy)
- ✅ Optional legacy redirect available via env flag, default off

Awaiting your **"Approve Phase C"** to proceed with the `system_settings` consolidation bridge.
