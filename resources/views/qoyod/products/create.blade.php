@extends('layouts.layoutMaster')

@section('title', 'إضافة منتج')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.products.index') }}">إدارة المنتجات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة منتج</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة منتج" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/qoyod/products/products-request-wizard.js'])

    <script>
        // القيم الخاصة بالضريبة 
        const zeroReasons = @json($zeroReasons);
        const exemptReasons = @json($exemptReasons);
    </script>
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
                                <span class="bs-stepper-subtitle">نوع المنتج والبيانات العامة</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#details-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">تفاصيل المنتج</span>
                                <span class="bs-stepper-subtitle">التفاصيل الإضافية للمنتج</span>
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

                    <form id="form" action="{{ route('qoyod.products.store') }}" method="POST">
                        @csrf

                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                <h5 class="text-center mb-4 text-primary">نوع المنتج</h5>
                                <div class="row d-flex justify-content-center mb-5">
                                    @php

                                        $oldType = old('type', 'Service');
                                    @endphp

                                    @foreach ($productType as $index => $type)
                                        <div class="col-md-2 mb-md-0 mb-4" title="{{ $type->label() }}">
                                            <div class="form-check custom-option custom-option-icon {{ $oldType === $type->value ? 'checked' : '' }}"
                                                onclick="document.getElementById('productType{{ $index }}').click();">
                                                <label class="form-check-label custom-option-content"
                                                    for="productType{{ $index }}">
                                                    <span class="custom-option-body">
                                                        <i class="icon-base {{ $type->icon() }}"></i>
                                                        <span class="custom-option-title">{{ $type->label() }}</span>
                                                    </span>
                                                    <input id="productType{{ $index }}" name="type"
                                                        class="form-check-input" type="radio" value="{{ $type->value }}"
                                                        {{ $oldType === $type->value ? 'checked' : '' }}
                                                        style="display: none;" />
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- SKU --}}
                                <div class="col-md-6">
                                    <label for="sku" class="form-label">الرقم التسلسلي (SKU) <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="sku" name="sku" class="form-control"
                                        value="{{ old('sku') }}" required />
                                    @error('sku')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Barcode --}}
                                <div class="col-md-6">
                                    <label for="barcode" class="form-label">الباركود</label>
                                    <input type="text" id="barcode" name="barcode" class="form-control"
                                        value="{{ old('barcode') }}" />
                                    @error('barcode')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- الاسم بالعربية --}}
                                <div class="col-md-6">
                                    <label for="name_ar" class="form-label">الاسم بالعربية <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="name_ar" name="name_ar" class="form-control"
                                        value="{{ old('name_ar') }}" required />
                                    @error('name_ar')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- الاسم بالإنجليزية --}}
                                <div class="col-md-6">
                                    <label for="name_en" class="form-label">الاسم بالإنجليزية <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="name_en" name="name_en" class="form-control"
                                        value="{{ old('name_en') }}" required />
                                    @error('name_en')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- الوصف --}}
                                <div class="col-md-12">
                                    <label for="description" class="form-label">الوصف</label>
                                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
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
                                {{-- الصنف --}}
                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">الصنف <span
                                            class="text-danger">*</span></label>
                                    <select id="category_id" name="category_id" class="select2 form-select"
                                        data-placeholder="اختر الصنف" required>
                                        <option value=""></option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat['id'] }}"
                                                {{ old('category_id') == $cat['id'] ? 'selected' : '' }}>
                                                {{ $cat['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- وحدة القياس --}}
                                <div class="col-md-6">
                                    <label for="product_unit_type_id" class="form-label">وحدة القياس <span
                                            class="text-danger">*</span></label>
                                    <select id="product_unit_type_id" name="product_unit_type_id"
                                        class="select2 form-select" data-placeholder="اختر وحدة القياس" required>
                                        <option value=""></option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit['id'] }}"
                                                {{ old('product_unit_type_id') == $unit['id'] ? 'selected' : '' }}>
                                                {{ $unit['unit_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_unit_type_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- سعر الشراء  --}}
                                <div class="col-md-6 d-none" id="wrapper_buying_price">
                                    <label for="buying_price" class="form-label">سعر الشراء <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="buying_price" name="buying_price"
                                        class="form-control" value="{{ old('buying_price') }}" />
                                    @error('buying_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- حساب المصروفات  --}}
                                <div class="col-md-6 d-none" id="wrapper_expense_account_id">
                                    <label for="expense_account_id" class="form-label">حساب المصروفات <span
                                            class="text-danger">*</span></label>
                                    <select id="expense_account_id" name="expense_account_id" class="select2 form-select"
                                        data-placeholder="اختر حساب المصروفات">
                                        <option value=""></option>
                                        @foreach ($expenseAccount as $acc)
                                            <option value="{{ $acc['id'] }}"
                                                {{ old('expense_account_id') == $acc['id'] ? 'selected' : '' }}>
                                                {{ $acc['code'] }} - {{ $acc['name_ar'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('expense_account_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- سعر البيع  --}}
                                <div class="col-md-6 d-none" id="wrapper_selling_price">
                                    <label for="selling_price" class="form-label">سعر البيع <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" id="selling_price" name="selling_price"
                                        class="form-control" value="{{ old('selling_price') }}" />
                                    @error('selling_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- حساب المبيعات  --}}
                                <div class="col-md-6 d-none" id="wrapper_sales_account_id">
                                    <label for="sales_account_id" class="form-label">حساب المبيعات <span
                                            class="text-danger">*</span></label>
                                    <select id="sales_account_id" name="sales_account_id" class="select2 form-select"
                                        data-placeholder="اختر حساب المبيعات">
                                        <option value=""></option>
                                        @foreach ($salesAccount as $acc)
                                            <option value="{{ $acc['id'] }}"
                                                {{ old('sales_account_id') == $acc['id'] ? 'selected' : '' }}>
                                                {{ $acc['code'] }} - {{ $acc['name_ar'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sales_account_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- الضريبة --}}
                                <div class="col-md-6">
                                    <label for="tax_id" class="form-label">الضريبة <span
                                            class="text-danger">*</span></label>
                                    <select id="tax_id" name="tax_id" class="select2 form-select"
                                        data-placeholder="اختر الضريبة" required>
                                        <option value=""></option>
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax['id'] }}"
                                                {{ old('tax_id') == $tax['id'] ? 'selected' : '' }}>
                                                {{ $tax['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tax_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- سبب الضريبة الخاصة  --}}
                                <div class="col-md-6 d-none" id="wrapper_special_tax_reason_id">
                                    <label for="special_tax_reason_id" class="form-label">سبب الضريبة الخاصة</label>
                                    <select id="special_tax_reason_id" name="special_tax_reason_id"
                                        class="select2 form-select" data-placeholder="اختر سبب الضريبة الخاصة" disabled>
                                        <option value=""></option>
                                    </select>
                                    @error('special_tax_reason_id')
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
