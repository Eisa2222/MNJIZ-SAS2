<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.setup.page_title') }} — {{ $app_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <style>
        body{font-family:'Cairo',sans-serif;background:linear-gradient(135deg,#0f4c75,#3282b8);min-height:100vh;display:flex;align-items:center}
        .auth-card{max-width:480px;margin:32px auto;background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.18);padding:32px}
        .auth-card h1{color:#0f4c75;font-weight:700;font-size:22px;margin-bottom:8px}
        .auth-card .lead{color:#4a5568;font-size:14px;margin-bottom:24px}
        .form-control{border-radius:8px;padding:12px}
        .btn-primary{background:#0f4c75;border-color:#0f4c75;border-radius:8px;padding:12px;font-weight:700}
        .btn-primary:hover{background:#0d3d61;border-color:#0d3d61}
    </style>
</head>
<body>

<div class="container">
    <div class="auth-card">
        <h1>{{ __('auth.setup.heading') }}</h1>
        <p class="lead">{{ __('auth.setup.lead', ['email' => $email]) }}</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/password/setup') }}" novalidate>
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label">{{ __('auth.setup.fields.password') }}</label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password" minlength="8">
                <div class="form-text">{{ __('auth.setup.password_hint') }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('auth.setup.fields.password_confirmation') }}</label>
                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password" minlength="8">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">{{ __('auth.setup.submit') }}</button>
            </div>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            {{ __('auth.setup.security_notice') }}
        </p>
    </div>
</div>

</body>
</html>
