<?php

declare(strict_types=1);

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Phase E — validates the AJAX `POST /checkout/apply-coupon` payload
 * before it reaches CouponService::validate().
 *
 * Note: a coupon being VALID is a domain check handled by
 * CouponService — this Request only enforces the *shape* of the
 * incoming JSON. authorize() returns true so anonymous visitors can
 * preview discounts on the public checkout page.
 */
final class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'           => ['required', 'string', 'max:64'],
            'plan_id'        => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle'  => ['required', 'in:monthly,yearly'],
            'amount'         => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}
