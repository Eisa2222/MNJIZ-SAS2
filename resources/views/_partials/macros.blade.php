@php
    use App\Helpers\SettingsHelper;

    $logoImage = SettingsHelper::get('image');
    $logoPath = $logoImage ? asset('storage/' . $logoImage) : asset('assets/shared/img/branding/Alburhan-Logo.png');
@endphp
<span class="app-brand-logo demo">
    <img src="{{ $logoPath }}" alt="Logo" height="30">
</span>
