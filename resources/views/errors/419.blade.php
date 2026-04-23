@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
    // إعدادات الصفحة الممررة من المتحكم
    $pageConfigs = ['myLayout' => 'blank'];
@endphp
@extends('layouts.layoutMaster')

@section('title', 'انتهت صلاحية الجلسة')

@section('page-style')
    <!-- Page -->
    @vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection

@section('content')
    <!-- Error -->
    <div class="container-xxl container-p-y" dir="rtl">
        <div class="misc-wrapper text-right">
            <h1 class="mb-2 mx-2 fs-xxlarge" style="line-height: 6rem;font-size: 6rem;">419</h1>
            <h4 class="mb-2 mx-2">انتهت صلاحية الجلسة ⚠️</h4>
            <p class="mb-6 mx-2">
                لقد انتهت صلاحية الجلسة الخاصة بك بسبب عدم النشاط لفترة طويلة.<br>
                يرجى تسجيل الدخول مرة أخرى لإعادة بدء الجلسة.
            </p>
            <a href="{{ url('/employees/login') }}" class="btn btn-primary mb-10">الذهاب إلى صفحة تسجيل الدخول</a>
            <div class="mt-4">
                <img src="{{ asset('assets/img/illustrations/boy-with-laptop-light.png') }}" alt="page-misc-error"
                    width="250" class="img-fluid">
            </div>
        </div>
    </div>
    <div class="container-fluid misc-bg-wrapper">
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}" height="355"
            alt="page-misc-error" data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png">
    </div>
    <!-- /Error -->
@endsection
