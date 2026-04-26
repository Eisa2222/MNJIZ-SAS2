<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — MNJIZ SaaS</title>

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #0f172a; color: #e2e8f0; }
        .admin-shell { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #020617; padding: 24px 16px; border-inline-end: 1px solid #1e293b; }
        .admin-sidebar h1 { font-size: 14px; color: #94a3b8; text-transform: uppercase; margin: 0 0 24px; letter-spacing: .5px; }
        .admin-sidebar a { display: block; padding: 8px 12px; color: #cbd5e1; text-decoration: none; border-radius: 6px; margin-bottom: 4px; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #1e293b; color: #fff; }
        .admin-main { padding: 32px 40px; }
        .admin-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .admin-header h2 { margin: 0; font-size: 24px; }
        .flash { background: #065f46; color: #d1fae5; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 20px; }
        .stat { display: inline-block; background: #334155; padding: 16px; border-radius: 6px; min-width: 160px; margin: 0 12px 12px 0; }
        .stat .v { font-size: 28px; font-weight: 600; }
        .stat .k { color: #94a3b8; font-size: 12px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: inline-start; border-bottom: 1px solid #334155; }
        button, .btn { background: #3b82f6; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #dc2626; }
        input, select { background: #0f172a; border: 1px solid #334155; color: #e2e8f0; padding: 8px; border-radius: 6px; width: 100%; }
        label { display: block; margin: 8px 0 4px; color: #94a3b8; font-size: 13px; }
        .impersonation-banner { background: #b91c1c; color: #fee2e2; text-align: center; padding: 8px; font-size: 14px; }
    </style>
</head>
<body>

{{-- Hotfix: detect which guard is logged in (admin OR super_admin from
     Phase B parallel guards), then bind a single $adminUser variable so
     the rest of this layout works with both. Also pick the right route
     prefix (admin.* vs super-admin.*) based on URL so links go to the
     same panel the user came in through. --}}
@php
    $adminUser = auth('admin')->user() ?? auth('super_admin')->user();
    $routePrefix = request()->is('super-admin*') ? 'super-admin' : 'admin';
@endphp

@if ($adminUser)
<div class="admin-shell">
    <aside class="admin-sidebar">
        <h1>MNJIZ SaaS</h1>
        <a href="{{ route($routePrefix.'.dashboard') }}" class="{{ request()->routeIs($routePrefix.'.dashboard') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ route($routePrefix.'.tenants.index') }}" class="{{ request()->routeIs($routePrefix.'.tenants.*') ? 'active' : '' }}">Tenants</a>
        <a href="{{ route($routePrefix.'.settings.index') }}" class="{{ request()->routeIs($routePrefix.'.settings.*') ? 'active' : '' }}">Central Settings</a>
        <a href="{{ route($routePrefix.'.subscriptions.index') }}" class="{{ request()->routeIs($routePrefix.'.subscriptions.*') ? 'active' : '' }}">Subscriptions</a>
        <a href="{{ route($routePrefix.'.coupons.index') }}" class="{{ request()->routeIs($routePrefix.'.coupons.*') ? 'active' : '' }}">Coupons</a>
        @if ($adminUser->role === 'super_admin')
            <a href="{{ route($routePrefix.'.landing-features.index') }}" class="{{ request()->routeIs($routePrefix.'.landing-features.*') ? 'active' : '' }}">Landing Features</a>
            <a href="{{ route($routePrefix.'.landing-faqs.index') }}" class="{{ request()->routeIs($routePrefix.'.landing-faqs.*') ? 'active' : '' }}">Landing FAQs</a>
        @endif
        <form method="POST" action="{{ route($routePrefix.'.logout') }}" style="margin-top:24px;">
            @csrf
            <button type="submit" class="btn btn-danger" style="width:100%;">Log out</button>
        </form>
        <p style="color:#64748b;font-size:12px;margin-top:24px;">
            Signed in as<br>
            <strong>{{ $adminUser->name }}</strong><br>
            <em>{{ $adminUser->role }}</em>
        </p>
    </aside>

    <main class="admin-main">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endif

</body>
</html>
