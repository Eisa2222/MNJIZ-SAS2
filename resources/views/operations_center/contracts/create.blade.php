@extends('layouts.layoutMaster')

@section('title', 'إضافة عقد جديد')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.contracts.index') }}"> العقود</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إضافة عقد جديد</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة عقد جديد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection
@section('page-script')
    @vite(['resources/assets/js/operations-center/contract/form-wizard-validation-contract.js', 'resources/assets/js/operations-center/contract/create-contract.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>

    <script>
        window.EnumOptions = {
            calculationType: @json($calculationTypes),
            paymentBatchType: @json($paymentBatchTypes),
        };
    </script>
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
                                <span class="bs-stepper-subtitle">تفاصيل العقد</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#offers-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">معلومات العرض</span>
                                <span class="bs-stepper-subtitle">العرض الفني، العرض المالي</span>
                            </span>
                        </button>
                    </div>

                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المرفقات</span>
                                <span class="bs-stepper-subtitle">رفع المرفقات المطلوبة</span>
                            </span>
                        </button>
                    </div>

                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#payments-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">4</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">دفعات العقد</span>
                                <span class="bs-stepper-subtitle">تفاصيل دفعات العقد </span>
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
                    <form id="power-form" action="{{ route('operations-center.contracts.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content  dstepper-block">

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label for="contract_name" class="form-label">اسم العقد</label>
                                    <input type="text" id="contract_name" name="contract_name" class="form-control"
                                        value="{{ old('contract_name') }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="contract_type" class="form-label">نوع العقد</label>
                                    <select id="contract_type" name="contract_type" class="form-select select2" required
                                        data-placeholder="اختر نوع العقد">
                                        <option value=""></option>
                                        <option value="main" {{ old('contract_type') == 'main' ? 'selected' : '' }}>
                                            رئيسي
                                        </option>
                                        <option value="supplementary"
                                            {{ old('contract_type') == 'supplementary' ? 'selected' : '' }}>
                                            ملحق</option>
                                    </select>
                                </div>

                                <div class="col-md-6 d-none" id="main_contract_container">
                                    <label for="main_contract_id" class="form-label">العقد الرئيسي</label>
                                    <select id="main_contract_id" name="main_contract_id" class="form-select select2"
                                        data-placeholder="اختر العقد الرئيسي">
                                        <option value=""></option>
                                        @foreach ($mainContracts as $mainContract)
                                            <option value="{{ $mainContract->id }}"
                                                {{ old('main_contract_id') == $mainContract->id ? 'selected' : '' }}>
                                                {{ $mainContract->contract_name }} ({{ $mainContract->contract_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="contract_manager_id" class="form-label">مسؤول العقد</label>
                                    <select id="contract_manager_id" name="contract_manager_id" class="form-select select2"
                                        required data-placeholder="اختر مسؤول العقد">
                                        <option value=""></option>
                                        @foreach ($employees as $manager)
                                            <option value="{{ $manager->id }}"
                                                {{ old('contract_manager_id') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="contract_status_id" class="form-label">حالة العقد</label>
                                    <select id="contract_status_id" name="contract_status_id" class="form-select select2"
                                        required data-placeholder="اختر حالة العقد">
                                        <option value=""></option>
                                        @foreach ($settings_contract_status as $status)
                                            <option value="{{ $status->id }}"
                                                {{ old('contract_status_id') == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="offer_id" class="form-label">العروض
                                        <span title="العروض المتاحة هنا هي العروض المعتمدة والتي لم يتم ربطها بعقد بعد"
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>

                                    <select id="offer_id" name="offer_id" class="form-select select2" required
                                        data-get-offer-details-route="{{ route('operations-center.contracts.getOfferDetails', ':id') }}"
                                        data-placeholder="اختر العرض ">
                                        <option value=""></option>
                                        @foreach ($offers as $offer)
                                            <option value="{{ $offer->id }}"
                                                {{ old('offer_id') == $offer->id ? 'selected' : '' }}>
                                                {{ $offer->offer_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">العميل
                                        <span title="لتغيير العميل، يرجى تعديله من العرض أولاً."
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <select id="customer_id" name="customer_id" class="form-select select2" disabled>
                                        <option value=""></option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="relationship_manager_id" class="form-label">مسؤول العلاقات
                                        <span title="لتغيير مسؤول العلاقة يجب تغييره من قائمة العملاء أولاً."
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <select id="relationship_manager_id" name="relationship_manager_id"
                                        class="form-select select2" disabled>
                                        <option value=""></option>
                                    </select>
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="contract_start_date" class="form-label">تاريخ بداية العقد </label>
                                    <input type="text" id="contract_start_date" name="contract_start_date"
                                        autocomplete="off" onkeydown="return false;" class="form-control hijri-picker"
                                        value="{{ old('contract_start_date') }}" required />
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="contract_end_date" class="form-label">تاريخ نهاية العقد</label>
                                    <input type="text" id="contract_end_date" name="contract_end_date"
                                        autocomplete="off" onkeydown="return false;" class="form-control hijri-picker"
                                        value="{{ old('contract_end_date') }}" />
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="expected_closure_date" class="form-label">تاريخ الإغلاق المتوقع </label>
                                    <input type="text" id="expected_closure_date" name="expected_closure_date"
                                        autocomplete="off" onkeydown="return false;" class="form-control hijri-picker"
                                        value="{{ old('expected_closure_date') }}" />
                                </div>

                                <div class="mb-4 col-md-3">
                                    <label for="is_private_and_secret" class="form-label">سري و خاص</label>
                                    <span title="سيتم اضافة عبارة سري و خاص في العقد"
                                        style="color: var(--primary-color);">
                                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                    </span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_private_and_secret"
                                            name="is_private_and_secret"
                                            {{ old('is_private_and_secret') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_private_and_secret">تفعيل وضع
                                            العقد سري و خاص</label>

                                    </div>
                                </div>

                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div id="offers-info" class="content">
                            {{-- <div class="row  g-3">
                            <div class="col-md-12">
                                <label class="form-label" for="technical_offer">العرض الفني</label>
                                <div id="editor-technical-offer" style="height: 300px;">
                                </div>
                                <input type="hidden" name="technical_offer" id="technical_offer">
                            </div>

                            <!-- العرض المالي -->
                            <div class="col-md-12">
                                <label class="form-label" for="financial_offer">العرض المالي</label>
                                <div id="editor-financial-offer" style="height: 300px;">
                                </div>
                                <input type="hidden" name="financial_offer" id="financial_offer">
                            </div>

                        </div> --}}

                            <!-- الحقول الموجودة مسبقاً -->
                            <div class="col-md-12">
                                <label class="form-label" for="technical_offer">العرض الفني</label>
                                <div id="editor-technical-offer" style="height: 300px;"></div>
                                <input type="hidden" name="technical_offer" id="technical_offer">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label" for="financial_offer">العرض المالي</label>
                                <div id="editor-financial-offer" style="height: 300px;"></div>
                                <input type="hidden" name="financial_offer" id="financial_offer">
                            </div>

                            <!-- الحقلين الجديدين للملحق - يظهران فقط عند اختيار نوع العقد "ملحق" -->
                            <div class="col-md-12 supplement-field d-none" id="supplement-preamble-container">
                                <label class="form-label" for="supplement_preamble">تمهيد ملحق العقد</label>
                                <div id="editor-supplement-preamble" style="height: 300px;"></div>
                                <input type="hidden" name="supplement_preamble" id="supplement_preamble">
                            </div>

                            <div class="col-md-12 supplement-field d-none" id="supplement-declaration-container">
                                <label class="form-label" for="supplement_terms">بنود ملحق العقد</label>
                                <div id="editor-supplement-declaration" style="height: 300px;"></div>
                                <input type="hidden" name="supplement_terms" id="supplement_terms">
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div id="additional-info" class="content">
                            <div class="row  g-3">
                                <div class="col-12 mt-3">

                                    <div id="additional-attachments-container">
                                    </div>

                                    <div class="mt-4 text-center">
                                        <button type="button" id="add-attachment" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus-circle me-1"></i> إضافة مرفق
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div id="payments-info" class="content">

                            <div id="payment-rows">
                            </div>

                            <div class="mt-4 text-center">
                                <button type="button" id="add-payment-btn" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> إضافة دفعة
                                </button>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">إضافة</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
