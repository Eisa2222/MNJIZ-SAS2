@extends('layouts.layoutMaster')

@section('title', 'إضافة حساب')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.accounts.index') }}">الحسابات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> إضافة حساب </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة حساب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/qoyod/accounts/account-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل الحساب</span>
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
                    <form id="form" action="{{ route('qoyod.accounts.store') }}" method="POST">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="name_ar" class="form-label">الاسم بالعربية <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="name_ar" name="name_ar" class="form-control"
                                        value="{{ old('name_ar') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="name_en" class="form-label">الاسم بالإنجليزية <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="name_en" name="name_en" class="form-control"
                                        value="{{ old('name_en') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="code" class="form-label">الرمز <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="code" name="code" class="form-control"
                                        value="{{ old('code') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="type" class="form-label">النوع <span
                                            class="text-danger">*</span></label>
                                    <select id="type" name="type" class="form-select select2" data-placeholder="اخترالنوع" required>
                                        <option value=""></option>
                                        @foreach ($accountTypeRaw as $item)
                                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="recieve_payments" class="form-label">استقبال المدفوعات <span
                                            class="text-danger">*</span></label>
                                    <select id="recieve_payments" name="recieve_payments" class="form-select select2" data-placeholder="هل الحساب يستقبل المدفوعات" required>
                                        <option value=""></option>
                                        
                                        <option value="true" {{ old('recieve_payments') === 'true' ? 'selected' : '' }}>
                                            نعم</option>
                                        <option value="false" {{ old('recieve_payments') === 'false' ? 'selected' : '' }}>
                                            لا</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea id="description" name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
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
