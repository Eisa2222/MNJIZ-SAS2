# Phase C — System Settings Bridge — SEALED

**Status:** ✅ Complete · 213/213 tests passing (201 baseline + 12 new)
**Date:** 2026-04-25
**Path:** Path C Hybrid — Compliance Gap Closure

---

## 1. Summary

Phase C delivers the spec-mandated single `system_settings` table (per
`saas-prompt (2).md`) **without removing** the legacy settings stacks
(`settings`, `central_settings`, `tenant_settings`). It does so via a
**bridge** strategy:

- `system_settings` is the new authoritative store for SaaS-wide
  configuration (mail, Moyasar, trial, landing, notifications, general).
- `SystemSettingsService` reads `system_settings` first, then falls back
  to `central_settings` for unknown keys, then to the explicit default.
- Writes to keys that already exist in `central_settings` are
  **dual-written** — preserving the 49 active call-sites that read from
  the Phase 3 store.
- The new `ApplySystemSettings` middleware runs on the central web group
  and overrides `config('mail.*')` and `config('services.moyasar.*')`
  per request, so a Super Admin saving SMTP credentials or a Moyasar
  secret takes effect on the very next request — no redeploy.
- The Super Admin Settings UI is a 6-tab Blade form
  (general / trial / Moyasar / mail / landing / notifications) mounted
  at both `/admin/settings` (legacy) and `/super-admin/settings` (Phase B).
  Sensitive fields are masked with `••••••••` and an empty submission is
  treated as “leave unchanged” to prevent accidental wipes.

Zero `composer.json` changes. Zero Laravel-version bumps. Zero deletions
of existing tables, models, or routes.

---

## 2. Files Changed

### New (12 files)

| File | Purpose |
|------|---------|
| `database/migrations/2026_04_25_110000_create_system_settings_table.php` | Central key/value table — id, key (unique), value (longText), group, label, is_encrypted, cast, timestamps. No `tenant_id`. |
| `app/Models/SystemSetting.php` | Eloquent model with static `get/set/setMany/forgetCache/allAsKeyValue` API, transparent Crypt encryption, cast normalization, 24h cache. |
| `app/Services/Settings/SystemSettingsService.php` | Bridge service — read order: system → central → default. Dual-writes on overlap. `maskedValue()` for sensitive keys. |
| `app/Http/Middleware/ApplySystemSettings.php` | Runtime applier — mail (8 keys) + Moyasar (5 keys). Silent no-op if table missing. |
| `app/Http/Requests/Admin/UpdateSystemSettingsRequest.php` | FormRequest with permissive nullable rules per tab. `tabPayload()` returns only the submitted group's keys. |
| `app/Http/Controllers/Admin/SystemSettingController.php` | `index/update/testMail/testMoyasar`. Drops empty/masked sensitive fields. Auto-detects `admin` vs `super-admin` route prefix from URL segment. |
| `resources/views/admin/settings/system.blade.php` | Vuexy-style 6-tab UI extending `admin.layout`. |
| `resources/views/admin/settings/_field.blade.php` | Text/email/url/number/password input partial. |
| `resources/views/admin/settings/_select.blade.php` | Select partial with `old()` support. |
| `resources/views/admin/settings/_toggle.blade.php` | Boolean checkbox with hidden-field unchecked-state trick. |
| `lang/en/admin.php` | English `settings.tabs.*` + `settings.fields.*`. |
| `tests/Feature/Admin/SystemSettingsBridgeTest.php` | 12 regression tests. |

### Modified (4 files)

| File | Change |
|------|--------|
| `lang/ar/admin.php` | Arabic `settings.tabs.*` + `settings.fields.*` translation tree. |
| `app/Http/Kernel.php` | Added alias `apply.system_settings` → `ApplySystemSettings::class`. |
| `routes/admin.php` | Replaced legacy single-key Central Settings group with Phase C tabbed group + `apply.system_settings` middleware. Phase 3 single-key endpoint preserved as `admin.settings.legacy.update`. |
| `routes/super-admin.php` | Mirrored Phase C settings group under `super-admin.settings.*`. |
| `app/Http/Middleware/RequireAdminRole.php` | Now accepts EITHER `admin` or `super_admin` guard so the same middleware works on both legacy and Phase B routes. Web guard still rejected. |

### Untouched (preserved by design)

- `central_settings` table + `CentralSetting` model — still functional, still consulted via fallback + dual-write.
- `tenant_settings` table — out of scope for Phase C (per-tenant only).
- `settings` Phase 0 wide-column singleton — still read by legacy code.
- `CentralSettingController` — retained as `admin.settings.legacy.update` endpoint for back-compat.

---

## 3. Settings Groups

Six tabs map to six groups, each persisted to `system_settings.group`:

| Tab | Keys |
|-----|------|
| **general** | app_name, app_logo, app_url, support_email, support_phone, default_timezone, default_language |
| **trial** | trial_enabled, trial_days, trial_requires_payment, trial_suspend_after_expiry, trial_warning_days[] |
| **moyasar** | moyasar_publishable_key, moyasar_secret_key 🔒, moyasar_webhook_secret 🔒, moyasar_test_mode, moyasar_enabled_methods[] |
| **mail** | mail_driver, mail_host, mail_port, mail_encryption, mail_username, mail_password 🔒, mail_from_address, mail_from_name |
| **landing** | hero_title, hero_subtitle, hero_cta_text, hero_cta_url, hero_image, footer_copyright, privacy_url, terms_url |
| **notifications** | notify_new_subscription, notify_payment_failed, notify_trial_expiring, notify_subscription_expiring, admin_notification_email |

🔒 = `is_encrypted=true` — stored as Crypt cipher-text at the column level.

---

## 4. Encryption

- **At rest:** `SystemSetting::setValueAttribute()` runs `Crypt::encryptString()`
  whenever `is_encrypted` is true. The DB column literally never sees plaintext.
  Verified by Test 2 — raw column value ≠ plaintext, `Crypt::decryptString` round-trips.
- **In transit (UI):** sensitive keys list is hard-coded in
  `SystemSettingsService::SENSITIVE_KEYS = ['mail_password', 'moyasar_secret_key', 'moyasar_webhook_secret']`.
  `maskedValue()` returns `••••••••` for those keys when populating the form,
  plaintext for everything else.
- **Empty-overwrite guard:** the controller drops sensitive fields whose
  submitted value is `''` or starts with `••`. Verified by Test 8 — masked
  re-submit and empty submit both leave the existing secret intact.
- **Test responses never echo secrets.** Verified by Tests 10 & 11 — the
  testMail and testMoyasar JSON responses are scanned for the known
  plaintext markers.

---

## 5. Runtime Config Override

`ApplySystemSettings` is mounted on the `admin.role:super_admin` group
(both `/admin/settings` and `/super-admin/settings` paths) via the
`apply.system_settings` alias. On every request it:

1. Loads `SystemSetting::allAsKeyValue()` (cached 24h, decrypted in-memory).
2. Maps mail keys → `Config::set('mail.*')` — driver, host, port (cast
   to int), username, password, encryption, from.address, from.name.
3. Maps Moyasar keys → `Config::set('services.moyasar.*')` — publishable_key,
   secret_key, webhook_secret, test_mode, enabled_methods.

Failure mode: if `system_settings` is missing or the cache driver is
unreachable, the middleware silently passes through. Boot-time stability
is non-negotiable for the central app.

Verified by Test 9 — middleware run synthetically against a fresh
Request, post-conditions assert all 4 mail config keys.

---

## 6. Routes

```
GET    /admin/settings                  → SystemSettingController@index
PUT    /admin/settings                  → SystemSettingController@update
POST   /admin/settings/test-mail        → SystemSettingController@testMail
POST   /admin/settings/test-moyasar     → SystemSettingController@testMoyasar
PUT    /admin/settings/legacy/{key}     → CentralSettingController@update   (Phase 3 back-compat)

GET    /super-admin/settings            → SystemSettingController@index
PUT    /super-admin/settings            → SystemSettingController@update
POST   /super-admin/settings/test-mail  → SystemSettingController@testMail
POST   /super-admin/settings/test-moyasar → SystemSettingController@testMoyasar
PUT    /super-admin/settings/legacy/{key} → CentralSettingController@update
```

All Phase C routes carry middleware
`['auth:<guard>', 'admin.role:super_admin', 'apply.system_settings']`
where `<guard>` is `admin` or `super_admin` per Phase B.

---

## 7. Tests

12 regression tests in `tests/Feature/Admin/SystemSettingsBridgeTest.php`,
all passing:

| # | Test | Coverage |
|---|------|----------|
| 1 | `set_and_get_round_trip_plain_value` | Static API basic round-trip. |
| 2 | `encrypted_values_are_stored_as_ciphertext` | Raw DB column ≠ plaintext, decrypt round-trips. |
| 3 | `set_many_invalidates_cache` | `setMany` invalidates `system_settings` cache key. |
| 4 | `service_falls_back_to_central_settings_when_key_missing_in_system_settings` | Read fallback chain. |
| 5 | `super_admin_settings_route_requires_authentication` | Guest → 302/401/403. |
| 6 | `admin_settings_requires_super_admin_role` | Support role → 403. |
| 7 | `super_admin_can_update_settings` | Authorized PUT round-trips through to DB. |
| 8 | `blank_secret_does_not_overwrite_existing_encrypted_value` | Masked + empty re-submits preserve existing secret. |
| 9 | `apply_system_settings_overrides_runtime_mail_config` | Middleware ⇒ `Config::set('mail.*')`. |
| 10 | `test_mail_does_not_expose_password_in_response` | testMail JSON never echoes mail_password. |
| 11 | `test_moyasar_does_not_expose_secret_in_response` | testMoyasar JSON never echoes moyasar_secret_key. |
| 12 | `central_settings_compatibility_dual_write` | Dual-write fires when central row exists. |

**Final suite:** `php artisan test` → **213 passed (558 assertions)**, 0 failures, 0 errors.

---

## 8. Risks Remaining

- **Pre-existing Phase 5 Qoyod boot bug** still breaks `php artisan route:list`
  (eager-bind on a missing config key). Documented earlier in this Path C run,
  out of scope for Phase C — does not affect tests, requests, or runtime.
- **Cache driver dependency.** `SystemSetting::cachedMap()` uses the default
  cache store. In production with Redis this is fine; on machines with
  array-driver cache the 24h TTL effectively becomes per-request, which is
  benign but slightly slower. No action needed.
- **Tenant settings are still independent.** Per-tenant overrides live in
  `tenant_settings` and are NOT touched by `ApplySystemSettings`. This is
  intentional — Phase C scope is central settings only.
- **Sensitive-keys list is hard-coded.** Adding a new secret in the future
  requires editing `SystemSettingsService::SENSITIVE_KEYS` AND adding
  `is_encrypted=true` at the call site. Acceptable for the current 3-key
  surface; revisit if it grows past ~10.

---

## 9. Final Verdict

**SEALED.** All Phase C exit criteria met:

- ✅ `system_settings` table created and migrated.
- ✅ `SystemSetting` model with encryption + casts + cache.
- ✅ `SystemSettingsService` bridge with central fallback + dual-write.
- ✅ `ApplySystemSettings` middleware overriding mail + Moyasar config at runtime.
- ✅ Super Admin 6-tab UI mounted at both `/admin/settings` and `/super-admin/settings`.
- ✅ Sensitive fields masked, empty submissions preserved, test endpoints never leak secrets.
- ✅ Arabic + English translations.
- ✅ 12 new regression tests, all passing.
- ✅ Full suite: **213/213** (201 baseline + 12 new).
- ✅ Zero deletions, zero `composer.json` edits, zero Laravel upgrade.

Ready for Phase D approval.
