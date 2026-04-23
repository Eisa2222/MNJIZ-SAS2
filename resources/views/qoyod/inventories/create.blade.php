@extends('layouts.layoutMaster')

@section('title', 'إضافة موقع')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.inventories.index') }}">المواقع</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة موقع</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة موقع" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/qoyod/inventories/inventories-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle"> الموقع والبيانات العامة</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#details-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">تفاصيل العنوان</span>
                                <span class="bs-stepper-subtitle">التفاصيل الإضافية للموقع</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="form" action="{{ route('qoyod.inventories.store') }}" method="POST">
                        @csrf

                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                {{-- الاسم بالعربية --}}
                                <div class="col-md-6">
                                    <label for="ar_name" class="form-label">الاسم بالعربية <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="ar_name" name="ar_name" class="form-control"
                                        value="{{ old('ar_name') }}" required />
                                    @error('ar_name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- الاسم بالإنجليزية --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label">الاسم بالإنجليزية <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name') }}" required />
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="account_id" class="form-label">حساب المخزون <span
                                            class="text-danger">*</span></label>
                                    <select id="account_id" name="account_id" class="select2 form-select"
                                        data-placeholder="اختر حساب المخزون">
                                        <option value=""></option>
                                        @foreach ($inventoryAccount as $acc)
                                            <option value="{{ $acc['id'] }}"
                                                {{ old('account_id') == $acc['id'] ? 'selected' : '' }}>
                                                {{ $acc['code'] }} - {{ $acc['name_ar'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="col-12 d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div id="details-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="shipping_address" class="form-label">اسم الشارع</label>
                                    <input type="text" id="shipping_address" name="shipping_address" class="form-control"
                                        value="{{ old('shipping_address') }}" required />
                                    @error('shipping_address')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="shipping_city" class="form-label">المدينة</label>
                                    <input type="text" id="shipping_city" name="shipping_city" class="form-control"
                                        value="{{ old('shipping_city') }}" required />
                                    @error('shipping_city')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="shipping_state" class="form-label">المنطقة</label>
                                    <input type="text" id="shipping_state" name="shipping_state" class="form-control"
                                        value="{{ old('shipping_state') }}" required />
                                    @error('shipping_state')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="shipping_zip" class="form-label">الرمز البريدي</label>
                                    <input type="text" id="shipping_zip" name="shipping_zip" class="form-control"
                                        value="{{ old('shipping_zip') }}" required />
                                    @error('shipping_zip')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                            </div>

                            <div class="col-12 d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <span class="d-sm-inline-block d-none me-sm-2">حفظ</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>




@endsection
