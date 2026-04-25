@extends('admin.layout')
@section('title', "Coupon: {$coupon->code}")

@section('content')
    <div class="admin-header">
        <h2>Coupon — <code>{{ $coupon->code }}</code></h2>
        <div>
            <a class="btn" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
            <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}" style="display:inline;">
                @csrf
                <button class="btn" type="submit" style="background:{{ $coupon->is_active ? '#dc2626' : '#065f46' }};">
                    {{ $coupon->is_active ? 'Disable' : 'Enable' }}
                </button>
            </form>
            <a class="btn" href="{{ route('admin.coupons.index') }}" style="background:#475569;">Back</a>
        </div>
    </div>

    @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif

    <div class="card">
        <h3 style="margin-top:0;">{{ $coupon->name }}</h3>
        @if ($coupon->description)<p style="color:#94a3b8;">{{ $coupon->description }}</p>@endif

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:16px;">
            <div class="stat"><div class="k">Type</div><div class="v" style="font-size:18px;">{{ $coupon->type->label() }}</div></div>
            <div class="stat"><div class="k">Value</div><div class="v" style="font-size:18px;">
                {{ $coupon->type->value === 'percentage' ? $coupon->value.'%' : number_format((float) $coupon->value, 2).' '.$coupon->currency }}
            </div></div>
            <div class="stat"><div class="k">Active</div><div class="v" style="font-size:18px;color:{{ $coupon->is_active ? '#10b981' : '#ef4444' }};">
                {{ $coupon->is_active ? 'YES' : 'NO' }}
            </div></div>
            <div class="stat"><div class="k">Redemptions</div><div class="v">
                {{ $coupon->redemptions_count }}{{ $coupon->max_redemptions ? '/'.$coupon->max_redemptions : ' (unlimited)' }}
            </div></div>
            <div class="stat"><div class="k">Audit rows</div><div class="v">{{ $coupon->uses_count }}</div></div>
            <div class="stat"><div class="k">Min amount</div><div class="v" style="font-size:18px;">
                {{ $coupon->min_amount ? number_format((float) $coupon->min_amount, 2).' '.$coupon->currency : '—' }}
            </div></div>
            <div class="stat"><div class="k">Expires</div><div class="v" style="font-size:18px;">
                {{ $coupon->redeem_by?->format('Y-m-d') ?? '—' }}
            </div></div>
            <div class="stat"><div class="k">Applies to</div><div class="v" style="font-size:18px;">
                {{ $coupon->applies_to === 'specific_plans' ? 'Specific plans' : 'Any plan' }}
            </div></div>
        </div>
    </div>
@endsection
