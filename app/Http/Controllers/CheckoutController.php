<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Billing\BillingCycle;
use App\Enums\Billing\PaymentGateway;
use App\Enums\Billing\PaymentStatus;
use App\Enums\Billing\SubscriptionStatus;
use App\Http\Requests\Checkout\ApplyCouponRequest;
use App\Jobs\CreateTenantJob;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Services\CouponService;
use App\Services\MoyasarService;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase E — public checkout flow.
 *
 *   GET  /checkout/{plan}            → show         (form + Moyasar.js)
 *   POST /checkout/apply-coupon      → applyCoupon  (AJAX JSON)
 *   GET  /checkout/callback          → callback     (Moyasar 3DS return)
 *   GET  /checkout/success           → success
 *   GET  /checkout/failure           → failure
 *
 * Design constraints:
 *
 *   • This is a NEW flow alongside the Phase 9 trial-signup at /register.
 *     /register stays as the no-card-required trial onboarding; checkout
 *     is for the (post-trial-or-direct) paid subscription path.
 *
 *   • No card data ever touches our server. Moyasar.js tokenises in the
 *     browser; we receive only the resulting `source.token` string.
 *
 *   • Secrets never appear in JSON responses or in the rendered HTML.
 *     Only `services.moyasar.publishable_key` reaches the view.
 *
 *   • The `callback` flow is wrapped in DB::transaction() so a partial
 *     failure (e.g. Subscription created but Payment insert fails) is
 *     rolled back cleanly — the operator sees the failure page and can
 *     retry without orphan rows.
 *
 *   • CouponService::apply() does the atomic
 *     `coupons.redemptions_count++` + writes a `coupon_uses` row in one
 *     inner transaction, so the discount audit trail is consistent
 *     with Phase 5's source-of-truth counter.
 */
final class CheckoutController extends Controller
{
    public function __construct(
        private CouponService $coupons,
        private MoyasarService $moyasar,
    ) {}

    // ─────────────────────────────────────────────────────────────────
    // GET /checkout/{plan}
    // ─────────────────────────────────────────────────────────────────
    public function show(Request $request, Plan $plan): View|RedirectResponse
    {
        if (! $plan->is_active || $plan->is_free) {
            return redirect()->route('marketing.pricing')->with('status', __('checkout.coupon.plan_mismatch'));
        }

        $cycle = $request->query('cycle', 'monthly');
        if (! in_array($cycle, ['monthly', 'yearly'], true)) {
            $cycle = 'monthly';
        }

        $amount = $cycle === 'yearly'
            ? (float) $plan->price_yearly
            : (float) $plan->price_monthly;

        return view('checkout.show', [
            'plan'              => $plan,
            'billing_cycle'     => $cycle,
            'amount'            => $amount,
            'currency'          => $plan->currency ?? 'SAR',
            'publishable_key'   => MoyasarService::publishableKey(),
            'enabled_methods'   => MoyasarService::enabledMethods(),
            'app_name'          => SystemSetting::get('app_name', 'MNJIZ'),
            'sandbox'           => (bool) SystemSetting::get('moyasar_test_mode', true),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /checkout/apply-coupon (AJAX)
    // ─────────────────────────────────────────────────────────────────
    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $result = $this->coupons->validate(
            code:          (string) $request->validated('code'),
            planId:        (int) $request->validated('plan_id'),
            billingCycle:  (string) $request->validated('billing_cycle'),
            amount:        (float) $request->validated('amount'),
        );

        // The 'coupon' Eloquent model is dropped from the JSON output —
        // we only echo the operator-safe fields (no model attributes
        // leak, especially never the `meta` or `description` columns).
        return response()->json([
            'valid'        => (bool) $result['valid'],
            'discount'     => (float) $result['discount'],
            'final_amount' => (float) $result['final_amount'],
            'message'      => (string) $result['message'],
            'code'         => $result['coupon']?->code,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /checkout/callback?id=<moyasar_payment_id>
    // ─────────────────────────────────────────────────────────────────
    public function callback(Request $request): RedirectResponse|Response
    {
        $paymentId = (string) $request->query('id', '');

        if ($paymentId === '') {
            return redirect()->route('checkout.failure', ['reason' => 'invalid']);
        }

        try {
            $payment = $this->moyasar->getPayment($paymentId);
        } catch (\Throwable $e) {
            Log::warning('checkout.callback.fetch_failed', [
                'gateway_payment_id' => $paymentId,
                'message'            => $e->getMessage(),
            ]);
            return redirect()->route('checkout.failure', ['reason' => 'invalid']);
        }

        $status = strtolower((string) ($payment['status'] ?? ''));

        if (! in_array($status, ['paid', 'authorized', 'captured'], true)) {
            return redirect()->route('checkout.failure', ['reason' => 'unpaid']);
        }

        $metadata = is_array($payment['metadata'] ?? null) ? $payment['metadata'] : [];

        $planId      = (int) ($metadata['plan_id']      ?? 0);
        $cycle       = (string) ($metadata['billing_cycle'] ?? 'monthly');
        $companyName = (string) ($metadata['company_name'] ?? '');
        $ownerEmail  = (string) ($metadata['owner_email'] ?? '');
        $ownerName   = (string) ($metadata['owner_name']  ?? '');
        $ownerPhone  = (string) ($metadata['owner_phone'] ?? '');
        $couponCode  = (string) ($metadata['coupon_code'] ?? '');

        $plan = Plan::find($planId);
        if (! $plan) {
            return redirect()->route('checkout.failure', ['reason' => 'invalid']);
        }

        // Idempotency — if we already processed this gateway payment id,
        // just bounce to success. Stops double-creation if the user
        // refreshes the callback URL.
        $existing = Payment::query()
            ->withoutGlobalScopes()
            ->where('gateway_payment_id', $paymentId)
            ->first();

        if ($existing) {
            return redirect()->route('checkout.success');
        }

        try {
            DB::transaction(function () use (
                $plan, $cycle, $companyName, $ownerEmail, $ownerName, $ownerPhone,
                $couponCode, $payment, $paymentId,
            ): void {
                // 1. Tenant — create OR find by company_name.
                $tenant = Tenant::query()
                    ->where('name', $companyName !== '' ? $companyName : $ownerEmail)
                    ->first();

                if (! $tenant) {
                    $slug   = $this->slugify($companyName !== '' ? $companyName : $ownerEmail);
                    $tenant = Tenant::create([
                        'name'   => $companyName !== '' ? $companyName : $ownerEmail,
                        'slug'   => $slug,
                        'status' => 'active',
                    ]);
                }

                // BelongsToTenant scope requires a current tenant context
                // for Subscription / Payment writes — set + reset around
                // the inserts, so this controller doesn't pollute the
                // request-scoped tenant for downstream middleware.
                TenantContext::set($tenant);

                try {
                    $billingCycle = $cycle === 'yearly' ? BillingCycle::Yearly : BillingCycle::Monthly;
                    $now          = now();
                    $periodEnd    = $billingCycle === BillingCycle::Yearly
                        ? $now->copy()->addYear()
                        : $now->copy()->addMonth();

                    // 2. Subscription — create new.
                    $subscription = Subscription::create([
                        'tenant_id'                 => $tenant->id,
                        'plan_id'                   => $plan->id,
                        'status'                    => SubscriptionStatus::Active->value,
                        'billing_cycle'             => $billingCycle->value,
                        'currency'                  => $plan->currency ?? 'SAR',
                        'gateway'                   => PaymentGateway::Moyasar->value,
                        'gateway_subscription_id'   => null,
                        'current_period_started_at' => $now,
                        'current_period_ends_at'    => $periodEnd,
                        'meta'                      => [
                            'checkout' => [
                                'company_name' => $companyName,
                                'owner_name'   => $ownerName,
                                'owner_email'  => $ownerEmail,
                                'owner_phone'  => $ownerPhone,
                                'coupon_code'  => $couponCode,
                            ],
                        ],
                    ]);

                    // 3. Coupon — record use atomically.
                    if ($couponCode !== '') {
                        $coupon = Coupon::query()->where('code', $couponCode)->first();
                        if ($coupon && $coupon->isValid()) {
                            $amountSar = (float) (($payment['amount'] ?? 0) / 100);
                            $original  = $billingCycle === BillingCycle::Yearly
                                ? (float) $plan->price_yearly
                                : (float) $plan->price_monthly;
                            $discount  = max(0.0, round($original - $amountSar, 2));

                            $this->coupons->apply(
                                coupon:         $coupon,
                                tenantId:       $tenant->id,
                                subscriptionId: $subscription->id,
                                discountAmount: $discount,
                            );

                            $subscription->coupon_id = $coupon->id;
                            $subscription->save();
                        }
                    }

                    // 4. Payment row — captured.
                    Payment::create([
                        'tenant_id'           => $tenant->id,
                        'invoice_id'          => null,
                        'status'              => PaymentStatus::Captured->value,
                        'amount'              => (float) (($payment['amount'] ?? 0) / 100),
                        'amount_refunded'     => 0,
                        'currency'            => strtoupper((string) ($payment['currency'] ?? 'SAR')),
                        'gateway'             => PaymentGateway::Moyasar->value,
                        'gateway_payment_id'  => $paymentId,
                        'card_last4'          => $this->extractLast4($payment),
                        'card_brand'          => $this->extractBrand($payment),
                        'source_type'         => (string) ($payment['source']['type'] ?? 'unknown'),
                        'paid_at'             => now(),
                        'meta'                => [
                            'plan_id'       => $plan->id,
                            'billing_cycle' => $cycle,
                            'company_name'  => $companyName,
                            'owner_email'   => $ownerEmail,
                            'coupon_code'   => $couponCode,
                        ],
                    ]);
                } finally {
                    TenantContext::forget();
                }
            });
        } catch (\Throwable $e) {
            Log::error('checkout.callback.persist_failed', [
                'gateway_payment_id' => $paymentId,
                'message'            => $e->getMessage(),
            ]);
            return redirect()->route('checkout.failure', ['reason' => 'invalid']);
        }

        // ── Phase F: dispatch the secondary job that provisions the
        // owner User + sends the welcome mail with the 48h setup link.
        // The Tenant + Subscription + Payment rows above are already
        // committed — this job is for SECONDARY work only.
        $useSetupLink = (bool) Config::get('tenancy.signup.use_setup_link', false);

        if ($useSetupLink) {
            // Re-resolve the records we just created (we're outside the
            // tenant context here so we deliberately bypass scopes).
            $createdTenant = Tenant::query()
                ->where('name', $companyName !== '' ? $companyName : $ownerEmail)
                ->first();

            if ($createdTenant) {
                $createdSub = Subscription::query()->withoutGlobalScopes()
                    ->where('tenant_id', $createdTenant->id)
                    ->latest('id')
                    ->first();

                $createdPay = Payment::query()->withoutGlobalScopes()
                    ->where('gateway_payment_id', $paymentId)
                    ->first();

                CreateTenantJob::dispatch([
                    'tenant_id'       => $createdTenant->id,
                    'plan_id'         => $plan->id,
                    'billing_cycle'   => $cycle,
                    'owner_name'      => $ownerName,
                    'owner_email'     => $ownerEmail,
                    'owner_phone'     => $ownerPhone,
                    'source'          => 'paid_checkout',
                    'subscription_id' => $createdSub?->id,
                    'payment_id'      => $createdPay?->id,
                    'coupon_code'     => $couponCode !== '' ? $couponCode : null,
                ]);

                return redirect()->route('checkout.account-pending', [
                    'email' => $ownerEmail,
                ]);
            }
        }

        return redirect()->route('checkout.success');
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /checkout/success
    // ─────────────────────────────────────────────────────────────────
    public function success(): View
    {
        return view('checkout.success', [
            'app_name' => SystemSetting::get('app_name', 'MNJIZ'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /checkout/account-pending  (Phase F)
    // ─────────────────────────────────────────────────────────────────
    /**
     * Landing page after signup or paid checkout completes under the
     * Phase F secure-onboarding flow. Tells the visitor to check their
     * inbox for a setup link. Carries no auth state and no secrets —
     * just the email address (already known to the visitor) for UX.
     */
    public function accountPending(Request $request): View
    {
        return view('checkout.account-pending', [
            'email'    => (string) $request->query('email', ''),
            'app_name' => SystemSetting::get('app_name', 'MNJIZ'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /checkout/failure
    // ─────────────────────────────────────────────────────────────────
    public function failure(Request $request): View
    {
        $reason = (string) $request->query('reason', 'unpaid');
        $key    = in_array($reason, ['unpaid', 'invalid'], true) ? $reason : 'unpaid';

        return view('checkout.failure', [
            'reason_text' => __('checkout.callback.'.$key),
            'app_name'    => SystemSetting::get('app_name', 'MNJIZ'),
        ]);
    }

    // ───────────────────────────────────────────────────── helpers

    private function slugify(string $value): string
    {
        $slug = \Illuminate\Support\Str::slug($value, '-');
        $slug = $slug !== '' ? $slug : 'tenant-'.\Illuminate\Support\Str::random(8);

        $base    = $slug;
        $counter = 1;
        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$counter);
        }
        return $slug;
    }

    /**
     * @param  array<string, mixed> $payment
     */
    private function extractLast4(array $payment): ?string
    {
        $source = $payment['source'] ?? [];
        if (! empty($source['number'])) {
            return substr((string) $source['number'], -4);
        }
        return $source['last_four'] ?? null;
    }

    /**
     * @param  array<string, mixed> $payment
     */
    private function extractBrand(array $payment): ?string
    {
        $source = $payment['source'] ?? [];
        return $source['company'] ?? null;
    }
}
