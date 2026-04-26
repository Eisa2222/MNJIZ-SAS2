<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('subscription.suspended.page_title') }} — {{ $app_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <style>
        body{font-family:'Cairo',sans-serif;background:#f7fafc;min-height:100vh;display:flex;align-items:center}
        .suspended-card{max-width:560px;margin:48px auto;background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.08);padding:40px;text-align:center}
        .icon-wrap{font-size:64px;color:#dc2626;line-height:1;margin-bottom:16px}
        h1{color:#0f4c75;font-weight:700;font-size:24px;margin-bottom:12px}
        .lead{color:#4a5568;font-size:15px;margin-bottom:24px}
        .firm-pill{display:inline-block;background:#fee2e2;color:#991b1b;padding:6px 14px;border-radius:999px;font-size:13px;font-weight:600;margin-bottom:20px}
        .actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:24px}
        .btn-outline-primary{color:#0f4c75;border-color:#0f4c75}
        .btn-primary{background:#0f4c75;border-color:#0f4c75}
    </style>
</head>
<body>

<div class="container">
    <div class="suspended-card">
        <div class="icon-wrap">⏸</div>

        <h1>{{ __('subscription.suspended.heading') }}</h1>

        <div class="firm-pill">{{ $tenant->name }}</div>

        <p class="lead">
            {{ __('subscription.suspended.body') }}
        </p>

        <p class="text-muted small mb-0">
            {{ __('subscription.suspended.support_hint') }}
            <a href="mailto:{{ $support_email }}" class="text-decoration-none" style="color:#0f4c75;font-weight:600;">
                {{ $support_email }}
            </a>
        </p>

        <div class="actions">
            <a href="/" class="btn btn-outline-primary">{{ __('subscription.actions.home') }}</a>
            <a href="{{ url('/pricing') }}" class="btn btn-primary">{{ __('subscription.actions.upgrade_now') }}</a>
        </div>
    </div>
</div>

</body>
</html>
