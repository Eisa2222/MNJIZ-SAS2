<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MNJIZ — منصة إدارة المكاتب القانونية والموارد البشرية</title>
    <meta name="description" content="نظام SaaS متعدد المستأجرين لإدارة المكاتب القانونية، الموارد البشرية، العقود، الجلسات، والمحاسبة — كل شيء في مكان واحد.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Cairo',sans-serif;background:#f7fafc;color:#1a202c;line-height:1.6}
        a{color:inherit;text-decoration:none}
        .container{max-width:1180px;margin:0 auto;padding:0 24px}
        nav{background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.05);position:sticky;top:0;z-index:50}
        nav .container{display:flex;align-items:center;justify-content:space-between;height:64px}
        nav .brand{font-weight:700;font-size:22px;color:#0f4c75}
        nav ul{display:flex;gap:24px;list-style:none}
        nav a.cta{background:#0f4c75;color:#fff;padding:10px 20px;border-radius:8px;font-weight:600}

        .hero{background:linear-gradient(135deg,#0f4c75 0%,#3282b8 100%);color:#fff;padding:80px 0 100px;text-align:center}
        .hero h1{font-size:42px;font-weight:700;margin-bottom:16px}
        .hero p{font-size:20px;opacity:.95;max-width:760px;margin:0 auto 32px}
        .hero .cta-row{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
        .btn{display:inline-block;padding:14px 32px;border-radius:8px;font-weight:600;font-size:16px;cursor:pointer;border:0}
        .btn-primary{background:#fff;color:#0f4c75}
        .btn-secondary{background:transparent;color:#fff;border:2px solid #fff}

        section{padding:72px 0}
        section h2{font-size:32px;font-weight:700;text-align:center;margin-bottom:8px}
        section .lead{text-align:center;color:#4a5568;margin-bottom:48px;max-width:660px;margin-left:auto;margin-right:auto}

        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
        .feature{background:#fff;border-radius:12px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,0.05)}
        .feature .icon{font-size:32px;margin-bottom:12px}
        .feature h3{font-size:18px;font-weight:700;margin-bottom:8px;color:#0f4c75}
        .feature p{color:#4a5568;font-size:14px}

        .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px}
        .step{text-align:center}
        .step .num{display:inline-block;width:48px;height:48px;line-height:48px;background:#0f4c75;color:#fff;border-radius:50%;font-weight:700;margin-bottom:16px}

        .pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;align-items:stretch}
        .plan{background:#fff;border:2px solid #e2e8f0;border-radius:16px;padding:32px 28px;display:flex;flex-direction:column}
        .plan.featured{border-color:#0f4c75;box-shadow:0 8px 24px rgba(15,76,117,0.15);transform:translateY(-8px);position:relative}
        .plan.featured::before{content:'الأكثر شيوعاً';position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:#0f4c75;color:#fff;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700}
        .plan h3{font-size:22px;font-weight:700;color:#0f4c75;margin-bottom:6px}
        .plan .desc{color:#718096;font-size:14px;margin-bottom:20px;min-height:42px}
        .plan .price{font-size:36px;font-weight:700;margin-bottom:6px}
        .plan .price small{font-size:14px;color:#718096;font-weight:400}
        .plan .yearly{color:#48bb78;font-size:13px;margin-bottom:20px}
        .plan .features{list-style:none;margin:20px 0;flex-grow:1}
        .plan .features li{padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#4a5568;display:flex;align-items:center;gap:8px}
        .plan .features li::before{content:'✓';color:#48bb78;font-weight:700;font-size:16px}
        .plan .btn{width:100%;text-align:center}

        .faq dl dt{font-weight:700;color:#0f4c75;padding:16px 0 8px}
        .faq dl dd{color:#4a5568;padding-bottom:16px;border-bottom:1px solid #e2e8f0}

        footer{background:#1a202c;color:#cbd5e0;padding:48px 0 24px;text-align:center}
        footer .links{display:flex;gap:24px;justify-content:center;margin-bottom:16px;flex-wrap:wrap}
        footer .copy{font-size:13px;color:#718096}
    </style>
</head>
<body>

<nav>
    <div class="container">
        <a href="/" class="brand">MNJIZ</a>
        <ul>
            <li><a href="#features">المميزات</a></li>
            <li><a href="#how">كيف يعمل</a></li>
            <li><a href="#pricing">الأسعار</a></li>
            <li><a href="#faq">الأسئلة الشائعة</a></li>
            <li><a href="/register" class="cta">ابدأ مجاناً</a></li>
        </ul>
    </div>
</nav>

<header class="hero">
    <div class="container">
        <h1>منصة واحدة لإدارة مكتبك القانوني بالكامل</h1>
        <p>الموارد البشرية، الدعاوى القضائية، الجلسات، العقود، التحصيل، الفوترة، والذكاء الاصطناعي — كلها في نظام واحد آمن متعدد المستأجرين.</p>
        <div class="cta-row">
            <a href="/register" class="btn btn-primary">ابدأ تجربتك المجانية لمدة 14 يوم</a>
            <a href="#pricing" class="btn btn-secondary">شاهد الأسعار</a>
        </div>
    </div>
</header>

<section id="features">
    <div class="container">
        <h2>كل ما يحتاجه مكتبك في مكان واحد</h2>
        <p class="lead">من إدارة الموظفين والعقود إلى متابعة الجلسات والتحصيلات والمحاسبة — بدون الحاجة لخمس أنظمة منفصلة.</p>
        <div class="features-grid">
            <div class="feature">
                <div class="icon">⚖️</div>
                <h3>إدارة الشؤون القانونية</h3>
                <p>الدعاوى، الجلسات، الخصوم، الوكالات الشرعية، المذكرات، والمستندات — مع تذكيرات تلقائية للجلسات.</p>
            </div>
            <div class="feature">
                <div class="icon">👥</div>
                <h3>الموارد البشرية</h3>
                <p>الموظفون، الحضور، الإجازات، السلف، المخالفات، المكافآت، الرواتب (WPS)، البصمة (BioStation).</p>
            </div>
            <div class="feature">
                <div class="icon">💰</div>
                <h3>التحصيل والفوترة</h3>
                <p>العقود، العروض، الدفعات، تذكيرات الاستحقاق، تكامل قيود (Qoyod) للمحاسبة.</p>
            </div>
            <div class="feature">
                <div class="icon">🤖</div>
                <h3>ذكاء اصطناعي قانوني</h3>
                <p>محادثة قانونية، صياغة المذكرات، استخلاص السوابق، تلخيص المستندات (OpenAI / Claude / Gemini).</p>
            </div>
            <div class="feature">
                <div class="icon">📋</div>
                <h3>المهام والاعتمادات</h3>
                <p>سير العمل، الاعتمادات متعددة المستويات، التذكيرات، تكامل Microsoft Teams.</p>
            </div>
            <div class="feature">
                <div class="icon">🔒</div>
                <h3>عزل تام بين العملاء</h3>
                <p>كل بيانات شركتك معزولة هيكلياً عن باقي العملاء — لا تسرب، لا اختلاط، 173 اختبار يثبت ذلك.</p>
            </div>
        </div>
    </div>
</section>

<section id="how" style="background:#fff">
    <div class="container">
        <h2>كيف يعمل</h2>
        <p class="lead">من التسجيل إلى استخدام النظام في أقل من 5 دقائق.</p>
        <div class="steps">
            <div class="step">
                <div class="num">١</div>
                <h3>سجّل حساباً</h3>
                <p>أدخل بريدك وكلمة المرور وأنشئ مكتبك في خطوة واحدة.</p>
            </div>
            <div class="step">
                <div class="num">٢</div>
                <h3>ابدأ تجربتك</h3>
                <p>14 يوم مجاناً لباقة Pro — كل المميزات بدون قيد.</p>
            </div>
            <div class="step">
                <div class="num">٣</div>
                <h3>أضف فريقك</h3>
                <p>ادعُ الموظفين، أنشئ الأدوار، اربط حساباتك.</p>
            </div>
            <div class="step">
                <div class="num">٤</div>
                <h3>اعمل بحرية</h3>
                <p>دعاوى، جلسات، عقود، فواتير — كل شيء جاهز.</p>
            </div>
        </div>
    </div>
</section>

<section id="pricing">
    <div class="container">
        <h2>أسعار شفافة، بدون مفاجآت</h2>
        <p class="lead">جميع الباقات تشمل تجربة مجانية لمدة 14 يوم. الاشتراك السنوي يوفر ما يعادل شهرين.</p>
        <div class="pricing-grid">
            @foreach($plans as $plan)
                <div class="plan {{ $plan->is_featured ? 'featured' : '' }}">
                    <h3>{{ $plan->name }}</h3>
                    <div class="desc">{{ $plan->description }}</div>
                    <div class="price">{{ number_format($plan->price_monthly) }} <small>{{ $plan->currency ?? 'SAR' }} / شهر</small></div>
                    <div class="yearly">سنوي: {{ number_format($plan->price_yearly) }} {{ $plan->currency ?? 'SAR' }} (وفّر شهرين)</div>
                    <ul class="features">
                        @foreach($plan->features->take(6) as $f)
                            <li>{{ $f->name }}: <strong>{{ $f->pivot->value }}</strong></li>
                        @endforeach
                    </ul>
                    <a href="/register?plan={{ $plan->slug }}" class="btn btn-primary" style="background:{{ $plan->is_featured ? '#0f4c75' : '#edf2f7' }};color:{{ $plan->is_featured ? '#fff' : '#0f4c75' }}">
                        {{ $plan->trial_days > 0 ? "ابدأ تجربة مجانية {$plan->trial_days} يوم" : 'ابدأ الآن' }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="faq" class="faq" style="background:#fff">
    <div class="container" style="max-width:800px">
        <h2>الأسئلة الشائعة</h2>
        <p class="lead">إجابات سريعة على الأسئلة الأكثر تكراراً.</p>
        <dl>
            <dt>هل بياناتي معزولة عن باقي العملاء؟</dt>
            <dd>نعم. كل عميل لديه عزل هيكلي على مستوى قاعدة البيانات (tenant_id) مع 173 اختبار آلي يتحقق من ذلك. لا يوجد أي طريقة لرؤية بيانات عميل آخر.</dd>

            <dt>هل يوجد فترة تجربة مجانية؟</dt>
            <dd>نعم — 14 يوماً كاملة على باقة Pro بكل المميزات (ذكاء اصطناعي، Microsoft Teams، Qoyod). لا حاجة لبطاقة ائتمان للبدء.</dd>

            <dt>كم تستغرق عملية الإعداد؟</dt>
            <dd>أقل من 5 دقائق للحساب الأساسي. ربط Microsoft / Qoyod / BioStation اختياري ويتم لاحقاً.</dd>

            <dt>هل يدعم النظام اللغة العربية؟</dt>
            <dd>النظام مصمم بالكامل للسوق السعودي — RTL كامل، تواريخ هجرية، دعم WPS، تكامل قيود.</dd>

            <dt>ماذا يحدث بعد انتهاء التجربة المجانية؟</dt>
            <dd>تتلقى تذكيرات قبل الانتهاء بـ 7 و 3 و 1 يوم. إذا لم تشترك، الحساب يتوقف لكن البيانات تُحفظ 30 يوماً قبل الحذف.</dd>

            <dt>هل يمكنني الترقية / التخفيض في أي وقت؟</dt>
            <dd>نعم. يمكنك تغيير الباقة في أي وقت من صفحة الفوترة. التغييرات تنعكس على الفاتورة التالية بشكل تناسبي.</dd>
        </dl>
    </div>
</section>

<footer>
    <div class="container">
        <div class="links">
            <a href="/register">ابدأ الآن</a>
            <a href="#pricing">الأسعار</a>
            <a href="#faq">الأسئلة الشائعة</a>
            <a href="mailto:support@mnjiz.sa">تواصل معنا</a>
        </div>
        <div class="copy">© {{ date('Y') }} MNJIZ — جميع الحقوق محفوظة</div>
    </div>
</footer>

</body>
</html>
