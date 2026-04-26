<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['app_name'] }} — {{ $settings['hero_title'] }}</title>
    <meta name="description" content="{{ $settings['hero_subtitle'] }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Cairo', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">

<!-- Navigation -->
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2 text-xl font-bold text-sky-700">
            @if ($settings['app_logo'])
                <img src="{{ $settings['app_logo'] }}" alt="" class="h-8">
            @endif
            {{ $settings['app_name'] }}
        </a>
        <ul class="hidden md:flex items-center gap-6 text-sm">
            <li><a href="#features" class="text-slate-600 hover:text-sky-700">المميزات</a></li>
            <li><a href="#pricing" class="text-slate-600 hover:text-sky-700">الأسعار</a></li>
            <li><a href="#faq" class="text-slate-600 hover:text-sky-700">الأسئلة</a></li>
            <li>
                <a href="{{ $settings['hero_cta_url'] }}"
                   class="inline-block bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-lg font-semibold transition">
                    {{ $settings['hero_cta_text'] }}
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Hero -->
<header class="relative bg-gradient-to-br from-sky-700 via-sky-600 to-sky-500 text-white py-20 md:py-28 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
        <div class="text-center md:text-right">
            <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">
                {{ $settings['hero_title'] }}
            </h1>
            <p class="text-lg md:text-xl text-sky-100 mb-8 leading-relaxed">
                {{ $settings['hero_subtitle'] }}
            </p>
            <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                <a href="{{ $settings['hero_cta_url'] }}"
                   class="bg-white text-sky-700 px-8 py-3 rounded-lg font-bold shadow-lg hover:scale-105 transition">
                    {{ $settings['hero_cta_text'] }}
                </a>
                <a href="#pricing"
                   class="border-2 border-white text-white px-8 py-3 rounded-lg font-bold hover:bg-white/10 transition">
                    شاهد الأسعار
                </a>
            </div>
        </div>
        @if ($settings['hero_image'])
            <div class="hidden md:block">
                <img src="{{ $settings['hero_image'] }}" alt="" class="w-full rounded-2xl shadow-2xl">
            </div>
        @endif
    </div>
</header>

<!-- Features -->
@if ($features->isNotEmpty())
<section id="features" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">كل ما يحتاجه مكتبك</h2>
            <p class="text-slate-600 max-w-2xl mx-auto">منظومة متكاملة من إدارة الموظفين إلى متابعة الجلسات والمحاسبة</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($features as $f)
                <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition">
                    @if ($f->icon)
                        <div class="text-4xl mb-3">{{ $f->icon }}</div>
                    @endif
                    <h3 class="text-lg font-bold text-sky-700 mb-2">{{ $f->title }}</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">{{ $f->description }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Pricing -->
<section id="pricing" class="py-20 bg-white" x-data="{ cycle: 'monthly' }">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">أسعار شفافة، بدون مفاجآت</h2>
            <p class="text-slate-600 mb-6">كل الباقات تشمل تجربة مجانية</p>

            <!-- Monthly/Yearly toggle -->
            <div class="inline-flex bg-slate-100 rounded-full p-1">
                <button @click="cycle = 'monthly'"
                        :class="cycle === 'monthly' ? 'bg-white shadow text-sky-700' : 'text-slate-500'"
                        class="px-6 py-2 rounded-full text-sm font-semibold transition">شهري</button>
                <button @click="cycle = 'yearly'"
                        :class="cycle === 'yearly' ? 'bg-white shadow text-sky-700' : 'text-slate-500'"
                        class="px-6 py-2 rounded-full text-sm font-semibold transition">
                    سنوي <span class="text-emerald-600 text-xs">(وفّر شهرين)</span>
                </button>
            </div>
        </div>

        @if ($plans->isEmpty())
            <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                <p class="text-slate-500">الباقات قيد الإعداد. الرجاء المحاولة لاحقاً.</p>
            </div>
        @else
            <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                @foreach ($plans as $plan)
                    <div @class([
                        'relative bg-white rounded-2xl p-8 transition',
                        'ring-2 ring-sky-600 shadow-xl scale-105' => $plan->is_featured,
                        'border border-slate-200 shadow-sm hover:shadow-md' => ! $plan->is_featured,
                    ])>
                        @if ($plan->is_featured && $plan->badge_text)
                            <span class="absolute -top-3 right-1/2 translate-x-1/2 bg-sky-600 text-white text-xs px-3 py-1 rounded-full font-bold">
                                {{ $plan->badge_text }}
                            </span>
                        @endif

                        <h3 class="text-xl font-bold text-sky-700 mb-2">{{ $plan->name }}</h3>
                        <p class="text-slate-500 text-sm mb-6 min-h-[2.5rem]">{{ $plan->description }}</p>

                        <div class="mb-6">
                            <span x-show="cycle === 'monthly'" class="text-4xl font-bold">{{ number_format((float) $plan->price_monthly) }}</span>
                            <span x-show="cycle === 'yearly'" class="text-4xl font-bold" x-cloak>{{ number_format((float) $plan->price_yearly) }}</span>
                            <span class="text-slate-500">{{ $plan->currency }} / <span x-show="cycle === 'monthly'">شهر</span><span x-show="cycle === 'yearly'" x-cloak>سنة</span></span>
                            @if ($plan->yearly_savings_percent > 0)
                                <p x-show="cycle === 'yearly'" x-cloak class="text-emerald-600 text-sm mt-1">وفّر {{ $plan->yearly_savings_percent }}%</p>
                            @endif
                        </div>

                        @if ($plan->planFeatures->isNotEmpty())
                            <ul class="space-y-2 mb-6 text-sm">
                                @foreach ($plan->planFeatures->take(8) as $pf)
                                    <li class="flex items-center gap-2">
                                        <span class="{{ $pf->included ? 'text-emerald-600' : 'text-slate-300' }}">✓</span>
                                        <span class="{{ $pf->included ? 'text-slate-700' : 'text-slate-400 line-through' }}">{{ $pf->feature }}{{ $pf->limit ? ' — '.$pf->limit : '' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a :href="`/checkout/{{ $plan->slug }}?cycle=${cycle}`"
                           @class([
                               'block text-center py-3 rounded-lg font-bold transition',
                               'bg-sky-600 hover:bg-sky-700 text-white' => $plan->is_featured,
                               'bg-slate-100 hover:bg-slate-200 text-sky-700' => ! $plan->is_featured,
                           ])>
                            ابدأ الآن
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- FAQ -->
@if ($faqs->isNotEmpty())
<section id="faq" class="py-20 bg-slate-50">
    <div class="max-w-3xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">الأسئلة الشائعة</h2>
            <p class="text-slate-600">إجابات على ما قد يدور في ذهنك</p>
        </div>
        <div class="space-y-3" x-data="{ open: null }">
            @foreach ($faqs as $i => $faq)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <button @click="open = (open === {{ $i }} ? null : {{ $i }})"
                            class="w-full flex items-center justify-between p-5 text-right hover:bg-slate-50 transition">
                        <span class="font-bold text-slate-900">{{ $faq->question }}</span>
                        <span class="text-sky-600 text-2xl transition" :class="{'rotate-180': open === {{ $i }}}">⌄</span>
                    </button>
                    <div x-show="open === {{ $i }}" x-collapse class="px-5 pb-5 text-slate-600 leading-relaxed" x-cloak>
                        {{ $faq->answer }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Footer -->
<footer class="bg-slate-900 text-slate-400 py-12">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <div class="text-2xl font-bold text-white mb-3">{{ $settings['app_name'] }}</div>
        <div class="flex flex-wrap justify-center gap-6 mb-6 text-sm">
            <a href="{{ $settings['privacy_url'] }}" class="hover:text-white">سياسة الخصوصية</a>
            <a href="{{ $settings['terms_url'] }}" class="hover:text-white">الشروط والأحكام</a>
            <a href="mailto:{{ $settings['support_email'] }}" class="hover:text-white">{{ $settings['support_email'] }}</a>
            @if ($settings['support_phone'])
                <a href="tel:{{ $settings['support_phone'] }}" class="hover:text-white">{{ $settings['support_phone'] }}</a>
            @endif
        </div>
        <p class="text-xs">{{ $settings['footer_copyright'] }}</p>
    </div>
</footer>

</body>
</html>
