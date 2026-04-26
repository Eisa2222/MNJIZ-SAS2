<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ __('emails.trial_warning.subject', ['app' => $app_name, 'days' => $days_remaining]) }}</title>
</head>
<body style="margin:0;padding:24px;background:#f7fafc;font-family:'Cairo',Tahoma,Arial,sans-serif;color:#1a202c;line-height:1.6;">

<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">

    <div style="background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;padding:32px 24px;text-align:center;">
        <div style="font-size:48px;line-height:1;">⏳</div>
        <h1 style="margin:12px 0 0;font-size:22px;font-weight:700;">
            {{ __('emails.trial_warning.heading') }}
        </h1>
    </div>

    <div style="padding:32px 24px;">
        <p style="font-size:16px;margin:0 0 16px;">
            {{ __('emails.trial_warning.greeting', ['name' => $user->name]) }}
        </p>

        <p style="font-size:14px;color:#4a5568;margin:0 0 24px;">
            {{ __('emails.trial_warning.body', [
                'plan' => $plan_name,
                'days' => $days_remaining,
                'date' => optional($trial_ends_at)->format('Y-m-d') ?? '—',
            ]) }}
        </p>

        <div style="background:#fef3c7;border-radius:8px;padding:16px;margin:0 0 24px;text-align:center;">
            <strong style="color:#92400e;font-size:15px;">{{ $days_remaining }} {{ __('emails.trial_warning.heading') }}</strong>
        </div>

        <div style="text-align:center;margin:0 0 24px;">
            <a href="{{ $pricing_url }}"
               style="display:inline-block;background:#0f4c75;color:#fff;padding:14px 32px;border-radius:8px;font-weight:700;text-decoration:none;font-size:15px;">
                {{ __('emails.trial_warning.cta') }}
            </a>
        </div>

        <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">

        <p style="font-size:13px;color:#4a5568;margin:0;">
            {{ __('emails.trial_warning.support_hint') }}
            <a href="mailto:{{ $support_email }}" style="color:#0f4c75;">{{ $support_email }}</a>
        </p>
    </div>

    <div style="background:#f7fafc;color:#718096;padding:16px;text-align:center;font-size:12px;">
        © {{ date('Y') }} {{ $app_name }}
    </div>
</div>

</body>
</html>
