<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\CreateTenantJob;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\CouponService;
use App\Services\MoyasarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * V2 — Public checkout flow. Spec lines 181-192.
 *
 *   GET  /checkout/{plan}            → show
 *   POST /checkout/apply-coupon      → applyCoupon (AJAX, returns JSON)
 *   GET  /checkout/callback          → callback (Moyasar redirects here)
 *   GET  /checkout/success           → success
 */
final class CheckoutController extends Controller
{
    public function __construct(
        private CouponService $coupons,
        private MoyasarService $moyasar,
    ) {}

    public function show(string $plan, Request $request): View|RedirectResponse
    {
        $planModel = Plan::where('slug', $plan)->where('is_active', true)->first();
        if (! $planModel) {
            return redirect('/')->with('status', 'الباقة غير موجودة.');
        }

        $cycle = $request->query('cycle', 'monthly');
        if (! in_array($cycle, ['monthly', 'yearly'], true)) {
            $cycle = 'monthly';
        }

        $amount = $cycle === 'yearly'
            ? (float) $planModel->price_yearly
            : (float) $planModel->price_monthly;

        return view('checkout.show', [
            'plan'             => $planModel,
            'billing_cycle'    => $cycle,
            'amount'           => $amount,
            'currency'         => $planModel->currency ?? 'SAR',
            'publishable_key'  => MoyasarService::publishableKey(),
            'enabled_methods'  => MoyasarService::enabledMethods(),
        ]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'           => ['required', 'string', 'max:64'],
            'plan_id'        => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle'  => ['required', 'in:monthly,yearly'],
            'amount'         => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->coupons->validate(
            $data['code'],
            (int) $data['plan_id'],
            (string) $data['billing_cycle'],
            (float) $data['amount'],
        );

        // Don't echo the Coupon model itself (would leak created_by, etc.).
        return response()->json([
            'valid'        => $result['valid'],
            'discount'     => $result['discount'],
            'final_amount' => $result['final_amount'],
            'message'      => $result['message'],
            'code'         => $result['coupon']?->code,
        ]);
    }

    /**
     * Moyasar redirects the browser here after the user pays. We
     * verify the payment status server-side via Moyasar API, then
     * dispatch CreateTenantJob to provision the tenant + DB.
     */
    public function callback(Request $request): RedirectResponse
    {
        $paymentId = (string) $request->query('id', '');
        if ($paymentId === '') {
            return redirect()->route('checkout.failure', ['reason' => 'invalid']);
        }

        try {
            $payment = $this->moyasar->getPayment($paymentId);
        } catch (\Throwable $e) {
            Log::warning('checkout.callback.fetch_failed', ['id' => $paymentId, 'msg' => $e->getMessage()]);
            return redirect()->route('checkout.failure', ['reason' => 'invalid']);
        }

        if (strtolower((string) ($payment['status'] ?? '')) !== 'paid') {
            return redirect()->route('checkout.failure', ['reason' => 'unpaid']);
        }

        // Idempotency
        if (\App\Models\Payment::where('moyasar_payment_id', $paymentId)->exists()) {
            return redirect()->route('checkout.success');
        }

        $metadata = (array) ($payment['metadata'] ?? []);

        // Dispatch async tenant creation job (Multi-DB tenancy).
        CreateTenantJob::dispatch([
            'moyasar_payment_id' => $paymentId,
            'amount'             => (float) (($payment['amount'] ?? 0) / 100),
            'currency'           => strtoupper((string) ($payment['currency'] ?? 'SAR')),
            'payment_method'     => (string) ($payment['source']['type'] ?? 'creditcard'),
            'metadata'           => $metadata,
            'moyasar_response'   => $payment,
        ]);

        return redirect()->route('checkout.success', ['email' => $metadata['owner_email'] ?? '']);
    }

    public function success(Request $request): View
    {
        return view('checkout.success', [
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function failure(Request $request): View
    {
        $reason = $request->query('reason', 'unpaid');
        return view('checkout.failure', ['reason' => $reason]);
    }
}
