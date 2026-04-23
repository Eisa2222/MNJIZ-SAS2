@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات الاصل')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('hr.custody.items.index') }}">إدارة الاصول</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل بيانات الاصل</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات الاصل" data-page-url="{{ url()->current() }}"
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
                    <form id="form" action="{{ route('hr.custody.items.update', $item->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
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
                                                {{ old('asset_category_id', $item->asset_category_id) == $type->id ? 'selected' : '' }}>
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
                                        value="{{ old('name', $item->name) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="serial_number" class="form-label">الرقم التسلسلي
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="serial_number" name="serial_number" class="form-control"
                                        value="{{ old('serial_number', $item->serial_number) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="price" class="form-label">السعر
                                    </label>
                                    <input type="text" id="price" name="price" class="form-control"
                                        value="{{ old('price', $item->price) }}" required>
                                </div>


                                <div class="col-md-6">
                                    <label for="storage_location_id" class="form-label">مرجعية الاصل
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="storage_location_id" name="storage_location_id" class="form-select select2"
                                        data-placeholder="اختر مرجعية الاصل">
                                        <option value="">اختر مرجعية الاصل</option>
                                        @foreach ($storageLocation as $location)
                                            <option value="{{ $location->id }}"
                                                {{ old('storage_location_id', $item->storage_location_id) == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="use_status" class="form-label">حالة الاستخدام </label>
                                    <select id="use_status" name="use_status" class="form-select select2"
                                        data-placeholder="اختر حالة الاستخدام" required>
                                        <option value="">اختر حالة الاستخدام</option>
                                        @foreach ($statusOptions as $type)
                                            <option value="{{ $type['id'] }}"
                                                {{ old('use_status', $item->use_status->value) == $type['id'] ? 'selected' : '' }}>
                                                {{ $type['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger">
                                        {{ $item->use_status->value === 'in_use' ? 'في حالة الاصل قيد الاستخدام لا يمكن تغيير الحالة' : '' }}
                                    </small>
                                </div>


                                <div class="col-md-12">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="اضف الوصف">{{ old('description', $item->description) }}</textarea>
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
