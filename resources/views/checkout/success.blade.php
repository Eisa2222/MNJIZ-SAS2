<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('checkout.success.title') }} — {{ $app_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>body{font-family:'Cairo',sans-serif;background:#f7fafc}</style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="text-center bg-white rounded shadow-sm p-5">
                    <div style="font-size:64px;color:#48bb78;line-height:1;">✓</div>
                    <h1 class="h3 mt-3" style="color:#0f4c75;font-weight:700;">{{ __('checkout.success.title') }}</h1>
                    <p class="text-muted">{{ __('checkout.success.thanks', ['app' => $app_name]) }}</p>
                    <a href="{{ url('/onboarding/welcome') }}" class="btn btn-primary mt-3">{{ __('checkout.success.next') }}</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
