@extends('layouts.layoutMaster')

@section('title', 'إضافة جلسة ')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li><a href="{{ route('legal-affairs.sessions.index') }}"> الجلسات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إضافة جلسة </a>
        <i class="ti ti-star favorite-icon" data-page-name="إضافة جلسة " data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/hijri-date/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" />
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/legal-affairs/sessions/create.js'])

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
    <!-- Form Wizard -->
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <!-- مراحل النموذج -->
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل الجلسة </span>
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
                    <form id="form" action="{{ route('legal-affairs.sessions.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div id="basic-info" class="content  dstepper-block">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="project_id" class="form-label">المشروع
                                        <span title="تظهر فقط المشاريع التي لها دعاوى نشطة."
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>

                                    <select id="project_id" name="project_id" class="form-select select2" required
                                        data-placeholder="اختر المشروع">
                                        <option value=""></option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}">
                                                {{ $project->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="lawsuit_id" class="form-label">
                                        الدعوى
                                        <span title="تظهر فقط الدعاوى النشطة." style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                        <span id="lawsuits-count" class="badge bg-label-info ms-1"
                                            style="display: none;"></span>
                                    </label>
                                    <div class="position-relative">
                                        <select id="lawsuit_id" name="lawsuit_id" class="form-select select2" required
                                            data-placeholder="اختر الدعوى" disabled>
                                            <option value="">اختر المشروع أولاً</option>
                                        </select>
                                        <!-- Loader للدعاوى -->
                                        <div id="lawsuits-loader"
                                            class="position-absolute top-50 end-0 translate-middle-y me-3"
                                            style="display: none;">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">جاري التحميل...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="session_name" class="form-label">اسم الجلسة</label>
                                    <input type="text" id="session_name" disabled name="session_name"
                                        class="form-control" value="{{ old('session_name') }}" readonly
                                        placeholder="سيتم إنشاؤه تلقائياً" />
                                </div>

                                <div class="col-md-6">
                                    <label for="assigned_to" class="form-label">
                                        المكلفين
                                        <span id="employees-count" class="badge bg-label-success ms-1"
                                            style="display: none;"></span>
                                        <span title="يظهر فقط المكلفين الذين تم إضافتهم في فريق العمل الخاص بالدعوى."
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <div class="position-relative">
                                        <select id="assigned_to" name="assigned_to[]" class="w-100 selectpicker"
                                            data-style="btn-default" required multiple data-actions-box="true"
                                            data-live-search="true" data-placeholder="اختر المكلفين" disabled>
                                            <option value="">اختر الدعوى أولاً</option>
                                        </select>
                                        <!-- Loader للمكلفين -->
                                        <div id="employees-loader"
                                            class="position-absolute top-50 end-0 translate-middle-y me-3"
                                            style="display: none; z-index: 1060;">
                                            <div class="spinner-border spinner-border-sm text-success" role="status">
                                                <span class="visually-hidden">جاري التحميل...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6  ">
                                    <label for="entity_ranks_id" class="form-label"> درجة الجهة </label>
                                    <select id="entity_ranks_id" name="entity_ranks_id" class="form-select select2"
                                        required data-placeholder="اختر  درجة الجهة ">
                                        <option value=""></option>
                                        @foreach ($settings_entity_ranks as $ranks)
                                            <option value="{{ $ranks->id }}"
                                                {{ old('entity_ranks_id') == $ranks->id ? 'selected' : '' }}>
                                                {{ $ranks->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3  " style="position: relative">
                                    <label for="session_date" class="form-label">التاريخ </label>
                                    <input type="text" id="session_date" name="session_date"
                                        class="form-control hijri-picker" value="{{ old('session_date') }}" required
                                        autocomplete="off" onkeydown="return false;" />
                                </div>

                                <div class="col-md-3  ">
                                    <label for="session_time" class="form-label">الوقت </label>
                                    <input type="time" id="session_time" name="session_time" class="form-control"
                                        value="{{ old('session_time') }}" required />
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary btn-submit">حفظ</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            // دالة لتنظيف selectpicker
            function resetSelectPicker(selectElement, placeholder, disabled = true) {
                selectElement.selectpicker('destroy');
                selectElement.empty();
                selectElement.append(`<option value="" disabled>${placeholder}</option>`);
                selectElement.prop('disabled', disabled);
                selectElement.selectpicker({
                    style: 'btn-default',
                    actionsBox: true,
                    liveSearch: true,
                    title: placeholder,
                    noneSelectedText: placeholder
                });
            }

            // عند تغيير المشروع
            $('#project_id').on('change', function() {
                const projectId = $(this).val();
                const lawsuitSelect = $('#lawsuit_id');
                const assignedSelect = $('#assigned_to');
                const sessionNameInput = $('#session_name');
                const lawsuitsLoader = $('#lawsuits-loader');
                const lawsuitsCount = $('#lawsuits-count');
                const employeesCount = $('#employees-count');

                // تنظيف فوري لجميع الحقول
                lawsuitSelect.empty().append('<option value="">جاري التحميل...</option>');
                sessionNameInput.val('');
                lawsuitsCount.hide();
                employeesCount.hide();

                // تنظيف المكلفين فوراً
                resetSelectPicker(assignedSelect, 'اختر الدعوى أولاً', true);

                if (projectId) {
                    // إظهار loader
                    lawsuitsLoader.show();
                    lawsuitSelect.prop('disabled', true);

                    // جلب الدعاوى
                    $.ajax({
                        url: '{{ route('legal-affairs.sessions.get-lawsuits') }}',
                        type: 'GET',
                        data: {
                            project_id: projectId
                        },
                        success: function(response) {
                            // إخفاء loader
                            lawsuitsLoader.hide();

                            lawsuitSelect.empty().append(
                                '<option value="">اختر الدعوى</option>');

                            if (response.lawsuits && response.lawsuits.length > 0) {
                                $.each(response.lawsuits, function(index, lawsuit) {
                                    lawsuitSelect.append(
                                        `<option value="${lawsuit.id}">${lawsuit.name}</option>`
                                    );
                                });
                                lawsuitSelect.prop('disabled', false);

                                // إظهار عدد الدعاوى
                                lawsuitsCount.text(`${response.lawsuits.length} دعوى`)
                                    .removeClass('bg-label-warning bg-label-danger').addClass(
                                        'bg-label-info').show();

                                toastr.success(
                                    `تم تحميل ${response.lawsuits.length} دعوى بنجاح`);

                            } else {
                                lawsuitSelect.append(
                                    '<option value="">لا توجد دعاوى متاحة</option>');
                                lawsuitsCount.text('0 دعوى').removeClass(
                                    'bg-label-info bg-label-danger').addClass(
                                    'bg-label-warning').show();

                                toastr.warning('لا توجد دعاوى متاحة لهذا المشروع');
                            }

                            // تحديث Select2
                            lawsuitSelect.select2({
                                placeholder: 'اختر الدعوى',
                                allowClear: true,
                                width: '100%',
                                language: 'ar',
                                dir: 'rtl'
                            });
                        },
                        error: function(xhr, status, error) {
                            // إخفاء loader
                            lawsuitsLoader.hide();

                            lawsuitSelect.empty().append(
                                '<option value="">خطأ في تحميل البيانات</option>');
                            lawsuitsCount.text('خطأ').removeClass(
                                'bg-label-info bg-label-warning').addClass(
                                'bg-label-danger').show();

                            toastr.error('حدث خطأ أثناء تحميل الدعاوى');
                        }
                    });
                } else {
                    // إذا لم يتم اختيار مشروع أو تم مسح الاختيار
                    lawsuitSelect.empty().append('<option value="">اختر المشروع أولاً</option>');
                    lawsuitSelect.prop('disabled', true);

                    // إعادة تعيين المكلفين إلى الحالة الافتراضية
                    resetSelectPicker(assignedSelect, 'اختر المشروع أولاً', true);

                    lawsuitsCount.hide();
                    employeesCount.hide();
                }
            });

            // عند تغيير الدعوى
            $('#lawsuit_id').on('change', function() {
                const lawsuitId = $(this).val();
                const assignedSelect = $('#assigned_to');
                const sessionNameInput = $('#session_name');
                const employeesLoader = $('#employees-loader');
                const employeesCount = $('#employees-count');

                if (lawsuitId) {
                    // إظهار loader
                    employeesLoader.show();

                    // تنظيف وتعطيل المكلفين
                    resetSelectPicker(assignedSelect, 'جاري التحميل...', true);

                    // جلب المكلفين واسم الجلسة
                    $.ajax({
                        url: '{{ route('legal-affairs.sessions.get-lawsuit-details') }}',
                        type: 'GET',
                        data: {
                            lawsuit_id: lawsuitId
                        },
                        success: function(response) {
                            // إخفاء loader
                            employeesLoader.hide();

                            // تدمير selectpicker
                            assignedSelect.selectpicker('destroy');
                            assignedSelect.empty();

                            if (response.employees && response.employees.length > 0) {
                                // إضافة الخيارات
                                $.each(response.employees, function(index, employee) {
                                    const isManager = response.manager_id == employee
                                        .id;
                                    const label = employee.name + (isManager ?
                                        ' - مدير المشروع' : '');

                                    assignedSelect.append(
                                        $('<option></option>')
                                        .attr('value', employee.user_id)
                                        .prop('selected', isManager)
                                        .text(label)
                                    );
                                });

                                assignedSelect.prop('disabled', false);

                                // إظهار عدد المكلفين
                                employeesCount.text(`${response.employees.length} موظف`)
                                    .removeClass('bg-label-warning bg-label-danger')
                                    .addClass('bg-label-success')
                                    .show();

                                toastr.success(
                                    `تم تحميل ${response.employees.length} موظف بنجاح`);

                            } else {
                                // لا يوجد موظفون
                                assignedSelect.append(
                                    '<option value="" disabled>لا يوجد مكلفون متاحون</option>'
                                );

                                employeesCount.text('0 موظف')
                                    .removeClass('bg-label-success bg-label-danger')
                                    .addClass('bg-label-warning')
                                    .show();

                                toastr.warning('لا يوجد مكلفون متاحون لهذه الدعوى');
                            }

                            // إعادة تهيئة selectpicker
                            assignedSelect.selectpicker({
                                style: 'btn-default',
                                actionsBox: true,
                                liveSearch: true,
                                title: 'اختر المكلفين',
                                noneSelectedText: 'لم يتم اختيار أي مكلف',
                                selectedTextFormat: 'count > 2'
                            });

                            // تحديث اسم الجلسة
                            if (response.session_name) {
                                sessionNameInput.val(response.session_name);
                            }
                        },
                        error: function(xhr, status, error) {
                            // إخفاء loader
                            employeesLoader.hide();

                            resetSelectPicker(assignedSelect, 'خطأ في تحميل البيانات', true);

                            employeesCount.text('خطأ')
                                .removeClass('bg-label-success bg-label-warning')
                                .addClass('bg-label-danger')
                                .show();

                            toastr.error('حدث خطأ أثناء تحميل المكلفين');
                        }
                    });

                } else {
                    // إذا لم يتم اختيار دعوى أو تم مسح الاختيار
                    resetSelectPicker(assignedSelect, 'اختر الدعوى أولاً', true);
                    sessionNameInput.val('');
                    employeesCount.hide();
                }
            });

            // التأكد من تنظيف المكلفين عند تحميل الصفحة
            $(window).on('load', function() {
                if (!$('#project_id').val()) {
                    resetSelectPicker($('#assigned_to'), 'اختر المشروع أولاً', true);
                }
            });
        });
    </script>
@endsection
