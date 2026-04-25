{{-- Reusable select row.
     Required: $name, $value, $options (associative array value=>label) --}}
@php
    $label = __('admin.settings.fields.' . $name);
@endphp

<div style="margin-bottom:14px;">
    <label for="f-{{ $name }}" style="display:block;font-weight:600;margin-bottom:6px;">
        {{ $label }}
    </label>
    <select id="f-{{ $name }}" name="{{ $name }}" style="width:100%;max-width:520px;">
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected(old($name, $value) === $optValue)>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>
</div>
