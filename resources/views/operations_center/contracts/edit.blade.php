@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات العقد')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="{{ route('operations-center.contracts.index') }}"> العقود</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل بيانات العقد</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات العقد" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/operations-center/contract/form-wizard-validation-contract.js', 'resources/assets/js/operations-center/contract/update-contract.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>

    <script>
        window.initialTechnical = @json(old(
                'technical_offer',
                $contract->contract_type == 'main'
                    ? $contract->offer->technical_offer ?? ''
                    : $contract->supplementary_technical_offer ?? ''));
        window.initialFinancial = @json(old(
                'financial_offer',
                $contract->contract_type == 'main'
                    ? $contract->offer->financial_offer ?? ''
                    : $contract->supplementary_financial_offer ?? ''));


        window.supplementPreamble = @json(old('supplement_preamble', $contract->supplement_preamble));
        window.supplementDeclaration = @json(old('supplement_terms', $contract->supplement_terms));


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
                    <form id="power-form" action="{{ route('operations-center.contracts.update', $contract->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="contract_name" class="form-label">اسم العقد</label>
                                    <input type="text" id="contract_name" name="contract_name" class="form-control"
                                        value="{{ old('contract_name', $contract->contract_name) }}" required />
                                </div>

                                <div class="col-md-6">
                                    <label for="contract_type" class="form-label">نوع العقد</label>
                                    <select id="contract_type" name="contract_type" class="form-select select2" required
                                        data-placeholder="اختر نوع العقد">
                                        <option value=""></option>
                                        <option value="main"
                                            {{ old('contract_type', $contract->contract_type) == 'main' ? 'selected' : '' }}>
                                            رئيسي</option>
                                        <option value="supplementary"
                                            {{ old('contract_type', $contract->contract_type) == 'supplementary' ? 'selected' : '' }}>
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
                                                {{ old('main_contract_id', $contract->main_contract_id) == $mainContract->id ? 'selected' : '' }}>
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
                                                {{ old('contract_manager_id', $contract->contract_manager_id) == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }}
                                            </option>
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
                                                {{ old('contract_status_id', $contract->contract_status_id) == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
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
                                        data-placeholder="اختر العرض المناسب">
                                        <option value=""></option>
                                        @foreach ($offers as $offer)
                                            <option value="{{ $offer->id }}"
                                                {{ old('offer_id', $contract->offer_id) == $offer->id ? 'selected' : '' }}>
                                                {{ $offer->offer_name }}
                                            </option>
                                        @endforeach
                                        {{-- إضافة خيار ديناميكي في حال كان العقد ملحق --}}
                                        @if ($contract->contract_type == 'supplementary' && $contract->mainContract && $contract->mainContract->offer)
                                            <option class="dynamic-option"
                                                value="{{ $contract->mainContract->offer->id }}" selected hidden>
                                                {{ $contract->mainContract->offer->offer_name }}
                                            </option>
                                        @endif
                                    </select>
                                </div>


                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label">العميل
                                        <span title="لتغيير العميل، يرجى تعديله من العرض أولاً."
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <select id="customer_id" name="customer_id" class="form-select select2" disabled
                                        data-placeholder=" العميل">
                                        <option value=""></option>
                                        @if ($contract->customer)
                                            <option value="{{ $contract->customer->id }}" selected>
                                                {{ $contract->customer->name }}</option>
                                        @else
                                            <option value="{{ $contract->mainContract->customer->id }}" selected>
                                                {{ $contract->mainContract->customer->name }}</option>
                                        @endif
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
                                        class="form-select select2" disabled data-placeholder=" مسؤول العلاقات">
                                        <option value=""></option>
                                        @if ($contract->relationshipManager)
                                            <option value="{{ $contract->relationshipManager->id }}" selected>
                                                {{ $contract->relationshipManager->name }}</option>
                                        @else
                                            <option value="{{ $contract->mainContract->relationshipManager->id }}"
                                                selected>
                                                {{ $contract->mainContract->relationshipManager->name }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="contract_start_date" class="form-label">تاريخ بداية العقد </label>
                                    <input type="text" id="contract_start_date" name="contract_start_date"
                                        autocomplete="off" onkeydown="return false;" class="form-control hijri-picker"
                                        value="{{ old('contract_start_date', $contract->contract_start_date) }}"
                                        required />
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="contract_end_date" class="form-label">تاريخ نهاية العقد</label>
                                    <input type="text" id="contract_end_date" name="contract_end_date"
                                        autocomplete="off" onkeydown="return false;" class="form-control hijri-picker"
                                        value="{{ old('contract_end_date', $contract->contract_end_date) }}" />
                                </div>

                                <div class="col-md-6" style="position: relative">
                                    <label for="expected_closure_date" class="form-label">تاريخ الإغلاق المتوقع </label>
                                    <input type="text" id="expected_closure_date" name="expected_closure_date"
                                        autocomplete="off" onkeydown="return false;" class="form-control hijri-picker"
                                        value="{{ old('expected_closure_date', $contract->expected_closure_date) }}" />
                                </div>

                                <div class="mb-4 col-md-3">
                                    <label for="is_private_and_secret" class="form-label">سري و خاص</label>
                                    <span title="سيتم اضافة عبارة سري و خاص في العقد" style="color: #d5a047;">
                                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                    </span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_private_and_secret"
                                            name="is_private_and_secret"
                                            {{ old('is_private_and_secret', $contract->is_private_and_secret) ? 'checked' : '' }}>
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
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label" for="technical_offer">العرض الفني</label>
                                    <div id="editor-technical-offer" style="height: 300px;">
                                        @if ($contract->contract_type == 'main')
                                            {!! old('technical_offer', $contract->offer->technical_offer ?? '') !!}
                                        @else
                                            {!! old('technical_offer', $contract->supplementary_technical_offer ?? '') !!}
                                        @endif
                                    </div>
                                    <input type="hidden" name="technical_offer" id="technical_offer">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label" for="financial_offer">العرض المالي</label>
                                    <div id="editor-financial-offer" style="height: 300px;">
                                        @if ($contract->contract_type == 'main')
                                            {!! old('financial_offer', $contract->offer->financial_offer ?? '') !!}
                                        @else
                                            {!! old('financial_offer', $contract->supplementary_financial_offer ?? '') !!}
                                        @endif
                                    </div>
                                    <input type="hidden" name="financial_offer" id="financial_offer">
                                </div>

                                <!-- الحقلين الجديدين للملحق - يظهران فقط عند اختيار نوع العقد "ملحق" -->
                                <div class="col-md-12 supplement-field {{ $contract->contract_type !== 'supplementary' ? 'd-none' : '' }}"
                                    id="supplement-preamble-container">
                                    <label class="form-label" for="supplement_preamble">تمهيد ملحق العقد</label>
                                    <div id="editor-supplement-preamble" style="height: 300px;">
                                        {!! old('supplement_preamble', $contract->supplement_preamble ?? '') !!}
                                    </div>
                                    <input type="hidden" name="supplement_preamble" id="supplement_preamble">
                                </div>

                                <div class="col-md-12 supplement-field {{ $contract->contract_type !== 'supplementary' ? 'd-none' : '' }}"
                                    id="supplement-declaration-container">
                                    <label class="form-label" for="supplement_terms">بنود ملحق العقد</label>
                                    <div id="editor-supplement-declaration" style="height: 300px;">
                                        {!! old('supplement_terms', $contract->supplement_terms ?? '') !!}
                                    </div>
                                    <input type="hidden" name="supplement_terms" id="supplement_terms">
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

                        <div id="additional-info" class="content">
                            <div class="row g-3">
                                <div class="col-sm-12 mt-3">
                                    <label class="form-label" for="attachments">المرفقات</label>
                                    <div id="attachment-fields">
                                        @can('مشاهدة مرفق العقد')

                                            @foreach ($contract->attachments as $attachment)
                                                <div class="row g-3 mb-2 existing-attachment"
                                                    data-id="{{ $attachment->id }}">
                                                    <div class="col-11">
                                                        <input type="text"
                                                            name="existing_attachment_names[{{ $attachment->id }}]"
                                                            class="form-control" placeholder="اسم المرفق"
                                                            value="{{ $attachment->name }}">
                                                    </div>
                                                    <div class="col-1">
                                                        <a href="{{ $attachment->share_link }}" target="_blank"
                                                            class="btn btn-info btn-sm"><i class="ti ti-eye fs-5"></i></a>
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm remove-existing-attachment"
                                                            data-id="{{ $attachment->id }}"><i
                                                                class="ti ti-trash"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endcan

                                        <div class="mt-4 text-center">
                                            <button type="button" id="add-attachment" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus-circle me-1"></i> إضافة مرفق
                                            </button>
                                        </div>
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

                            <p class="text-danger text-center small">
                                هنا تظهر الدفعات المجدولة فقط
                            </p>
                            <!-- الحاوية الرئيسية للدفعات -->
                            <div id="payment-rows">
                                <input type="hidden" id="sum_percentages" name="sum_percentages" value="0">
                                @foreach (old('payments', $contract->scheduledPayments ?? []) as $i => $payment)
                                    <div class="row payment-row g-3 align-items-end mb-3">
                                        <input type="hidden" name="payments[{{ $i }}][id]"
                                            value="{{ data_get($payment, 'id') }}">

                                        <div class="col-md-3 p-2">
                                            <label class="form-label">طريقة الحساب</label>
                                            <select name="payments[{{ $i }}][calculation_type]"
                                                class="form-select select2 payment-type" data-index="{{ $i }}"
                                                data-placeholder="اختر طريقة الحساب ">
                                                @foreach ($calculationTypes as $type)
                                                    <option value="{{ $type['id'] }}"
                                                        {{ old("payments.$i.calculation_type", $payment->calculation_type->value) === $type['id'] ? 'selected' : '' }}>
                                                        {{ $type['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3 p-2">
                                            <label class="form-label"> نوع الدفعة </label>
                                            <select name="payments[{{ $i }}][payment_batch_type]"
                                                class="form-select select2 " data-index="{{ $i }}"
                                                data-placeholder="اختر  نوع الدفعة  ">
                                                @foreach ($paymentBatchTypes as $type)
                                                    <option value="{{ $type['id'] }}"
                                                        {{ old("payments.$i.payment_batch_type", $payment->payment_batch_type->value) === $type['id'] ? 'selected' : '' }}>
                                                        {{ $type['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                        {{-- حقل النسبة --}}
                                        <div id="percentage-col-{{ $i }}" class="col-md-3 p-2 percentage-col"
                                            style="{{ old('payments.' . $i . '.calculation_type', $payment->calculation_type->value) !== 'percentage' ? 'display:none;' : '' }}">
                                            <label class="form-label">النسبة %</label>
                                            <input type="text" name="payments[{{ $i }}][percentage]"
                                                class="form-control"
                                                value="{{ old('payments.' . $i . '.percentage', $payment->percentage) }}">
                                        </div>

                                        {{-- حقل المبلغ --}}
                                        <div id="amount-col-{{ $i }}" class="col-md-3 p-2 amount-col"
                                            style="{{ old('payments.' . $i . '.calculation_type', $payment->calculation_type->value) !== 'fixed' ? 'display:none;' : '' }}">
                                            <label class="form-label">المبلغ</label>
                                            <input type="text" name="payments[{{ $i }}][fixed_amount]"
                                                class="form-control"
                                                value="{{ old('payments.' . $i . '.fixed_amount', $payment->fixed_amount) }}">
                                        </div>

                                        <div class="col-md-2 p-2">
                                            <label class="form-label">تاريخ الاستحقاق</label>
                                            <input type="date" name="payments[{{ $i }}][due_date]"
                                                class="form-control"
                                                value="{{ old('payments.' . $i . '.due_date', optional($payment->due_date)->format('Y-m-d')) }}">
                                        </div>

                                        <div class="col-md-1 p-2">
                                            <button type="button" class="btn btn-sm btn-danger remove-payment-btn"
                                                title="حذف الدفعة">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- زر الإضافة في الأسفل -->
                            <div class="mt-4 text-center">
                                <button type="button" id="add-payment-btn" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> إضافة دفعة
                                </button>
                            </div>

                            <!-- أزرار الانتقال والحفظ -->
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
