@extends('layouts.layoutMaster')

@section('title', 'إضافة إيصال')

@section('breadcrumb')
    <li><a href="#">قيود</a></li>
    <li><a href="{{ route('qoyod.receipts.index') }}"> الإيصالات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة إيصال</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة إيصال" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/qoyod/receipts/receipts-request-wizard.js'])
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const $type = $('#contact_type');
            const $custWrap = $('#customers-wrapper');
            const $suppWrap = $('#suppliers-wrapper');
            const $custSel = $('#customer_id');
            const $suppSel = $('#supplier_id');
            const $custLoad = $('#custLoading');
            const $suppLoad = $('#suppLoading');

            // إخفاء المربعات في البداية
            $custWrap.hide();
            $suppWrap.hide();

            $type.on('change', function() {
                const v = $(this).val();

                // إخفاء الجميع قبل التفرقة
                $custWrap.hide();
                $suppWrap.hide();
                $custSel.html('');
                $suppSel.html('');

                if (v === 'عميل') {
                    // عرض قائمة العملاء
                    $custWrap.show();
                    $custLoad.show();

                    $.ajax({
                        url: "{{ route('qoyod.customers.get-customers') }}",
                        method: 'GET',
                        success(data) {
                            let opts = '<option value="">اختر العميل</option>';
                            data.forEach(item => {
                                opts +=
                                    `<option value="${item.id}">${item.name}</option>`;
                            });
                            $custSel.html(opts);
                        },
                        error() {
                            $custSel.html('<option value="">خطأ في جلب العملاء</option>');
                            toastr.error('فشل في تحميل قائمة العملاء');
                        },
                        complete() {
                            $custLoad.hide();
                        }
                    });

                } else if (v === 'مورد') {
                    // عرض قائمة الموردين
                    $suppWrap.show();
                    $suppLoad.show();

                    $.ajax({
                        url: "{{ route('qoyod.vendors.get-vendors') }}",
                        method: 'GET',
                        success(data) {
                            let opts = '<option value="">اختر المورد</option>';
                            data.forEach(item => {
                                opts +=
                                    `<option value="${item.id}"> ${item.name}</option>`;
                            });
                            $suppSel.html(opts);
                        },
                        error() {
                            $suppSel.html('<option value="">خطأ في جلب الموردين</option>');
                            toastr.error('فشل في تحميل قائمة الموردين');
                        },
                        complete() {
                            $suppLoad.hide();
                        }
                    });
                }
            });

        });
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
                                <span class="bs-stepper-subtitle">تفاصيل الإيصال</span>
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

                    <form id="form" action="{{ route('qoyod.receipts.store') }}" method="POST">
                        @csrf

                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">

                                {{-- reference --}}
                                <div class="col-md-6">
                                    <label for="reference" class="form-label">
                                        رقم المرجع
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="reference" name="reference" class="form-control"
                                        value="{{ old('reference') }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_type" class="form-label">الجهة <span
                                            class="text-danger">*</span></label>
                                    <select id="contact_type" name="contact_type" class="form-control select2"
                                        data-placeholder="اختر الجهة">
                                        <option value=""></option>
                                        <option value="عميل" {{ old('contact_type') == 'عميل' ? 'selected' : '' }}>عميل
                                        </option>
                                        <option value="مورد" {{ old('contact_type') == 'مورد' ? 'selected' : '' }}>مورد
                                        </option>
                                    </select>
                                </div>

                                {{-- قائمة العملاء --}}
                                <div class="col-md-6" id="customers-wrapper" style="position: relative;">
                                    <label for="customer_id" class="form-label">العميل <span
                                            class="text-danger">*</span></label>
                                    <select id="customer_id" name="customer_id" class="form-control select2"
                                        data-placeholder="اختر العميل">
                                        {{-- يُملأ عبر AJAX --}}
                                    </select>
                                    <div id="custLoading" class="position-absolute bottom-50 end-0 translate-middle-y me-3"
                                        style="display:none">
                                        <i class="ti ti-loader ti-spin"></i>
                                    </div>
                                </div>

                                {{-- قائمة الموردين --}}
                                <div class="col-md-6" id="suppliers-wrapper" style="position: relative;">
                                    <label for="supplier_id" class="form-label">المورد <span
                                            class="text-danger">*</span></label>
                                    <select id="supplier_id" name="supplier_id" class="form-control select2"
                                        data-placeholder="اختر المورد">
                                        {{-- يُملأ عبر AJAX --}}
                                    </select>
                                    <div id="suppLoading" class="position-absolute bottom-50 end-0 translate-middle-y me-3"
                                        style="display:none">
                                        <i class="ti ti-loader ti-spin"></i>
                                    </div>
                                </div>

                                <div class="col-md-6" id="account_id">
                                    <label for="account_id" class="form-label">الحساب <span
                                            class="text-danger">*</span></label>
                                    <select id="account_id" name="account_id" class="select2 form-select"
                                        data-placeholder="اختر الحساب ">
                                        <option value=""></option>
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc['id'] }}"
                                                {{ old('account_id') == $acc['id'] ? 'selected' : '' }}>
                                                {{ $acc['code'] }} - {{ $acc['name_ar'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="kind" class="form-label">النوع <span
                                            class="text-danger">*</span></label>
                                    <select id="kind" name="kind" class="select2 form-select"
                                        data-placeholder="اختر النوع">
                                        <option value=""></option>
                                        <option value="قبض">قبض</option>
                                        <option value="صرف">صرف</option>
                                    </select>
                                </div>


                                <div class="col-md-6">
                                    <label for="amount" class="form-label">المبلغ <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="amount" name="amount" class="form-control"
                                        value="{{ old('amount') }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="date" class="form-label">التاريخ <span
                                            class="text-danger">*</span></label>
                                    <input type="date" id="date" name="date" class="form-control"
                                        value="{{ old('date') }}" required />
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
