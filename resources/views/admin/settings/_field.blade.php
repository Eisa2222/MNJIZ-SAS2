{{-- Reusable input row for the System Settings tabs.
     Required: $name (settings key), $value
     Optional: $type ('text'|'email'|'url'|'password'|'number'), $sensitive (bool) --}}
@php
    $type      = $type ?? 'text';
    $sensitive = $sensitive ?? false;
    $label     = __('admin.settings.fields.' . $name);
@endphp

<div style="margin-bottom:14px;">
    <label for="f-{{ $name }}" style="display:block;font-weight:600;margin-bottom:6px;">
        {{ $label }}
    </label>
    <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           value="{{ old($name, $value) }}"
           autocomplete="{{ $sensitive ? 'new-password' : 'off' }}"
           @if ($sensitive && $value) placeholder="••••••••" @endif
           style="width:100%;max-width:520px;">
    @if ($sensitive && $value)
        <small style="color:#64748b;display:block;margin-top:4px;">
            {{ __('admin.settings.sensitive_hint') }}
        </small>
    @endif
</div>
