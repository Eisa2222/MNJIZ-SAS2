@extends('admin.layout')
@section('title', 'Edit '.$coupon->code)

@section('content')
    <div class="admin-header"><h2>Edit {{ $coupon->code }}</h2></div>

    @if ($errors->any())
        <div class="flash" style="background:#b91c1c;color:#fee2e2;">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card" style="max-width:640px;">
        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
            @csrf @method('PUT')

            <label>Code</label>
            <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required>

            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $coupon->name) }}" required>

            <label>Description</label>
            <input type="text" name="description" value="{{ old('description', $coupon->description) }}">

            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label>Type</label>
                    <select name="type" required>
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}" {{ $coupon->type->value===$t->value?'selected':'' }}>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Value</label>
                    <input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value) }}" required>
                </div>
                <div style="flex:1;">
                    <label>Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', $coupon->currency) }}" maxlength="3">
                </div>
            </div>

            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label>Duration</label>
                    <select name="duration" required>
                        @foreach ($durations as $d)
                            <option value="{{ $d->value }}" {{ $coupon->duration->value===$d->value?'selected':'' }}>{{ $d->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Duration in months</label>
                    <input type="number" name="duration_in_months" value="{{ old('duration_in_months', $coupon->duration_in_months) }}" min="1">
                </div>
            </div>

            <label>Applies to</label>
            <select name="applies_to" required>
                <option value="any" {{ $coupon->applies_to==='any'?'selected':'' }}>Any plan</option>
                <option value="specific_plans" {{ $coupon->applies_to==='specific_plans'?'selected':'' }}>Specific plans</option>
            </select>

            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label>Minimum invoice amount</label>
                    <input type="number" step="0.01" name="min_amount" value="{{ old('min_amount', $coupon->min_amount) }}" min="0">
                </div>
                <div style="flex:1;">
                    <label>Max redemptions</label>
                    <input type="number" name="max_redemptions" value="{{ old('max_redemptions', $coupon->max_redemptions) }}" min="1">
                </div>
            </div>

            <label>Redeem by</label>
            <input type="datetime-local" name="redeem_by" value="{{ old('redeem_by', $coupon->redeem_by?->format('Y-m-d\TH:i')) }}">

            <label style="margin-top:12px;">
                <input type="checkbox" name="is_active" value="1" {{ $coupon->is_active?'checked':'' }} style="width:auto;"> Active
            </label>

            <button type="submit" class="btn" style="margin-top:16px;">Save</button>
            <a href="{{ route('admin.coupons.index') }}" style="margin-inline-start:12px;color:#94a3b8;">Cancel</a>
        </form>
    </div>
@endsection
