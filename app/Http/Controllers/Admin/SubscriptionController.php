<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Billing\Subscription\CancelSubscriptionAction;
use App\Actions\Billing\Subscription\ResumeSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super Admin view over every tenant's subscription. Bypasses TenantScope
 * on the Subscription model via `withoutTenancy()` since the caller is a
 * platform operator, not a tenant.
 */
final class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = Subscription::withoutTenancy()
            ->with(['plan', 'tenantRelation'])
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function show(int $id): View
    {
        $subscription = Subscription::withoutTenancy()
            ->with(['plan', 'tenantRelation', 'invoices.payments', 'coupon'])
            ->findOrFail($id);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function cancel(int $id, Request $request, CancelSubscriptionAction $cancel): RedirectResponse
    {
        $sub = Subscription::withoutTenancy()->findOrFail($id);
        $cancel->execute($sub, immediate: $request->boolean('immediate'));

        return back()->with('status', "Subscription #{$sub->id} canceled.");
    }

    public function resume(int $id, ResumeSubscriptionAction $resume): RedirectResponse
    {
        $sub = Subscription::withoutTenancy()->findOrFail($id);
        $resume->execute($sub);

        return back()->with('status', "Subscription #{$sub->id} resumed.");
    }
}
