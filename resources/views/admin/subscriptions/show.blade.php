@extends('admin.layout')
@section('title', 'Subscription #'.$subscription->id)

@section('content')
    <div class="admin-header">
        <h2>Subscription #{{ $subscription->id }}</h2>
        <div>
            @if (in_array($subscription->status->value, ['trialing','active','past_due']))
                <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" style="display:inline;"
                      onsubmit="return confirm('Cancel this subscription?');">
                    @csrf
                    <button class="btn btn-danger" type="submit">Cancel at period end</button>
                </form>
                <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" style="display:inline;"
                      onsubmit="return confirm('Cancel IMMEDIATELY? Tenant loses access now.');">
                    @csrf
                    <input type="hidden" name="immediate" value="1">
                    <button class="btn btn-danger" type="submit">Cancel immediately</button>
                </form>
            @endif
            @if (in_array($subscription->status->value, ['canceled','paused']))
                <form method="POST" action="{{ route('admin.subscriptions.resume', $subscription->id) }}" style="display:inline;">
                    @csrf
                    <button class="btn" type="submit">Resume</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <p><strong>Tenant:</strong> {{ $subscription->tenantRelation?->name ?? '—' }}
           <small>({{ $subscription->tenantRelation?->slug }})</small></p>
        <p><strong>Plan:</strong> {{ $subscription->plan?->name ?? '—' }} ({{ $subscription->billing_cycle->label() }})</p>
        <p><strong>Status:</strong> {{ $subscription->status->label() }}</p>
        <p><strong>Currency:</strong> {{ $subscription->currency }}</p>
        <p><strong>Gateway:</strong> {{ $subscription->gateway->label() }}</p>
        <p><strong>Coupon:</strong> {{ $subscription->coupon?->code ?? '—' }}</p>
        <hr style="border-color:#334155;">
        <p><strong>Trial ends:</strong> {{ $subscription->trial_ends_at?->format('Y-m-d H:i') ?? '—' }}</p>
        <p><strong>Current period:</strong>
           {{ $subscription->current_period_started_at?->format('Y-m-d') ?? '—' }}
           → {{ $subscription->current_period_ends_at?->format('Y-m-d') ?? '—' }}</p>
        <p><strong>Canceled at:</strong> {{ $subscription->canceled_at?->format('Y-m-d H:i') ?? '—' }}</p>
        <p><strong>Ends at:</strong> {{ $subscription->ends_at?->format('Y-m-d H:i') ?? '—' }}</p>
        <p><strong>Grace ends at:</strong> {{ $subscription->grace_ends_at?->format('Y-m-d H:i') ?? '—' }}</p>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Invoices ({{ $subscription->invoices->count() }})</h3>
        @if ($subscription->invoices->isEmpty())
            <p style="color:#94a3b8;">No invoices yet.</p>
        @else
        <table>
            <thead><tr><th>Number</th><th>Status</th><th>Total</th><th>Paid</th><th>Issued</th></tr></thead>
            <tbody>
            @foreach ($subscription->invoices as $inv)
                <tr>
                    <td>{{ $inv->number }}</td>
                    <td>{{ $inv->status->label() }}</td>
                    <td>{{ number_format($inv->total, 2) }} {{ $inv->currency }}</td>
                    <td>{{ number_format($inv->amount_paid, 2) }} {{ $inv->currency }}</td>
                    <td>{{ $inv->issued_at?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
@endsection
