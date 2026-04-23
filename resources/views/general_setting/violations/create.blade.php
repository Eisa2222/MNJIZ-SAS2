@extends('layouts.layoutMaster')

@section('title', 'إضافة نوع مخالفة جديد')

@section('breadcrumb')
    <li><a href="{{ route('settings-violations.index') }}">أنواع المخالفات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إضافة نوع مخالفة جديد</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة نوع مخالفة جديد" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/form-wizard-validation-setting-violation.js'])
    
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <!-- استخدام bs-stepper لإنشاء واجهة خطوات الإدخال -->
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <!-- الخطوة 1: المعلومات الأساسية -->
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل النوع</span>
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
                    <form id="violation-form" action="{{ route('settings-violations.store') }}" method="POST">
                        @csrf
                        <!-- الخطوة 1: المعلومات الأساسية -->
                        <div id="basic-info" class="content dstepper-block">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="violation_type" class="form-label">نوع المخالفة</label>
                                    <select id="violation_type" name="violation_type" class="form-select select2"
                                        data-placeholder="اختر نوع المخالفة">
                                        <option value=""></option>

                                        <option value="delay"
                                            {{ old('violation_type') == 'delay' ? 'selected' : '' }}>
                                            تأخير
                                        </option>

                                        <option value="early_leave"
                                            {{ old('violation_type') == 'early_leave' ? 'selected' : '' }}>
                                            ترك العمل المبكر
                                        </option>

                                        <option value="absence"
                                            {{ old('violation_type') == 'absence' ? 'selected' : '' }}>
                                            غياب
                                        </option>

                                        <option value="after_hours"
                                            {{ old('violation_type') == 'after_hours' ? 'selected' : '' }}>
                                            البقاء بعد انتهاء الدوام
                                        </option>

                                        <option value="other"
                                            {{ old('violation_type') == 'other' ? 'selected' : '' }}>
                                            اخرى
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="settings_violation_category_id" class="form-label">تصنيف المخالفة</label>
                                    <select id="settings_violation_category_id" name="settings_violation_category_id"
                                        class="form-select select2" data-placeholder="اختر تصنيف المخالفة">
                                        <option value=""></option>
                                        @foreach ($violationCategory as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="duration_unit" class="form-label">وحدة المدة</label>
                                    <select id="duration_unit" name="duration_unit" class="form-select select2"
                                        data-placeholder="اختر وحدة المدة">
                                        <option value=""></option>

                                        <option value="minutes" {{ old('duration_unit') == 'minutes' ? 'selected' : '' }}>
                                            دقائق
                                        </option>
                                        <option value="hours" {{ old('duration_unit') == 'hours' ? 'selected' : '' }}>
                                            ساعات
                                        </option>
                                        <option value="days" {{ old('duration_unit') == 'days' ? 'selected' : '' }}>أيام
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="duration_from" class="form-label">المدة من</label>
                                    <input type="number" id="duration_from" name="duration_from" class="form-control"
                                        value="{{ old('duration_from') }}" />
                                </div>

                                <div class="col-md-4">
                                    <label for="duration_to" class="form-label">المدة إلى</label>
                                    <input type="number" id="duration_to" name="duration_to" class="form-control"
                                        value="{{ old('duration_to') }}" />
                                </div>

                                <div class="col-md-12">
                                    <label for="description" class="form-label">وصف المخالفة</label>
                                    <textarea type="text" id="description" name="description" class="form-control" value="{{ old('description') }}"
                                        placeholder="أدخل  وصف المخالفة"></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="penalty_first" class="form-label">الجزاء أول مرة</label>
                                    <select id="penalty_first" name="penalty_first" class="form-select select2"
                                        data-placeholder="اختر الجزاء أول مرة">
                                        <option value=""></option>
                                        <optgroup label="نسب مئوية">
                                            <option value="percentage:5">5%</option>
                                            <option value="percentage:10">10%</option>
                                            <option value="percentage:15">15%</option>
                                            <option value="percentage:20">20%</option>
                                            <option value="percentage:25">25%</option>
                                            <option value="percentage:30">30%</option>
                                            <option value="percentage:50">50%</option>
                                            <option value="percentage:75">75%</option>
                                        </optgroup>
                                        <optgroup label="أيام">
                                            <option value="days:1">يوم</option>
                                            <option value="days:2">يومان</option>
                                            <option value="days:3">ثلاثة أيام</option>
                                            <option value="days:4">أربعة أيام</option>
                                            <option value="days:5">خمسة أيام</option>
                                        </optgroup>
                                        <optgroup label="إجراءات أخرى">
                                            <option value="warning:written">إنذار كتابي</option>
                                            <option value="ban:promotion">الحرمان من الترقيات أو العلاوات لمرة واحدة
                                            </option>
                                            <option value="termination:with_benefit_30">فصل من الخدمة مع المكافأة إذا لم
                                                يتجاوز الغياب (30) يوماً</option>
                                            <option value="termination:article_80">فصل من الخدمة طبقاً للمادة (الثمانون) من
                                                نظام العمل</option>
                                            <option value="termination:article_80_10days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرة أيام، في نطاق حكم المادة (80) من نظام
                                                العمل</option>
                                            <option value="termination:article_80_20days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرين يوماً، في نطاق حكم المادة (80) من
                                                نظام العمل</option>
                                            <option value="termination:with_benefit">فصل مع المكافأة</option>
                                            <option value="termination:without_benefit">فصل بدون مكافأة، أو اشعار، أو
                                                تعويض؛
                                                بموجب المادة (80)</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="penalty_second" class="form-label">الجزاء ثاني مرة</label>
                                    <select id="penalty_second" name="penalty_second" class="form-select select2"
                                        data-placeholder="اختر الجزاء ثاني مرة">
                                        <option value=""></option>
                                        <optgroup label="نسب مئوية">
                                            <option value="percentage:5">5%</option>
                                            <option value="percentage:10">10%</option>
                                            <option value="percentage:15">15%</option>
                                            <option value="percentage:20">20%</option>
                                            <option value="percentage:25">25%</option>
                                            <option value="percentage:30">30%</option>
                                            <option value="percentage:50">50%</option>
                                            <option value="percentage:75">75%</option>
                                        </optgroup>
                                        <optgroup label="أيام">
                                            <option value="days:1">يوم</option>
                                            <option value="days:2">يومان</option>
                                            <option value="days:3">ثلاثة أيام</option>
                                            <option value="days:4">أربعة أيام</option>
                                            <option value="days:5">خمسة أيام</option>
                                        </optgroup>
                                        <optgroup label="إجراءات أخرى">
                                            <option value="warning:written">إنذار كتابي</option>
                                            <option value="ban:promotion">الحرمان من الترقيات أو العلاوات لمرة واحدة
                                            </option>
                                            <option value="termination:with_benefit_30">فصل من الخدمة مع المكافأة إذا لم
                                                يتجاوز الغياب (30) يوماً</option>
                                            <option value="termination:article_80">فصل من الخدمة طبقاً للمادة (الثمانون) من
                                                نظام العمل</option>
                                            <option value="termination:article_80_10days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرة أيام، في نطاق حكم المادة (80) من نظام
                                                العمل</option>
                                            <option value="termination:article_80_20days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرين يوماً، في نطاق حكم المادة (80) من
                                                نظام العمل</option>
                                            <option value="termination:with_benefit">فصل مع المكافأة</option>
                                            <option value="termination:without_benefit">فصل بدون مكافأة، أو اشعار، أو
                                                تعويض؛
                                                بموجب المادة (80)</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="penalty_third" class="form-label">الجزاء ثالث مرة</label>
                                    <select id="penalty_third" name="penalty_third" class="form-select select2"
                                        data-placeholder="اختر الجزاء ثالث مرة">
                                        <option value=""></option>
                                        <optgroup label="نسب مئوية">
                                            <option value="percentage:5">5%</option>
                                            <option value="percentage:10">10%</option>
                                            <option value="percentage:15">15%</option>
                                            <option value="percentage:20">20%</option>
                                            <option value="percentage:25">25%</option>
                                            <option value="percentage:30">30%</option>
                                            <option value="percentage:50">50%</option>
                                            <option value="percentage:75">75%</option>
                                        </optgroup>
                                        <optgroup label="أيام">
                                            <option value="days:1">يوم</option>
                                            <option value="days:2">يومان</option>
                                            <option value="days:3">ثلاثة أيام</option>
                                            <option value="days:4">أربعة أيام</option>
                                            <option value="days:5">خمسة أيام</option>
                                        </optgroup>
                                        <optgroup label="إجراءات أخرى">
                                            <option value="warning:written">إنذار كتابي</option>
                                            <option value="ban:promotion">الحرمان من الترقيات أو العلاوات لمرة واحدة
                                            </option>
                                            <option value="termination:with_benefit_30">فصل من الخدمة مع المكافأة إذا لم
                                                يتجاوز الغياب (30) يوماً</option>
                                            <option value="termination:article_80">فصل من الخدمة طبقاً للمادة (الثمانون) من
                                                نظام العمل</option>
                                            <option value="termination:article_80_10days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرة أيام، في نطاق حكم المادة (80) من نظام
                                                العمل</option>
                                            <option value="termination:article_80_20days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرين يوماً، في نطاق حكم المادة (80) من
                                                نظام العمل</option>
                                            <option value="termination:with_benefit">فصل مع المكافأة</option>
                                            <option value="termination:without_benefit">فصل بدون مكافأة، أو اشعار، أو
                                                تعويض؛
                                                بموجب المادة (80)</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="penalty_fourth" class="form-label">الجزاء رابع مرة</label>
                                    <select id="penalty_fourth" name="penalty_fourth" class="form-select select2"
                                        data-placeholder="اختر الجزاء رابع مرة">
                                        <option value=""></option>
                                        <optgroup label="نسب مئوية">
                                            <option value="percentage:5">5%</option>
                                            <option value="percentage:10">10%</option>
                                            <option value="percentage:15">15%</option>
                                            <option value="percentage:20">20%</option>
                                            <option value="percentage:25">25%</option>
                                            <option value="percentage:30">30%</option>
                                            <option value="percentage:50">50%</option>
                                            <option value="percentage:75">75%</option>
                                        </optgroup>
                                        <optgroup label="أيام">
                                            <option value="days:1">يوم</option>
                                            <option value="days:2">يومان</option>
                                            <option value="days:3">ثلاثة أيام</option>
                                            <option value="days:4">أربعة أيام</option>
                                            <option value="days:5">خمسة أيام</option>
                                        </optgroup>
                                        <optgroup label="إجراءات أخرى">
                                            <option value="warning:written">إنذار كتابي</option>
                                            <option value="ban:promotion">الحرمان من الترقيات أو العلاوات لمرة واحدة
                                            </option>
                                            <option value="termination:with_benefit_30">فصل من الخدمة مع المكافأة إذا لم
                                                يتجاوز الغياب (30) يوماً</option>
                                            <option value="termination:article_80">فصل من الخدمة طبقاً للمادة (الثمانون) من
                                                نظام العمل</option>
                                            <option value="termination:article_80_10days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرة أيام، في نطاق حكم المادة (80) من نظام
                                                العمل</option>
                                            <option value="termination:article_80_20days">الفصل دون مكافأة، أو تعويض، على
                                                أن
                                                يسبقه إنذار كتابي بعد الغياب مدة عشرين يوماً، في نطاق حكم المادة (80) من
                                                نظام العمل</option>
                                            <option value="termination:with_benefit">فصل مع المكافأة</option>
                                            <option value="termination:without_benefit">فصل بدون مكافأة، أو اشعار، أو
                                                تعويض؛
                                                بموجب المادة (80)</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label for="extra_deduction" class="form-label">ملاحظات إضافية</label>
                                    <textarea type="text" id="extra_deduction" name="extra_deduction" class="form-control"
                                        placeholder="أدخل الملاحظات الإضافية" value="{{ old('extra_deduction') }}"></textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-submit">حفظ </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
