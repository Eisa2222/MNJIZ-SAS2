@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات الطلب ')

@section('breadcrumb')
    <li><a href="#">الخدمات الإلكترونية</a></li>
    <li><a href="{{ route('account.electronic-services.purchase-requests.index') }}">طلبات المشتريات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل بيانات الطلب </a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات الطلب " data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/purchase_requests_wizard.js'])
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
                                <span class="bs-stepper-subtitle">تفاصيل الطلب و الكميات</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">

                    <form id="purchase-requests-form"
                        action="{{ route('account.electronic-services.purchase-requests.update', $purchaseRequest->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="request-info" class="content">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label for="item_name" class="form-label">الطلب</label>
                                    <input type="text" name="item_name" id="item_name" class="form-control"
                                        placeholder="ادخل الطلب الخاص بك"
                                        value="{{ old('item_name', $purchaseRequest->item_name) }}" required>
                                </div>

                                <!-- التصنيف -->
                                <div class="col-md-4">
                                    <label for="purchase_category_id" class="form-label">التصنيف</label>
                                    <select name="purchase_category_id" id="purchase_category_id"
                                        class="form-select select2" required data-placeholder="اختر التصنيف">
                                        <option value=""></option>
                                        @foreach ($purchase_category as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('purchase_category_id', $purchaseRequest->purchase_category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- الكمية -->
                                <div class="col-md-4">
                                    <label for="item_quantity" class="form-label">الكمية</label>
                                    <input type="number" name="item_quantity" id="item_quantity"
                                        value="{{ old('item_quantity', $purchaseRequest->item_quantity) }}"
                                        class="form-control" required placeholder="ادخل الكمية">
                                </div>

                                <!-- ملاحظة -->
                                <div class="col-md-12">
                                    <label for="item_description" class="form-label">ملاحظة</label>
                                    <textarea name="item_description" id="item_description" class="form-control" rows="3" placeholder="اضف ملاحظة">{{ old('item_description', $purchaseRequest->item_description) }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
