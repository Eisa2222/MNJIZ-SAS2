# Phase D — Landing Content Dynamic — SEALED

**Status:** ✅ Complete · 224/224 tests passing (213 baseline + 11 new)
**Date:** 2026-04-25
**Path:** Path C Hybrid — Compliance Gap Closure

---

## 1. Summary

Phase D moves the public marketing surface from hardcoded Blade copy
(Phase 9) to a CMS the Super Admin operates without redeploys:

- **Hero** (title / subtitle / CTA / image) and **footer** (copyright /
  privacy / terms / support email) read from the Phase C
  `system_settings` store via the Landing tab in `/super-admin/settings`.
- **Feature blocks** (the 6-card grid on `/`) move into a new
  `landing_features` table with a full Super Admin CRUD — create / edit /
  delete / drag-to-reorder via SortableJS.
- **FAQ entries** move into a new `landing_faqs` table with the same
  CRUD pattern.
- **Pricing grid** keeps reading the existing `Plan` model (already
  dynamic since Phase 9 — out of Phase D scope).

The landing view is **defensive**: every dynamic field falls back either
to a Phase 9 hardcoded string (via `lang/{ar,en}/landing.php` keys) or
to a sane default. A brand-new install with empty CMS tables still
renders a polished page.

The `Marketing\LandingController` from Phase 9 was **extended, not
replaced** — same routes, same view path, just extra data in the view bag.

Zero `composer.json` changes. Zero Tailwind. Zero new UI frameworks.
Zero deletions.

---

## 2. Tables added

| Migration | Table | Columns |
|-----------|-------|---------|
| `2026_04_25_120000_create_landing_features_table.php` | `landing_features` | id, title (255), description (text), icon (64, nullable), image (500, nullable), is_active (bool, default true, indexed), sort_order (uint, default 0, indexed), timestamps |
| `2026_04_25_120001_create_landing_faqs_table.php` | `landing_faqs` | id, question (500), answer (text), is_active (bool, default true, indexed), sort_order (uint, default 0, indexed), timestamps |

Both tables are central-only — no `tenant_id`, no `BelongsToTenant`.

---

## 3. Models

### `App\Models\LandingFeature`

```php
protected $fillable = ['title','description','icon','image','is_active','sort_order'];
protected $casts    = ['is_active' => 'boolean', 'sort_order' => 'integer'];

public function scopeActive(Builder $q): Builder { return $q->where('is_active', true); }
public function scopeOrdered(Builder $q): Builder { return $q->orderBy('sort_order')->orderBy('id'); }
```

### `App\Models\LandingFaq`

Identical API surface — `active()` + `ordered()` scopes, same casts.

---

## 4. Controllers

| Controller | Methods | Routes mounted on |
|------------|---------|-------------------|
| `App\Http\Controllers\Marketing\LandingController` | `index()` (extended), `pricing()` (extended) | `GET /` and `GET /pricing` |
| `App\Http\Controllers\Admin\LandingFeatureController` | `index, create, store, edit, update, destroy, sort` | `/admin/landing-features/*` AND `/super-admin/landing-features/*` |
| `App\Http\Controllers\Admin\LandingFaqController` | `index, create, store, edit, update, destroy, sort` | `/admin/landing-faqs/*` AND `/super-admin/landing-faqs/*` |

The two admin controllers use a private `resolveRoutePrefix()` helper
(reads first URL segment) so the same controller serves both the legacy
`admin.*` routes and the new Phase B `super-admin.*` routes — same
pattern as the Phase C `SystemSettingController`.

### Form Requests (4)

- `App\Http\Requests\Admin\StoreLandingFeatureRequest`
- `App\Http\Requests\Admin\UpdateLandingFeatureRequest`
- `App\Http\Requests\Admin\StoreLandingFaqRequest`
- `App\Http\Requests\Admin\UpdateLandingFaqRequest`

Each carries `prepareForValidation()` to coerce `is_active` (checkbox)
and `sort_order` (string from `<input type=number>`) into their proper
types before validation. `authorize()` accepts EITHER `admin` or
`super_admin` guard so the same FormRequest works behind both routes.

---

## 5. Views

### Public

- `resources/views/marketing/landing.blade.php` — rewired to consume
  `$features`, `$faqs`, `$hero`, `$footer` from controller. Existing
  inline `<style>` block preserved verbatim. `<title>` and brand text
  pulled from `SystemSetting::get('app_name')`. Defensive `@forelse`
  fallbacks render translated copy when the CMS is empty.

### Admin (8 files)

```
resources/views/admin/landing-features/
├── index.blade.php   ← table + SortableJS drag/drop + flash bar
├── create.blade.php  ← thin wrapper around _form
├── edit.blade.php    ← thin wrapper around _form
└── _form.blade.php   ← shared create/edit form partial

resources/views/admin/landing-faqs/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── _form.blade.php
```

All extend `admin.layout` (Phase 3 dark-theme inline-CSS shell). The
SortableJS bundle is loaded from a CDN inline (no asset-pipeline
changes). Drag-to-reorder POSTs `{ order: [id, id, ...] }` to the sort
endpoint and updates the flash bar with the success message.

Sidebar links added to `admin.layout.blade.php` — visible only to
`role === 'super_admin'`.

---

## 6. Routes

Mounted under both legacy and Phase B trees, all gated by
`auth + admin.role:super_admin`:

```
GET    /admin/landing-features                  → index
GET    /admin/landing-features/create           → create
POST   /admin/landing-features                  → store
POST   /admin/landing-features/sort             → sort  (JSON)
GET    /admin/landing-features/{id}/edit        → edit
PUT    /admin/landing-features/{id}             → update
DELETE /admin/landing-features/{id}             → destroy

GET    /admin/landing-faqs                      → index
GET    /admin/landing-faqs/create               → create
POST   /admin/landing-faqs                      → store
POST   /admin/landing-faqs/sort                 → sort  (JSON)
GET    /admin/landing-faqs/{id}/edit            → edit
PUT    /admin/landing-faqs/{id}                 → update
DELETE /admin/landing-faqs/{id}                 → destroy
```

Identical mirror under `/super-admin/landing-features/*` and
`/super-admin/landing-faqs/*` with `super-admin.*` route names.

The public `GET /` route is **unchanged** — same controller, same view
path, just richer data.

---

## 7. Tests

11 regression tests in `tests/Feature/Admin/LandingContentTest.php`,
all passing on first run:

| # | Test | Coverage |
|---|------|----------|
| 1 | `landing_page_loads` | `GET /` returns 200, fallback copy renders |
| 2 | `active_features_render_on_landing` | DB → public view round-trip |
| 3 | `active_faqs_render_on_landing` | DB → public view round-trip |
| 4 | `inactive_feature_hidden_from_landing` | `is_active=false` is excluded |
| 5 | `sort_endpoint_reorders_features` | SortableJS POST updates `sort_order` in order |
| 6 | `super_admin_can_create_feature` | Happy-path POST persists |
| 7 | `super_admin_can_update_feature` | Happy-path PUT updates |
| 8 | `super_admin_can_delete_feature` | Happy-path DELETE removes row |
| 9 | `sort_endpoint_for_faqs_persists_order` | Same as #5 for FAQs |
| 10 | `super_admin_landing_routes_work` | All 4 GETs on `/super-admin/*` return 200 |
| 11 | `admin_landing_routes_still_work` | All 4 GETs on `/admin/*` return 200 (legacy preserved) |

**Final suite:** `php artisan test` → **224 passed (595 assertions)**, 0 failures, 0 errors.

---

## 8. UI Compliance

| Requirement | Status |
|-------------|--------|
| Blade only | ✅ — no Vue/React anywhere |
| Bootstrap / Vuexy only in admin (no Tailwind) | ✅ — admin views extend the existing inline-CSS Vuexy-style `admin.layout` (no Tailwind utility classes) |
| Public landing uses simple inline CSS | ✅ — same approach as Phase 9 (no framework on the marketing page) |
| jQuery + SweetAlert2 + Toastr | ⚠️ partial — the existing `admin.layout` does NOT load jQuery/SweetAlert2/Toastr (Phase 3 set it up with native `confirm()` and inline flash bar). Phase D matches the established pattern: native `confirm()` for delete, inline `.flash` div for success. SortableJS is loaded inline from a CDN. **No regression vs the existing tenants/coupons CRUD.** |
| Yajra DataTables | ⚠️ not used — Phase 3 admin tables use plain server-rendered tables + Laravel paginator. Landing entries are typically <30 rows so server pagination at 50/page is sufficient and matches the established style. The `yajra/laravel-datatables` package remains installed for future modules. |
| Translations ar/en | ✅ — `lang/ar/landing.php` + `lang/en/landing.php` cover public copy AND admin CRUD labels |
| Same admin module pattern | ✅ — controller signature, FormRequest authorize(), redirect-back-with-status all match `CouponController` / `TenantController` |

---

## 9. Risks Remaining

- **SortableJS via CDN** — works offline-by-cache once loaded but a fresh
  load behind a strict CSP needs `script-src https://cdn.jsdelivr.net`.
  If CSP becomes strict, swap to a vendored copy in `public/js/`.
- **Feature `image` and `icon` are free text** — operator can paste any
  URL. Not a risk for first-party use, but if landing-page editing is
  ever delegated to non-engineers, add an upload pipeline + URL allow-list.
- **FAQ `answer` is plain text only.** The schema is `text` so HTML
  could be added later, but the view currently `{{ $faq->answer }}`
  escapes everything. Adding HTML support means adding a sanitiser.
- **Sidebar visibility check uses `auth('admin')->user()->role`** — works
  but ties the sidebar to the legacy admin guard. The Super Admin
  guard's parallel session won't see the Landing links. Acceptable for
  Path C (legacy admin layout still serves both); revisit when the
  admin layout itself gets a Phase B-aware refactor.
- **Route-model binding param names** are `landing_feature` and
  `landing_faq` (snake_case) to match Laravel's implicit binding rules
  for kebab-case URLs. Confirmed working by tests 5–11.

---

## 10. Files Changed

**New (16)**

```
database/migrations/2026_04_25_120000_create_landing_features_table.php
database/migrations/2026_04_25_120001_create_landing_faqs_table.php
app/Models/LandingFeature.php
app/Models/LandingFaq.php
app/Http/Controllers/Admin/LandingFeatureController.php
app/Http/Controllers/Admin/LandingFaqController.php
app/Http/Requests/Admin/StoreLandingFeatureRequest.php
app/Http/Requests/Admin/UpdateLandingFeatureRequest.php
app/Http/Requests/Admin/StoreLandingFaqRequest.php
app/Http/Requests/Admin/UpdateLandingFaqRequest.php
resources/views/admin/landing-features/{index,create,edit,_form}.blade.php   ×4
resources/views/admin/landing-faqs/{index,create,edit,_form}.blade.php       ×4
lang/ar/landing.php
lang/en/landing.php
tests/Feature/Admin/LandingContentTest.php
PHASE_D_LANDING_DYNAMIC_REPORT.md
```

**Modified (4)**

```
app/Http/Controllers/Marketing/LandingController.php   ← inject features/faqs/hero/footer
resources/views/marketing/landing.blade.php             ← consume dynamic data
resources/views/admin/layout.blade.php                  ← sidebar links (super_admin only)
routes/admin.php                                         ← landing-features + landing-faqs groups
routes/super-admin.php                                   ← mirrored landing groups
```

**Untouched (preserved)**

- `Plan` model + pricing grid logic
- `marketing/pricing.blade.php` (kept — also receives `$hero`/`$footer` data now)
- All Phase A/B/C infrastructure
- All 49+ legacy `central_settings` call-sites
- Vuexy theme assets / package.json / composer.json

---

## 11. Verdict

**SEALED.** All Phase D exit criteria met:

- ✅ `landing_features` + `landing_faqs` tables created and migrated
- ✅ Models with `active()`/`ordered()` scopes + boolean casts
- ✅ `LandingController` returns features + faqs + hero + footer
- ✅ Public landing fully dynamic with defensive fallbacks
- ✅ Super Admin CRUD for both tables (create / edit / delete / sort)
- ✅ SortableJS drag-to-reorder persisting via JSON POST
- ✅ Mounted on BOTH `/admin/*` and `/super-admin/*` route trees
- ✅ Form Requests with proper validation + checkbox/int coercion
- ✅ ar + en translations for public copy AND admin CRUD
- ✅ 11 new regression tests, all passing
- ✅ Full suite: **224/224** (213 baseline + 11 new)
- ✅ Zero deletions, zero `composer.json` edits, zero Tailwind, zero SPA frameworks
- ✅ Public `/` still loads (verified by test #1 + tinker render check)

Ready for Phase E approval.
