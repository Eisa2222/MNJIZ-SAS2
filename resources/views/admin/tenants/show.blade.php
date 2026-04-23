@extends('admin.layout')
@section('title', $tenant->name)

@section('content')
    <div class="admin-header">
        <h2>{{ $tenant->name }} <small style="color:#94a3b8;font-size:14px;">#{{ $tenant->id }} · {{ $tenant->slug }}</small></h2>
        <div>
            <a class="btn" href="{{ route('admin.tenants.edit', $tenant) }}">Edit</a>
            @if ($tenant->isActive())
                <form method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}" style="display:inline;">
                    @csrf <button class="btn btn-danger">Suspend</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.tenants.activate', $tenant) }}" style="display:inline;">
                    @csrf <button class="btn">Activate</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <p><strong>Status:</strong> {{ strtoupper($tenant->status) }}</p>
        <p><strong>Domain:</strong> {{ $tenant->domain ?? '—' }}</p>
        <p><strong>Created:</strong> {{ $tenant->created_at }}</p>
        <p><strong>Tenant URL:</strong> <code>{{ url('/t/'.$tenant->slug) }}</code></p>
    </div>

    <div class="card" style="margin-top:16px;">
        <h3>Users ({{ $tenant->users()->count() }})</h3>
        <p style="color:#94a3b8;">Impersonation UI will be populated once Spatie Teams land a tenant-scoped user list in Phase 3.5.</p>
    </div>
@endsection
