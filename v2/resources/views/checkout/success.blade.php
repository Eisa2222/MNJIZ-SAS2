<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تم الدفع — MNJIZ</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
    <style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-xl p-10 max-w-md w-full text-center">
    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">✓</div>
    <h1 class="text-2xl font-bold text-slate-900 mb-3">تم الدفع بنجاح</h1>
    <p class="text-slate-600 mb-2">شكراً لاشتراكك في MNJIZ.</p>
    @if ($email)
        <p class="text-sm text-slate-500 mb-6">سيصلك بريد على <strong>{{ $email }}</strong> خلال دقائق يحتوي رابط إعداد كلمة المرور.</p>
    @endif
    <a href="/" class="inline-block bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-lg font-bold transition">العودة للصفحة الرئيسية</a>
</div>
</body>
</html>
