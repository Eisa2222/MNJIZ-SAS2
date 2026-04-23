@php
    $pageConfigs = ['myLayout' => 'blank'];

    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'تحت التطوير ')

@section('page-style')
    <!-- Page -->
    @vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection

@section('content')
    <!--تحت التطوير -->
    <div class="container-xxl container-p-y">
        <div class="misc-wrapper text-center">
            <h4 class="mb-2 mx-2">تحت التطوير! 🚧</h4>
            <p class="mb-6 mx-2">
                نعتذر عن الإزعاج، نحن نقوم حالياً ببعض أعمال التطوير
            </p>
            <div class="mt-12">
                <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}"
                    alt="page-misc-under-maintenance" width="550" class="img-fluid">
            </div>
        </div>
    </div>
    <div class="container-fluid misc-bg-wrapper misc-under-maintenance-bg-wrapper">
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}" height="355"
            alt="page-misc-under-maintenance" data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png">
    </div>
    <!-- /تحت التطوير -->
@endsection
