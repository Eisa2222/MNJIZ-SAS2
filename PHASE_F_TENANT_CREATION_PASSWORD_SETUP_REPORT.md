# Phase F — Tenant Creation Job + Password Setup + Secure Welcome Flow — SEALED

**Status:** ✅ Complete · 261/261 tests passing (245 baseline + 16 new)
**Date:** 2026-04-26
**Path:** Path C Hybrid — Compliance Gap Closure

---

## 1. Summary

Phase F replaces the Phase 9 / Phase E synchronous "create user with a
password from the signup form (or auto-create on checkout)" pattern
with the spec-mandated **secure-onboarding** flow:

- **`CreateTenantJob`** runs on the queue and provisions the owner
  `User` with an unreachable placeholder password.
- A 48h **signed setup URL** is issued via `URL::temporarySignedRoute`
  and emailed via the new **`TenantWelcomeMail`** — there is no
  plaintext (or temporary) password anywhere in the message.
- A new **`TenantPasswordSetupController`** validates the URL signature
  AND the per-token sha256 hash, then lets the user pick their real
  password. The token row is deleted on success — one-shot link.
- Optional Super Admin notification fires via
  **`NewTenantSubscriptionNotification`** when
  `notify_new_subscription` is true and `admin_notification_email` is
  configured (Phase C SystemSetting tab).
- Both the `/register` trial flow AND the Phase E checkout `callback`
  now dispatch the job — but ONLY when the
  `tenancy.signup.use_setup_link` feature flag is on. The legacy
  inline-password path remains the default so Phase 9 / Phase E test
  baselines (incl. the 245-test suite) are 100 % preserved.

Zero `composer.json` changes. Zero Laravel-version bumps. Zero deletions.
Zero touches on Phase E's synchronous Tenant + Subscription + Payment
creation inside the checkout transaction (the spec required atomicity
there for the Phase 5 billing tests, and Phase F respects it — the
secondary work is what gets queued).

---

## 2. Discovery Findings

| Area | Pre-Phase-F state | Phase F bridge |
|------|-------------------|---------------|
| `PublicSignupController::store()` | Validates `password` from form, hashes, creates `User`, calls `auth()->login()`, sends `Marketing\WelcomeMail` (queued), redirects to `onboarding.welcome`. | Adds a feature-flag branch: when on, password rule is removed, no inline user creation, no auto-login, dispatches `CreateTenantJob`, redirects to `checkout.account-pending`. |
| `CheckoutController::callback()` | Synchronously creates Tenant + Subscription + Payment + CouponUse inside `DB::transaction()` then redirects to success. | Keeps the synchronous block intact (Phase E tests #5, #6 require it). After the commit, when the flag is on, also dispatches `CreateTenantJob` and redirects to `checkout.account-pending`. |
| `Tenant` model | `name`, `slug`, `domain`, `status`, `plan_id`, `meta`. | Unchanged. |
| `User` model | Has `password`, `must_change_password`, `status`, `email_verified_at`. Uses `BelongsToTenant`. | Unchanged. We simply set `password = placeholder hash` + `must_change_password = true` + `email_verified_at = null` until the setup form is completed. |
| `WelcomeMail` (Phase 9) | Generic welcome email queued at end of trial signup. | Untouched — still used by the legacy inline-password path. New `TenantWelcomeMail` is for the setup-link flow. |
| `password_reset_tokens` (Laravel default) | 5-min expiry, used by tenant `users` broker. | Untouched — Phase F adds a SEPARATE `tenant_password_setup_tokens` table with a 48h expiry. |
| `config/auth.php` | 3 guards (`web`, `admin`, `super_admin`) + 3 brokers. | Untouched — the new flow uses signed URLs, not Laravel's password broker. |
| `routes/central.php` | `/register`, `/checkout/*`, `/onboarding/welcome`, etc. | Adds `/password/setup/{token}` (signed, GET) + `/password/setup` (throttled, POST) + `/checkout/account-pending`. |
| `routes/tenant.php` | Health check + tenant billing portal only. | Untouched. The setup routes are central because the user is pre-auth. |
| `config/queue.php` | `QUEUE_CONNECTION=database` in real env, `sync` in tests. | Sync test driver runs jobs immediately, so `Bus::fake()` / `Mail::fake()` keep tests deterministic. |

### Compatibility risks identified — and how Phase F addresses each

1. **CheckoutComplianceTest #5, #6 require synchronous DB persistence.**
   → Phase F leaves the `DB::transaction` block 100 % intact. The job
   only handles secondary work that runs AFTER the commit.
2. **`Auth\RegistrationTest` exists in the (excluded-from-suite) Breeze
   directory.** → The new Phase F tests live in
   `tests/Feature/TenantAuth/` (not excluded). Default feature flag is
   `false` so the legacy `PublicSignupController` behaviour is bit-for-bit
   preserved.
3. **`TenantAwareJob` trait must capture tenant id at dispatch.** →
   `TenantWelcomeMail` already uses it (constructor calls
   `captureTenant()`); `CreateTenantJob` itself doesn't need the trait
   because it explicitly resolves the tenant by id and uses
   `TenantContext::runAs()` to scope each tenant-aware operation.

---

## 3. Tenant Creation Flow

### `App\Jobs\CreateTenantJob`

```
public function __construct(public array $payload)
public function handle(TenantPasswordSetupService $setup): void
```

**Required payload keys:** `tenant_id`, `owner_email`, `owner_name`, `source`
**Optional:** `plan_id`, `billing_cycle`, `subscription_id`, `payment_id`, `coupon_code`, `owner_phone`

**Execution steps**

1. Loads the Tenant (returns silently if not found).
2. Loads the optional Subscription under `TenantContext::runAs(...)` so
   `BelongsToTenant` doesn't filter it out.
3. **DB::transaction**:
    - Resolves or creates the owner `User` under `TenantContext::runAs()`:
        - Looked up by `tenant_id + email` (idempotent — re-running this
          job on the same payload returns the existing row, never
          duplicates).
        - Created with `password = TenantPasswordSetupService::placeholderPasswordHash()`
          (bcrypt of 64-byte random — unreachable via guesswork) and
          `must_change_password = true`.
4. `TenantPasswordSetupService::issue($user)` writes a row to
   `tenant_password_setup_tokens` with the sha256 hash of a fresh 64-hex
   plaintext token, and returns a **48h signed URL**. The plaintext
   token is NEVER persisted.
5. `Mail::to($user->email)->queue(new TenantWelcomeMail(...))` — the
   mail itself goes to the mail queue; the job returns immediately.
6. **Optional** Super Admin notification — see §6.

**Idempotency guarantees**

| Concern | Guard |
|---------|-------|
| Duplicate User on retry | `where(tenant_id, email)` lookup before insert |
| Duplicate setup link | `updateOrInsert(['email' => …])` replaces prior row |
| Duplicate mail send | Job exits cleanly on missing tenant/empty payload; mail queue itself is the consumer's responsibility |
| Notification flood | Opt-in via `notify_new_subscription` and only when `admin_notification_email` is configured |

**Tries / backoff:** 3 tries, 30s backoff. Failure logs a generic
`create_tenant_job.*` event with `tenant_id` only — never the token,
URL, or password.

---

## 4. Password Setup Flow

### Database

**Table:** `tenant_password_setup_tokens`

```
email      string(191) PRIMARY
token      string(191)         ← sha256(plaintext), never plaintext
created_at timestamp default CURRENT_TIMESTAMP
```

Migration: `2026_04_25_140000_create_tenant_password_setup_tokens_table.php`.

**One outstanding row per email** — re-issuing always replaces the
prior token, invalidating any older email automatically.

### `App\Services\Auth\TenantPasswordSetupService`

```
public const ROUTE_NAME  = 'tenant.password.setup'
public const VALID_HOURS = 48

public function issue(User $user): string                  // returns signed URL
public function verify(string $email, string $token): bool // hash compare + 48h check
public function consume(string $email): void               // single-use guarantee
public static function placeholderPasswordHash(): string   // bcrypt(random_64)
public function forceIssueForTesting(string $email): string // returns plaintext token
```

### Routes

```
GET  /password/setup/{token}    → TenantPasswordSetupController@show
                                  middleware: signed
                                  where token = [A-Fa-f0-9]{64}

POST /password/setup             → TenantPasswordSetupController@store
                                  middleware: throttle:6,1
```

### Controller

- `show()` — checks signed URL (handled by `signed` middleware) THEN
  re-verifies the token by sha256 hash compare. Returns 403 with a
  generic `auth.setup.invalid_or_expired` message on any failure (no
  account-enumeration leakage).
- `store()` — Form Request validates payload (email, token, password
  ≥ 8, confirmed). Re-verifies the token. Force-fills the new
  password (hashed), `must_change_password = false`, `status = active`,
  `email_verified_at = now()`. Calls `consume()` to delete the token.
  Redirects to `/login` with a success flash. Never auto-logs in
  (consistent with Phase 5 password-reset).

---

## 5. Mail Flow

### `App\Mail\TenantWelcomeMail`

- Implements `ShouldQueue` + uses `TenantAwareJob` trait
  (constructor calls `captureTenant()` so the tenant context is
  re-seated on the mail-queue worker).
- Subject: `__('emails.tenant_welcome.subject', ['app' => :app])`
- View: `resources/views/emails/tenant/welcome.blade.php`

### Email content

Sections rendered in the email:

1. **Header** — gradient banner, "Welcome to {app_name}".
2. **Greeting** — addressed by user's name.
3. **Account summary table** — firm name, owner email, plan name,
   billing cycle (monthly/yearly), trial end / period end. NO password,
   NO secret, NO payment id.
4. **Setup CTA** — bold button → setup URL.
5. **Fallback URL** — plaintext link for clients that strip buttons.
6. **Login URL** — for after the password is set.
7. **Support email** — pulled from `SystemSetting::get('support_email')`.
8. **Security notice** — explicit "we will never ask for your password
   via email or phone" footer.

### What the email NEVER contains

- Plaintext or "temporary" passwords (verified by test #4 —
  `welcome_mail_does_not_contain_plaintext_password`).
- Moyasar secret key, payment id, card last4.
- The plaintext setup token outside the URL itself (it's only
  in the `?token=` query parameter, covered by the URL signature).

---

## 6. Signup Integration

### Default mode (`tenancy.signup.use_setup_link = false`) — LEGACY

`PublicSignupController::store()` behaves EXACTLY as Phase 9:
- Requires `password` + `password_confirmation` in the form
- Creates User inline with `Hash::make($data['password'])`
- Calls `auth()->login($user)`
- Sends queued `Marketing\WelcomeMail`
- Redirects to `onboarding.welcome`

→ **All pre-Phase-F tests (incl. legacy Breeze RegistrationTest behaviour
expectations) keep passing.**

### Phase F mode (`tenancy.signup.use_setup_link = true`)

- Validation rules drop `password`, add optional `phone`.
- `DB::transaction` creates Tenant + Subscription only (no User).
- Dispatches `CreateTenantJob` with `source = 'trial'`.
- Redirects to `checkout.account-pending` page.
- **No `auth()->login()`** — the visitor must click the email link.

→ Tests #6, #8 in `CreateTenantJobTest` cover this branch.

---

## 7. Checkout Integration

### Default mode

`CheckoutController::callback()` is unchanged from Phase E — the
synchronous `DB::transaction` creates Tenant + Subscription + Payment
+ CouponUse and redirects to `checkout.success`.

### Phase F mode

After the synchronous transaction commits, the controller:
1. Re-resolves the freshly-created Tenant + Subscription + Payment by
   id (we're outside the tenant context here, so `withoutGlobalScopes()`).
2. Dispatches `CreateTenantJob` with `source = 'paid_checkout'`,
   passing the resolved subscription_id + payment_id + coupon_code.
3. Redirects to `checkout.account-pending` instead of
   `checkout.success`.

→ Test #7 in `CreateTenantJobTest`
(`checkout_callback_dispatches_job_when_flag_on`) verifies this.

→ Phase E tests #5, #6 still pass because the synchronous block is
unchanged when the flag is off (which is the test default).

---

## 8. Security Guarantees

| Concern | Guarantee |
|---------|-----------|
| **No plaintext password in any email** | `TenantWelcomeMail` view contains only the setup URL. Test #4 `welcome_mail_does_not_contain_plaintext_password` greps for "temporary password", "your password is", "temp password" patterns. |
| **48h signed URL** | `URL::temporarySignedRoute(ROUTE_NAME, now()->addHours(48), [...])` — Laravel's `signed` middleware rejects tampered or expired URLs with 403. |
| **Token hash at rest** | Only sha256 hash is stored in `tenant_password_setup_tokens.token`. A DB leak never reveals a working token. Verify uses `hash_equals()` for constant-time compare. |
| **One-shot link** | `consume()` deletes the row after a successful setup. Test #5 confirms re-submitting the same token after success fails with `assertSessionHasErrors(['email'])`. |
| **No token / URL in logs** | All logs use `tenant_id` only. No `Log::info($url)`, `Log::info($token)`, or `Log::info($payload)` anywhere in `CreateTenantJob`, `TenantPasswordSetupService`, or `TenantPasswordSetupController`. |
| **Account enumeration resistance** | `show()` and `store()` both return the same generic `auth.setup.invalid_or_expired` message regardless of whether email/token/expiry was the actual failure reason. |
| **Throttling** | POST `/password/setup` is throttled `6,1` (6 req/min). |
| **No auto-login on setup** | Controller redirects to `/login` after success — operator re-enters credentials, consistent with Phase 5 password-reset. |
| **Placeholder password unreachable** | `placeholderPasswordHash()` = bcrypt of `Str::random(64)`. ~10^77 keyspace. Effectively unreachable via guesswork. |

---

## 9. Tests

### `tests/Feature/Jobs/CreateTenantJobTest.php` — 10 tests

| # | Test | Coverage |
|---|------|----------|
| 1 | `job_creates_tenant_admin_user` | User created with tenant_id + must_change_password=true |
| 2 | `job_is_idempotent` | Running job 3× yields exactly 1 user row |
| 3 | `welcome_mail_is_queued` | `Mail::assertQueued(TenantWelcomeMail)` |
| 4 | `welcome_mail_does_not_contain_plaintext_password` | Greps render() for "temporary password", "your password is" |
| 5 | `welcome_mail_contains_signed_setup_link` | URL contains `/password/setup/`, `signature=`, `expires=` |
| 6 | `trial_signup_dispatches_job_when_flag_on` | `Bus::assertDispatched(CreateTenantJob)` after POST /register |
| 7 | `checkout_callback_dispatches_job_when_flag_on` | `Bus::assertDispatched(CreateTenantJob)` after GET /checkout/callback |
| 8 | `no_auto_login_when_setup_link_flow_enabled` | `$this->assertGuest()` post-signup |
| 9 | `super_admin_notification_sent_when_enabled` | `Notification::assertSentTo` AnonymousNotifiable @ ops@mnjiz.sa |
| 10 | `disabled_notification_does_nothing` | `Notification::assertNothingSent` when toggle is false |

### `tests/Feature/TenantAuth/TenantPasswordSetupTest.php` — 6 tests

| # | Test | Coverage |
|---|------|----------|
| 1 | `setup_link_valid_for_48_hours` | Fresh URL → 200 |
| 2 | `expired_setup_link_rejected` | URL signed 50h ago → 403 |
| 3 | `tampered_signature_rejected` | Email param swapped → 403 |
| 4 | `password_setup_updates_password` | POST sets new hash + must_change_password=false |
| 5 | `token_is_deleted_after_successful_setup` | `tenant_password_setup_tokens` count goes 1 → 0; re-use fails |
| 6 | `wrong_token_returns_403` | Properly signed URL with bogus token → 403 (defence in depth) |

**Total Phase F new tests:** 16 (over the 14-test target).

**Final suite:** `php artisan test` → **261 passed (690 assertions)**, 0 failures, 0 errors.

---

## 10. Files Changed

**New (12)**

```
database/migrations/2026_04_25_140000_create_tenant_password_setup_tokens_table.php
app/Jobs/CreateTenantJob.php
app/Services/Auth/TenantPasswordSetupService.php
app/Mail/TenantWelcomeMail.php
app/Notifications/NewTenantSubscriptionNotification.php
app/Http/Controllers/Auth/TenantPasswordSetupController.php
app/Http/Requests/Auth/TenantPasswordSetupRequest.php
resources/views/emails/tenant/welcome.blade.php
resources/views/auth/tenant-password-setup.blade.php
resources/views/checkout/account-pending.blade.php
lang/ar/auth.php                                                       (new file)
lang/ar/emails.php                                                     (new file)
lang/en/emails.php                                                     (new file)
tests/Feature/Jobs/CreateTenantJobTest.php
tests/Feature/TenantAuth/TenantPasswordSetupTest.php
PHASE_F_TENANT_CREATION_PASSWORD_SETUP_REPORT.md
```

**Modified (5)**

```
config/tenancy.php                                          ← added signup.use_setup_link feature flag
routes/central.php                                          ← added password setup + account-pending routes
app/Http/Controllers/Auth/Signup/PublicSignupController.php ← feature-flag branch dispatching CreateTenantJob
app/Http/Controllers/CheckoutController.php                 ← post-commit job dispatch + accountPending() method
lang/en/auth.php                                            ← added auth.setup.* keys
lang/ar/checkout.php                                        ← added checkout.pending.* keys
lang/en/checkout.php                                        ← added checkout.pending.* keys
```

**Untouched (preserved by design)**

- `Marketing\WelcomeMail` + `emails.marketing.welcome` view (legacy
  trial flow still uses these when the flag is off)
- Phase 5 `Subscription`, `Coupon`, `ApplyCouponAction`, billing tests
- Phase E `CheckoutController` synchronous DB::transaction block + 21 tests
- Laravel's stock `password_reset_tokens` table + Phase 5 password
  reset flow (`/forgot-password`, `/reset-password/{token}`)
- `config/auth.php` (no new guards or brokers)

---

## 11. Risks Remaining

- **Feature flag is OFF by default.** Production deployments must
  flip `TENANT_SIGNUP_USE_SETUP_LINK=true` (or `tenancy.signup.use_setup_link`)
  to activate the secure flow. This is intentional — it lets each
  environment opt in after operator readiness review (welcome email
  template review, Mailgun/SES warm-up confirmation, etc.).

- **The signup form view** (`resources/views/auth/signup.blade.php`)
  still renders password fields in legacy mode. When the flag is on,
  the controller no longer requires them but the view still shows
  them. A small follow-up should hide them when the flag is on so the
  user isn't asked for a password they won't actually need. Out of
  Phase F scope (no controller / security regression).

- **Phase E tests #5, #6 still expect synchronous Tenant + Subscription
  + Payment in the DB** — Phase F preserves this exactly. The job is
  dispatched AFTER the commit and only when the flag is on, so under
  the default (flag off) test environment these assertions remain
  identical.

- **Token table grows unbounded.** A user who never clicks the setup
  link leaves a row behind forever. A simple scheduled command
  (`php artisan tokens:prune`) could delete rows older than 48h, but
  the failure mode is benign: an expired row's URL fails the `signed`
  middleware check anyway, so the row is just dead weight. A future
  housekeeping job is recommended but out of Phase F scope.

- **Notification routes via `mail` only.** Phase C's
  `admin_notification_email` is a single email address, so we use
  `Notification::route('mail', $email)`. If a future phase wants to
  fan out to multiple super admins or to Slack, the
  `NewTenantSubscriptionNotification::via()` array can be extended
  without touching the job.

- **No one-click "resend setup link" UI yet.** A user who loses the
  welcome email today has to ask support. Adding a self-service
  "resend link" button on the login page is recommended for Phase G+.

- **The legacy `Marketing\WelcomeMail` is still queued** in legacy
  signup mode. When the operator flips the flag, the legacy mail will
  no longer be sent (the controller branch skips it). The mail class
  is kept in code for any operator who needs to roll back the flag.

---

## 12. Verdict

**SEALED.** All Phase F exit criteria met:

- ✅ `CreateTenantJob` runs via Queue, accepts the spec-mandated
  payload, idempotent, uses `DB::transaction`, sets up tenant context
  safely
- ✅ NEVER sends a temporary or plaintext password
- ✅ Issues 48h signed URL via `URL::temporarySignedRoute('tenant.password.setup', ...)`
- ✅ `TenantWelcomeMail` Mailable + Blade view rendering the link only
- ✅ Password broker review — kept `users` broker untouched; new
  `tenant_password_setup_tokens` table with 48h expiry serves the new flow
- ✅ `TenantPasswordSetupController` `show` + `store` with signed URL
  verification + token consumption
- ✅ Trial signup integration via feature flag (legacy preserved)
- ✅ Checkout integration via feature flag (Phase E sync block preserved)
- ✅ Account-pending Blade view (RTL, Bootstrap, no Tailwind)
- ✅ Trial settings sourced from `SystemSetting`
  (`notify_new_subscription`, `admin_notification_email`)
- ✅ ar + en translations for the new auth, email, and checkout keys
- ✅ 16 new regression tests, all passing
- ✅ Full suite: **261/261** (245 baseline + 16 new) — every Phase A→E
  test untouched
- ✅ Zero deletions, zero `composer.json` edits, zero Laravel upgrade,
  zero Tailwind, zero SPA framework
- ✅ No password, token, or URL ever printed to logs

Ready for Phase G approval.
