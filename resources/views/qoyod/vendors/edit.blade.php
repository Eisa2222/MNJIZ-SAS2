@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات المورد')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.vendors.index') }}">الموردين</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#"> تعديل بيانات المورد </a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات المورد" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/qoyod/vendors/vendor-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل المورد</span>
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
                    <form id="form" action="{{ route('qoyod.vendors.update',$vendor['id']) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="basic-info" class="content dstepper-block">

                            <div class="row  g-3">

                                <div class="col-md-6">
                                    <label for="name" class="form-label">اسم المورد</label>
                                    <span class="text-danger">*</span>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name',$vendor['name']) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="organization" class="form-label">اسم المنشأة </label>
                                    <input type="text" id="organization" name="organization" class="form-control"
                                        value="{{ old('organization',$vendor['organization']) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="phone_number" class="form-label">رقم الاتصال</label>
                                    <input type="text" id="phone_number" name="phone_number" class="form-control"
                                        value="{{ old('phone_number',$vendor['phone_number']) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">البريد الإلكتروني</label>
                                    <input type="text" id="email" name="email" class="form-control"
                                        value="{{ old('email',$vendor['email']) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="tax_number" class="form-label">الرقم الضريبي</label>
                                    <input type="text" id="tax_number" name="tax_number" class="form-control"
                                        value="{{ old('tax_number',$vendor['tax_number']) }}" required />
                                </div>

                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
