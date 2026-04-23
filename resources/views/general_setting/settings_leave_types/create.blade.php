@extends('layouts.layoutMaster')

@section('title', 'إضافة نوع إجازة جديد')

@section('breadcrumb')
    <li><a href="{{ route('settings-leave-types.index') }}">أنواع الإجازات</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">إضافة نوع إجازة جديد</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة نوع إجازة جديد" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/leave-type-wizard.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#step-basic">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">البيانات الأساسية</span>
                                <span class="bs-stepper-subtitle">اسم وإعدادات أولية</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-left"></i></div>
                    <div class="step" data-target="#step-conditions">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">الإعدادات والشروط</span>
                                <span class="bs-stepper-subtitle">الرصيد والفترات الزمنية</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-left"></i></div>
                    <div class="step" data-target="#step-advanced">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">التفاصيل المتقدمة</span>
                                <span class="bs-stepper-subtitle">المرفقات والقيود الإضافية</span>
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

                    <form id="leave-type-form" action="{{ route('settings-leave-types.store') }}" method="POST">
                        @csrf

                        {{-- المرحلة الأولى: البيانات الأساسية --}}
                        <div id="step-basic" class="content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">اسم نوع الإجازة</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        placeholder="أدخل اسم نوع الإجازة" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="gender_applicability" class="form-label">تنطبق على</label>
                                    <select id="gender_applicability" name="gender_applicability"
                                        class="form-select select2" data-placeholder="اختر من القائمة" required
                                        {{ old('is_global') ? 'disabled' : '' }}>
                                        <option value=""></option>
                                        <option value="both"
                                            {{ old('gender_applicability') == 'both' ? 'selected' : '' }}>
                                            كلا الجنسين
                                        </option>
                                        <option value="female"
                                            {{ old('gender_applicability') == 'female' ? 'selected' : '' }}>
                                            الإناث فقط
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">وحدة احتساب الإجازة</label>
                                    <select id="leave_unit_type" name="leave_unit_type" class="form-select select2"
                                        data-placeholder="اختر وحدة احتساب الإجازة" required>
                                        <option value=""></option>
                                        <option value="full_day"
                                            {{ old('leave_unit_type', 'full_day') == 'full_day' ? 'selected' : '' }}>
                                            أيام كاملة فقط
                                        </option>
                                        <option value="half_day"
                                            {{ old('leave_unit_type') == 'half_day' ? 'selected' : '' }}>
                                            نصف يوم
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="advance_notice_days" class="form-label">
                                        الإشعار المسبق بالأيام
                                        <i class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="عدد الأيام المطلوبة كإشعار مسبق قبل طلب الإجازة"></i>
                                    </label>
                                    <input type="number" id="advance_notice_days" name="advance_notice_days"
                                        class="form-control" min="0" value="{{ old('advance_notice_days', 0) }}">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid"
                                            value="1" {{ old('is_paid', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_paid">مدفوعة الأجر</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_global" name="is_global"
                                            value="1" {{ old('is_global') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_global">
                                            هل الإجازة عامة تطبّق على جميع الموظفين تلقائيًا؟
                                            <i class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="هذا الخيار يستخدم لتطبيق الإجازة على جميع الموظفين دون الحاجة لطلب إجازة مثل إجازات الأعياد، ويجب تحديد فترة زمنية لها"></i>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="count_weekends"
                                            name="count_weekends" value="1"
                                            {{ old('count_weekends') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="count_weekends">
                                            احتساب أيام الإجازة الأسبوعية ضمن أيام الإجازة
                                            <i class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="إذا كان هذا الخيار مفعلاً، سيتم احتساب أيام الجمعة والسبت ضمن أيام الإجازة عند طلبها"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-right ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs me-sm-2 me-0"></i>
                                </button>
                            </div>
                        </div>

                        {{-- المرحلة الثانية: الإعدادات والشروط --}}
                        <div id="step-conditions" class="content">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_deductible"
                                            name="is_deductible" value="1"
                                            {{ old('is_deductible') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_deductible">
                                            عند تقديم الطلب، يُخصم من الرصيد السنوي
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_carry_forwardable"
                                            name="is_carry_forwardable" value="1"
                                            {{ old('is_carry_forwardable') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_carry_forwardable">
                                            هل الإجازة قابلة للترحيل للسنة التالية؟
                                            <i class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="هذا الخيار خاص بالإجازة السنوية فقط"></i>
                                        </label>
                                    </div>
                                </div>

                                {{-- حقول التواريخ (للإجازات العامة فقط) --}}
                                <div id="dateFields" class="row g-3"
                                    style="{{ old('is_global') ? 'display:flex;' : 'display:none;' }}">
                                    <div class="col-md-4">
                                        <label for="start_date" class="form-label">
                                            من تاريخ <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" id="start_date" name="start_date" class="form-control"
                                            min="{{ now()->format('Y-m-d') }}" value="{{ old('start_date') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="end_date" class="form-label">
                                            إلى تاريخ <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" id="end_date" name="end_date" class="form-control"
                                            min="{{ now()->format('Y-m-d') }}" value="{{ old('end_date') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="daysCalculated" class="form-label">عدد الأيام بين التاريخين</label>
                                        <input type="text" id="daysCalculated" class="form-control" readonly disabled
                                            value="{{ old('is_global') && old('start_date') && old('end_date')
                                                ? \Carbon\Carbon::parse(old('end_date'))->diffInDays(\Carbon\Carbon::parse(old('start_date'))) + 1
                                                : '' }}">
                                    </div>
                                </div>

                                {{-- الحد الأقصى للأيام (للإجازات العادية فقط) --}}
                                <div id="manualDays" class="row g-3"
                                    style="{{ old('is_global') ? 'display:none;' : 'display:flex;' }}">
                                    <div class="col-md-6">
                                        <label for="days" class="form-label">
                                            الحد الأقصى للأيام <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" id="days" name="days" class="form-control"
                                            min="0" value="{{ old('days') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="max_requests" class="form-label">الحد الأقصى لعدد الطلبات</label>
                                        <input type="number" id="max_requests" name="max_requests" class="form-control"
                                            min="0" value="{{ old('max_requests', 0) }}">
                                    </div>
                                </div>
                                {{-- عتبة سنوات الخدمة --}}
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="service_years_threshold" class="form-label">
                                            عدد سنوات الخدمة التي تتغيّر بعدها أيام الإجازة
                                            <i class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="بعد وصول الموظف لهذا العدد من سنوات الخدمة، سيحصل على عدد أيام مختلف من الإجازة"></i>
                                        </label>
                                        <input type="number" id="service_years_threshold" name="service_years_threshold"
                                            class="form-control" min="0"
                                            value="{{ old('service_years_threshold', 0) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="days_after_threshold" class="form-label">
                                            عدد الأيام بعد اجتياز عتبة سنوات الخدمة
                                        </label>
                                        <input type="number" id="days_after_threshold" name="days_after_threshold"
                                            class="form-control" min="0"
                                            value="{{ old('days_after_threshold', 0) }}">
                                    </div>
                                </div>

                                {{-- ملاحظات إضافية --}}
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-right ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs me-sm-2 me-0"></i>
                                </button>
                            </div>
                        </div>

                        {{-- المرحلة الثالثة: التفاصيل المتقدمة --}}
                        <div id="step-advanced" class="content">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="min_service_years" class="form-label">
                                        سنوات الخدمة الأدنى
                                        <i class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="الحد الأدنى لسنوات الخدمة المطلوبة للاستفادة من هذا النوع من الإجازات"></i>
                                    </label>
                                    <input type="number" id="min_service_years" name="min_service_years"
                                        class="form-control" min="0" value="{{ old('min_service_years') }}">
                                </div>
                                <div class="col-md-6">
                                    {{-- يمكن إضافة حقول إضافية هنا --}}
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_attachments"
                                            name="has_attachments" value="1"
                                            {{ old('has_attachments') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_attachments">
                                            تتطلب مرفقات عند الطلب
                                            <i class="fa fa-question-circle text-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="مثل التقارير الطبية للإجازة المرضية"></i>
                                        </label>
                                    </div>
                                </div>
                                <div id="attachmentDescRow" class="col-12"
                                    style="{{ old('has_attachments') ? 'display:block;' : 'display:none;' }}">
                                    <label for="attachment_description" class="form-label">
                                        نوع المرفقات المطلوبة <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="attachment_description" name="attachment_description"
                                        class="form-control" placeholder="مثلاً: تقرير طبي، شهادة ، إلخ..."
                                        value="{{ old('attachment_description') }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-right ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">
                                    حفظ
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
