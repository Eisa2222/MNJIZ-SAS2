@extends('admin.layout')
@section('title', __('admin.settings.title'))

@section('content')
    <div class="admin-header">
        <h2>{{ __('admin.settings.title') }}</h2>
    </div>

    @if (session('status'))
        <div class="flash" style="background:#065f46;color:#d1fae5;">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash" style="background:#b91c1c;color:#fee2e2;">{{ $errors->first() }}</div>
    @endif

    {{-- Tabs (Bootstrap-style nav, written in plain markup so the existing
         admin.layout's inline CSS can style them like the rest of the panel). --}}
    <div class="card" style="padding:0;">
        <div style="display:flex;flex-wrap:wrap;border-bottom:1px solid #1f2937;">
            @foreach ($tabs as $tabKey)
                @php
                    $isActive = $tab === $tabKey;
                    $url = route("{$routePrefix}.settings.index", ['tab' => $tabKey]);
                @endphp
                <a href="{{ $url }}"
                   style="padding:14px 22px;text-transform:capitalize;
                          {{ $isActive ? 'background:#1e293b;color:#60a5fa;border-bottom:2px solid #60a5fa;'
                                       : 'color:#94a3b8;' }}">
                    {{ __('admin.settings.tabs.' . $tabKey) }}
                </a>
            @endforeach
        </div>

        <div style="padding:24px;">
            <form method="POST" action="{{ route("{$routePrefix}.settings.update") }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="group" value="{{ $tab }}">

                {{-- ─── General ──────────────────────────────────────── --}}
                @if ($tab === 'general')
                    @include('admin.settings._field', ['name' => 'app_name',         'value' => $values['app_name']         ?? null])
                    @include('admin.settings._field', ['name' => 'app_logo',         'value' => $values['app_logo']         ?? null])
                    @include('admin.settings._field', ['name' => 'app_url',          'value' => $values['app_url']          ?? null, 'type' => 'url'])
                    @include('admin.settings._field', ['name' => 'support_email',    'value' => $values['support_email']    ?? null, 'type' => 'email'])
                    @include('admin.settings._field', ['name' => 'support_phone',    'value' => $values['support_phone']    ?? null])
                    @include('admin.settings._field', ['name' => 'default_timezone', 'value' => $values['default_timezone'] ?? null])
                    @include('admin.settings._select', [
                        'name'    => 'default_language',
                        'value'   => $values['default_language'] ?? null,
                        'options' => ['ar' => 'العربية', 'en' => 'English'],
                    ])
                @endif

                {{-- ─── Trial ──────────────────────────────────────────── --}}
                @if ($tab === 'trial')
                    @include('admin.settings._toggle', ['name' => 'trial_enabled',              'value' => $values['trial_enabled']              ?? null])
                    @include('admin.settings._field',  ['name' => 'trial_days',                 'value' => $values['trial_days']                 ?? null, 'type' => 'number'])
                    @include('admin.settings._toggle', ['name' => 'trial_requires_payment',     'value' => $values['trial_requires_payment']     ?? null])
                    @include('admin.settings._toggle', ['name' => 'trial_suspend_after_expiry', 'value' => $values['trial_suspend_after_expiry'] ?? null])
                @endif

                {{-- ─── Moyasar ────────────────────────────────────────── --}}
                @if ($tab === 'moyasar')
                    @include('admin.settings._field',  ['name' => 'moyasar_publishable_key', 'value' => $values['moyasar_publishable_key'] ?? null])
                    @include('admin.settings._field',  ['name' => 'moyasar_secret_key',      'value' => $values['moyasar_secret_key']      ?? null, 'type' => 'password', 'sensitive' => true])
                    @include('admin.settings._field',  ['name' => 'moyasar_webhook_secret',  'value' => $values['moyasar_webhook_secret']  ?? null, 'type' => 'password', 'sensitive' => true])
                    @include('admin.settings._toggle', ['name' => 'moyasar_test_mode',       'value' => $values['moyasar_test_mode']       ?? null])

                    <div style="margin-top:12px;">
                        <button type="button" id="btn-test-moyasar" class="btn"
                                data-url="{{ route("{$routePrefix}.settings.test-moyasar") }}">
                            {{ __('admin.settings.test_moyasar') }}
                        </button>
                        <span id="moyasar-test-result" style="margin-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}:12px;color:#94a3b8;"></span>
                    </div>
                @endif

                {{-- ─── Mail ───────────────────────────────────────────── --}}
                @if ($tab === 'mail')
                    @include('admin.settings._select', [
                        'name'    => 'mail_driver',
                        'value'   => $values['mail_driver'] ?? null,
                        'options' => ['smtp' => 'SMTP', 'log' => 'Log', 'array' => 'Array', 'sendmail' => 'sendmail'],
                    ])
                    @include('admin.settings._field',  ['name' => 'mail_host',          'value' => $values['mail_host']         ?? null])
                    @include('admin.settings._field',  ['name' => 'mail_port',          'value' => $values['mail_port']         ?? null, 'type' => 'number'])
                    @include('admin.settings._select', [
                        'name'    => 'mail_encryption',
                        'value'   => $values['mail_encryption'] ?? null,
                        'options' => ['' => '—', 'tls' => 'TLS', 'ssl' => 'SSL'],
                    ])
                    @include('admin.settings._field',  ['name' => 'mail_username',      'value' => $values['mail_username']     ?? null])
                    @include('admin.settings._field',  ['name' => 'mail_password',      'value' => $values['mail_password']     ?? null, 'type' => 'password', 'sensitive' => true])
                    @include('admin.settings._field',  ['name' => 'mail_from_address',  'value' => $values['mail_from_address'] ?? null, 'type' => 'email'])
                    @include('admin.settings._field',  ['name' => 'mail_from_name',     'value' => $values['mail_from_name']    ?? null])

                    <div style="margin-top:12px;">
                        <input type="email" id="test-mail-to" placeholder="{{ __('admin.settings.test_mail_to') }}"
                               style="width:280px;display:inline-block;">
                        <button type="button" id="btn-test-mail" class="btn"
                                data-url="{{ route("{$routePrefix}.settings.test-mail") }}">
                            {{ __('admin.settings.test_mail') }}
                        </button>
                        <span id="mail-test-result" style="margin-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}:12px;color:#94a3b8;"></span>
                    </div>
                @endif

                {{-- ─── Landing ────────────────────────────────────────── --}}
                @if ($tab === 'landing')
                    @include('admin.settings._field', ['name' => 'hero_title',       'value' => $values['hero_title']       ?? null])
                    @include('admin.settings._field', ['name' => 'hero_subtitle',    'value' => $values['hero_subtitle']    ?? null])
                    @include('admin.settings._field', ['name' => 'hero_cta_text',    'value' => $values['hero_cta_text']    ?? null])
                    @include('admin.settings._field', ['name' => 'hero_cta_url',     'value' => $values['hero_cta_url']     ?? null, 'type' => 'url'])
                    @include('admin.settings._field', ['name' => 'hero_image',       'value' => $values['hero_image']       ?? null])
                    @include('admin.settings._field', ['name' => 'footer_copyright', 'value' => $values['footer_copyright'] ?? null])
                    @include('admin.settings._field', ['name' => 'privacy_url',      'value' => $values['privacy_url']      ?? null, 'type' => 'url'])
                    @include('admin.settings._field', ['name' => 'terms_url',        'value' => $values['terms_url']        ?? null, 'type' => 'url'])
                @endif

                {{-- ─── Notifications ──────────────────────────────────── --}}
                @if ($tab === 'notifications')
                    @include('admin.settings._toggle', ['name' => 'notify_new_subscription',       'value' => $values['notify_new_subscription']       ?? null])
                    @include('admin.settings._toggle', ['name' => 'notify_payment_failed',         'value' => $values['notify_payment_failed']         ?? null])
                    @include('admin.settings._toggle', ['name' => 'notify_trial_expiring',         'value' => $values['notify_trial_expiring']         ?? null])
                    @include('admin.settings._toggle', ['name' => 'notify_subscription_expiring',  'value' => $values['notify_subscription_expiring']  ?? null])
                    @include('admin.settings._field',  ['name' => 'admin_notification_email',      'value' => $values['admin_notification_email']      ?? null, 'type' => 'email'])
                @endif

                <div style="margin-top:20px;">
                    <button type="submit" class="btn">{{ __('admin.settings.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- jQuery + SweetAlert2 + Toastr (project convention). The admin.layout
         doesn't ship them globally; the Test buttons rely on plain fetch
         instead so the layout stays unchanged. --}}
    <script>
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function postJson(url, payload) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(payload || {}),
                }).then(async r => ({ ok: r.ok, body: await r.json().catch(() => ({})) }));
            }

            const mailBtn = document.getElementById('btn-test-mail');
            if (mailBtn) {
                mailBtn.addEventListener('click', () => {
                    const to = (document.getElementById('test-mail-to') || {}).value || '';
                    const out = document.getElementById('mail-test-result');
                    out.textContent = '…';
                    postJson(mailBtn.dataset.url, { to }).then(({ ok, body }) => {
                        out.textContent = body.message || (ok ? 'OK' : 'Error');
                        out.style.color = ok ? '#34d399' : '#fca5a5';
                    });
                });
            }
            const moyasarBtn = document.getElementById('btn-test-moyasar');
            if (moyasarBtn) {
                moyasarBtn.addEventListener('click', () => {
                    const out = document.getElementById('moyasar-test-result');
                    out.textContent = '…';
                    postJson(moyasarBtn.dataset.url).then(({ ok, body }) => {
                        out.textContent = body.message || (ok ? 'OK' : 'Error');
                        out.style.color = ok ? '#34d399' : '#fca5a5';
                    });
                });
            }
        })();
    </script>
@endsection
