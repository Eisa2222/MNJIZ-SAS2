<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>مرحباً بك في {{ $appName }}</title>
</head>
<body style="margin:0;padding:24px;background:#f7fafc;font-family:Tahoma,Arial,sans-serif;color:#1a202c;line-height:1.6;">

<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:linear-gradient(135deg,#0c4a6e,#0369a1);color:#fff;padding:32px;text-align:center;">
        <h1 style="margin:0;font-size:24px;font-weight:700;">مرحباً بك في {{ $appName }}</h1>
    </div>

    <div style="padding:32px;">
        <p style="margin:0 0 16px;">مرحباً <strong>{{ $tenant->owner_name }}</strong>،</p>

        <p style="color:#4a5568;margin:0 0 24px;">
            تم إنشاء حساب <strong>{{ $tenant->company_name }}</strong> على منصة {{ $appName }} بنجاح.
            باقتك المختارة: <strong>{{ $plan->name }}</strong>.
        </p>

        <div style="background:#edf2f7;border-radius:8px;padding:16px;margin-bottom:24px;font-size:14px;">
            <table style="width:100%">
                <tr><td style="color:#718096;padding:4px 0;">الشركة</td><td style="text-align:end;font-weight:600;">{{ $tenant->company_name }}</td></tr>
                <tr><td style="color:#718096;padding:4px 0;">البريد</td><td style="text-align:end;font-weight:600;">{{ $tenant->owner_email }}</td></tr>
                <tr><td style="color:#718096;padding:4px 0;">الباقة</td><td style="text-align:end;font-weight:600;">{{ $plan->name }}</td></tr>
            </table>
        </div>

        <h3 style="color:#0c4a6e;font-size:16px;margin:0 0 8px;">إعداد كلمة المرور</h3>
        <p style="font-size:14px;color:#4a5568;margin:0 0 16px;">
            هذا الرابط صالح <strong>48 ساعة</strong> ولن نطلب منك كلمة مرور مؤقتة في أي بريد.
        </p>

        <div style="text-align:center;margin:0 0 24px;">
            <a href="{{ $setupUrl }}"
               style="display:inline-block;background:#0369a1;color:#fff;padding:14px 32px;border-radius:8px;font-weight:700;text-decoration:none;">
                حدّد كلمة المرور الآن
            </a>
        </div>

        <p style="font-size:11px;color:#a0aec0;text-align:center;word-break:break-all;direction:ltr;">{{ $setupUrl }}</p>

        <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">

        <p style="font-size:13px;color:#4a5568;margin:0 0 8px;">
            بعد التهيئة، يمكنك الدخول من:<br>
            <a href="{{ $loginUrl }}" style="color:#0369a1;font-weight:600;">{{ $loginUrl }}</a>
        </p>

        @if ($supportEmail)
            <p style="font-size:13px;color:#4a5568;margin:0;">
                دعم: <a href="mailto:{{ $supportEmail }}" style="color:#0369a1;">{{ $supportEmail }}</a>
            </p>
        @endif
    </div>

    <div style="background:#f7fafc;color:#718096;padding:16px;text-align:center;font-size:12px;">
        © {{ date('Y') }} {{ $appName }}
    </div>
</div>

</body>
</html>
