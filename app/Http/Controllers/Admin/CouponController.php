<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Billing\CouponDuration;
use App\Enums\Billing\CouponType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()->latest('id')->paginate(25);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'types'     => CouponType::cases(),
            'durations' => CouponDuration::cases(),
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $coupon = Coupon::create($request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', "Coupon '{$coupon->code}' created.");
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', [
            'coupon'    => $coupon,
            'types'     => CouponType::cases(),
            'durations' => CouponDuration::cases(),
        ]);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        // Phase E — `code` is immutable after creation. Strip it from the
        // payload so the operator can't accidentally rename a coupon
        // that's already in flight (which would silently invalidate
        // every existing redemption history join).
        $data = $request->validated();
        unset($data['code']);

        $coupon->update($data);

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', "Coupon '{$coupon->code}' updated.");
    }

    /**
     * Phase E — single-coupon detail view (read-only).
     */
    public function show(Coupon $coupon): View
    {
        $coupon->loadCount('uses');

        return view('admin.coupons.show', [
            'coupon' => $coupon,
        ]);
    }

    /**
     * Phase E — flip is_active without going through the edit form.
     * The Phase 5 list view shows a YES/NO badge; this gives the
     * operator a one-click way to disable a misbehaving coupon.
     */
    public function toggle(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        $state = $coupon->is_active ? 'enabled' : 'disabled';

        return back()->with('status', "Coupon '{$coupon->code}' {$state}.");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        // Phase E guard — refuse to delete a coupon that's already been
        // redeemed at least once. Redemption history relies on the FK,
        // so soft-deleting it (especially with cascading) loses the
        // analytics. The operator should toggle it inactive instead.
        if ((int) $coupon->redemptions_count > 0) {
            return back()->with('status', "Coupon '{$coupon->code}' has been used and cannot be deleted. Toggle it inactive instead.");
        }

        $coupon->delete();

        return back()->with('status', "Coupon '{$coupon->code}' archived.");
    }
}
