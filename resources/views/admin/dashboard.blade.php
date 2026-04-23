@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
    <div class="admin-header">
        <h2>Platform Overview</h2>
    </div>

    <div>
        <div class="stat">
            <div class="k">Tenants (Total)</div>
            <div class="v">{{ number_format($stats['tenants_total']) }}</div>
        </div>
        <div class="stat">
            <div class="k">Active Tenants</div>
            <div class="v">{{ number_format($stats['tenants_active']) }}</div>
        </div>
        <div class="stat">
            <div class="k">Suspended</div>
            <div class="v">{{ number_format($stats['tenants_suspended']) }}</div>
        </div>
        <div class="stat">
            <div class="k">Users (All Tenants)</div>
            <div class="v">{{ number_format($stats['users_total']) }}</div>
        </div>
        <div class="stat">
            <div class="k">Admins</div>
            <div class="v">{{ number_format($stats['admins_total']) }}</div>
        </div>
    </div>
@endsection
