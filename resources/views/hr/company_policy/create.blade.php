@extends('layouts.layoutMaster')

@section('title', 'اللوائح و السياسات ')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> اللوائح و السياسات </a>
        <i class="ti ti-star favorite-icon" data-page-name="اللوائح و السياسات " data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/css/intl-tel.css'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/hr/company_policy/create_wizard.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#personal-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">لوائح وسياسات الشركة</span>
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
                    <form id="form" action="{{ route('hr.company-policy.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="personal-info" class="content">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label" for="name">
                                        الاسم
                                        <span class="text-danger">*</span></label>
                                    <input type="text" name="name[]" id="name_0" class="form-control">
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label" for="file">
                                        المرفق
                                        <span class="text-danger">*</span></label>
                                    <input type="file" name="file[]" id="file_0" class="form-control" accept=".pdf">
                                    <small class="text-muted">مسموح PDF فقط الحجم الأقصى 10 ميغابايت</small>
                                </div>

                                {{-- <div class="col-md-2">
                                    <label for="is_mandatory" class="form-label">هل هي إلزامية</label>
                                    <div class="form-check form-switch">
                                        <!-- إضافة hidden input مع نفس الـ name -->
                                        <input type="hidden" name="is_mandatory[0]" value="0">
                                        <input class="form-check-input" type="checkbox" id="is_mandatory_0"
                                            name="is_mandatory[0]" value="1">
                                        <label class="form-check-label" for="is_mandatory_0">
                                            هل هي إلزامية على الموظف
                                        </label>
                                    </div>
                                </div> --}}

                            </div>

                            <div class="d-flex justify-content-center mt-12">
                                <button type="button" id="addNew" class="btn btn-sm btn-primary">
                                    إضافة المزيد
                                </button>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
