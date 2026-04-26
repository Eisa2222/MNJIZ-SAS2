<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول لوحة التحكم — MNJIZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 to-sky-900 flex items-center justify-center p-4">

<div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8">
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-white font-bold text-2xl">M</div>
        <h1 class="text-2xl font-bold text-slate-900">لوحة Super Admin</h1>
        <p class="text-slate-500 text-sm">منصة MNJIZ SaaS</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('super-admin.login.attempt') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-slate-700 text-sm font-semibold mb-1">البريد الإلكتروني</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
        </div>

        <div>
            <label class="block text-slate-700 text-sm font-semibold mb-1">كلمة المرور</label>
            <input type="password" name="password" required
                   class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none">
        </div>

        <label class="flex items-center text-sm text-slate-600">
            <input type="checkbox" name="remember" class="ml-2 rounded border-slate-300">
            تذكَّرني
        </label>

        <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white py-3 rounded-lg font-bold transition">
            تسجيل الدخول
        </button>
    </form>
</div>

</body>
</html>
