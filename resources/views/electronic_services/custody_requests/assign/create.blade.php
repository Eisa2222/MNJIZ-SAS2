@extends('layouts.layoutMaster')

@section('title', 'طلب عهدة')

@section('breadcrumb')
    <li><a href="#">الخدمات الإلكترونية</a></li>
    <li><a href="{{ route('account.electronic-services.custody-requests.index') }}">طلبات العهد</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> طلب عهدة</a>
        <i class="ti ti-star favorite-icon" data-page-name=" طلب  عهدة" data-page-url="{{ url()->current() }}"
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
    <script>
        window.appUrls = {
            requestUrl: "{{ route('account.electronic-services.custody-requests.filter') }}"
        };
    </script>
    @vite(['resources/assets/js/electronic-services/custody_requests_wizard.js', 'resources/assets/js/electronic-services/custody/create-assign.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#request-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">بيانات الطلب</span>
                                <span class="bs-stepper-subtitle">تفاصيل الطلب</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">

                    <form id="form" action="{{ route('account.electronic-services.custody-requests.assign.store') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <div id="request-info" class="content">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">
                                        التصنيف
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="category_id" name="category_id" class="form-select select2"
                                        data-placeholder="اختر التصنيف">
                                        <option value=""></option>
                                        @foreach ($assetCategory as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="col-md-6">
                                    <label for="custody_item_id" class="form-label">
                                        العهدة
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="custody_item_id" name="custody_item_id" class="form-select select2"
                                        data-placeholder="اختر العهدة">
                                        <option value=""></option>
                                    </select>
                                </div>

                                <!-- ملاحظة -->
                                <div class="col-md-12">
                                    <label for="notes" class="form-label">ملاحظة</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="اضف ملاحظة">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex flex-row-reverse mt-4">
                                <button class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
