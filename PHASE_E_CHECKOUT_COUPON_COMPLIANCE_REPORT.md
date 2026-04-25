# Phase E — Checkout + Coupon Compliance — SEALED

**Status:** ✅ Complete · 245/245 tests passing (224 baseline + 21 new)
**Date:** 2026-04-25
**Path:** Path C Hybrid — Compliance Gap Closure

---

## 1. Summary

Phase E is a **Bridge / Compliance** pass — NOT a billing rebuild. It
closes the spec gaps in the Phase 5 billing engine while leaving every
existing call-site, model, action, and test assertion intact:

- Adds the spec-mandated **`CheckoutController`** with the four required
  methods (`show`, `applyCoupon`, `callback`, `success`) plus a
  `failure` companion view.
- Adds the spec-mandated **`MoyasarService`** at `app/Services/` as a
  thin façade over the existing Phase 5 `MoyasarClient` — exposes
  `createPayment` / `getPayment` / `refundPayment` with raw-array
  signatures while the Phase 5 DTO-based `MoyasarPaymentService` keeps
  serving `ChargeInvoiceAction` unchanged.
- Adds **`CouponService`** that returns an **array** from `validate()`
  (never throws on bad coupons — UI can render the message directly)
  and writes both the new `coupon_uses` audit row and the existing
  atomic `coupons.redemptions_count` counter in **one transaction** —
  the two stores can never drift.
- Adds **`coupon_uses` table + `CouponUse` model** alongside (NOT
  instead of) the Phase 5 `redemptions_count` counter.
- Extends the **Coupon model** with the spec-shaped methods
  (`isValid`, `isExpired`, `hasReachedMaxUses`, `getRemainingUses`,
  `isApplicableToPlan`, `isApplicableToBillingCycle`,
  `calculateDiscount`, `scopeActive`) **on top of** the Phase 5
  `isRedeemable` / `computeDiscount` API. A `uses_count` accessor maps
  the spec name to the Phase 5 column so both naming conventions work.
- Hardens the **Admin Coupon CRUD**: adds `show` + `toggle`; `destroy`
  refuses if the coupon has already been redeemed; `code` is now
  immutable post-creation.
- Updates the **landing pricing CTAs** to point to `checkout.show`
  while leaving `/register` (Phase 9 trial onboarding) untouched.

Zero `composer.json` changes. Zero Laravel-version bumps. Zero
deletions. Zero touches on Phase 5 `ApplyCouponAction`,
`MoyasarPaymentService`, `BillingController`, `Coupon` schema, or any
of the 224 baseline tests.

---

## 2. Existing Billing Reused

Phase E reuses (does NOT replace) all of these Phase 5 components:

| Component | Path | Role |
|-----------|------|------|
| `MoyasarClient` | `app/Services/Billing/Gateway/MoyasarClient.php` | HTTP calls to api.moyasar.com — Phase E `MoyasarService` delegates to it |
| `MoyasarPaymentService` | `app/Services/Billing/Gateway/MoyasarPaymentService.php` | DTO-based gateway; still bound to `PaymentServiceInterface`; still used by `ChargeInvoiceAction` |
| `PaymentServiceInterface` | `app/Contracts/Billing/PaymentServiceInterface.php` | Untouched |
| `ApplyCouponAction` | `app/Actions/Billing/ApplyCouponAction.php` | Untouched — still throws `CouponRedemptionException` for the Phase 5 subscription-attached coupon flow |
| `BillingController@coupon` (tenant portal) | `app/Http/Controllers/Tenant/BillingController.php` | Untouched |
| `Coupon` schema (DB) | `database/migrations/2026_04_22_130000_*` | Untouched — `redemptions_count` stays the source of truth |
| `MoyasarWebhookController` | `app/Http/Controllers/Webhooks/MoyasarWebhookController.php` | Untouched |
| `Subscription.coupon_id` FK | Phase 5 migration | Reused by Phase E checkout `callback` |
| `Invoice.discount_amount`, `Invoice.meta` | Phase 5 migration | Reused; still available for in-tenant invoicing |
| `Payment.meta` JSON | Phase 5 migration | Reused; checkout writes `plan_id`, `billing_cycle`, `company_name`, `owner_email`, `coupon_code` |
| 224 baseline tests | `tests/Feature/**` | All still pass after Phase E |

---

## 3. Coupon Compliance

### Schema bridge

| Spec name | Phase 5 column | Bridge |
|-----------|----------------|--------|
| `uses_count` | `redemptions_count` | `getUsesCountAttribute()` accessor proxies the column. Spec callers and Phase 5 callers both work. |
| `coupon_uses` table | (didn't exist) | New table added in `2026_04_25_130000_create_coupon_uses_table.php`. Written by `CouponService::apply()` IN THE SAME TRANSACTION as the atomic counter increment, so the two sources stay consistent. |

### Methods added to `Coupon` (Phase 5 originals kept verbatim)

```php
public function uses(): HasMany                                     // hasMany(CouponUse)
public function getUsesCountAttribute(): int                        // alias for redemptions_count
public function scopeActive(Builder $query): Builder
public function isValid(): bool
public function isExpired(): bool
public function hasReachedMaxUses(): bool
public function getRemainingUses(): ?int                            // null when uncapped
public function isApplicableToPlan(int $planId): bool               // honours applies_to=specific_plans
public function isApplicableToBillingCycle(string $cycle): bool     // optional, via meta.billing_cycles
public function calculateDiscount(float $amount): float             // alias for computeDiscount
```

### `CouponService` (new — `app/Services/CouponService.php`)

```php
public function validate(string $code, int $planId, string $billingCycle, float $amount): array
public function apply(Coupon $coupon, int|string|null $tenantId, ?int $subscriptionId, float $discountAmount): CouponUse
```

Validation messages (all localised — `lang/{ar,en}/checkout.php`):

| Failure | Message key |
|---------|-------------|
| Empty code | `checkout.coupon.code_required` |
| Code not in DB | `checkout.coupon.not_found` |
| `is_active=false` | `checkout.coupon.inactive` |
| Past `redeem_by` | `checkout.coupon.expired` |
| Exhausted max uses | `checkout.coupon.exhausted` |
| Plan restriction | `checkout.coupon.plan_mismatch` |
| Cycle restriction | `checkout.coupon.cycle_mismatch` (with `:cycle`) |
| Below min amount | `checkout.coupon.min_amount` (with `:amount`, `:currency`) |
| Success | `checkout.coupon.applied` (with `:discount`, `:currency`) |

`apply()` is wrapped in `DB::transaction(...)` and uses
`DB::table('coupons')->increment('redemptions_count')` — a single
SQL `UPDATE coupons SET redemptions_count = redemptions_count + 1`
that is race-safe under concurrent checkouts.

---

## 4. Checkout Flow

### Public routes

```
GET  /checkout/{plan:slug}      → CheckoutController@show         (Bootstrap+jQuery view)
POST /checkout/apply-coupon     → CheckoutController@applyCoupon  (AJAX, throttle:30,1)
GET  /checkout/callback         → CheckoutController@callback     (Moyasar 3DS return)
GET  /checkout/success          → CheckoutController@success
GET  /checkout/failure          → CheckoutController@failure
```

All routes mounted with the `apply.system_settings` middleware, so the
Super-Admin-saved Moyasar publishable key + enabled methods override
env defaults at runtime (Phase C wiring).

### `show()` — checkout form

Renders `resources/views/checkout/show.blade.php`:
- Plan summary panel (name, cycle, original / discount / final amount)
- Company-details form (`company_name`, `owner_name`, `owner_email`,
  `owner_phone`)
- Coupon input + Apply button (calls AJAX endpoint)
- Moyasar.js mount point with `data-publishable-key`,
  `data-amount`, `data-currency`, `data-methods` attributes
- Sandbox notice when `moyasar_test_mode=true`

UI stack: Bootstrap 5 RTL + jQuery 3.7.1 (both via CDN). NO Tailwind,
NO SPA framework, NO custom inline assets beyond a small RTL-aware
style block.

### `applyCoupon()` — AJAX endpoint

Validates payload via `App\Http\Requests\Checkout\ApplyCouponRequest`
(code 1–64, plan_id exists, billing_cycle in monthly/yearly, amount
0–999999.99). Returns:

```json
{
  "valid": true,
  "discount": 20.0,
  "final_amount": 180.0,
  "message": "Coupon applied — 20.00 SAR off.",
  "code": "TENPC"
}
```

The Coupon Eloquent model is **never** serialised to JSON — only the
6 operator-safe scalar fields above. No `description`, no `meta`,
no `redemptions_count` leaks.

### `callback()` — Moyasar 3DS return

```
GET /checkout/callback?id=<moyasar_payment_id>
```

1. Reads payment from gateway via `MoyasarService::getPayment()`.
2. If status not in `[paid, authorized, captured]` → redirect to
   failure page.
3. Idempotency: if a Payment already exists for the gateway id → bounce
   to success (handles refresh on the success page).
4. Inside `DB::transaction(...)`:
   - Find or create `Tenant` by `metadata.company_name`
   - Set `TenantContext::set($tenant)` so `BelongsToTenant` global
     scope writes accept the new rows
   - Create `Subscription` (status=Active, gateway=Moyasar, period
     start=now, end=+1 month or +1 year)
   - If `metadata.coupon_code` resolves to a valid coupon, call
     `CouponService::apply()` (atomic counter + audit row) and set
     `subscription.coupon_id`
   - Create `Payment` (status=Captured, currency from gateway,
     `card_last4` only — no PAN, never)
   - `TenantContext::forget()` in the `finally` block so the request
     doesn't leak tenant state to downstream middleware
5. Redirect to `checkout.success` on success, `checkout.failure` with
   `?reason=invalid` on persistence failure.

### `success()` / `failure()`

Static views (`checkout/success.blade.php`, `checkout/failure.blade.php`)
— centred Bootstrap card with check / cross icon. Failure page also
links back to `/pricing` and `mailto:` the support email pulled from
`SystemSetting::get('support_email')`.

---

## 5. Moyasar Integration

### `MoyasarService` (new — `app/Services/MoyasarService.php`)

Spec-shaped façade:

```php
public function createPayment(array $payload): array
public function getPayment(string $paymentId): array
public function refundPayment(string $paymentId, array $payload): array
public static function toHalalas(float $amountInSar): int          // 1 SAR = 100 halalas
public static function publishableKey(): string                    // safe to render
public static function enabledMethods(): array                     // ['creditcard','applepay','stcpay'] default
```

- Delegates ALL HTTP calls to the existing Phase 5 `MoyasarClient`
  (no duplicated retry/auth/timeout logic).
- Reads keys from `config('services.moyasar.*')` which is already
  overridden at runtime by `ApplySystemSettings` middleware (Phase C).
- Logging discipline: `createPayment` wraps in try/catch and logs
  ONLY a generic `moyasar.create_payment_failed` message — NEVER the
  payload (it may contain a one-shot tokenised source).
- `getPayment` returns the raw decoded array so the callback can read
  `status`, `amount`, `currency`, `source`, `metadata` directly.

### Front-end (Moyasar.js)

The checkout view exposes the necessary attributes
(`data-publishable-key`, `data-amount`, `data-currency`,
`data-methods`) on a `#moyasar-placeholder` div. Wiring Moyasar.js
itself is intentionally left as a `{{-- mount point --}}` comment in
the view — it depends on the operator's exact Moyasar widget version
(.v1 vs .v2) and is the Front-End team's deliverable. The data flow
is fully wired backend-side: the gateway will POST back to
`/checkout/callback?id=…` once payment completes.

Supported methods (from the spec):
- `creditcard` — Visa / Mastercard / mada
- `applepay`
- `stcpay`

---

## 6. Routes

### Central (added in `routes/central.php`)

```
POST /checkout/apply-coupon  → CheckoutController@applyCoupon  (throttle:30,1)
GET  /checkout/callback      → CheckoutController@callback
GET  /checkout/success       → CheckoutController@success
GET  /checkout/failure       → CheckoutController@failure
GET  /checkout/{plan:slug}   → CheckoutController@show
```

Order matters: static paths registered BEFORE the `{plan:slug}`
catch-all so Laravel doesn't treat `callback` as a slug.

### Admin (added in `routes/admin.php` AND `routes/super-admin.php`)

```
GET  /admin/coupons/{coupon}            → CouponController@show       (all admin roles)
POST /admin/coupons/{coupon}/toggle     → CouponController@toggle     (super_admin only)
```

### Untouched

All Phase 5 routes (`/t/{tenant}/billing/*`, `/webhooks/moyasar`,
`/admin/coupons` CRUD index/create/store/edit/update/destroy,
`/register`, `/onboarding/welcome`) are 100% as Phase 5 left them.

---

## 7. Tests

### `tests/Feature/Coupons/CouponServiceComplianceTest.php` — 11 tests

| # | Test | Coverage |
|---|------|----------|
| 1 | `invalid_coupon_returns_array_no_exception` | Bad code → array, never throws |
| 2 | `expired_coupon_message` | Past `redeem_by` → expired message |
| 3 | `max_uses_reached_message` | `redemptions_count >= max_redemptions` → exhausted |
| 4 | `plan_restriction` | `applies_to=specific_plans` honoured for both ok+bad cases |
| 5 | `billing_cycle_restriction` | `meta.billing_cycles=['yearly']` rejects monthly |
| 6 | `min_order_restriction` | `min_amount` enforced + value in message |
| 7 | `percentage_discount_calculation` | 10% of 200 = 20 |
| 8 | `fixed_discount_calculation` | 50 fixed off 200 = 150 final |
| 9 | `remaining_uses_helper` | null for uncapped, 7 for 10/3 |
| 10 | `apply_creates_coupon_use_row` | Audit row written |
| 11 | `apply_increments_redemptions_count_atomically` | 3 calls → counter=3, 3 rows; `uses_count` accessor agrees |

### `tests/Feature/Checkout/CheckoutComplianceTest.php` — 10 tests

| # | Test | Coverage |
|---|------|----------|
| 1 | `checkout_page_loads` | GET `/checkout/{plan}` returns 200, plan name visible |
| 2 | `apply_coupon_ajax_returns_valid_json` | JSON shape: valid/discount/final_amount/code |
| 3 | `invalid_coupon_ajax_returns_valid_false` | Same shape with valid=false |
| 4 | `callback_rejects_unpaid_payment` | Http::fake() failed → redirect to failure, no DB rows |
| 5 | `callback_creates_payment_subscription_tenant_on_paid` | Full happy-path provisioning |
| 6 | `coupon_code_metadata_applied_on_callback` | Coupon resolved → `subscription.coupon_id` + atomic counter + `coupon_uses` row |
| 7 | `success_page_loads` | GET `/checkout/success` returns 200 |
| 8 | `failure_page_loads` | GET `/checkout/failure?reason=unpaid` returns 200 |
| 9 | `no_secret_appears_in_checkout_view` | `moyasar_secret_key` plaintext NEVER in HTML; publishable IS rendered |
| 10 | `landing_pricing_links_point_to_checkout` | GET `/` contains `href="/checkout/<slug>"` |

**Final suite:** `php artisan test` → **245 passed (661 assertions)**, 0 failures, 0 errors.

---

## 8. Risks Remaining

- **Moyasar.js front-end wiring is left as a placeholder.** The view
  exposes all necessary data-attributes and the `callback_url` route,
  but the actual `Moyasar.init({...})` JS call is the front-end team's
  job (it depends on the exact Moyasar widget version they integrate).
  The backend flow is fully testable today via the `Http::fake()`
  pattern used in tests #4–#6.

- **Card data is never stored.** Only `card_last4` and `card_brand`
  end up in `payments` — both delivered by Moyasar in the
  payment.source array. No PAN, no CVV, no expiry. Confirmed by code
  review of `extractLast4`/`extractBrand` helpers.

- **`callback` is a GET.** Standard for 3DS gateway returns (Moyasar
  redirects the user back via browser navigation). It's idempotent —
  duplicate hits return a 302 to success without creating a second
  Tenant/Subscription/Payment, thanks to the
  `Payment::where('gateway_payment_id', ...)` early-return.

- **CouponService uses `DB::table('coupons')->increment(...)` directly.**
  This bypasses Eloquent events on Coupon; intentional, since we want
  the increment to be a single atomic SQL `UPDATE` rather than
  fetch-then-save. If business listeners need to fire on redemption,
  attach them to the `CouponUse` model events — that row IS Eloquent.

- **`callback` does NOT create a User/login.** It provisions Tenant +
  Subscription + Payment, but the operator still has to register a
  user via `/register?plan=...` to actually log in. A future Phase
  could move user creation into the checkout, but that requires
  password handling, email verification, and trial-vs-paid path
  unification — out of Phase E scope.

- **Coupon `code` immutability is enforced at the controller layer
  (CouponController::update unsets `code` from the payload).** If a
  future code path bypasses CouponController and writes directly via
  `Coupon::find(...)->update(['code' => 'X'])`, it will succeed. A
  model-event guard would be more defensive — recommended for Phase F+.

- **No CSRF token check on `/checkout/callback`.** The gateway can't
  send CSRF tokens; that's expected. Moyasar's webhook handler
  (separate route) does signature verification. The callback is safe
  because (a) it requires a real `id` that resolves at the gateway,
  (b) it's idempotent, (c) it never trusts metadata for status — only
  the gateway-returned `status` field decides success.

---

## 9. Files Changed

**New (12)**

```
database/migrations/2026_04_25_130000_create_coupon_uses_table.php
app/Models/CouponUse.php
app/Services/CouponService.php
app/Services/MoyasarService.php
app/Http/Controllers/CheckoutController.php
app/Http/Requests/Checkout/ApplyCouponRequest.php
resources/views/checkout/show.blade.php
resources/views/checkout/success.blade.php
resources/views/checkout/failure.blade.php
resources/views/admin/coupons/show.blade.php
lang/ar/checkout.php
lang/en/checkout.php
tests/Feature/Coupons/CouponServiceComplianceTest.php
tests/Feature/Checkout/CheckoutComplianceTest.php
PHASE_E_CHECKOUT_COUPON_COMPLIANCE_REPORT.md
```

**Modified (5)**

```
app/Models/Coupon.php                              ← added 9 spec methods + uses() relation + uses_count accessor
app/Http/Controllers/Admin/CouponController.php    ← added show + toggle + destroy guard + code-immutability
routes/central.php                                 ← added 5 checkout routes
routes/admin.php                                   ← added show + toggle routes for coupons
routes/super-admin.php                             ← mirrored show + toggle
resources/views/marketing/landing.blade.php        ← pricing CTA → checkout.show
```

---

## 10. Verdict

**SEALED.** All Phase E exit criteria met:

- ✅ `CheckoutController` with `show` / `applyCoupon` / `callback` / `success` (+ `failure` companion)
- ✅ `MoyasarService` with `createPayment` / `getPayment` / `refundPayment` (delegates to Phase 5 `MoyasarClient` — no duplication)
- ✅ Moyasar.js placeholder wired with publishable key + enabled methods (`creditcard`, `applepay`, `stcpay`)
- ✅ `CouponService::validate()` returns array, never throws on bad coupon
- ✅ `CouponService::apply()` uses atomic `DB::increment('redemptions_count')` inside `DB::transaction`, writes `coupon_uses` audit row
- ✅ Checkout metadata captures `plan_id`, `billing_cycle`, `company_name`, `owner_*`, `coupon_code`
- ✅ Coupon model: `isValid`, `isApplicableToPlan`, `isApplicableToBillingCycle`, `hasReachedMaxUses`, `isExpired`, `calculateDiscount`, `getRemainingUses`, `scopeActive`, `uses()` relation, `uses_count` accessor
- ✅ `coupon_uses` table + `CouponUse` model
- ✅ Admin Coupon CRUD: index, create, store, **show**, edit, update, destroy, **toggle** + destroy guard against used coupons + code immutability
- ✅ ar + en checkout translations
- ✅ Landing pricing CTAs route to `checkout.show`
- ✅ 21 new regression tests, all passing
- ✅ Full suite: **245/245** (224 baseline + 21 new) — Phase 5 billing tests untouched
- ✅ Zero deletions, zero `composer.json` edits, zero Laravel upgrade, zero Tailwind, zero SPA framework
- ✅ Card data never stored, secret key never in JSON / HTML / logs

Ready for Phase F approval.
