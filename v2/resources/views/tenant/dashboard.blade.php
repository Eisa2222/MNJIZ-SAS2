<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>{{ $tenant->company_name }} — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
    @vite('resources/css/app.css')
<style>body{font-family:'Cairo',sans-serif}</style>
</head>
<body class="bg-slate-50 min-h-screen p-8">
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-900 mb-2">مرحباً، {{ $tenant->company_name }}</h1>
    <p class="text-slate-600 mb-6">معرّف المستأجر: <code class="bg-slate-200 px-2 py-1 rounded">{{ $tenant->id }}</code></p>

    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-lg font-bold mb-4">معلومات الحساب</h2>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <dt class="text-slate-500">صاحب الحساب</dt><dd class="font-semibold">{{ $tenant->owner_name }}</dd>
            <dt class="text-slate-500">البريد</dt><dd class="font-semibold">{{ $tenant->owner_email }}</dd>
            <dt class="text-slate-500">المنطقة الزمنية</dt><dd class="font-semibold">{{ $tenant->timezone }}</dd>
            <dt class="text-slate-500">الحالة</dt><dd class="font-semibold text-emerald-600">{{ $tenant->status }}</dd>
        </dl>
    </div>
</div>
</body>
</html>
