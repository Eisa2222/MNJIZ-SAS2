<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>إعداد كلمة المرور</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
<style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-gradient-to-br from-sky-700 to-sky-900 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">حدّد كلمة المرور</h1>
    <p class="text-slate-600 text-sm mb-6">رابط آمن صالح لمدة 48 ساعة. اختر كلمة مرور قوية للحساب <strong>{{ $email }}</strong>.</p>

    @if (session('status'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg p-3 mb-4 text-sm">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
            <ul class="space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/password/setup') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">كلمة المرور الجديدة</label>
            <input type="password" name="password" required minlength="8" autocomplete="new-password"
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                   class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 outline-none">
        </div>

        <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3 rounded-lg font-bold transition">
            حفظ كلمة المرور
        </button>
    </form>
</div>
</body>
</html>
