@extends('admin.layout')
@section('title', __('landing.admin.features.edit'))

@section('content')
    <div class="admin-header">
        <h2>{{ __('landing.admin.features.edit') }} #{{ $feature->id }}</h2>
        <a class="btn" href="{{ route("{$prefix}.landing-features.index") }}">{{ __('landing.admin.common.back') }}</a>
    </div>

    @include('admin.landing-features._form', [
        'action' => route("{$prefix}.landing-features.update", $feature),
        'method' => 'PUT',
        'feature' => $feature,
    ])
@endsection
