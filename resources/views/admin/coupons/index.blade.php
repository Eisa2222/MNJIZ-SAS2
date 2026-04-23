@extends('admin.layout')
@section('title', 'Coupons')

@section('content')
    <div class="admin-header">
        <h2>Coupons</h2>
        <a class="btn" href="{{ route('admin.coupons.create') }}">+ New Coupon</a>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>Code</th><th>Name</th><th>Type</th><th>Value</th><th>Redeemed</th><th>Expires</th><th>Active</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($coupons as $c)
                <tr>
                    <td><code>{{ $c->code }}</code></td>
                    <td>{{ $c->name }}</td>
                    <td>{{ $c->type->label() }}</td>
                    <td>{{ $c->type->value === 'percentage' ? $c->value.'%' : number_format($c->value, 2).' '.$c->currency }}</td>
                    <td>{{ $c->redemptions_count }}{{ $c->max_redemptions ? '/'.$c->max_redemptions : '' }}</td>
                    <td>{{ $c->redeem_by?->format('Y-m-d') ?? '—' }}</td>
                    <td>
                        <span style="padding:3px 8px;border-radius:4px;font-size:12px;background:{{ $c->is_active ? '#065f46' : '#991b1b' }};">
                            {{ $c->is_active ? 'YES' : 'NO' }}
                        </span>
                    </td>
                    <td style="text-align:end;">
                        <a class="btn" href="{{ route('admin.coupons.edit', $c) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" style="display:inline;"
                              onsubmit="return confirm('Archive coupon {{ $c->code }}?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" type="submit">Archive</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:#64748b;padding:24px;">No coupons yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $coupons->links() }}</div>
@endsection
