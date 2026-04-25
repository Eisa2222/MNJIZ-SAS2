{{-- Reusable boolean toggle (rendered as a Bootstrap-style checkbox).
     Required: $name, $value (bool) --}}
@php
    $label   = __('admin.settings.fields.' . $name);
    $checked = (bool) old($name, $value);
@endphp

<div style="margin-bottom:14px;display:flex;align-items:center;gap:10px;">
    {{-- Hidden field carries the unchecked state ("0") so the controller
         always sees a value — same trick as Laravel's standard pattern. --}}
    <input type="hidden" name="{{ $name }}" value="0">
    <input id="f-{{ $name }}" name="{{ $name }}" type="checkbox" value="1"
           {{ $checked ? 'checked' : '' }} style="width:auto;">
    <label for="f-{{ $name }}" style="font-weight:600;">
        {{ $label }}
    </label>
</div>
