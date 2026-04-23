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
        $coupon->update($request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', "Coupon '{$coupon->code}' updated.");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('status', "Coupon '{$coupon->code}' archived.");
    }
}
