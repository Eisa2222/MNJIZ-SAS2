<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Billing — {{ $tenant->name }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Arial, sans-serif; background: #f8fafc; margin: 0; color: #0f172a; }
        .wrap { max-width: 960px; margin: 32px auto; padding: 0 20px; }
        h1 { margin: 0 0 24px; font-size: 24px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
        .muted { color: #64748b; }
        .status-badge { padding: 4px 10px; border-radius: 4px; font-size: 12px; color: #fff; }
        .ok { background: #059669; } .warn { background: #d97706; } .bad { background: #dc2626; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 10px; text-align: start; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        button, .btn { background: #2563eb; color: #fff; border: 0; padding: 8px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-danger { background: #dc2626; }
        .flash { background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; }
        .flash-err { background: #fef2f2; border-color: #ef4444; color: #991b1b; }
        input[type=text] { padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; width: 240px; }
        .banner-imp { background: #b91c1c; color: #fee2e2; padding: 10px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>

@if (!empty($isImpersonating))
    <div class="banner-imp">
        ⚠️ أنت حاليًا Super Admin يتجسّد كمستخدم.
        <form method="POST" action="{{ url('/admin/impersonation/stop') }}" style="display:inline;">
            @csrf <button class="btn">Stop impersonating</button>
        </form>
    </div>
@endif

<div class="wrap">
    <h1>الفواتير والاشتراك — {{ $tenant->name }}</h1>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="flash flash-err">{{ $errors->first() }}</div>
    @endif

    {{-- ====== Current subscription ====== --}}
    <div class="card">
        <h3 style="margin-top:0;">الاشتراك الحالي</h3>
        @if (!$subscription)
            <p class="muted">لا يوجد اشتراك نشط.</p>
        @else
            <p><strong>الخطة:</strong> {{ $subscription->plan?->name ?? '—' }}
               <small class="muted">({{ $subscription->billing_cycle->label() }})</small></p>
            <p>
                <strong>الحالة:</strong>
                @php
                    $s = $subscription->status->value;
                    $class = in_array($s, ['active','trialing']) ? 'ok' : ($s==='past_due' ? 'warn' : 'bad');
                @endphp
                <span class="status-badge {{ $class }}">{{ $subscription->status->label() }}</span>
            </p>
            @if ($subscription->onTrial())
                <p class="muted">الفترة التجريبية تنتهي: {{ $subscription->trial_ends_at->format('Y-m-d') }}</p>
            @endif
            @if ($subscription->current_period_ends_at)
                <p class="muted">الفترة الحالية تنتهي: {{ $subscription->current_period_ends_at->format('Y-m-d') }}</p>
            @endif
            @if ($subscription->coupon)
                <p class="muted">الكوبون المطبَّق: <code>{{ $subscription->coupon->code }}</code></p>
            @endif

            <div style="margin-top:12px;display:flex;gap:8px;">
                @if ($subscription->status->value !== 'canceled' && $subscription->status->value !== 'expired')
                    <form method="POST" action="{{ route('tenant.billing.cancel') }}"
                          onsubmit="return confirm('إلغاء الاشتراك في نهاية الفترة الحالية؟');">
                        @csrf
                        <button class="btn btn-danger" type="submit">إلغاء الاشتراك</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('tenant.billing.resume') }}">
                        @csrf
                        <button class="btn" type="submit">استعادة الاشتراك</button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    {{-- ====== Apply coupon ====== --}}
    @if ($subscription)
    <div class="card">
        <h3 style="margin-top:0;">تطبيق كوبون</h3>
        <form method="POST" action="{{ route('tenant.billing.coupon.apply') }}" style="display:flex;gap:8px;">
            @csrf
            <input type="text" name="code" placeholder="رمز الكوبون" required>
            <button class="btn" type="submit">تطبيق</button>
        </form>
    </div>
    @endif

    {{-- ====== Invoices history ====== --}}
    <div class="card">
        <h3 style="margin-top:0;">سجل الفواتير</h3>
        @if ($invoices->isEmpty())
            <p class="muted">لا توجد فواتير.</p>
        @else
            <table>
                <thead>
                    <tr><th>الرقم</th><th>الحالة</th><th>المبلغ</th><th>المدفوع</th><th>التاريخ</th></tr>
                </thead>
                <tbody>
                @foreach ($invoices as $inv)
                    <tr>
                        <td>{{ $inv->number }}</td>
                        <td>{{ $inv->status->label() }}</td>
                        <td>{{ number_format($inv->total, 2) }} {{ $inv->currency }}</td>
                        <td>{{ number_format($inv->amount_paid, 2) }} {{ $inv->currency }}</td>
                        <td>{{ $inv->issued_at?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
</body>
</html>
