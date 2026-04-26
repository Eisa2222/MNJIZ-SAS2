# Phase H — Subscription Access Control (CheckSubscription) — SEALED

**Status:** ✅ Complete · 280/280 tests passing (271 baseline + 9 new)
**Date:** 2026-04-26
**Path:** Path C Hybrid — Compliance Gap Closure

---

## 1. Summary

Phase H adds the spec-mandated `CheckSubscription` middleware that
gates tenant-scoped routes by current-subscription state. The
middleware never aborts with 500 — every code path returns either
`$next($request)`, an explicit `RedirectResponse`, or a rendered view
with a deliberate HTTP status.

**State → outcome mapping**

| Subscription state           | Outcome                                            |
|------------------------------|----------------------------------------------------|
| `Active`                     | `$next($request)` — full access                    |
| `Trialing`                   | `$next($request)` — full access                    |
| `PastDue`                    | 302 → `tenant.billing.index` (pay outstanding)     |
| `Expired` / `Canceled` / `Paused` | 302 → `marketing.pricing` (upgrade)         |
| No subscription              | 302 → `marketing.pricing` (pick a plan)            |
| Tenant `suspended`           | 403 → `tenant.suspended` rendered view             |

Critical preservation: the existing Phase 5 `InitializeTenantMiddleware`
still throws 403 for suspended tenants in the canonical request path
— so the existing
`TenantBillingPortalTest::test_suspended_tenant_returns_403_before_controller_runs`
keeps passing. Phase H's suspended branch is defence-in-depth for any
future code path that bypasses that middleware (programmatic tenant
context, console-triggered requests, etc.).

Zero `composer.json` changes. Zero Laravel-version bumps. Zero
deletions. Zero touches on `Subscription` schema, `Tenant` model,
`InitializeTenantMiddleware`, or any Phase 5/E/F/G test.

---

## 2. Subscription States Mapping

The middleware reads `Subscription::status` (cast to
`SubscriptionStatus` enum) and dispatches via PHP `match` — exhaustive
on every enum case so a future enum addition triggers a compile-time
warning rather than silent fallthrough.

```php
return match ($subscription->status) {
    SubscriptionStatus::Active, SubscriptionStatus::Trialing
        => $next($request),
    SubscriptionStatus::PastDue
        => $this->redirectToBillingPortal($request, 'past_due'),
    SubscriptionStatus::Expired,
    SubscriptionStatus::Canceled,
    SubscriptionStatus::Paused
        => $this->redirectToUpgrade($request, 'expired'),
};
```

Notes:
- **`Canceled` is treated as upgrade-needed**, not as transitional. In
  Phase 5 a cancelled-but-still-in-period subscription has
  `isEntitling()=true`, but Phase H's stance is conservative: once
  cancellation is recorded the operator should be redirected to renew
  proactively. This matches the user spec's "السماح: trialing/active
  فقط" rule and is the safer default.
- **`Paused` follows the upgrade path** for the same reason — pause
  is admin-initiated and the operator-visible signal should be "go pick
  a plan again."

---

## 3. Middleware Logic

### Order of operations inside `handle()`

1. `TenantContext::current()` — if no tenant resolved, **pass through**.
   Real central requests (with `tenancy.fallback_enabled=false` or
   simply unresolved) flow through unchanged.
2. `isExempt($request)` — match the request route name against the
   `EXEMPT_ROUTE_PATTERNS` list AND the URL path against critical
   prefixes. Exempt routes bypass ALL state checks.
3. `$tenant->isActive()` — if the tenant itself is suspended, render
   the suspended view (HTTP 403). Returns immediately.
4. Subscription lookup — `Subscription::query()->withoutTenancy()->where('tenant_id', …)->orderByDesc('id')->first()`.
   If null → upgrade redirect.
5. Status `match` — see §2.

### Logging discipline

The middleware logs nothing — it's a pure routing concern. Failures
inside `redirectToBillingPortal` / `redirectToUpgrade` would surface
as 500 from Laravel itself, not from the middleware code.

### "Never 500" guarantee

- All four return paths (`$next`, `redirect()->guest(...)`,
  `response()->view(..., 403)`, `match` return) are explicit
  `Response`-returning expressions.
- The exhaustive `match` covers every `SubscriptionStatus` case;
  adding a future case without updating Phase H produces a PHP
  `UnhandledMatchError` at runtime, which would surface as 500 — but
  Phase H's enum coverage is verified by Test #9
  (`no_500_errors_in_any_state`) that exercises ALL six current cases.

---

## 4. Route Protection

### Where the middleware is wired

```php
// routes/tenant.php — Phase H subscription-gated container
Route::middleware(['auth', 'check.subscription'])->group(function () {
    // Future: tenant feature routes go here.
});
```

The container is currently **empty**. Real tenant features today live
in the legacy `routes/web.php` under `/employees/*` (Phase 2
compatibility layer). Phase H establishes the gate mechanism so that
when those routes migrate into `routes/tenant.php` they inherit the
gate automatically — no per-route plumbing.

### Routes deliberately OUTSIDE the gate

- `tenant.ping` — pure infra, no domain access
- `tenant.billing.*` — must stay reachable so a past-due / no-sub
  user can pay
- `tenant.suspended` — the page we redirect TO when suspended

These are also exempt by name in the middleware's
`EXEMPT_ROUTE_PATTERNS` so even if a future route group accidentally
adds `check.subscription` over them, the middleware still passes them
through.

---

## 5. Exceptions (Exempt Routes)

The middleware passes through these without any state check.

| Route name pattern               | Why exempt                                   |
|----------------------------------|----------------------------------------------|
| `tenant.billing.*`               | Past-due users must pay outstanding invoices |
| `host.tenant.billing.*`          | Same, on host-resolved tenant routes (Phase A) |
| `admin.subscriptions.*`          | Admin actions must work regardless of tenant state |
| `tenant.suspended`               | The page we redirect to on suspension       |
| `host.tenant.suspended`          | Same on host-resolved routes                |
| `checkout.*`                     | Public checkout flow — paying their way back |
| `tenant.password.setup` / `.store` | Phase F password setup is pre-auth         |
| `login` / `logout`               | Auth flows must always reach                 |
| `admin.login*` / `admin.logout`  | Admin auth                                   |
| `super-admin.login*` / `super-admin.logout` | Super Admin auth                  |
| `webhooks.*`                     | Gateway / external system callbacks          |
| `health.*`                       | Load-balancer probes                         |
| `marketing.*`                    | Public marketing surface (the upgrade target) |
| `register` / `register.store`    | Public signup                                |
| `tenant.ping` / `host.tenant.ping` | Health probe                               |

URL-prefix fallback (belt-and-braces for routes without names):
`/checkout/`, `/password/setup`, `/webhooks/`, `/health`.

---

## 6. Views

### `resources/views/tenant/suspended.blade.php` (NEW)

- RTL Bootstrap 5 (CDN), Cairo font (matches existing checkout / setup
  pages)
- Pause icon, branded header, firm-name pill, body copy + support email
  pulled from `SystemSetting`
- Two CTAs — "Back to home" and "View plans" (linking `/pricing`)
- **HTTP 403** when rendered by the middleware, **HTTP 403** when
  rendered by the standalone `tenant.suspended` route — consistent.

### Existing tenant billing portal — UNCHANGED

`resources/views/tenant/billing/index.blade.php` is the same Phase 5
view; Phase H redirects past-due tenants here.

### No "upgrade" view — uses existing `marketing.pricing`

The existing `/pricing` page (Phase 9 + Phase D dynamic) is the
canonical upgrade surface. Phase H redirects `Expired` / `Canceled`
/ `Paused` / no-subscription cases to it. No separate
`tenant/upgrade.blade.php` needed.

---

## 7. Tests

`tests/Feature/Tenant/CheckSubscriptionTest.php` — 9 tests, all
passing.

| # | Test | Coverage |
|---|------|----------|
| 1 | `active_subscription_allows_access` | Active → 200 OK + `$next($request)` body |
| 2 | `trialing_subscription_allows_access` | Trialing → 200 OK |
| 3 | `expired_subscription_redirects_to_pricing` | Expired → 302 with `Location: /pricing` |
| 4 | `past_due_redirects_to_billing_portal` | PastDue → 302 with `/billing` in `Location` |
| 5 | `suspended_tenant_renders_suspended_view` | Tenant.STATUS_SUSPENDED → 403 + view contains heading |
| 6 | `no_subscription_redirects_to_pricing` | Tenant with zero rows → 302 `/pricing` |
| 7 | `excluded_routes_bypass_middleware` | 8 exempt route names all return 200 even with Expired sub |
| 8 | `central_routes_not_affected` | No tenant + fallback off → pass-through |
| 9 | `no_500_errors_in_any_state` | All 6 enum cases produce 200/302/403 — never 500 |

**Final suite:** `php artisan test` → **280 passed (762 assertions)**, 0 failures, 0 errors.

The middleware is unit-tested in isolation via direct `handle()`
invocation with synthesised `Request` objects whose route resolver
returns named routes. This bypasses the full Laravel kernel so the
test suite stays fast (~50s for the 9 tests) and doesn't require
spinning up the tenant routing layer per test.

---

## 8. Files Changed

**New (5)**

```
app/Http/Middleware/CheckSubscription.php
resources/views/tenant/suspended.blade.php
lang/ar/subscription.php
lang/en/subscription.php
tests/Feature/Tenant/CheckSubscriptionTest.php
PHASE_H_SUBSCRIPTION_ACCESS_REPORT.md
```

**Modified (2)**

```
app/Http/Kernel.php          ← added 'check.subscription' alias
routes/tenant.php            ← added tenant.suspended route + empty
                                middleware-protected container
```

**Untouched (preserved by design)**

- `Subscription` model + schema — no changes
- `SubscriptionStatus` enum — no changes
- `Tenant` model — no changes
- `InitializeTenantMiddleware` — still throws 403 for suspended tenants
  in the canonical path; Phase H does NOT replace this behaviour
- `Tenant\BillingController` + tenant billing portal — unchanged
- `routes/admin.php`, `routes/super-admin.php`, `routes/central.php` —
  Phase H middleware NEVER applied to admin / super-admin / central
  groups
- All Phase A→G commits and 271 baseline tests — every assertion still
  passes

---

## 9. Risks Remaining

- **Phase 5 `InitializeTenantMiddleware` still throws 403** for
  suspended tenants in the canonical tenant routing path (`/t/{tenant}/...`).
  This is intentional — `TenantBillingPortalTest::test_suspended_tenant_returns_403_before_controller_runs`
  asserts that exact 403 status. Phase H's suspended branch is
  defence-in-depth and does NOT replace that behaviour. A future phase
  could swap the 403 for the suspended view, but it would need to
  update that test simultaneously.

- **The protected route group is empty.** Phase H's gate is wired and
  works (verified in tests), but no real user-facing tenant feature
  currently routes through it. Real tenant features live in
  `routes/web.php` under `/employees/*` (Phase 2 compatibility).
  Migrating those routes to the new gated group is a Phase 6/I
  concern. When it happens, existing legacy tests that hit
  `/employees/*` will need re-examination.

- **`Canceled` redirects to upgrade.** This is a stricter stance than
  `SubscriptionStatus::isEntitling()` (which considers Canceled
  entitling until `ends_at`). The user spec said "السماح: active
  فقط أو trialing فقط" — Canceled is excluded by that contract.
  Operators with cancelled-but-period-still-active subs see the
  upgrade page instead of full access. If a future product decision
  wants the entitlement-window behaviour, swap the match arm to call
  `$subscription->isEntitling()` instead.

- **`tenant.suspended` route is registered inside `routes/tenant.php`.**
  It runs through the same `tenant.init` middleware as everything else,
  which means a request to `/t/{slug}/suspended` for a tenant that's
  ALREADY past the InitializeTenantMiddleware 403 won't reach the
  suspended view — it would 403 there first. Phase H's middleware
  redirect path uses `route('tenant.billing.index', ...)` and
  `route('marketing.pricing')`, neither of which trigger the
  suspended-view route. The view IS rendered directly by the
  middleware via `response()->view(...)`, so the route exists more as
  a developer-facing URL than as a runtime target.

- **"No 500" is enforced by the exhaustive `match`.** If a future
  Phase adds a new `SubscriptionStatus` case without updating
  Phase H, PHP's `UnhandledMatchError` would surface as 500. Test #9
  exercises all 6 current cases, but a new one would need a
  corresponding test branch — flagged as a code-review checklist item.

- **`tenancy.fallback_enabled=true` is the production default**, which
  means `TenantContext::current()` resolves the default tenant for any
  request. If `check.subscription` is ever applied to central routes by
  mistake, the default tenant's subscription state would gate them.
  Mitigated today by route-name + URL-prefix exempt list, but the
  recommended discipline is "only apply `check.subscription` to routes
  inside `routes/tenant.php`."

---

## 10. Verdict

**SEALED.** All Phase H exit criteria met:

- ✅ `CheckSubscription` middleware at `app/Http/Middleware/CheckSubscription.php`
- ✅ `handle(Request, Closure)` signature with exhaustive state-driven
  branching (Active/Trialing pass, PastDue → portal, Expired/Canceled/Paused
  → pricing, suspended tenant → 403 view, no sub → pricing)
- ✅ Registered as `'check.subscription'` alias in `app/Http/Kernel.php`
- ✅ Wired into `routes/tenant.php` via a dedicated subscription-gated
  group (currently empty container, ready for Phase 6+ feature
  migration)
- ✅ Exempts billing portal, checkout, password setup, auth, webhooks,
  health, marketing, ping, suspended page itself — by route-name pattern
  AND URL-prefix fallback
- ✅ `tenant.suspended` view (RTL, Bootstrap, no Tailwind) + matching
  route returning HTTP 403
- ✅ ar + en `subscription.*` translation keys (suspended page,
  block reasons, action labels)
- ✅ 9 new regression tests, all passing
- ✅ Full suite: **280/280** (271 baseline + 9 new) — every Phase
  A→G test untouched
- ✅ Zero deletions, zero `composer.json` edits, zero Laravel upgrade,
  zero schema changes, zero Tailwind, zero `abort(500)`
- ✅ Phase E checkout, Phase F password setup, Phase G trial lifecycle —
  all still pass through the exempt-route list

Ready for Phase I approval.
