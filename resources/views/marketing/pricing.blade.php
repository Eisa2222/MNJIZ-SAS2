<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الأسعار — MNJIZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Cairo',Arial,sans-serif;background:#f7fafc;color:#1a202c;line-height:1.6}
        a{color:inherit;text-decoration:none}
        .container{max-width:1180px;margin:0 auto;padding:0 24px}
        nav{background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.05);position:sticky;top:0;z-index:50}
        nav .container{display:flex;align-items:center;justify-content:space-between;height:64px}
        nav .brand{font-weight:700;font-size:22px;color:#0f4c75}
        nav a.cta{background:#0f4c75;color:#fff;padding:10px 20px;border-radius:8px;font-weight:600}
        section{padding:64px 0}
        h1{font-size:36px;text-align:center;margin-bottom:8px;color:#0f4c75}
        .lead{text-align:center;color:#4a5568;margin-bottom:48px;max-width:700px;margin-left:auto;margin-right:auto}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;align-items:stretch}
        .plan{background:#fff;border:2px solid #e2e8f0;border-radius:16px;padding:32px 28px;display:flex;flex-direction:column}
        .plan.featured{border-color:#0f4c75;box-shadow:0 8px 24px rgba(15,76,117,0.15);transform:translateY(-8px);position:relative}
        .plan.featured::before{content:'الأكثر شيوعاً';position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:#0f4c75;color:#fff;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700}
        .plan h3{font-size:24px;font-weight:700;color:#0f4c75;margin-bottom:6px}
        .plan .desc{color:#718096;font-size:14px;margin-bottom:20px;min-height:48px}
        .plan .price{font-size:40px;font-weight:700;margin-bottom:6px}
        .plan .price small{font-size:14px;color:#718096;font-weight:400}
        .plan .yearly{color:#48bb78;font-size:13px;margin-bottom:20px}
        .plan .features{list-style:none;margin:20px 0;flex-grow:1}
        .plan .features li{padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#4a5568;display:flex;align-items:flex-start;gap:8px}
        .plan .features li::before{content:'✓';color:#48bb78;font-weight:700;font-size:16px;flex-shrink:0}
        .btn{display:inline-block;padding:14px 32px;border-radius:8px;font-weight:600;font-size:15px;cursor:pointer;border:0;width:100%;text-align:center}
        .btn-primary{background:#0f4c75;color:#fff}
        .btn-secondary{background:#edf2f7;color:#0f4c75}
    </style>
</head>
<body>

<nav>
    <div class="container">
        <a href="/" class="brand">MNJIZ</a>
        <a href="/register" class="cta">ابدأ مجاناً</a>
    </div>
</nav>

<section>
    <div class="container">
        <h1>الأسعار</h1>
        <p class="lead">جميع الباقات تشمل تجربة مجانية لمدة 14 يوم. الاشتراك السنوي يوفر ما يعادل شهرين.</p>

        <div class="grid">
            @foreach($plans as $plan)
                <div class="plan {{ $plan->is_featured ? 'featured' : '' }}">
                    <h3>{{ $plan->name }}</h3>
                    <div class="desc">{{ $plan->description }}</div>
                    <div class="price">{{ number_format($plan->price_monthly) }} <small>{{ $plan->currency ?? 'SAR' }} / شهر</small></div>
                    <div class="yearly">سنوي: {{ number_format($plan->price_yearly) }} {{ $plan->currency ?? 'SAR' }} (وفّر شهرين)</div>
                    <ul class="features">
                        @foreach($plan->features as $f)
                            <li>{{ $f->name }}: <strong>{{ $f->pivot->value === '__unlimited__' ? 'غير محدود' : $f->pivot->value }}</strong></li>
                        @endforeach
                    </ul>
                    <a href="/register?plan={{ $plan->slug }}" class="btn {{ $plan->is_featured ? 'btn-primary' : 'btn-secondary' }}">
                        {{ $plan->trial_days > 0 ? "ابدأ تجربة مجانية {$plan->trial_days} يوم" : 'ابدأ الآن' }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

</body>
</html>
