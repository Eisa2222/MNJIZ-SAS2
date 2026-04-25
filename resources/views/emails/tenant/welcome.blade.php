<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ __('emails.tenant_welcome.subject', ['app' => $app_name]) }}</title>
</head>
<body style="margin:0;padding:24px;background:#f7fafc;font-family:'Cairo',Tahoma,Arial,sans-serif;color:#1a202c;line-height:1.6;">

<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">

    {{-- ─── header ───────────────────────────────────────── --}}
    <div style="background:linear-gradient(135deg,#0f4c75,#3282b8);color:#fff;padding:32px 24px;text-align:center;">
        <h1 style="margin:0;font-size:24px;font-weight:700;">{{ __('emails.tenant_welcome.heading', ['app' => $app_name]) }}</h1>
    </div>

    {{-- ─── body ─────────────────────────────────────────── --}}
    <div style="padding:32px 24px;">
        <p style="font-size:16px;margin:0 0 16px;">
            {{ __('emails.tenant_welcome.greeting', ['name' => $user->name]) }}
        </p>

        <p style="font-size:14px;color:#4a5568;margin:0 0 24px;">
            {{ __('emails.tenant_welcome.intro', ['firm' => $tenant->name, 'app' => $app_name]) }}
        </p>

        {{-- ─── account summary ────────────────────────── --}}
        <div style="background:#edf2f7;border-radius:8px;padding:16px;margin:0 0 24px;">
            <h3 style="margin:0 0 12px;font-size:14px;color:#0f4c75;">{{ __('emails.tenant_welcome.summary_title') }}</h3>
            <table style="width:100%;font-size:13px;color:#2d3748;">
                <tr>
                    <td style="padding:4px 0;color:#718096;">{{ __('emails.tenant_welcome.fields.firm') }}</td>
                    <td style="padding:4px 0;text-align:end;font-weight:600;">{{ $tenant->name }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;color:#718096;">{{ __('emails.tenant_welcome.fields.email') }}</td>
                    <td style="padding:4px 0;text-align:end;font-weight:600;">{{ $user->email }}</td>
                </tr>
                @if ($plan_name)
                    <tr>
                        <td style="padding:4px 0;color:#718096;">{{ __('emails.tenant_welcome.fields.plan') }}</td>
                        <td style="padding:4px 0;text-align:end;font-weight:600;">{{ $plan_name }}</td>
                    </tr>
                @endif
                @if ($billing_cycle)
                    <tr>
                        <td style="padding:4px 0;color:#718096;">{{ __('emails.tenant_welcome.fields.cycle') }}</td>
                        <td style="padding:4px 0;text-align:end;font-weight:600;">{{ $billing_cycle }}</td>
                    </tr>
                @endif
                @if ($trial_ends_at)
                    <tr>
                        <td style="padding:4px 0;color:#718096;">{{ __('emails.tenant_welcome.fields.trial_ends') }}</td>
                        <td style="padding:4px 0;text-align:end;font-weight:600;">{{ $trial_ends_at->format('Y-m-d') }}</td>
                    </tr>
                @elseif ($period_ends_at)
                    <tr>
                        <td style="padding:4px 0;color:#718096;">{{ __('emails.tenant_welcome.fields.period_ends') }}</td>
                        <td style="padding:4px 0;text-align:end;font-weight:600;">{{ $period_ends_at->format('Y-m-d') }}</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- ─── primary CTA — setup link ───────────────── --}}
        <h3 style="margin:0 0 8px;font-size:16px;color:#0f4c75;">{{ __('emails.tenant_welcome.setup_title') }}</h3>
        <p style="font-size:14px;color:#4a5568;margin:0 0 16px;">
            {{ __('emails.tenant_welcome.setup_intro', ['hours' => $valid_hours]) }}
        </p>

        <div style="text-align:center;margin:0 0 24px;">
            <a href="{{ $setup_url }}"
               style="display:inline-block;background:#0f4c75;color:#fff;padding:14px 32px;border-radius:8px;font-weight:700;text-decoration:none;font-size:15px;">
                {{ __('emails.tenant_welcome.setup_cta') }}
            </a>
        </div>

        <p style="font-size:12px;color:#a0aec0;text-align:center;margin:0 0 8px;">
            {{ __('emails.tenant_welcome.fallback_url_hint') }}
        </p>
        <p style="font-size:11px;color:#a0aec0;text-align:center;word-break:break-all;margin:0 0 24px;direction:ltr;">
            {{ $setup_url }}
        </p>

        <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">

        {{-- ─── secondary info ─────────────────────────── --}}
        <p style="font-size:13px;color:#4a5568;margin:0 0 8px;">
            {{ __('emails.tenant_welcome.login_hint') }}
            <a href="{{ $login_url }}" style="color:#0f4c75;font-weight:600;">{{ $login_url }}</a>
        </p>

        <p style="font-size:13px;color:#4a5568;margin:0 0 8px;">
            {{ __('emails.tenant_welcome.support_hint') }}
            <a href="mailto:{{ $support_email }}" style="color:#0f4c75;">{{ $support_email }}</a>
        </p>

        <p style="font-size:12px;color:#a0aec0;margin:24px 0 0;">
            {{ __('emails.tenant_welcome.security_notice') }}
        </p>
    </div>

    {{-- ─── footer ───────────────────────────────────────── --}}
    <div style="background:#f7fafc;color:#718096;padding:16px;text-align:center;font-size:12px;">
        © {{ date('Y') }} {{ $app_name }}
    </div>
</div>

</body>
</html>
