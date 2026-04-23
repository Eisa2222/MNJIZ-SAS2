<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Billing\CouponDuration;
use App\Enums\Billing\CouponType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id;

        return [
            'code'               => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique('coupons', 'code')->ignore($couponId)],
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'type'               => ['required', Rule::in(array_column(CouponType::cases(), 'value'))],
            'value'              => ['required', 'numeric', 'min:0'],
            'currency'           => ['nullable', 'string', 'size:3'],
            'duration'           => ['required', Rule::in(array_column(CouponDuration::cases(), 'value'))],
            'duration_in_months' => ['nullable', 'integer', 'min:1'],
            'applies_to'         => ['required', Rule::in(['any', 'specific_plans'])],
            'min_amount'         => ['nullable', 'numeric', 'min:0'],
            'max_redemptions'    => ['nullable', 'integer', 'min:1'],
            'redeem_by'          => ['nullable', 'date'],
            'is_active'          => ['boolean'],
        ];
    }
}
