<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مرحباً في MNJIZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Cairo',Arial,sans-serif;background:#f7fafc;color:#1a202c;margin:0;padding:40px 20px}
        .wrap{max-width:760px;margin:0 auto;background:#fff;border-radius:16px;padding:40px;box-shadow:0 1px 3px rgba(0,0,0,0.05)}
        h1{color:#0f4c75;font-size:28px;margin:0 0 8px}
        .lead{color:#4a5568;font-size:16px;margin-bottom:24px}
        .alert{background:#c6f6d5;border:1px solid #38a169;color:#22543d;padding:14px 16px;border-radius:8px;margin-bottom:24px;font-size:14px}
        .checklist{list-style:none;padding:0;margin:0 0 32px}
        .checklist li{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px}
        .checklist li.done{background:#f0fff4;border-color:#9ae6b4}
        .checklist .check{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0}
        .checklist .check.done{background:#48bb78;color:#fff}
        .checklist .check.pending{background:#edf2f7;color:#a0aec0;border:2px dashed #cbd5e0}
        .checklist .label{flex-grow:1}
        .checklist .label small{display:block;color:#718096;font-size:12px;margin-top:2px}
        .checklist a{color:#0f4c75;font-weight:600;font-size:13px;text-decoration:none}
        .cta{display:block;text-align:center;background:#0f4c75;color:#fff;padding:16px;border-radius:8px;text-decoration:none;font-weight:600;font-size:16px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>مرحباً، {{ optional($user)->name }} 👋</h1>
    <p class="lead">تم إنشاء مكتبك "{{ optional($tenant)->name }}". هذه الخطوات الأربع تأخذ أقل من 5 دقائق ولكنها ستوفر عليك ساعات لاحقاً.</p>

    @if(session('signup.success'))
        <div class="alert">{{ session('signup.success') }}</div>
    @endif

    <ul class="checklist">
        <li class="{{ $checklist['account_created'] ? 'done' : '' }}">
            <span class="check {{ $checklist['account_created'] ? 'done' : 'pending' }}">{{ $checklist['account_created'] ? '✓' : '1' }}</span>
            <div class="label">
                <strong>أنشئ حسابك</strong>
                <small>تم — حسابك جاهز للاستخدام.</small>
            </div>
        </li>

        <li class="{{ $checklist['team_invited'] ? 'done' : '' }}">
            <span class="check {{ $checklist['team_invited'] ? 'done' : 'pending' }}">{{ $checklist['team_invited'] ? '✓' : '2' }}</span>
            <div class="label">
                <strong>ادعُ زملاءك</strong>
                <small>ضِف موظفي مكتبك للوصول إلى النظام.</small>
            </div>
            <a href="{{ url('/employees/dashboard') }}">اذهب الآن →</a>
        </li>

        <li class="{{ $checklist['first_lawsuit'] ? 'done' : '' }}">
            <span class="check {{ $checklist['first_lawsuit'] ? 'done' : 'pending' }}">{{ $checklist['first_lawsuit'] ? '✓' : '3' }}</span>
            <div class="label">
                <strong>أنشئ أول دعوى</strong>
                <small>اطّلع على سير العمل القانوني الكامل.</small>
            </div>
            <a href="{{ url('/employees/dashboard') }}">ابدأ →</a>
        </li>

        <li class="{{ $checklist['first_contract'] ? 'done' : '' }}">
            <span class="check {{ $checklist['first_contract'] ? 'done' : 'pending' }}">{{ $checklist['first_contract'] ? '✓' : '4' }}</span>
            <div class="label">
                <strong>سجّل أول عقد</strong>
                <small>جرّب نظام التحصيل والفوترة.</small>
            </div>
            <a href="{{ url('/employees/dashboard') }}">ابدأ →</a>
        </li>
    </ul>

    <a href="{{ url('/employees/dashboard') }}" class="cta">انتقل إلى لوحة التحكم</a>
</div>
</body>
</html>
