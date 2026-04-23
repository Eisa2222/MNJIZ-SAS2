@extends('admin.layout')
@section('title', 'New Coupon')

@section('content')
    <div class="admin-header"><h2>Create Coupon</h2></div>

    @if ($errors->any())
        <div class="flash" style="background:#b91c1c;color:#fee2e2;">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card" style="max-width:640px;">
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @csrf

            <label>Code</label>
            <input type="text" name="code" value="{{ old('code') }}" required placeholder="SUMMER25">

            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>

            <label>Description (optional)</label>
            <input type="text" name="description" value="{{ old('description') }}">

            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label>Type</label>
                    <select name="type" required>
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}" {{ old('type')===$t->value?'selected':'' }}>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Value</label>
                    <input type="number" step="0.01" name="value" value="{{ old('value') }}" required>
                </div>
                <div style="flex:1;">
                    <label>Currency (for fixed)</label>
                    <input type="text" name="currency" value="{{ old('currency', 'SAR') }}" maxlength="3">
                </div>
            </div>

            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label>Duration</label>
                    <select name="duration" required>
                        @foreach ($durations as $d)
                            <option value="{{ $d->value }}" {{ old('duration')===$d->value?'selected':'' }}>{{ $d->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Duration in months (for repeating)</label>
                    <input type="number" name="duration_in_months" value="{{ old('duration_in_months') }}" min="1">
                </div>
            </div>

            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label>Applies to</label>
                    <select name="applies_to" required>
                        <option value="any" {{ old('applies_to','any')==='any'?'selected':'' }}>Any plan</option>
                        <option value="specific_plans" {{ old('applies_to')==='specific_plans'?'selected':'' }}>Specific plans</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Minimum invoice amount</label>
                    <input type="number" step="0.01" name="min_amount" value="{{ old('min_amount') }}" min="0">
                </div>
            </div>

            <div style="display:flex;gap:12px;">
                <div style="flex:1;">
                    <label>Max redemptions</label>
                    <input type="number" name="max_redemptions" value="{{ old('max_redemptions') }}" min="1">
                </div>
                <div style="flex:1;">
                    <label>Redeem by (optional)</label>
                    <input type="datetime-local" name="redeem_by" value="{{ old('redeem_by') }}">
                </div>
            </div>

            <label style="margin-top:12px;">
                <input type="checkbox" name="is_active" value="1" checked style="width:auto;"> Active
            </label>

            <button type="submit" class="btn" style="margin-top:16px;">Create</button>
            <a href="{{ route('admin.coupons.index') }}" style="margin-inline-start:12px;color:#94a3b8;">Cancel</a>
        </form>
    </div>
@endsection
