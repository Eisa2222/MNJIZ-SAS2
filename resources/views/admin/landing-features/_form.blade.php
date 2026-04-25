{{-- Phase D — shared create/edit form for landing-page Feature blocks. --}}
<div class="card" style="max-width:760px;">
    @if ($errors->any())
        <div style="background:#7f1d1d;color:#fee2e2;padding:10px 14px;border-radius:6px;margin-bottom:16px;">
            <ul style="margin:0;padding-inline-start:18px;">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST') @method($method) @endif

        <label for="title">{{ __('landing.admin.features.fields.title') }} *</label>
        <input id="title" name="title" type="text" required maxlength="255"
               value="{{ old('title', $feature?->title) }}">

        <label for="description">{{ __('landing.admin.features.fields.description') }} *</label>
        <textarea id="description" name="description" rows="3" required maxlength="1000"
                  style="background:#0f172a;border:1px solid #334155;color:#e2e8f0;padding:8px;border-radius:6px;width:100%;font-family:inherit;font-size:14px;">{{ old('description', $feature?->description) }}</textarea>

        <label for="icon">{{ __('landing.admin.features.fields.icon') }}</label>
        <input id="icon" name="icon" type="text" maxlength="64" placeholder="⚖️"
               value="{{ old('icon', $feature?->icon) }}">

        <label for="image">{{ __('landing.admin.features.fields.image') }}</label>
        <input id="image" name="image" type="text" maxlength="500" placeholder="https://..."
               value="{{ old('image', $feature?->image) }}">

        <label for="sort_order">{{ __('landing.admin.features.fields.sort_order') }}</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="9999"
               value="{{ old('sort_order', $feature?->sort_order ?? 0) }}">

        <label style="display:flex;align-items:center;gap:8px;margin-top:16px;cursor:pointer;">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" style="width:auto;"
                   @checked(old('is_active', $feature?->is_active ?? true))>
            <span>{{ __('landing.admin.features.fields.is_active') }}</span>
        </label>

        <div style="margin-top:24px;display:flex;gap:8px;">
            <button class="btn" type="submit">{{ __('landing.admin.common.save') }}</button>
            <a class="btn" style="background:#475569;" href="{{ route("{$prefix}.landing-features.index") }}">
                {{ __('landing.admin.common.cancel') }}
            </a>
        </div>
    </form>
</div>
