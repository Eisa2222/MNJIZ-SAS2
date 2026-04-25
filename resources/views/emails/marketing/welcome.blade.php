<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مرحباً في MNJIZ</title>
</head>
<body style="font-family:Cairo,Arial,sans-serif;background:#f7fafc;margin:0;padding:24px">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.05)">
    <h1 style="color:#0f4c75;font-size:24px;margin:0 0 16px">مرحباً، {{ $user->name }} 👋</h1>

    <p style="color:#1a202c;font-size:16px;line-height:1.7">
        أهلاً بك في <strong>MNJIZ</strong>. تم إنشاء مكتبك "<strong>{{ $tenant->name }}</strong>" بنجاح،
        وبدأت تجربتك المجانية على باقة <strong>{{ $plan->name ?? 'Pro' }}</strong>.
    </p>

    @if($trial_ends)
        <div style="background:#edf2f7;border-radius:8px;padding:16px;margin:20px 0">
            <strong>📅 تجربتك المجانية تنتهي:</strong> {{ \Carbon\Carbon::parse($trial_ends)->format('Y-m-d') }}
            <div style="color:#4a5568;font-size:14px;margin-top:6px">
                ستصلك تذكيرات قبل الانتهاء بـ 7 و 3 و 1 يوم.
            </div>
        </div>
    @endif

    <h2 style="color:#0f4c75;font-size:18px;margin:24px 0 12px">الخطوات التالية</h2>
    <ol style="color:#1a202c;font-size:15px;line-height:1.9;padding-right:20px">
        <li>أضف زملاءك في الفريق من إعدادات الموظفين.</li>
        <li>اربط حساب Microsoft للوصول إلى Teams و OneDrive.</li>
        <li>عرّف أول دعوى أو عقد لتجربة سير العمل الكامل.</li>
        <li>اطّلع على المركز التعريفي داخل النظام.</li>
    </ol>

    <p style="margin:24px 0">
        <a href="{{ url('/employees/dashboard') }}"
           style="background:#0f4c75;color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;display:inline-block;font-weight:600">
            ابدأ الآن
        </a>
    </p>

    <hr style="border:0;border-top:1px solid #e2e8f0;margin:32px 0">

    <p style="color:#718096;font-size:13px;margin:0">
        تحتاج مساعدة؟ راسلنا على
        <a href="mailto:support@mnjiz.sa" style="color:#0f4c75">support@mnjiz.sa</a>.
    </p>
</div>
</body>
</html>
