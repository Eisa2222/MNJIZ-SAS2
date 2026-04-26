<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — MNJIZ Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-slate-50 min-h-screen">

@auth('super_admin')
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col">
        <div class="p-6 border-b border-slate-800">
            <div class="text-xl font-bold text-white">MNJIZ</div>
            <div class="text-xs text-slate-400">Super Admin Panel</div>
        </div>

        <nav class="flex-1 p-4 space-y-1 text-sm">
            @php
                $links = [
                    ['super-admin.dashboard',         'لوحة التحكم',  '🏠'],
                    ['super-admin.tenants.index',     'المستأجرون',   '🏢'],
                    ['super-admin.plans.index',       'الباقات',      '💼'],
                    ['super-admin.subscriptions.index','الاشتراكات',  '📋'],
                    ['super-admin.payments.index',    'المدفوعات',    '💳'],
                    ['super-admin.coupons.index',     'الكوبونات',    '🏷️'],
                    ['super-admin.landing.features.index', 'مميزات الصفحة', '✨'],
                    ['super-admin.landing.faqs.index',     'الأسئلة الشائعة', '❓'],
                    ['super-admin.settings.index',    'الإعدادات',    '⚙️'],
                ];
            @endphp
            @foreach ($links as [$route, $label, $icon])
                <a href="{{ \Route::has($route) ? route($route) : '#' }}"
                   @class([
                       'flex items-center gap-3 px-3 py-2 rounded-lg transition',
                       'bg-sky-600 text-white' => request()->routeIs(str_replace('.index','.*',$route)) || request()->routeIs($route),
                       'hover:bg-slate-800 hover:text-white' => ! (request()->routeIs(str_replace('.index','.*',$route)) || request()->routeIs($route)),
                   ])>
                    <span>{{ $icon }}</span>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="p-4 border-t border-slate-800">
            <div class="text-sm font-semibold text-white">{{ auth('super_admin')->user()->name }}</div>
            <div class="text-xs text-slate-400 mb-3">{{ auth('super_admin')->user()->email }}</div>
            <form method="POST" action="{{ route('super-admin.logout') }}">
                @csrf
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-semibold transition">
                    تسجيل الخروج
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 overflow-x-hidden">
        @if (session('status'))
            <div class="bg-emerald-50 border-r-4 border-emerald-500 text-emerald-800 p-4 m-6 rounded-lg">
                {{ session('status') }}
            </div>
        @endif
        <div class="p-6">
            @yield('content')
        </div>
    </main>
</div>
@else
    @yield('content')
@endauth

</body>
</html>
