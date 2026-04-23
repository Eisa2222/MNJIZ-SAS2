@extends('layouts.layoutMaster')

@section('title', 'إضافة وحدة قياس')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.product-unit-types.index') }}"> وحدات قياس المنتجات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> إضافة وحدة قياس </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة وحدة قياس" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/qoyod/products/units/unit-request-wizard.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل وحدة قياس المنتجات</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="form" action="{{ route('qoyod.product-unit-types.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">

                            <div class="row  g-3">

                                <div class="col-md-6">
                                    <label for="unit_name" class="form-label">الوحدة</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="unit_name" name="unit_name" class="form-control"
                                        value="{{ old('unit_name') }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="unit_representation" class="form-label">طريقة العرض</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="unit_representation" name="unit_representation"
                                        class="form-control" value="{{ old('unit_representation') }}" required />
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
