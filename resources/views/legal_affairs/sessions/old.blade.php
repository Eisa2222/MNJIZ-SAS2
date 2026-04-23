@extends('layouts.layoutMaster')

@section('title', 'إضافة جلسة جديدة ')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li><a href="{{ route('legal-affairs.lawsuits.index') }}"> الدعاوى</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إضافة جلسة جديدة </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة جلسة جديدة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/typeahead-js/typeahead.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])

    {{-- التاريخ الهجري   --}}
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
    {{-- التاريخ الهجري   --}}
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/typeahead-js/typeahead.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/form-validation.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });


        });
        window.moment = moment;

        function loadHijriDatePicker() {
            // تحميل مكتبة التقويم الهجري بعد التأكد من تحميل مكتبة moment.js
            if (typeof window.moment === 'undefined') {
                console.error('Moment.js is not loaded. Please check the script path.');
                return;
            }

            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);

            // بعد تحميل مكتبة التقويم الهجري، تهيئة التقويم
            script.onload = function() {
                initializeHijriPicker(); // استدعاء تهيئة التقويم
            };
            script.onerror = function() {
                console.error('Failed to load Hijri Datepicker library.');
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
                    // format: 'iYYYY-iMM-iDD'
                });
            });
        }

        window.addEventListener('DOMContentLoaded', loadHijriDatePicker);
    </script>
@endsection

@section('content')
    <style>
        .was-validated .form-control:valid,
        .form-control.is-valid {
            border-color: var(--primary-color) !important;
            border-width: 1px;
        }
    </style>
    <div class="card mb-6">

        <div class="card-body pt-4">
            @if ($errors->any())
                <div class="alert alert-danger mt-2">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="needs-validation" novalidate action="{{ route('legal-affairs.sessions.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="row g-3">

                    <div class="col-md-6 ">
                        <label for="" class="form-label">المشروع</label>
                        <input type="text" class="form-control" disabled />

                    </div>

                    <div class="col-md-6 ">
                        <label for="" class="form-label">الدعوى</label>
                        <input type="text" class="form-control" disabled />

                    </div>

                    <div class="col-md-6 ">
                        <label for="" class="form-label">اسم الجلسة</label>
                        <input type="text" class="form-control" disabled />
                    </div>

                    <!-- المكلفين -->
                    <div class="col-md-6 ">
                        <label for="assigned_to" class="form-label">المكلفين
                            <span title="يظهر فقط المكلفين الذين تم اضافتهم في فريق العمل الخاص بالدعوى."
                                style="color: var(--primary-color);">
                                <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                            </span>
                        </label>
                        {{-- <select id="assigned_to" name="assigned_to[]" class="w-100 selectpicker" data-style="btn-default"
                            required multiple data-actions-box="true" data-live-search="true"
                            data-placeholder="اختر المكلفين">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->user_id }}"
                                    {{ old('assigned_to') == $employee->user_id ||
                                    (!old('assigned_to') && $lawsuit->project->manager_user->id == $employee->user_id)
                                        ? 'selected'
                                        : '' }}>
                                    {{ $employee->name }}
                                    @if ($lawsuit->project->manager_user->id == $employee->user_id)
                                        - مدير المشروع
                                    @endif
                                </option>
                            @endforeach
                        </select> --}}
                        <div class="invalid-feedback">حقل المكلفين مطلوب</div>
                    </div>

                    <div class="col-md-6  ">
                        <label for="entity_ranks_id" class="form-label"> درجة الجهة </label>
                        <select id="entity_ranks_id" name="entity_ranks_id" class="form-select select2" required
                            data-placeholder="اختر  درجة الجهة ">
                            <option value=""></option>
                            @foreach ($settings_entity_ranks as $ranks)
                                <option value="{{ $ranks->id }}"
                                    {{ old('entity_ranks_id') == $ranks->id ? 'selected' : '' }}>
                                    {{ $ranks->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">درجة الجهة مطلوبة</div>
                    </div>

                    <!-- التاريخ  -->
                    <div class="col-md-3  " style="position: relative">
                        <label for="session_date" class="form-label">التاريخ </label>
                        <input type="text" id="session_date" name="session_date" class="form-control hijri-picker"
                            value="{{ old('session_date') }}" required autocomplete="off" onkeydown="return false;" />
                        <div class="invalid-feedback">التاريخ مطلوب</div>
                    </div>

                    <div class="col-md-3  ">
                        <label for="session_time" class="form-label">الوقت </label>
                        <input type="time" id="session_time" name="session_time" class="form-control"
                            value="{{ old('session_time') }}" required />
                        <div class="invalid-feedback">الوقت مطلوب</div>
                    </div>

                    {{-- <div class="col-md-6  ">
                    <label for="session_type" class="form-label"> نوع الجلسة</label>
                    <input type="text" id="session_type" name="session_type" class="form-control"
                        value="{{ old('session_type') }}" required />
                    <div class="invalid-feedback"> نوع الجلسة مطلوبة </div>
                </div> --}}

                    {{-- <div class="col-md-6  ">
                        <label for="session_type" class="form-label"> نوع الجلسة </label>
                        <select id="session_type" name="session_type" class="form-select select2" required
                            data-placeholder="اختر  نوع الجلسة ">
                            <option value=""></option>
                            @foreach ($sessionType as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('session_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">نوع الجلسة مطلوبة</div>
                    </div> --}}

                    {{-- اهمية الجلسة --}}
                    {{-- <div class="col-md-6  ">
                    <label for="session_importance" class="form-label">أهمية الجلسة</label>
                    <select id="session_importance" name="session_importance" class="form-select select2" required
                        data-placeholder="اختر أهمية الجلسة">
                        <option value="منخفضة" {{ old('session_importance')=='منخفضة' ? 'selected' : '' }}>منخفضة
                        </option>
                        <option value="عادية" {{ old('session_importance')=='عادية' ? 'selected' : '' }}>عادية
                        </option>
                        <option value="مرتفعة" {{ old('session_importance', 'مرتفعة' )=='مرتفعة' ? 'selected' : '' }}>
                            مرتفعة</option>
                    </select>
                    <div class="invalid-feedback"> أهمية الجلسة مطلوبة</div>
                </div> --}}

                </div>
                <div class="mt-2 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-3 waves-effect waves-light">حفظ</button>
                </div>
            </form>

        </div>
    </div>

@endsection
