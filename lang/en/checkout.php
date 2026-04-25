<?php

declare(strict_types=1);

return [
    'title'              => 'Checkout',
    'plan_summary'       => 'Plan summary',
    'billing_cycle'      => 'Billing cycle',
    'monthly'            => 'Monthly',
    'yearly'             => 'Yearly',
    'plan'               => 'Plan',
    'company_section'    => 'Company details',
    'fields' => [
        'company_name' => 'Company / firm name',
        'owner_name'   => 'Owner name',
        'owner_email'  => 'Email',
        'owner_phone'  => 'Phone',
    ],
    'amount_section'     => 'Amount',
    'original_amount'    => 'Subtotal',
    'discount'           => 'Discount',
    'final_amount'       => 'Total due',
    'coupon' => [
        'placeholder'    => 'Enter discount code',
        'apply'          => 'Apply',
        'remove'         => 'Remove',
        'code_required'  => 'Please enter a coupon code.',
        'not_found'      => 'This code does not exist.',
        'inactive'       => 'This coupon is not active.',
        'expired'        => 'This coupon has expired.',
        'exhausted'      => 'This coupon has reached its maximum number of uses.',
        'plan_mismatch'  => 'This coupon does not apply to the selected plan.',
        'cycle_mismatch' => 'This coupon does not apply to the (:cycle) cycle.',
        'min_amount'     => 'Minimum order amount is :amount :currency.',
        'applied'        => 'Coupon applied — :discount :currency off.',
    ],
    'pay'                => 'Pay now',
    'pay_methods'        => 'Accepted payment methods',
    'sandbox_notice'     => 'This is a sandbox account — no real charge will occur.',
    'success' => [
        'title'   => 'Payment successful',
        'thanks'  => 'Thank you for subscribing to :app.',
        'next'    => 'Go to dashboard',
    ],
    'failure' => [
        'title'   => 'Payment failed',
        'reason'  => 'The payment did not complete. You were not charged.',
        'try'     => 'Try again',
        'support' => 'Contact support',
    ],
    'callback' => [
        'unpaid'  => 'Payment status did not complete.',
        'invalid' => 'Invalid payment data.',
    ],
    'cta_on_pricing'     => 'Subscribe now',

    // Phase F — account-pending page after the new secure-onboarding flow.
    'pending' => [
        'title'           => 'Your request has been received',
        'body'            => 'We have sent you an email at :email containing a link to set your password and activate your account.',
        'expiry_notice'   => 'The link is valid for :hours hours from now.',
        'home'            => 'Back to home',
    ],
];
