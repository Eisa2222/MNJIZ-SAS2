<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('checkout.title') }} — {{ $app_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    {{-- Bootstrap 5 (already used elsewhere in the public surface) — no Tailwind. --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <style>
        body{font-family:'Cairo',sans-serif;background:#f7fafc;color:#1a202c}
        .checkout-shell{max-width:980px;margin:32px auto;padding:0 16px}
        .checkout-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.05);padding:28px}
        .summary{background:#0f4c75;color:#fff;border-radius:14px;padding:24px}
        .summary h3{font-size:18px;margin-bottom:12px}
        .row-line{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.15)}
        .row-line:last-child{border-bottom:0;font-weight:700;font-size:18px}
        .pay-methods{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
        .pay-pill{background:#edf2f7;color:#0f4c75;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .form-section h4{font-size:16px;margin:0 0 12px;color:#0f4c75;font-weight:700}
        .alert-coupon{margin-top:8px;padding:10px 12px;border-radius:6px;font-size:14px;display:none}
        .alert-coupon.ok{background:#d1fae5;color:#065f46;display:block}
        .alert-coupon.bad{background:#fee2e2;color:#991b1b;display:block}
    </style>
</head>
<body>

<div class="checkout-shell">
    <h1 class="mb-4" style="color:#0f4c75;font-weight:700;">{{ __('checkout.title') }}</h1>

    <div class="row g-4">
        <div class="col-lg-7 order-lg-1 order-2">
            <div class="checkout-card">
                {{-- Company section --}}
                <div class="form-section mb-4">
                    <h4>{{ __('checkout.company_section') }}</h4>
                    <form id="checkout-form" novalidate>
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <input type="hidden" name="billing_cycle" value="{{ $billing_cycle }}">
                        <input type="hidden" name="coupon_code" id="applied-coupon-code" value="">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('checkout.fields.company_name') }}</label>
                                <input type="text" class="form-control" name="company_name" required maxlength="120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('checkout.fields.owner_name') }}</label>
                                <input type="text" class="form-control" name="owner_name" required maxlength="120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('checkout.fields.owner_email') }}</label>
                                <input type="email" class="form-control" name="owner_email" required maxlength="191">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('checkout.fields.owner_phone') }}</label>
                                <input type="tel" class="form-control" name="owner_phone" required maxlength="32">
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Coupon section --}}
                <div class="form-section mb-4">
                    <h4>{{ __('checkout.coupon.apply') }}</h4>
                    <div class="input-group">
                        <input type="text" id="coupon-code" class="form-control" placeholder="{{ __('checkout.coupon.placeholder') }}" maxlength="64">
                        <button type="button" id="coupon-apply-btn" class="btn btn-primary">{{ __('checkout.coupon.apply') }}</button>
                    </div>
                    <div id="coupon-feedback" class="alert-coupon" role="status"></div>
                </div>

                {{-- Payment section (Moyasar.js placeholder) --}}
                <div class="form-section">
                    <h4>{{ __('checkout.pay') }}</h4>
                    @if ($sandbox)
                        <p class="text-muted small mb-3">⚠️ {{ __('checkout.sandbox_notice') }}</p>
                    @endif
                    <div id="moyasar-placeholder"
                         data-publishable-key="{{ $publishable_key }}"
                         data-amount="{{ $amount }}"
                         data-currency="{{ $currency }}"
                         data-methods="{{ implode(',', $enabled_methods) }}"
                         class="border rounded p-4 text-center text-muted">
                        {{-- Front-end integrators wire Moyasar.js here:
                             Moyasar.init({
                                 element: '#moyasar-placeholder',
                                 amount: <data-amount>*100,
                                 currency: '<data-currency>',
                                 publishable_api_key: '<data-publishable-key>',
                                 methods: ['<data-methods>'],
                                 callback_url: '{{ route('checkout.callback') }}',
                                 metadata: { /* injected from form fields */ }
                             });
                        --}}
                        <span>Moyasar.js mount point</span>
                        <div class="pay-methods justify-content-center">
                            @foreach ($enabled_methods as $m)
                                <span class="pay-pill">{{ strtoupper($m) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary side panel --}}
        <div class="col-lg-5 order-lg-2 order-1">
            <div class="summary">
                <h3>{{ __('checkout.plan_summary') }}</h3>
                <div class="row-line">
                    <span>{{ __('checkout.plan') }}</span>
                    <span><strong>{{ $plan->name }}</strong></span>
                </div>
                <div class="row-line">
                    <span>{{ __('checkout.billing_cycle') }}</span>
                    <span>{{ __('checkout.'.$billing_cycle) }}</span>
                </div>
                <div class="row-line">
                    <span>{{ __('checkout.original_amount') }}</span>
                    <span id="summary-original">{{ number_format($amount, 2) }} {{ $currency }}</span>
                </div>
                <div class="row-line" id="summary-discount-row" style="display:none;">
                    <span>{{ __('checkout.discount') }}</span>
                    <span id="summary-discount">- 0.00 {{ $currency }}</span>
                </div>
                <div class="row-line">
                    <span>{{ __('checkout.final_amount') }}</span>
                    <span id="summary-final">{{ number_format($amount, 2) }} {{ $currency }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── jQuery (CDN) for AJAX coupon apply — no SPA framework. ─── --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    (function ($) {
        'use strict';

        var routes = {
            applyCoupon: '{{ route('checkout.apply-coupon') }}',
        };
        var ctx = {
            planId:       {{ $plan->id }},
            billingCycle: '{{ $billing_cycle }}',
            currency:     '{{ $currency }}',
            amount:       {{ $amount }},
        };

        function setFeedback(msg, ok) {
            $('#coupon-feedback').removeClass('ok bad').addClass(ok ? 'ok' : 'bad').text(msg);
        }

        // Phase H+ collaborative-audit fix: disable button + show "Validating…"
        // text during the AJAX call so the user can't double-submit and gets
        // visible feedback. The button is restored in `complete` (always
        // fires, success or error), preventing a permanent stuck state.
        var $applyBtn   = $('#coupon-apply-btn');
        var $codeInput  = $('#coupon-code');
        var APPLY_LABEL = '{{ __('checkout.coupon.apply') }}';
        var BUSY_LABEL  = '{{ __('checkout.coupon.applying') }}';

        $applyBtn.on('click', function () {
            // Re-entry guard: AJAX call already in flight.
            if ($applyBtn.prop('disabled')) { return; }

            var code = $.trim($codeInput.val());
            if (! code) { setFeedback('{{ __('checkout.coupon.code_required') }}', false); return; }

            $applyBtn.prop('disabled', true).text(BUSY_LABEL);
            $codeInput.prop('disabled', true);

            $.ajax({
                url: routes.applyCoupon,
                method: 'POST',
                dataType: 'json',
                data: {
                    _token:        '{{ csrf_token() }}',
                    code:          code,
                    plan_id:       ctx.planId,
                    billing_cycle: ctx.billingCycle,
                    amount:        ctx.amount,
                },
                success: function (res) {
                    setFeedback(res.message, !! res.valid);
                    if (res.valid) {
                        $('#applied-coupon-code').val(res.code || code);
                        $('#summary-discount').text('- ' + Number(res.discount).toFixed(2) + ' ' + ctx.currency);
                        $('#summary-discount-row').show();
                        $('#summary-final').text(Number(res.final_amount).toFixed(2) + ' ' + ctx.currency);
                    } else {
                        $('#applied-coupon-code').val('');
                        $('#summary-discount-row').hide();
                        $('#summary-final').text(Number(ctx.amount).toFixed(2) + ' ' + ctx.currency);
                    }
                },
                error: function () {
                    setFeedback('Error', false);
                },
                complete: function () {
                    $applyBtn.prop('disabled', false).text(APPLY_LABEL);
                    $codeInput.prop('disabled', false);
                }
            });
        });
    })(jQuery);
</script>

</body>
</html>
