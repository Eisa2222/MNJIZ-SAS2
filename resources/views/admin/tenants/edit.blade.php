@extends('admin.layout')
@section('title', 'Edit '.$tenant->name)

@section('content')
    <div class="admin-header"><h2>Edit {{ $tenant->name }}</h2></div>

    @if ($errors->any())
        <div class="flash" style="background:#b91c1c;color:#fee2e2;">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card" style="max-width:600px;">
        <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
            @csrf @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required>

            <label>Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}" required>

            <label>Custom Domain</label>
            <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}">

            <label>Status</label>
            <select name="status" required>
                <option value="active"    {{ $tenant->status==='active'    ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ $tenant->status==='suspended' ? 'selected' : '' }}>Suspended</option>
            </select>

            <button type="submit" class="btn" style="margin-top:16px;">Save</button>
            <a href="{{ route('admin.tenants.show', $tenant) }}" style="margin-inline-start:12px;color:#94a3b8;">Cancel</a>
        </form>
    </div>
@endsection
