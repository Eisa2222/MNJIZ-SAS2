@extends('layouts.layoutMaster')

@section('title', 'تعديل عهدة لموظف')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('hr.custody.requests.index') }}">طلبات العهد</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> تعديل عهدة لموظف</a>
        <i class="ti ti-star favorite-icon" data-page-name=" تعديل  عهدة لموظف" data-page-url="{{ url()->current() }}"
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
            requestUrl: "{{ route('hr.custody.items.filter') }}"
        };

        window.currentData = {
            custodyItemId: "{{ old('custody_item_id', $custody_request->custody_item_id) }}",
            categoryId: "{{ old('category_id', $custody_request->item->asset_category_id ?? '') }}",
            locationId: "{{ old('location_id', $custody_request->item->storage_location_id ?? '') }}",
            custodyItemName: "{{ $custody_request->item->name ?? '' }}",
            custodyItemSerial: "{{ $custody_request->item->serial_number ?? '' }}"
        };
    </script>
    @vite(['resources/assets/js/hr/custody/custody_requests_wizard.js', 'resources/assets/js/hr/custody/update-assign.js'])
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

                    <form id="form" action="{{ route('hr.custody.requests.assign.update', $custody_request->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="request-info" class="content">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="employee_id" class="form-label">
                                        الموظف
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="employee_id" name="employee_id" class="form-select select2"
                                        data-placeholder="اختر الموظف">
                                        <option value=""></option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ old('employee_id', $custody_request->employee_id) == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

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
                                    <label for="location_id" class="form-label">
                                        مرجعية الاصل
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="location_id" name="location_id" class="form-select select2"
                                        data-placeholder="اختر مرجعية الاصل">
                                        <option value=""></option>
                                        @foreach ($storageLocation as $location)
                                            <option value="{{ $location->id }}"
                                                {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}</option>
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
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="اضف ملاحظة">{{ old('notes', $custody_request->notes) }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex flex-row-reverse mt-4">
                                <button class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
