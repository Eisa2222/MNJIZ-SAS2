@extends('layouts.layoutMaster')

@section('title', 'إضافة اصل')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('hr.custody.items.index') }}">إدارة الاصول</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة اصل</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة اصل" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    @if (old('serial_numbers'))
        <script>
            window.oldSerialNumbers = @json(old('serial_numbers'));
        </script>
    @endif
@endsection

@section('page-script')
    @vite(['resources/assets/js/hr/custody/items-request-wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل الاصل </span>
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
                    <form id="form" action="{{ route('hr.custody.items.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="asset_category_id" class="form-label">التصنيف
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="asset_category_id" name="asset_category_id" class="form-select select2"
                                        data-placeholder="اختر تصنيف الاصل" required>
                                        <option value="">اختر تصنيف الاصل</option>
                                        @foreach ($assetCategory as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('asset_category_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="name" class="form-label">الاصل
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name') }}" required>
                                </div>


                                <div class="col-md-6">
                                    <label for="price" class="form-label">السعر
                                    </label>
                                    <input type="text" id="price" name="price" class="form-control"
                                        value="{{ old('price') }}" required>
                                </div>


                                <div class="col-md-6">
                                    <label for="storage_location_id" class="form-label">مرجعية الاصل
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="storage_location_id" name="storage_location_id" class="form-select select2"
                                        data-placeholder="اختر مكان تخزين الاصل" required>
                                        <option value="">اختر مكان تخزين الاصل</option>
                                        @foreach ($storageLocation as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('storage_location_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="اضف الوصف">{{ old('description') }}</textarea>
                                </div>

                                <!-- -->
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <p class="text-muted text-center my-0">
                                    الارقام التسلسلية للاصول
                                </p>
                                <div class="border-1 border-light border-dashed my-5"></div>
                                <!-- -->

                                <div class="col-12">
                                    <div id="serialNumbersContainer">
                                        <!-- الرقم التسلسلي الأول -->
                                        <div class="serial-number-container mb-3" data-index="0">
                                            <div class="serial-number-header form-label">
                                                الرقم التسلسلي
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-11">
                                                    <input type="text" name="serial_numbers[]" class="form-control"
                                                        placeholder="أدخل الرقم التسلسلي" required
                                                        value="{{ old('serial_numbers.0') }}">
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-primary btn-sm my-3 add-serial-btn"
                                    onclick="addSerialNumber()">
                                    إضافة رقم تسلسلي
                                </button>
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
@endsection
