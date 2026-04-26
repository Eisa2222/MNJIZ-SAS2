<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعذّر إتمام الدفع — MNJIZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-xl p-10 max-w-md w-full text-center">
    <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">✕</div>
    <h1 class="text-2xl font-bold text-slate-900 mb-3">تعذّر إتمام الدفع</h1>
    <p class="text-slate-600 mb-6">
        @if ($reason === 'invalid')
            بيانات الدفعة غير صحيحة.
        @else
            لم يكتمل الدفع. لم يتم خصم أي مبلغ.
        @endif
    </p>
    <div class="flex gap-3 justify-center">
        <a href="/" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-2 rounded-lg font-bold transition">المحاولة مرة أخرى</a>
        <a href="mailto:{{ \App\Models\SystemSetting::get('support_email','support@mnjiz.sa') }}"
           class="border-2 border-slate-300 text-slate-700 px-6 py-2 rounded-lg font-bold transition hover:bg-slate-50">تواصل مع الدعم</a>
    </div>
</div>
</body>
</html>
