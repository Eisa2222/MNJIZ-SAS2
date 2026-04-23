@extends('admin.layout')
@section('title', 'Tenants')

@section('content')
    <div class="admin-header">
        <h2>Tenants</h2>
        <a class="btn" href="{{ route('admin.tenants.create') }}">+ New Tenant</a>
    </div>

    <form method="GET" style="margin-bottom:16px;display:flex;gap:8px;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or slug...">
        <select name="status" style="width:auto;">
            <option value="">All statuses</option>
            <option value="active"    {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
            <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>

    <div class="card" style="padding:0;overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Slug</th><th>Domain</th><th>Status</th><th>Created</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($tenants as $tenant)
                <tr>
                    <td>{{ $tenant->id }}</td>
                    <td>{{ $tenant->name }}</td>
                    <td><code>{{ $tenant->slug }}</code></td>
                    <td>{{ $tenant->domain ?? '—' }}</td>
                    <td>
                        <span style="padding:3px 8px;border-radius:4px;font-size:12px;background:{{ $tenant->status === 'active' ? '#065f46' : '#991b1b' }};">
                            {{ strtoupper($tenant->status) }}
                        </span>
                    </td>
                    <td>{{ $tenant->created_at?->format('Y-m-d') }}</td>
                    <td style="text-align:end;">
                        <a class="btn" href="{{ route('admin.tenants.show', $tenant) }}">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#64748b;padding:24px;">No tenants yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $tenants->links() }}</div>
@endsection
