@extends('layouts.layoutMaster')

@section('title', 'ضبط استكمال الجلسة ')

@section('breadcrumb')
    <li><a href="#">الشؤون القانونية</a></li>
    <li><a href="{{ route('legal-affairs.sessions.index') }}"> الجلسات</a></li>
    <li>
        <a href="{{ route('legal-affairs.sessions.show', $session->id) }}" title="{{ $session->session_name }}">
            {{ Str::limit($session->session_name, 20) }}
        </a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> ضبط استكمال الجلسة </a>
        <i class="ti ti-star favorite-icon" data-page-name="ضبط استكمال الجلسة " data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/legal-affairs/sessions/session-completion/edit-session-completion.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script>
        window.moment = moment;

        function loadHijriDatePicker() {

            var script = document.createElement('script');
            script.src = "{{ asset('assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js') }}";
            document.head.appendChild(script);

            script.onload = function() {
                initializeHijriPicker();
            };
            script.onerror = function() {
                console.error('Failed to load Hijri Datepicker library.');
            };
        }

        function initializeHijriPicker() {
            $(document).ready(function() {
                let todayGregorian = moment().format('YYYY-MM-DD');
                let todayHijri = moment().format('iYYYY-iMM-iDD');
                $(".hijri-picker").hijriDatePicker({
                    hijri: true,
                    showSwitcher: true,
                    useCurrent: false,
                    showClear: true,
                    showTodayButton: true,
                    showClose: true,
                    todayBtn: true,
                    todayHighlight: true,

                    minDate: todayGregorian,
                    hijriMinDate: todayHijri,
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
                    <div class="step" data-target="#basic-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الأساسية</span>
                                <span class="bs-stepper-subtitle">تفاصيل ضبط الجلسة</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"><i class="ti ti-chevron-right"></i></div>
                    <div class="step" data-target="#additional-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">المعلومات الإضافية</span>
                                <span class="bs-stepper-subtitle">تفاصيل إضافية عن ضبط الجلسة</span>
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
                    <form id="form"
                        action="{{ route('legal-affairs.sessions.session-completion.store', $session->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div id="basic-info" class="content  dstepper-block">
                            <div class="row g-3">

                                <input type="hidden" name="lawsuit" value="{{ $session->lawsuit->id }}">

                                <div class="col-md-6 ">
                                    <label for="" class="form-label">المشروع

                                        <span title="تعديل المشروع يتم من المشاريع." style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <input type="text" class="form-control"
                                        value="{{ $session->lawsuit->project->project_name }}" disabled />
                                </div>

                                <div class="col-md-6 ">
                                    <label for="" class="form-label">الدعوى
                                        <span title="تعديل اسم الدعوى يتم من قائمة الدعاوى."
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <input type="text" class="form-control" value="{{ $session->lawsuit->name }}"
                                        disabled />
                                </div>

                                <div class="col-md-6 ">
                                    <label for="" class="form-label">اسم الجلسة
                                        <span title="اسم الجلسة ياتي تلقائي من اسم الدعوة والرقم المتسلسل للجلسة."
                                            style="color: var(--primary-color);">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                                        </span>
                                    </label>
                                    <input type="text" class="form-control" value="{{ $session->session_name }}"
                                        disabled />
                                </div>

                                <div class="col-md-6 ">
                                    <label for="summary_report_status" class="form-label">التقرير الإجمالي</label>
                                    <select id="summary_report_status" name="summary_report_status"
                                        class="form-select select2" data-placeholder="اختر حالة التقرير الإجمالي">
                                        <option value=""></option>
                                        @foreach ($summaryReportStatus as $status)
                                            <option value="{{ $status['id'] }}"
                                                {{ old('summary_report_status', $session->summary_report_status->value) == $status['id'] ? 'selected' : '' }}>
                                                {{ $status['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 " id="last_objection_deadline_container"
                                    style="display: none; position: relative">
                                    <label for="last_objection_deadline" class="form-label">تاريخ آخر مهلة
                                        للاعتراض</label>
                                    <input type="text" id="last_objection_deadline" name="last_objection_deadline"
                                        value="{{ old('last_objection_deadline', $session->hijri_last_objection) }}"
                                        class="form-control hijri-picker" autocomplete="off" onkeydown="return false;">
                                </div>

                                <div class="col-md-6 ">
                                    <label for="session_type" class="form-label"> نوع الجلسة </label>
                                    <select id="session_type" name="session_type" class="form-select select2"
                                        data-placeholder="اختر  نوع الجلسة ">
                                        <option value=""></option>
                                        @foreach ($sessionType as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('session_type', $session->session_type) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 " style="display: none" id="rule_type_container">
                                    <label for="rule_type" class="form-label"> نوع الحكم </label>
                                    <select id="rule_type" name="rule_type" class="form-select select2"
                                        data-placeholder="اختر نوع الحكم">
                                        <option value=""></option>
                                        @foreach ($ruleType as $type)
                                            <option value="{{ $type->id }}"
                                                {{ old('rule_type', $session->rule_type) == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- حقل صيغة التنفيذ (مخفى في البداية) -->
                                <div class="col-md-6 " style="display: none" id="execution_format_container">
                                    <label class="form-label">صيغة تنفيذية</label>

                                    <div class="mt-5">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="execution_format_yes"
                                                name="execution_format" value="yes"
                                                {{ old('execution_format', $session->execution_format) == 'yes' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="execution_format_yes">
                                                نعم
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" id="execution_format_no"
                                                name="execution_format" value="no"
                                                {{ old('execution_format', $session->execution_format) == 'no' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="execution_format_no">
                                                لا
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 " id="expected_execution_date_container"
                                    style="display: none; position: relative">
                                    <label for="expected_execution_date" class="form-label">التاريخ المتوقع
                                        للتنفيذ</label>
                                    <input type="text" id="expected_execution_date" name="expected_execution_date"
                                        value="{{ old('expected_execution_date', $session->hijri_expected_execution) }}"
                                        class="form-control hijri-picker" autocomplete="off" onkeydown="return false;">
                                </div>

                                <!-- دقائق التنفيذ -->
                                <div class="col-md-6 ">
                                    <label for="execution_minutes" class="form-label">دقائق التنفيذ</label>
                                    <input type="number" id="execution_minutes" name="execution_minutes"
                                        class="form-control"
                                        value="{{ old('execution_minutes', $session->execution_minutes) }}" />
                                </div>

                                <!-- الملاحظات -->
                                <div class="col-12 ">
                                    <label for="notes" class="form-label">الملاحظات</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="5">{{ old('notes', $session->notes) }}</textarea>
                                </div>

                            </div>
                            <div class="d-flex justify-content-end mt-4">

                                <button type="button" class="btn btn-primary btn-next">
                                    <span class="align-middle d-sm-inline-block d-none me-sm-2">التالي</span>
                                    <i class="ti ti-arrow-right ti-xs"></i>
                                </button>
                            </div>
                        </div>
                        <!-- المرحلة الثانية: المعلومات الإضافية -->
                        <div id="additional-info" class="content">
                            <p class="text-danger small text-center">
                                سيتم ارسال الاستبيان للعميل بعد حفظ استكمال الجلسة اذا تم ارفاق مرفق ضبط الجلسة او مرفق
                                الحكم
                            </p>
                            <div class="row g-3">

                                <div class="col-md-6 ">
                                    <label for="session_control_attached" class="form-label"> مرفق ضبط الجلسة</label>
                                    <input type="file" id="session_control_attached" name="session_control_attached"
                                        class="form-control" />

                                    @if (isset($session->session_control_attached) && !empty($session->session_control_attached))
                                        <div class="mt-2">
                                            <label>ملف مرفق:</label>
                                            <a href="{{ asset('storage/' . $session->session_control_attached) }}"
                                                target="_blank" class="btn btn-link">
                                                عرض المرفق
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Display Rule Attachment -->
                                <div class="col-md-6 " id="rule_attached_container" style="display: none">
                                    <label for="rule_attached" class="form-label"> مرفق الحكم</label>
                                    <input type="file" id="rule_attached" name="rule_attached"
                                        class="form-control" />

                                    @if (isset($session->rule_attached) && !empty($session->rule_attached))
                                        <div class="mt-2">
                                            <label>ملف مرفق:</label>
                                            <a href="{{ asset('storage/' . $session->rule_attached) }}" target="_blank"
                                                class="btn btn-link">
                                                عرض المرفق
                                            </a>
                                        </div>
                                    @endif
                                </div>

                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-label-secondary btn-prev">
                                    <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                    <span class="align-middle d-sm-inline-block d-none">السابق</span>
                                </button>
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

            function toggleObjectionField() {
                var selectedValue = $('#summary_report_status').val();
                var objectionDeadlineField = $('#last_objection_deadline_container');
                var objectionDeadlineInput = $('#last_objection_deadline');

                // إظهار الحقل إذا كانت القيمة "حكم موضوعي" أو "حكم شكلي"
                if (selectedValue == "substantive_ruling" || selectedValue == "formal_ruling") {

                    // if (selectedValue === 'حكم موضوعي' || selectedValue === 'حكم شكلي') {
                    objectionDeadlineField.show();
                    objectionDeadlineInput.prop('required', true);

                    // الحصول على تاريخ اليوم
                    var today = new Date();
                    today.setDate(today.getDate() + 1);
                    var minDate = today.toISOString().split('T')[0];

                    // تعيين الحد الأدنى للتاريخ المسموح به
                    objectionDeadlineInput.attr('min', minDate);

                } else {
                    objectionDeadlineField.hide();
                    objectionDeadlineInput.prop('required', false);
                    objectionDeadlineInput.val('');
                }
            }

            // استدعاء الوظيفة عند تحميل الصفحة
            toggleObjectionField();

            // استدعاء الوظيفة عند تغيير القيمة
            $('#summary_report_status').change(function() {
                toggleObjectionField();
            });

            //


        });

        $(document).ready(function() {
            function toggleRuleFields() {
                var sessionType = $('#session_type').val();
                if (sessionType == '2') {
                    $('#rule_type_container').show();
                    $('#rule_attached_container').show();
                } else {
                    $('#rule_type_container').hide();
                    $('#rule_attached_container').hide();
                    // يمكنك إعادة تعيين القيم إذا لزم الأمر
                    $('#rule_type').val('').trigger('change');
                }
            }


            toggleRuleFields();

            // عند تغيير القيمة في حقل نوع الجلسة
            $('#session_type').change(function() {
                toggleRuleFields();
            });


            // نوع الحكم و الصيغة التنفيذية
            const ruleTypeSelect = $('#rule_type');
            const executionFormatContainer = $('#execution_format_container');

            function toggleExecutionFormat() {
                if (ruleTypeSelect.val() == '1') {
                    executionFormatContainer.show();
                    $('input[name="execution_format"]').attr('required', true);

                } else {
                    executionFormatContainer.hide();
                    $('input[name="execution_format"]').removeAttr('required');
                    $('input[name="execution_format"]').prop('checked', false);

                    $('#expected_execution_date_container').hide();
                    $('#expected_execution_date').val('');
                }
            }

            // استدعاء الدالة عند تحميل الصفحة للتحقق من الحالة الحالية
            toggleExecutionFormat();

            // الاستماع إلى حدث التغيير على اختيار نوع الحكم باستخدام select2
            ruleTypeSelect.on('change', function() {
                toggleExecutionFormat();
            });
        });

        $(document).ready(function() {

            function checkExecutionFormat(showAlert = false) {
                var executionFormat = $('input[name="execution_format"]:checked').val();


                if (executionFormat === 'yes') {
                    $('#expected_execution_date_container').hide();
                    $('#expected_execution_date').val('');
                    if (showAlert) {
                        Swal.fire({
                            title: 'هل تريد إغلاق الدعوى',
                            text: '  عند اختيار صيغة تنفيذية يتم اغلاق الدعوى . لا يمكن التراجع عن هذا الإجراء!',
                            icon: 'warning',
                            showCancelButton: true, // يعرض زر الإلغاء
                            showConfirmButton: true, // يعرض زر التأكيد
                            showDenyButton: false, // لا يعرض زر الرفض
                            buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                            customClass: {
                                popup: 'custom-popup', // تخصيص شكل النافذة
                                title: 'custom-title', // تخصيص شكل العنوان
                                text: 'custom-text', // تخصيص شكل النص
                                confirmButton: 'btn btn-success custom-confirm', // تخصيص زر التأكيد
                                cancelButton: 'btn btn-danger custom-cancel' // تخصيص زر الإلغاء
                            },
                            confirmButtonText: 'تأكيد',
                            cancelButtonText: 'إلغاء',
                            reverseButtons: false // لعكس ترتيب الأزرار
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // إذا أكد المستخدم، يتم تحديد الخيار "نعم"
                                $('#execution_format_yes').prop('checked', true);
                                toastr.info('سيتم اغلاق الدعوى بعد استكمال الجلسة');
                            } else {
                                // إذا ألغى المستخدم، إزالة التحديد
                                $('input[name="execution_format"]').prop('checked', false);

                            }
                        });
                    }
                } else if (executionFormat === 'no') {
                    $('#expected_execution_date_container').show();
                }
            }


            checkExecutionFormat();

            // تشغيل الدالة عند تغيير قيمة execution_format
            $('input[name="execution_format"]').change(function() {
                checkExecutionFormat();
            });
        });
    </script>
@endsection
