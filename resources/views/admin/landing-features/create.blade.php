@extends('admin.layout')
@section('title', __('landing.admin.features.create'))

@section('content')
    <div class="admin-header">
        <h2>{{ __('landing.admin.features.create') }}</h2>
        <a class="btn" href="{{ route("{$prefix}.landing-features.index") }}">{{ __('landing.admin.common.back') }}</a>
    </div>

    @include('admin.landing-features._form', [
        'action' => route("{$prefix}.landing-features.store"),
        'method' => 'POST',
        'feature' => null,
    ])
@endsection
