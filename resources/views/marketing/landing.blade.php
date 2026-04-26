<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\SystemSetting::get('app_name', 'MNJIZ') }} — {{ __('landing.meta.title') }}</title>
    <meta name="description" content="{{ __('landing.meta.description') }}">

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
        .hero img.hero-art{max-width:520px;width:100%;margin-top:32px;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.25)}
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
        .feature img.thumb{width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:16px}

        .pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;align-items:stretch}
        .plan{background:#fff;border:2px solid #e2e8f0;border-radius:16px;padding:32px 28px;display:flex;flex-direction:column}
        .plan.featured{border-color:#0f4c75;box-shadow:0 8px 24px rgba(15,76,117,0.15);transform:translateY(-8px);position:relative}
        .plan.featured::before{content:'{{ __('landing.pricing.most_popular') }}';position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:#0f4c75;color:#fff;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700}
        .plan h3{font-size:22px;font-weight:700;color:#0f4c75;margin-bottom:6px}
        .plan .desc{color:#718096;font-size:14px;margin-bottom:20px;min-height:42px}
        .plan .price{font-size:36px;font-weight:700;margin-bottom:6px}
        .plan .price small{font-size:14px;color:#718096;font-weight:400}
        .plan .yearly{color:#48bb78;font-size:13px;margin-bottom:20px}
        .plan .features{list-style:none;margin:20px 0;flex-grow:1}
        .plan .features li{padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:14px;color:#4a5568;display:flex;align-items:center;gap:8px}
        .plan .features li::before{content:'✓';color:#48bb78;font-weight:700;font-size:16px}
        .plan .btn{width:100%;text-align:center}

        .faq dl dt{font-weight:700;color:#0f4c75;padding:16px 0 8px;cursor:pointer}
        .faq dl dd{color:#4a5568;padding-bottom:16px;border-bottom:1px solid #e2e8f0}

        footer{background:#1a202c;color:#cbd5e0;padding:48px 0 24px;text-align:center}
        footer .links{display:flex;gap:24px;justify-content:center;margin-bottom:16px;flex-wrap:wrap}
        footer .copy{font-size:13px;color:#718096}
    </style>
</head>
<body>

<nav>
    <div class="container">
        <a href="/" class="brand">{{ \App\Models\SystemSetting::get('app_name', 'MNJIZ') }}</a>
        <ul>
            <li><a href="#features">{{ __('landing.nav.features') }}</a></li>
            <li><a href="#pricing">{{ __('landing.nav.pricing') }}</a></li>
            <li><a href="#faq">{{ __('landing.nav.faq') }}</a></li>
            <li><a href="{{ $hero['cta_url'] }}" class="cta">{{ __('landing.nav.cta') }}</a></li>
        </ul>
    </div>
</nav>

{{-- ─── HERO (dynamic) ────────────────────────────────────────── --}}
<header class="hero">
    <div class="container">
        <h1>{{ $hero['title'] }}</h1>
        <p>{{ $hero['subtitle'] }}</p>
        <div class="cta-row">
            <a href="{{ $hero['cta_url'] }}" class="btn btn-primary">{{ $hero['cta_text'] }}</a>
            <a href="#pricing" class="btn btn-secondary">{{ __('landing.hero.see_pricing') }}</a>
        </div>
        @if (! empty($hero['image']))
            <img src="{{ $hero['image'] }}" alt="" class="hero-art">
        @endif
    </div>
</header>

{{-- ─── FEATURES (dynamic — falls back to Phase 9 hardcoded copy) ── --}}
<section id="features">
    <div class="container">
        <h2>{{ __('landing.features.title') }}</h2>
        <p class="lead">{{ __('landing.features.lead') }}</p>
        <div class="features-grid">
            @forelse ($features as $feature)
                <div class="feature">
                    @if (! empty($feature->image))
                        <img src="{{ $feature->image }}" alt="" class="thumb">
                    @endif
                    @if (! empty($feature->icon))
                        <div class="icon">{{ $feature->icon }}</div>
                    @endif
                    <h3>{{ $feature->title }}</h3>
                    <p>{{ $feature->description }}</p>
                </div>
            @empty
                {{-- Defensive fallback so a brand-new install still renders. --}}
                <div class="feature"><div class="icon">⚖️</div><h3>{{ __('landing.features.fallback.legal.title') }}</h3><p>{{ __('landing.features.fallback.legal.body') }}</p></div>
                <div class="feature"><div class="icon">👥</div><h3>{{ __('landing.features.fallback.hr.title') }}</h3><p>{{ __('landing.features.fallback.hr.body') }}</p></div>
                <div class="feature"><div class="icon">💰</div><h3>{{ __('landing.features.fallback.billing.title') }}</h3><p>{{ __('landing.features.fallback.billing.body') }}</p></div>
                <div class="feature"><div class="icon">🤖</div><h3>{{ __('landing.features.fallback.ai.title') }}</h3><p>{{ __('landing.features.fallback.ai.body') }}</p></div>
            @endforelse
        </div>
    </div>
</section>

{{-- ─── PRICING (existing plans data — unchanged) ─────────────── --}}
<section id="pricing" style="background:#fff">
    <div class="container">
        <h2>{{ __('landing.pricing.title') }}</h2>
        <p class="lead">{{ __('landing.pricing.lead') }}</p>
        <div class="pricing-grid">
            @forelse($plans as $plan)
                <div class="plan {{ $plan->is_featured ? 'featured' : '' }}">
                    <h3>{{ $plan->name }}</h3>
                    <div class="desc">{{ $plan->description }}</div>
                    <div class="price">{{ number_format($plan->price_monthly) }} <small>{{ $plan->currency ?? 'SAR' }} {{ __('landing.pricing.per_month') }}</small></div>
                    <div class="yearly">{{ __('landing.pricing.yearly') }}: {{ number_format($plan->price_yearly) }} {{ $plan->currency ?? 'SAR' }} {{ __('landing.pricing.save_two_months') }}</div>
                    <ul class="features">
                        @foreach($plan->features->take(6) as $f)
                            <li>{{ $f->name }}: <strong>{{ $f->pivot->value }}</strong></li>
                        @endforeach
                    </ul>
                    <a href="{{ route('checkout.show', ['plan' => $plan->slug]) }}" class="btn btn-primary" style="background:{{ $plan->is_featured ? '#0f4c75' : '#edf2f7' }};color:{{ $plan->is_featured ? '#fff' : '#0f4c75' }}">
                        {{ __('checkout.cta_on_pricing') }}
                    </a>
                </div>
            @empty
                {{-- Hotfix: graceful empty-state when DefaultPlansSeeder hasn't run.
                     Stops the customer landing on a blank "Pricing" header section. --}}
                <div style="grid-column:1/-1;text-align:center;padding:48px 24px;background:#fff;border-radius:12px;color:#4a5568;border:2px dashed #e2e8f0;">
                    <p style="margin:0;font-size:15px;">
                        {{ __('landing.pricing.empty_state') }}
                        <a href="mailto:{{ $footer['support_email'] ?? 'support@mnjiz.sa' }}" style="color:#0f4c75;font-weight:600;">{{ __('landing.pricing.empty_state_link') }}</a>.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ─── FAQ (dynamic) ─────────────────────────────────────────── --}}
<section id="faq" class="faq">
    <div class="container" style="max-width:800px">
        <h2>{{ __('landing.faq.title') }}</h2>
        <p class="lead">{{ __('landing.faq.lead') }}</p>
        <dl>
            @forelse ($faqs as $faq)
                <dt>{{ $faq->question }}</dt>
                <dd>{{ $faq->answer }}</dd>
            @empty
                {{-- Defensive fallback so a brand-new install still renders. --}}
                <dt>{{ __('landing.faq.fallback.isolation.q') }}</dt>
                <dd>{{ __('landing.faq.fallback.isolation.a') }}</dd>
                <dt>{{ __('landing.faq.fallback.trial.q') }}</dt>
                <dd>{{ __('landing.faq.fallback.trial.a') }}</dd>
                <dt>{{ __('landing.faq.fallback.upgrade.q') }}</dt>
                <dd>{{ __('landing.faq.fallback.upgrade.a') }}</dd>
            @endforelse
        </dl>
    </div>
</section>

{{-- ─── FOOTER (dynamic) ──────────────────────────────────────── --}}
<footer>
    <div class="container">
        <div class="links">
            <a href="{{ $hero['cta_url'] }}">{{ __('landing.footer.start_now') }}</a>
            <a href="#pricing">{{ __('landing.nav.pricing') }}</a>
            <a href="#faq">{{ __('landing.nav.faq') }}</a>
            @if (! empty($footer['privacy_url']))
                <a href="{{ $footer['privacy_url'] }}">{{ __('landing.footer.privacy') }}</a>
            @endif
            @if (! empty($footer['terms_url']))
                <a href="{{ $footer['terms_url'] }}">{{ __('landing.footer.terms') }}</a>
            @endif
            @if (! empty($footer['support_email']))
                <a href="mailto:{{ $footer['support_email'] }}">{{ __('landing.footer.contact') }}</a>
            @endif
        </div>
        <div class="copy">{{ $footer['copyright'] }}</div>
    </div>
</footer>

</body>
</html>
