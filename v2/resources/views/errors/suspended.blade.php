<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>الحساب موقوف</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
<style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-xl p-10 max-w-md w-full text-center">
    <div class="text-5xl mb-3">⏸</div>
    <h1 class="text-2xl font-bold text-slate-900 mb-2">الحساب موقوف مؤقتاً</h1>
    <p class="text-slate-600 mb-6">حساب <strong>{{ $tenant->company_name }}</strong> موقوف. الرجاء التواصل مع الدعم لإعادة التفعيل.</p>
    <a href="mailto:{{ \App\Models\SystemSetting::get('support_email','support@mnjiz.sa') }}"
       class="inline-block bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-lg font-bold transition">
        تواصل مع الدعم
    </a>
</div>
</body>
</html>
