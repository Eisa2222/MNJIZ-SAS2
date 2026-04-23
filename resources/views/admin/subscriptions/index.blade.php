@extends('admin.layout')
@section('title', 'Subscriptions')

@section('content')
    <div class="admin-header">
        <h2>Subscriptions</h2>
    </div>

    <form method="GET" style="margin-bottom:16px;display:flex;gap:8px;">
        <select name="status" style="width:auto;">
            <option value="">All statuses</option>
            @foreach(['trialing','active','past_due','paused','canceled','expired'] as $s)
                <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ strtoupper($s) }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>

    <div class="card" style="padding:0;overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Tenant</th><th>Plan</th><th>Status</th><th>Cycle</th>
                    <th>Period ends</th><th>Gateway</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($subscriptions as $sub)
                <tr>
                    <td>#{{ $sub->id }}</td>
                    <td>{{ $sub->tenantRelation?->name ?? '—' }} <small style="color:#94a3b8;">({{ $sub->tenantRelation?->slug }})</small></td>
                    <td>{{ $sub->plan?->name ?? '—' }}</td>
                    <td>
                        <span style="padding:3px 8px;border-radius:4px;font-size:12px;background:{{ in_array($sub->status->value, ['active','trialing'])?'#065f46':($sub->status->value==='past_due'?'#b45309':'#991b1b') }};">
                            {{ strtoupper($sub->status->value) }}
                        </span>
                    </td>
                    <td>{{ $sub->billing_cycle->label() }}</td>
                    <td>{{ $sub->current_period_ends_at?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $sub->gateway->label() }}</td>
                    <td style="text-align:end;">
                        <a class="btn" href="{{ route('admin.subscriptions.show', $sub->id) }}">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#64748b;padding:24px;">No subscriptions.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $subscriptions->links() }}</div>
@endsection
