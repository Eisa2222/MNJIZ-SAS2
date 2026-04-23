@extends('admin.layout')
@section('title', 'New Tenant')

@section('content')
    <div class="admin-header"><h2>Create Tenant</h2></div>

    @if ($errors->any())
        <div class="flash" style="background:#b91c1c;color:#fee2e2;">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card" style="max-width:600px;">
        <form method="POST" action="{{ route('admin.tenants.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>

            <label>Slug (used in URL /t/{slug})</label>
            <input type="text" name="slug" value="{{ old('slug') }}" required>

            <label>Custom Domain (optional)</label>
            <input type="text" name="domain" value="{{ old('domain') }}">

            <label>Status</label>
            <select name="status" required>
                <option value="active" selected>Active</option>
                <option value="suspended">Suspended</option>
            </select>

            <button type="submit" class="btn" style="margin-top:16px;">Create Tenant</button>
            <a href="{{ route('admin.tenants.index') }}" style="margin-inline-start:12px;color:#94a3b8;">Cancel</a>
        </form>
    </div>
@endsection
