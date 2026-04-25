<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب — MNJIZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Cairo',Arial,sans-serif;background:linear-gradient(135deg,#0f4c75 0%,#3282b8 100%);min-height:100vh;padding:40px 20px;color:#1a202c}
        .card{max-width:520px;margin:0 auto;background:#fff;border-radius:16px;padding:40px;box-shadow:0 12px 40px rgba(0,0,0,0.15)}
        .card h1{font-size:24px;color:#0f4c75;margin-bottom:6px}
        .card .lead{color:#4a5568;font-size:14px;margin-bottom:24px}
        label{display:block;font-weight:600;font-size:14px;margin-bottom:6px;color:#1a202c}
        input,select{width:100%;padding:12px 14px;border:1px solid #cbd5e0;border-radius:8px;font-size:15px;font-family:inherit}
        input:focus,select:focus{outline:0;border-color:#0f4c75;box-shadow:0 0 0 3px rgba(15,76,117,0.2)}
        .field{margin-bottom:18px}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .terms{display:flex;gap:8px;align-items:flex-start;font-size:13px;color:#4a5568;margin:8px 0 20px}
        .terms input{width:auto;margin-top:3px}
        .btn{width:100%;background:#0f4c75;color:#fff;padding:14px;border:0;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;font-family:inherit}
        .btn:hover{background:#0d3d5e}
        .err{color:#c53030;font-size:13px;margin-top:4px}
        .errors{background:#fed7d7;border:1px solid #fc8181;padding:12px 14px;border-radius:8px;color:#742a2a;margin-bottom:18px;font-size:14px}
        .footer{text-align:center;margin-top:24px;font-size:13px;color:#718096}
        .footer a{color:#0f4c75;font-weight:600}
        .brand{text-align:center;color:#fff;font-size:24px;font-weight:700;margin-bottom:24px}
    </style>
</head>
<body>

<div class="brand">MNJIZ</div>

<form method="POST" action="{{ route('register.store') }}" class="card">
    @csrf
    <h1>أنشئ مكتبك في دقيقة</h1>
    <p class="lead">تجربة مجانية لمدة 14 يوم. لا حاجة لبطاقة ائتمان.</p>

    @if($errors->any())
        <div class="errors">
            <ul style="padding-right:18px">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="field">
        <label>اسم المكتب القانوني</label>
        <input type="text" name="firm_name" value="{{ old('firm_name') }}" required placeholder="مثال: مكتب البرهان للمحاماة">
    </div>

    <div class="row">
        <div class="field">
            <label>اسمك الكامل</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="field">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
    </div>

    <div class="row">
        <div class="field">
            <label>كلمة المرور</label>
            <input type="password" name="password" required minlength="8">
        </div>
        <div class="field">
            <label>تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" required minlength="8">
        </div>
    </div>

    <div class="row">
        <div class="field">
            <label>الباقة</label>
            <select name="plan">
                @foreach($plans as $p)
                    <option value="{{ $p->slug }}" {{ ($pre_plan ?? old('plan', 'pro')) === $p->slug ? 'selected' : '' }}>
                        {{ $p->name }} — {{ number_format($p->price_monthly) }} {{ $p->currency ?? 'SAR' }}/شهر
                    </option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>دورة الفوترة</label>
            <select name="cycle">
                <option value="monthly" {{ old('cycle', 'monthly') === 'monthly' ? 'selected' : '' }}>شهري</option>
                <option value="yearly"  {{ old('cycle') === 'yearly' ? 'selected' : '' }}>سنوي (وفّر شهرين)</option>
            </select>
        </div>
    </div>

    <label class="terms">
        <input type="checkbox" name="terms" value="1" required>
        <span>أوافق على <a href="#">الشروط والأحكام</a> وسياسة الخصوصية.</span>
    </label>

    <button type="submit" class="btn">ابدأ التجربة المجانية</button>

    <div class="footer">
        لديك حساب بالفعل؟ <a href="/employees">سجّل الدخول</a>
    </div>
</form>

</body>
</html>
