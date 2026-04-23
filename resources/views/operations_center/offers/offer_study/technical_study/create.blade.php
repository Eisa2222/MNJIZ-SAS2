@extends('layouts.layoutMaster')

@section('title', 'إضافة الدراسة ')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li><a href="#"> دراسات العروض </a></li>
    <li><a href="#"> الدراسة الفنية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إضافة الدراسة</a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة الدراسة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/shepherd/shepherd.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/operations-center/offer/offer-study/offer-study-validation.js'])
    <script>
        function loadHijriDatePicker() {
            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);
            // بعد تحميل مكتبة التقويم الهجري، تهيئة التقويم
            script.onload = function() {
                initializeHijriPicker();
            };
        }

        function initializeHijriPicker() {
            $(document).ready(function() {
                $(".hijri-picker").hijriDatePicker({
                    hijri: true,
                    showSwitcher: true,
                    useCurrent: false,
                    showClear: true,
                    showTodayButton: true,
                    showClose: true,
                    todayBtn: true,
                    todayHighlight: true,
                });
            });
        }

        window.addEventListener('DOMContentLoaded', loadHijriDatePicker);
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-6">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <!-- الخطوة الأولى -->
                    <div class="step" data-target="#account-details-validation">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">الدراسة الفنية </span>
                                <span class="bs-stepper-subtitle">تفاصيل الدراسة الفنية للعرض</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    @if ($errors->any())
                        <div class="alert alert-danger my-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form id="form" action="{{ route('projects.store') }}" method="POST" onSubmit="return false"
                        enctype="multipart/form-data">
                        @csrf

                        <div id="account-details-validation" class="content">
                            <div class="row g-4">

                                <div class="col-md-6">
                                    <label class="form-label" for="expected_start_date">تاريخ البداية المتوقع <span class="text-danger">*</span></label>
                                    <input type="text" name="expected_start_date" placeholder="تاريخ البداية المتوقع"
                                        id="expected_start_date" class="form-control hijri-picker" autocomplete="off"
                                        onkeydown="return false;" value="{{ old('expected_start_date') }}" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="expected_end_date">تاريخ النهاية المتوقع <span class="text-danger">*</span></label>
                                    <input type="text" name="expected_end_date" placeholder="تاريخ النهاية المتوقع"
                                        id="expected_end_date" class="form-control hijri-picker" autocomplete="off"
                                        onkeydown="return false;" value="{{ old('expected_end_date') }}" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="technical_manager_id">مدير الشؤون الفنية <span class="text-danger">*</span>
                                        <span title="هنا يظهر الموظفين الذين لديهم صلاحيات الإعتماد الفني للمشاريع"
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <select class="select2 form-select" id="technical_manager_id"
                                        name="technical_manager_id" data-placeholder="اختر مدير الشؤون الفنية">
                                        <option value="">اختر مدير الشؤون الفنية</option>

                                        @foreach ($technical_manager_id as $technical_manager)
                                            <option value="{{ $technical_manager['user_id'] }}"
                                                {{ old('technical_manager_id') == $technical_manager['user_id'] ? 'selected' : '' }}>
                                                {{ $technical_manager['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="manager_user_id">مدير المشروع <span class="text-danger">*</span></label>
                                    <select class="select2 form-select" id="manager_user_id" name="manager_user_id"
                                        data-placeholder="اختر مدير المشروع">
                                        <option value="">اختر مدير المشروع</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->user_id }}"
                                                {{ old('manager_user_id') == $employee->user_id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="team_members">فريق المشروع <span class="text-danger">*</span></label>
                                    <select multiple id="team_members" name="team_members[]" class="select2 form-select"
                                        data-placeholder="اختر فريق المشروع">
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->user_id }}"
                                                data-user_id="{{ $employee->user_id }}"
                                                {{ collect(old('team_members'))->contains($employee->user_id) ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="financial_claim">إجمالي المطالبة المالية</label>
                                    <input type="number" min="0" step="0.01" name="financial_claim"
                                        id="financial_claim" class="form-control" placeholder="ادخل إجمالي المطالبة المالية"
                                        value="{{ old('financial_claim') }}" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="non_financial_claim">المطالبة غير المالية</label>
                                    <input type="text" name="non_financial_claim" id="non_financial_claim"
                                        class="form-control" placeholder="ادخل وصف المطالبة غير المالية"
                                        value="{{ old('non_financial_claim') }}" />
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="other_claim">مطالبة اخرى</label>
                                    <input type="text" name="other_claim" id="other_claim" class="form-control"
                                        placeholder="ادخل وصف المطالبة الاخرى" value="{{ old('other_claim') }}" />
                                </div>

                                <div class="col-sm-12">
                                    <label class="form-label" for="description">وصف المشروع</label>
                                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="ادخل وصف المشروع">{{ old('description') }}</textarea>
                                </div>

                                <div class="col-sm-12">
                                    <label class="form-label" for="scope_of_work">نطاق العمل</label>
                                    <textarea name="scope_of_work" id="scope_of_work" class="form-control" rows="4"
                                        placeholder="ادخل نطاق العمل">{{ old('scope_of_work') }}</textarea>
                                </div>


                                <div class="col-12 d-flex justify-content-end">
                                    <button class="btn btn-primary btn-next btn-submit">حفظ</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
