@extends('admin.layout')
@section('title', 'Admin Login')

@section('content')
<div style="display:grid;place-items:center;min-height:100vh;">
    <div class="card" style="width:360px;">
        <h2 style="margin:0 0 24px;">MNJIZ SaaS — Super Admin</h2>

        @if ($errors->any())
            <div class="flash" style="background:#b91c1c;color:#fee2e2;">{{ $errors->first() }}</div>
        @endif

        {{-- Phase B: same view serves both /admin/login and /super-admin/login.
             The SuperAdmin LoginController passes $loginAction; the legacy
             Admin LoginController doesn't, so we fall back to the existing
             route name. --}}
        <form method="POST" action="{{ $loginAction ?? route('admin.login.attempt') }}">
            @csrf
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label>Password</label>
            <input type="password" name="password" required>

            <label style="margin-top:12px;">
                <input type="checkbox" name="remember" value="1" style="width:auto;"> Remember me
            </label>

            <button type="submit" class="btn" style="margin-top:16px;width:100%;">Sign in</button>
        </form>
    </div>
</div>
@endsection
