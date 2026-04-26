<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إتمام الدفع — {{ $plan->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
    {{-- Moyasar.js v1 (spec line 186 — Credit Card + Apple Pay + STC Pay) --}}
    <link rel="stylesheet" href="https://cdn.moyasar.com/mpf/1.15.0/moyasar.css">
    <script src="https://cdn.moyasar.com/mpf/1.15.0/moyasar.js"></script>
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-slate-50 min-h-screen py-8">

<div class="max-w-5xl mx-auto px-4">
    <a href="/" class="text-sky-700 text-sm hover:underline">← العودة للصفحة الرئيسية</a>
    <h1 class="text-3xl font-bold text-slate-900 mt-3 mb-6">إتمام الدفع</h1>

    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Left: Form (2 cols) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Company info -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 mb-4">بيانات الشركة</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الشركة *</label>
                        <input type="text" id="f_company" required maxlength="120"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">اسم صاحب الحساب *</label>
                        <input type="text" id="f_owner" required maxlength="120"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني *</label>
                        <input type="email" id="f_email" required maxlength="191"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الجوال *</label>
                        <input type="tel" id="f_phone" required maxlength="32"
                               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Coupon -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 mb-4">كود الخصم (اختياري)</h2>
                <div class="flex gap-2">
                    <input type="text" id="f_coupon" placeholder="أدخل الكود"
                           class="flex-1 border border-slate-300 rounded-lg px-4 py-2 uppercase focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
                    <button type="button" id="btn_coupon"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition">
                        تطبيق
                    </button>
                </div>
                <div id="coupon_msg" class="mt-3 text-sm hidden"></div>
            </div>

            <!-- Payment -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 mb-4">الدفع</h2>
                @if (empty($publishable_key))
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm">
                        ⚠ بوابة الدفع غير مُعدَّة بعد. الرجاء التواصل مع الدعم.
                    </div>
                @else
                    <div class="mysr-form" id="moyasar-mount"></div>
                @endif
            </div>
        </div>

        <!-- Right: Summary (1 col) -->
        <div class="space-y-4">
            <div class="bg-gradient-to-br from-sky-700 to-sky-600 text-white rounded-2xl p-6 shadow-lg sticky top-4">
                <h2 class="text-lg font-bold mb-4">ملخص الطلب</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-sky-500 pb-2">
                        <span>الباقة</span>
                        <span class="font-bold">{{ $plan->name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-sky-500 pb-2">
                        <span>الدورة</span>
                        <span>{{ $billing_cycle === 'yearly' ? 'سنوي' : 'شهري' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-sky-500 pb-2">
                        <span>السعر الأصلي</span>
                        <span id="sum_original">{{ number_format($amount, 2) }} {{ $currency }}</span>
                    </div>
                    <div class="flex justify-between border-b border-sky-500 pb-2 hidden" id="sum_discount_row">
                        <span>الخصم</span>
                        <span class="text-emerald-300" id="sum_discount">- 0.00 {{ $currency }}</span>
                    </div>
                    <div class="flex justify-between text-lg pt-2">
                        <span>الإجمالي</span>
                        <span class="font-bold" id="sum_final">{{ number_format($amount, 2) }} {{ $currency }}</span>
                    </div>
                </div>

                <div class="mt-6 text-xs text-sky-100 leading-relaxed">
                    <div class="font-semibold mb-2">طرق الدفع المقبولة:</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($enabled_methods as $m)
                            <span class="inline-block bg-white/20 px-2 py-1 rounded">{{ strtoupper($m) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = {
    plan_id:       {{ $plan->id }},
    plan_slug:     '{{ $plan->slug }}',
    cycle:         '{{ $billing_cycle }}',
    currency:      '{{ $currency }}',
    amount:        {{ $amount }},
    finalAmount:   {{ $amount }},
    couponCode:    '',
    pubKey:        @json($publishable_key),
    methods:       @json($enabled_methods),
    callbackUrl:   '{{ route('checkout.callback') }}',
};

// ─── Coupon AJAX ───────────────────────────────────────────────────
const btnCoupon = document.getElementById('btn_coupon');
const inpCoupon = document.getElementById('f_coupon');
const msg = document.getElementById('coupon_msg');

btnCoupon.addEventListener('click', async () => {
    const code = inpCoupon.value.trim();
    if (! code) { return showMsg('الرجاء إدخال كود الكوبون.', false); }

    btnCoupon.disabled = true;
    btnCoupon.textContent = 'جارٍ التحقق...';

    try {
        const res = await fetch('{{ route('checkout.apply-coupon') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                code,
                plan_id: ctx.plan_id,
                billing_cycle: ctx.cycle,
                amount: ctx.amount,
            }),
        });
        const data = await res.json();

        if (data.valid) {
            ctx.couponCode = data.code;
            ctx.finalAmount = data.final_amount;
            document.getElementById('sum_discount').textContent = '- ' + Number(data.discount).toFixed(2) + ' ' + ctx.currency;
            document.getElementById('sum_discount_row').classList.remove('hidden');
            document.getElementById('sum_final').textContent = Number(data.final_amount).toFixed(2) + ' ' + ctx.currency;
            showMsg(data.message, true);
            initMoyasar();   // re-init with new amount
        } else {
            ctx.couponCode = '';
            ctx.finalAmount = ctx.amount;
            document.getElementById('sum_discount_row').classList.add('hidden');
            document.getElementById('sum_final').textContent = Number(ctx.amount).toFixed(2) + ' ' + ctx.currency;
            showMsg(data.message, false);
        }
    } finally {
        btnCoupon.disabled = false;
        btnCoupon.textContent = 'تطبيق';
    }
});

function showMsg(text, ok) {
    msg.classList.remove('hidden', 'text-emerald-700', 'text-red-700', 'bg-emerald-50', 'bg-red-50', 'border-emerald-200', 'border-red-200');
    msg.classList.add('p-3', 'rounded-lg', 'border');
    msg.classList.add(ok ? 'text-emerald-700' : 'text-red-700');
    msg.classList.add(ok ? 'bg-emerald-50' : 'bg-red-50');
    msg.classList.add(ok ? 'border-emerald-200' : 'border-red-200');
    msg.textContent = text;
}

// ─── Moyasar.js wiring ────────────────────────────────────────────
function initMoyasar() {
    if (! ctx.pubKey || typeof Moyasar === 'undefined') return;
    const mount = document.getElementById('moyasar-mount');
    if (! mount) return;
    mount.innerHTML = '';

    Moyasar.init({
        element:             '#moyasar-mount',
        amount:              Math.round(ctx.finalAmount * 100),   // halalas
        currency:            ctx.currency,
        description:         'اشتراك ' + ctx.plan_slug + ' (' + ctx.cycle + ')',
        publishable_api_key: ctx.pubKey,
        callback_url:        ctx.callbackUrl,
        methods:             ctx.methods,
        metadata: {
            plan_id:       String(ctx.plan_id),
            billing_cycle: ctx.cycle,
            company_name:  document.getElementById('f_company').value,
            owner_name:    document.getElementById('f_owner').value,
            owner_email:   document.getElementById('f_email').value,
            owner_phone:   document.getElementById('f_phone').value,
            coupon_code:   ctx.couponCode,
        },
    });
}

// Init once the form is filled (or on first load)
document.addEventListener('DOMContentLoaded', () => {
    if (ctx.pubKey) initMoyasar();
});

// Re-init when company fields change so the metadata stays current.
['f_company','f_owner','f_email','f_phone'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
        if (ctx.pubKey) initMoyasar();
    });
});
</script>

</body>
</html>
