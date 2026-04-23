<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Actions\Billing\ApplyCouponAction;
use App\Actions\Billing\Subscription\CancelSubscriptionAction;
use App\Actions\Billing\Subscription\ResumeSubscriptionAction;
use App\Enums\Billing\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Tenant-facing billing pages — rendered under /t/{tenant}/billing/*.
 * TenantContext guarantees every query is scoped to the current tenant via
 * BelongsToTenant, so no additional defensive filtering is needed.
 */
final class BillingController extends Controller
{
    public function index(): View
    {
        /** @var Tenant $tenant */
        $tenant = TenantContext::requireCurrent();

        /** @var Subscription|null $subscription */
        $subscription = Subscription::query()
            ->with(['plan', 'coupon'])
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::PastDue->value,
                SubscriptionStatus::Canceled->value,
            ])
            ->latest('id')
            ->first();

        $invoices = Invoice::query()
            ->with('payments')
            ->latest('id')
            ->limit(20)
            ->get();

        return view('tenant.billing.index', compact('tenant', 'subscription', 'invoices'));
    }

    public function cancel(CancelSubscriptionAction $cancel): RedirectResponse
    {
        $subscription = $this->activeSubscription();
        if (! $subscription) {
            return back()->with('status', 'No active subscription to cancel.');
        }

        $cancel->execute($subscription, immediate: false);

        return back()->with('status', 'Subscription canceled. Access continues until the end of the current period.');
    }

    public function resume(ResumeSubscriptionAction $resume): RedirectResponse
    {
        $subscription = Subscription::query()
            ->where('status', SubscriptionStatus::Canceled->value)
            ->latest('id')
            ->first();

        if (! $subscription) {
            return back()->with('status', 'No canceled subscription found.');
        }

        $resume->execute($subscription);

        return back()->with('status', 'Subscription resumed.');
    }

    public function applyCoupon(\Illuminate\Http\Request $request, ApplyCouponAction $apply): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $subscription = $this->activeSubscription();
        if (! $subscription) {
            return back()->with('status', 'No active subscription to apply a coupon to.')->withErrors(['code' => 'No active subscription.']);
        }

        try {
            $apply->execute($subscription, $data['code']);
            return back()->with('status', "Coupon '{$data['code']}' applied.");
        } catch (\App\Exceptions\Billing\CouponRedemptionException $e) {
            return back()->withErrors(['code' => $e->getMessage()])->withInput();
        }
    }

    private function activeSubscription(): ?Subscription
    {
        return Subscription::query()
            ->whereIn('status', [
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::PastDue->value,
            ])
            ->latest('id')
            ->first();
    }
}
