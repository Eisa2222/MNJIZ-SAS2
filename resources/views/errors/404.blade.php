@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
    // التحقق من إعدادات الصفحة الممررة من المتحكم
    $pageConfigs = ['myLayout' => 'blank'];
@endphp
@extends('layouts.layoutMaster')

@section('title', 'خطأ - الصفحة غير موجودة')

@section('page-style')
    <!-- Page -->
    @vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection

@section('content')
    <!-- Error -->
    <div class="container-xxl container-p-y" dir="rtl">
        <div class="misc-wrapper text-right">
            <h1 class="mb-2 mx-2 fs-xxlarge" style="line-height: 6rem;font-size: 6rem;">404</h1>
            <h4 class="mb-2 mx-2">الصفحة غير موجودة ⚠️</h4>
            <p class="mb-6 mx-2">لم نتمكن من العثور على الصفحة التي تبحث عنها</p>
            <a href="{{ url('dashboard') }}" class="btn btn-primary mb-10">العودة إلى الصفحة الرئيسية</a>
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
