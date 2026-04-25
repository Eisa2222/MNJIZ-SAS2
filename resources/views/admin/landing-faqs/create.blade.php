@extends('admin.layout')
@section('title', __('landing.admin.faqs.create'))

@section('content')
    <div class="admin-header">
        <h2>{{ __('landing.admin.faqs.create') }}</h2>
        <a class="btn" href="{{ route("{$prefix}.landing-faqs.index") }}">{{ __('landing.admin.common.back') }}</a>
    </div>

    @include('admin.landing-faqs._form', [
        'action' => route("{$prefix}.landing-faqs.store"),
        'method' => 'POST',
        'faq'    => null,
    ])
@endsection
