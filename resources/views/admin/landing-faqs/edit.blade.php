@extends('admin.layout')
@section('title', __('landing.admin.faqs.edit'))

@section('content')
    <div class="admin-header">
        <h2>{{ __('landing.admin.faqs.edit') }} #{{ $faq->id }}</h2>
        <a class="btn" href="{{ route("{$prefix}.landing-faqs.index") }}">{{ __('landing.admin.common.back') }}</a>
    </div>

    @include('admin.landing-faqs._form', [
        'action' => route("{$prefix}.landing-faqs.update", $faq),
        'method' => 'PUT',
        'faq'    => $faq,
    ])
@endsection
