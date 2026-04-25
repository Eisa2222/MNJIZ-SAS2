<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('checkout.failure.title') }} — {{ $app_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif;background:#f7fafc}</style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="text-center bg-white rounded shadow-sm p-5">
                    <div style="font-size:64px;color:#dc2626;line-height:1;">✕</div>
                    <h1 class="h3 mt-3" style="color:#dc2626;font-weight:700;">{{ __('checkout.failure.title') }}</h1>
                    <p class="text-muted">{{ __('checkout.failure.reason') }}</p>
                    <p class="text-muted small mt-2">{{ $reason_text }}</p>

                    <div class="mt-4 d-flex justify-content-center gap-2">
                        <a href="{{ url('/pricing') }}" class="btn btn-primary">{{ __('checkout.failure.try') }}</a>
                        <a href="mailto:{{ \App\Models\SystemSetting::get('support_email', 'support@mnjiz.sa') }}" class="btn btn-outline-secondary">{{ __('checkout.failure.support') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
