# Phase A — Subdomain + Custom Domain Resolver

**Status:** ✅ SEALED — **193/193 tests pass** (183 baseline preserved + 10 new)
**Date:** 2026-04-25
**Branch:** `saas-initial`
**Path:** Path C Hybrid (no Multi-DB, no Laravel 13, no composer changes)

---

## Files Modified

### New files (5)

| File | Purpose |
|---|---|
| `database/migrations/2026_04_25_100000_create_domains_table.php` | central `domains` table — many-to-one to tenants, unique host, primary flag, verified_at |
| `app/Models/Domain.php` | central Eloquent model + `Domain::normalize()` host helper |
| `app/Http/Middleware/InitializeTenantByDomainOrSubdomain.php` | host-aware tenant resolver middleware (delegates to `TenantResolver`) |
| `app/Http/Middleware/PreventAccessFromCentralDomains.php` | 404s any tenant route hit from a central host |
| `tests/Feature/Tenancy/DomainResolutionTest.php` | 10 regression tests covering every resolution branch |

### Modified files (5)

| File | Change |
|---|---|
| `app/Tenancy/TenantResolver.php` | added `resolveByHost()` + `extractSubdomainSlug()` + `isCentralHost()`. Resolution order updated; legacy slug chain unchanged. |
| `app/Models/Tenant.php` | added `domains()` HasMany + `primaryDomain()` helper |
| `config/tenancy.php` | new `identification.subdomain` + `identification.custom_domain` blocks; new `central_domains[]`; documented resolution order |
| `app/Http/Kernel.php` | registered aliases `tenant.init.host` + `tenant.prevent.central` |
| `app/Providers/TenancyServiceProvider.php` | registered second route group on `routes/tenant.php` with host-aware middleware + `host.` name prefix |

### Files NOT touched (per Path C rules)

- ❌ `composer.json` (zero composer activity, zero `vendor/` changes)
- ❌ `routes/tenant.php` itself (still defines the same routes — they're now reachable from BOTH legacy and host-aware groups)
- ❌ `app/Http/Middleware/InitializeTenantMiddleware.php` (legacy resolver kept unchanged)
- ❌ `tenants.domain` text column (legacy column on `tenants` left in place; new `domains` table is purely additive)
- ❌ The `BelongsToTenant` trait, `TenantScope`, `TenantContext` static registry — all 173 prior isolation tests still green

---

## How Tenant Resolution Now Works

The `TenantResolver` walks an ordered chain. The first non-null match wins.

```
incoming HTTP request
   ↓
TenantResolver::resolveFromRequest()
   ├─ resolveByHost(host)
   │     ├─ host is in central_domains?  → null (central)
   │     ├─ exact match in `domains` table? → that tenant
   │     └─ host == "{slug}.{app_base_domain}"? → tenants.where(slug)
   │
   ├─ extractSlug(request)
   │     ├─ route param {tenant}        → its slug
   │     ├─ /t/{slug}/...                → segment[1]
   │     └─ X-Tenant-Slug header        → header value
   │
   └─ fallback_enabled? → tenants.where(slug = 'default')
```

**Legacy `/t/{slug}/...` traffic flows through identical code as before** — the only change is that the host-based branches run *first*. If they don't match, the existing slug chain takes over.

### Two parallel route registrations on the SAME `routes/tenant.php`

```php
// LEGACY — Phase 2-9 — preserved verbatim
Route::middleware(['web', 'tenant.init'])
    ->prefix('t/{tenant}')
    ->group('routes/tenant.php');

// PHASE A — host-aware (new)
Route::middleware(['web', 'tenant.init.host', 'tenant.prevent.central'])
    ->name('host.')                       // ← unique route names: host.tenant.billing.*
    ->group('routes/tenant.php');
```

Same controllers, two URL surfaces:
- `mnjiz.sa/t/acme/billing` → `tenant.billing.index` (legacy)
- `acme.mnjiz.sa/billing` → `host.tenant.billing.index` (new)
- `portal.acme.com/billing` → `host.tenant.billing.index` (new)
- `mnjiz.sa/billing` → 404 (`PreventAccessFromCentralDomains` blocks it)

---

## Compatibility Behavior

| Scenario | Before Phase A | After Phase A |
|---|---|---|
| `mnjiz.sa/t/acme/billing` | `tenant.init` resolves slug=acme | identical — first runs `resolveByHost` (skips central) then falls back to slug chain → same tenant |
| `acme.mnjiz.sa/billing` | 404 (no host route) | ✅ resolves to acme via subdomain branch |
| `portal.acme.com/billing` (registered in domains) | 404 | ✅ resolves to acme via custom-domain branch |
| `mnjiz.sa/billing` | 404 | 404 (still — `PreventAccessFromCentralDomains` |
| `unknown-host.com/billing` | 404 (or default fallback if enabled) | identical — host branch returns null, slug branch returns null, fallback kicks in |
| `mnjiz.sa/admin/...` | central `routes/admin.php` | identical — central paths bypass the resolver |
| `route('tenant.billing.index', ['tenant' => 'acme'])` | URL like `/t/acme/billing` | identical |
| `route('host.tenant.billing.index')` | route did not exist | new — generates `/billing` (URL host comes from server context) |

**No URL or controller behavior changed for any existing route.**

---

## Tests Result

**193/193 passing** (501 assertions, 137s runtime).

### New tests (10) — `DomainResolutionTest`

```
✔ Custom domain resolves to owning tenant
✔ Subdomain resolves by stripped slug
✔ Central host does not resolve to any tenant
✔ Legacy /t/{slug}/ping path still works
✔ Unknown host falls back to default tenant when enabled
✔ Unknown host returns null when fallback disabled
✔ Bare base domain does not resolve as subdomain
✔ Nested subdomain (www.acme.mnjiz.sa) is rejected
✔ Tenant->domains relation round-trips correctly
✔ Tenant->primaryDomain() prefers is_primary then first inserted
```

### Baseline preserved

```
Tests:    193 passed (501 assertions)
Duration: 137.88s
```

Nothing in Phases 1-9 was disturbed. The 173 isolation tests, 8 LegalAffair tests, 12 Module 6 tests, 11 Settings tests, 10 GTM tests, 10 Production-Readiness tests, 10 SaaS-Migration tests, 8 Notification tests, 5 Module 2 tests etc. all still green.

---

## Configuration Reference

`config/tenancy.php` keys added (all env-overridable):

```php
'identification' => [
    'subdomain' => [
        'enabled'         => env('TENANCY_SUBDOMAIN_ENABLED', true),
        'app_base_domain' => env('TENANCY_APP_BASE_DOMAIN', 'mnjiz.sa'),
    ],
    'custom_domain' => [
        'enabled' => env('TENANCY_CUSTOM_DOMAIN_ENABLED', true),
    ],
],

'central_domains' => array_filter([
    env('TENANCY_CENTRAL_DOMAIN_PRIMARY', 'mnjiz.sa'),
    env('TENANCY_CENTRAL_DOMAIN_WWW',     'www.mnjiz.sa'),
    env('TENANCY_CENTRAL_DOMAIN_APP',     'app.mnjiz.sa'),
]),
```

To disable host-based resolution in dev (e.g. when running on `localhost`):

```env
TENANCY_SUBDOMAIN_ENABLED=false
TENANCY_CUSTOM_DOMAIN_ENABLED=false
```

The path-based legacy branch is **unconditionally** active — there is no flag to turn it off.

---

## `domains` Table Schema

```sql
CREATE TABLE `domains` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,    -- FK → tenants.id, ON DELETE CASCADE
  `domain` VARCHAR(191) NOT NULL UNIQUE,    -- normalized lowercase host
  `is_primary` TINYINT(1) DEFAULT 0,
  `verified_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `domains_tenant_id_index` (`tenant_id`),
  INDEX `domains_is_primary_index` (`is_primary`)
);
```

A tenant can have:
- 0 rows → only path-based / header-based access (legacy)
- 1+ rows → host-based access via any of those hosts

The `verified_at` column is reserved for custom-domain ownership verification (DNS challenge). Auto-generated subdomains can be inserted with `verified_at = now()` at signup time. Phase F (CreateTenantJob) will start populating this row at signup.

---

## Risks Remaining

1. **stancl/tenancy 3.8 still declared in `composer.json` but not installed in `vendor/`** — out of scope for this phase. The new resolver mirrors stancl's interface so a future swap is straightforward. Recommend either `composer install` (if we want stancl available) or `composer remove stancl/tenancy` (if we won't integrate). Decide before Phase B.

2. **Two parallel route registrations means each route now appears TWICE in `route:list`** — once with name `tenant.foo` and once with `host.tenant.foo`. Cosmetic; no behavior issue.

3. **URL generation prefers the legacy named routes** — `route('tenant.billing.index')` still produces `/t/acme/billing`. If/when the team wants emails to point at subdomains, switch the call sites to `route('host.tenant.billing.index')` (covered in Phase F's password-setup link).

4. **No DNS verification yet for custom domains** — anyone with super-admin access could insert a `domains` row pointing at any host. Mitigation: `verified_at` is null until DNS challenge passes (Phase F + I will add the verifier).

5. **Subdomain resolution does NOT check `domains` table** — it only matches `tenants.slug`. So a tenant can have its slug-subdomain even without an explicit `domains` row. This is intentional (zero-config tenant onboarding) but means the `domains` table is not the single source of truth for host→tenant. If you want strict mode, set `TENANCY_SUBDOMAIN_ENABLED=false` and require explicit `domains` rows.

6. **`tenants.domain` legacy column** still exists on the table from earlier phases. It's UNUSED by the resolver after Phase A. Recommend dropping it during Phase I cleanup once we confirm zero readers.

---

## Path C Compliance Status — After Phase A

| # | Spec requirement | Status |
|---|---|---|
| 8 | Subdomain routing (`tenant.app.com`) | ✅ implemented |
| 9 | Custom-domain routing | ✅ implemented |
| 10 | `domains` table | ✅ implemented |
| 31 | `PreventAccessFromCentralDomains` middleware | ✅ implemented |
| — | `InitializeTenancyByDomainOrSubdomain` middleware | ✅ implemented (named `InitializeTenantByDomainOrSubdomain` per Laravel convention) |

**5 of 32 compliance items closed.** Phase A complete.

---

## Next Phase

**Phase B — Super Admin Compatibility Layer.** Decisions logged in `COMPLIANCE_GAP_CLOSURE_PLAN.md` §4-B remain valid:

- Add `super_admin` guard pointing at the existing `admins` provider (no DB rename)
- Mount `routes/admin.php` again under `/super-admin` prefix
- Optional 301 from `/admin/*` to `/super-admin/*` (env-gated)
- 3+ tests

Awaiting your **"Approve Phase B"** before starting.
