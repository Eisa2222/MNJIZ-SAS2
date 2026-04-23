@extends('layouts.layoutMaster')

@section('title', 'تعديل انتهاك')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li>
        <a href="{{ route('hr.violations-penalties.index') }}">إدارة الانتهاكات و العقوبات</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تعديل انتهاك</a>
        <i class="ti ti-star favorite-icon" data-page-name="تعديل انتهاك" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

<!-- Vendor Styles -->
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
    @vite(['resources/assets/js/violations_penalties_wizard.js'])
    <script>
        $(document).ready(function() {
            // متغيرات لتخزين معلومات الانتهاك والتكرار
            var violationDetails = null;
            var occurrenceInfo = null;

            // دالة لتحديث عرض تفاصيل الانتهاك والتكرار
            function updateViolationDisplay() {
                var detailsHtml = '';

                // إذا لم يتم تحديد انتهاك بعد
                if (!violationDetails && !occurrenceInfo) {
                    detailsHtml += `
                <div class="text-center py-4 my-2">
                    <i class="far fa-clipboard fa-2x text-secondary mb-2"></i>
                    <p class="text-secondary mb-0">يرجى اختيار نوع الانتهاك لعرض تفاصيل العقوبة</p>
                </div>
            `;
                    $('#violation-details').html(detailsHtml);
                    return;
                }

                // عرض تفاصيل الانتهاك إذا كانت متوفرة
                if (violationDetails) {
                    // قسم وصف الانتهاك
                    detailsHtml += `
                <div class="info-section mb-3">
                    <div class="section-header d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        <h6 class="m-0">وصف الانتهاك</h6>
                    </div>
                    <div class="section-body py-3">
                        <p class="mb-0">${violationDetails.description}</p>
                    </div>
                </div>
            `;

                    // قسم العقوبات المحتملة
                    detailsHtml += `
                <div class="info-section mb-3">
                    <div class="section-header d-flex align-items-center">
                        <i class="fas fa-balance-scale text-info me-2"></i>
                        <h6 class="m-0">العقوبات المحتملة</h6>
                    </div>
                    <div class="section-body p-0">
                        <div class="penalties-table">
                            <div class="penalties-header">
                                <div class="penalty-column">المرة الأولى</div>
                                <div class="penalty-column">المرة الثانية</div>
                                <div class="penalty-column">المرة الثالثة</div>
                                <div class="penalty-column">المرة الرابعة</div>
                            </div>
                            <div class="penalties-body">
                                <div class="penalty-column ${getCurrentPenaltyClass(1)}">
                                    ${getPenaltyWithIcon(violationDetails.formatted_penalty_first || 'غير محدد')}
                                </div>
                                <div class="penalty-column ${getCurrentPenaltyClass(2)}">
                                    ${getPenaltyWithIcon(violationDetails.formatted_penalty_second || 'غير محدد')}
                                </div>
                                <div class="penalty-column ${getCurrentPenaltyClass(3)}">
                                    ${getPenaltyWithIcon(violationDetails.formatted_penalty_third || 'غير محدد')}
                                </div>
                                <div class="penalty-column ${getCurrentPenaltyClass(4)}">
                                    ${getPenaltyWithIcon(violationDetails.formatted_penalty_fourth || 'غير محدد')}
                                </div>
                            </div>
                        </div>

                        ${violationDetails.extra_deduction ?
                            `<div class="p-3 border-top">
                                                                        <small>
                                                                            <i class="fas fa-coins text-warning me-1"></i>
                                                                            <strong>خصم إضافي:</strong> ${violationDetails.extra_deduction}
                                                                        </small>
                                                                    </div>` : ''}
                    </div>
                </div>
            `;
                }

                // عرض معلومات التكرار إذا كانت متوفرة
                if (occurrenceInfo) {
                    // تحديد لون ورمز حسب مستوى التكرار
                    var iconClass = 'fa-info-circle text-info';

                    if (occurrenceInfo.count > 0) {
                        if (occurrenceInfo.occurrence === 2) {
                            iconClass = 'fa-exclamation-circle text-warning';
                        } else if (occurrenceInfo.occurrence >= 3) {
                            iconClass = 'fa-exclamation-triangle text-danger';
                        }
                    }

                    detailsHtml += `
                <div class="info-section mb-3">
                    <div class="section-header d-flex align-items-center">
                        <i class="fas fa-history text-primary me-2"></i>
                        <h6 class="m-0">معلومات التكرار</h6>
                    </div>
                    <div class="section-body py-3">
                        <div class="d-flex">
                            <div class="me-3 pt-2">
                                <i class="fas ${iconClass} fa-lg"></i>
                            </div>
                            <div>
                                ${occurrenceInfo.count === 0 ?
                                    `<p class="mb-0">هذه هي المرة الأولى لهذا الانتهاك للموظف المحدد.</p>` :
                                    `<p class="mb-0">تم تسجيل هذا الانتهاك <strong>${occurrenceInfo.count}</strong> مرة سابقة للموظف المحدد.</p>
                                                                             <p class="mb-0">سيتم تسجيل هذا الانتهاك كـ <strong class="occurrence-number">المرة ${getOccurrenceText(occurrenceInfo.occurrence)}</strong> (تكرار رقم <strong>${occurrenceInfo.occurrence}</strong>)</p>`
                                }
                            </div>
                        </div>
                    </div>
                </div>

                <div class="info-section mb-0">
                    <div class="section-header d-flex align-items-center">
                        <i class="fas fa-gavel text-secondary me-2"></i>
                        <h6 class="m-0">العقوبة المطبقة</h6>
                    </div>
                    <div class="section-body py-3">
                        <div >
                            ${getPenaltyWithIcon(occurrenceInfo.penaltyText)}
                        </div>
                    </div>
                </div>
            `;

                    // إضافة حقول مخفية
                    detailsHtml += '<input type="hidden" name="occurrence" value="' + occurrenceInfo.occurrence +
                        '">';
                    detailsHtml += '<input type="hidden" name="penalty_text" value="' + occurrenceInfo
                        .penaltyText + '">';
                }

                // تحديث العرض
                $('#violation-details').html(detailsHtml);
            }

            // دالة مساعدة لتحويل رقم التكرار إلى نص
            function getOccurrenceText(occurrence) {
                switch (occurrence) {
                    case 1:
                        return 'الأولى';
                    case 2:
                        return 'الثانية';
                    case 3:
                        return 'الثالثة';
                    case 4:
                        return 'الرابعة';
                    default:
                        return occurrence;
                }
            }

            // دالة مساعدة لتنسيق العقوبة مع أيقونة مناسبة
            function getPenaltyWithIcon(penalty) {
                if (penalty.includes('%')) {
                    return `<i class="fas fa-percent me-1 text-danger"></i> ${penalty}`;
                } else if (penalty.includes('يوم') || penalty.includes('أيام')) {
                    return `<i class="fas fa-calendar-times me-1 text-danger"></i> ${penalty}`;
                } else if (penalty.includes('إنذار')) {
                    return `<i class="fas fa-exclamation-triangle me-1 text-warning"></i> ${penalty}`;
                } else {
                    return penalty;
                }
            }

            // دالة مساعدة لتحديد فئة خلية العقوبة الحالية
            function getCurrentPenaltyClass(penaltyNumber) {
                if (occurrenceInfo && occurrenceInfo.occurrence === penaltyNumber) {
                    return 'current-penalty';
                }
                return '';
            }

            // دالة التحقق من عدد مرات التكرار
            function checkOccurrenceCount(employeeId, violationId, skipCount = false) {

                // إضافة مؤشر تحميل مؤقت
                $('#violation-details').append(`
            <div id="occurrence-loading" class="text-center py-3 my-2">
                <div class="spinner-border spinner-border-sm text-secondary" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <span class="ms-2">جاري حساب عدد مرات التكرار...</span>
            </div>
        `);

                // استدعاء AJAX للحصول على معلومات التكرار
                $.ajax({
                    url: "{{ route('hr.violations-penalties.get-occurrence-count') }}",
                    type: 'GET',
                    data: {
                        employee_id: employeeId,
                        violation_id: violationId,
                        violation_id_edit: {{ $violation->id }}, // إضافة معرف الانتهاك الحالي للتعديل
                    },
                    success: function(response) {

                        $('#occurrence-loading').remove();

                        if (response.success) {
                            // تخزين معلومات التكرار
                            occurrenceInfo = {
                                count: response.count,
                                occurrence: response.occurrence,
                                penaltyText: response.applicable_penalty || 'غير محدد'
                            };
                        } else {
                            // رسالة خطأ
                            occurrenceInfo = {
                                error: true,
                                message: response.message || 'خطأ غير معروف'
                            };
                        }

                        // تحديث العرض
                        updateViolationDisplay();
                    },
                    error: function(xhr) {
                        console.error('API Error', xhr);
                        $('#occurrence-loading').remove();

                        // تخزين معلومات الخطأ
                        occurrenceInfo = {
                            error: true,
                            message: 'خطأ في استدعاء API: ' + (xhr.responseText || 'خطأ غير معروف')
                        };

                        // تحديث العرض
                        updateViolationDisplay();
                    }
                });
            }

            // التحقق من التكرار عند تغيير الموظف أو نوع الانتهاك
            function updateOccurrenceInfo() {
                var employeeId = $('#employee_id').val();
                var violationId = $('#settings_violation_id').val();

                // إعادة تعيين معلومات التكرار
                occurrenceInfo = null;

                // تحديث العرض بدون معلومات التكرار
                updateViolationDisplay();

                if (employeeId && violationId) {
                    checkOccurrenceCount(employeeId, violationId, true);
                }
            }

            // استماع للتغييرات
            $('#employee_id, #settings_violation_id').on('change', updateOccurrenceInfo);

            // عرض تفاصيل الانتهاك
            function showViolationDetails(violationId) {
                if (!violationId) {
                    // إعادة تعيين معلومات الانتهاك
                    violationDetails = null;
                    updateViolationDisplay();
                    return;
                }

                // إضافة مؤشر تحميل مؤقت
                $('#violation-details').html(`
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-secondary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                        <span class="ms-2">جاري تحميل تفاصيل الانتهاك...</span>
                    </div>
                `);

                $.ajax({
                    url: "{{ route('hr.violations-penalties.details', ['id' => 'id']) }}".replace('id',
                    violationId),
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // تخزين تفاصيل الانتهاك
                            violationDetails = response.data;
                        } else {
                            // إعادة تعيين تفاصيل الانتهاك في حالة الخطأ
                            violationDetails = null;
                        }

                        // تحديث العرض
                        updateViolationDisplay();
                    },
                    error: function(xhr) {
                        console.error('خطأ في استدعاء API:', xhr.responseText);

                        // إعادة تعيين تفاصيل الانتهاك في حالة الخطأ
                        violationDetails = null;

                        // تحديث العرض
                        updateViolationDisplay();
                    }
                });
            }

            // عند تغيير نوع الانتهاك
            $('#settings_violation_id').on('change', function() {
                var violationId = $(this).val();
                showViolationDetails(violationId);
            });

            // تحميل البيانات الأولية عند تحميل الصفحة
            if ($('#settings_violation_id').val()) {
                showViolationDetails($('#settings_violation_id').val());

                // تحميل بيانات التكرار أيضًا إذا كان الموظف محددًا
                if ($('#employee_id').val()) {
                    checkOccurrenceCount($('#employee_id').val(), $('#settings_violation_id').val(), true);
                }
            }

            // إضافة CSS للتنسيقات
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    #violation-details {
                        background-color: #f8f9fa;
                        padding: 1rem;
                        border-radius: 4px;
                    }

                    .info-section {
                        border: 1px solid #e0e0e0;
                        border-radius: 4px;
                        background-color: white;
                        overflow: hidden;
                    }

                    .section-header {
                        background-color: #f8f9fa;
                        padding: 0.7rem 1rem;
                        border-bottom: 1px solid #e0e0e0;
                    }

                    .section-body {
                        padding: 0 1rem;
                    }

                    .penalties-table {
                        width: 100%;
                    }

                    .penalties-header {
                        display: flex;
                        background-color: #f8f9fa;
                        border-bottom: 1px solid #e0e0e0;
                    }

                    .penalties-body {
                        display: flex;
                    }

                    .penalty-column {
                        flex: 1;
                        padding: 0.8rem;
                        text-align: center;
                        border-left: 1px solid #e0e0e0;
                    }

                    .penalty-column:last-child {
                        border-left: none;
                    }

                    .current-penalty {
                        font-weight: bold;
                        background-color: #f0f8ff;
                    }

                    .applied-penalty {
                        text-align: center;
                        font-weight: bold;
                        font-size: 1.1rem;
                        padding: 0.5rem;
                        background-color: #f8f9fa;
                        border-radius: 4px;
                        border: 1px solid #e0e0e0;
                    }

                    .occurrence-number {
                        color: #0d6efd;
                    }
                `)
                .appendTo('head');
        });

        document.addEventListener('DOMContentLoaded', function() {
            const isAppealableCheckbox = document.getElementById('is_appealable');
            const appealDaysInput = document.getElementById('appeal_days');

            function updateAppealDaysState() {
                appealDaysInput.disabled = !isAppealableCheckbox.checked;
            }

            // تنفيذ الدالة عند تحميل الصفحة
            updateAppealDaysState();

            // تنفيذ الدالة عند تغيير حالة الـ checkbox
            isAppealableCheckbox.addEventListener('change', updateAppealDaysState);
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#request-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">بيانات الإنتهاك</span>
                                <span class="bs-stepper-subtitle">تفاصيل الإنتهاك و العقوية</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">

                    <form id="violations-penalties-form"
                        action="{{ route('hr.violations-penalties.update', $violation->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <!-- الخطوة الأولى: بيانات الطلب -->
                        <div id="request-info" class="content">
                            <div class="row g-3">

                                <!-- الموظف -->
                                <div class="col-md-6 mb-3">
                                    <label for="employee_id" class="form-label">
                                        الموظف
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select name="employee_id" id="employee_id" class="form-control select2" required
                                        data-placeholder="اختر الموظف">
                                        <option value=""></option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}"
                                                {{ $violation->employee_id == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- تاريخ الانتهاك -->
                                <div class="col-md-6 mb-3">
                                    <label for="violation_date" class="form-label">تاريخ الانتهاك <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local" name="violation_date" id="violation_date" class="form-control"
                                        value="{{ old('violation_date', $violation->violation_date) }}" max="{{ now()->format('Y-m-d\TH:i') }}"
                                        required>

                                </div>

                                <!-- نوع الانتهاك -->
                                <div class="col-md-12 mb-3">
                                    <label for="settings_violation_id" class="form-label">نوع الانتهاك <span
                                            class="text-danger">*</span></label>
                                    <select name="settings_violation_id" id="settings_violation_id"
                                        class="form-control select2" required data-placeholder="اختر نوع الانتهاك">
                                        <option value=""></option>
                                        @foreach ($categorizedViolations as $category => $violations)
                                            <optgroup label="{{ $category }}">
                                                @foreach ($violations as $violationType)
                                                    <option value="{{ $violationType->id }}"
                                                        {{ $violation->settings_violation_id == $violationType->id ? 'selected' : '' }}>
                                                        {{ $violationType->description }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>

                                </div>


                                <!-- معلومات العقوبة -->
                                <div class="col-md-12 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body" id="violation-details">
                                            <h6 class="card-subtitle mb-2 text-muted">تفاصيل العقوبة</h6>
                                            <p class="card-text text-center text-muted">يرجى اختيار نوع الانتهاك لعرض تفاصيل
                                                العقوبة</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- التظلم -->
                                <div class="col-md-6 mb-3 d-flex align-items-center">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="is_appealable"
                                            name="is_appealable" value="1"
                                            {{ $violation->is_appealable ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="is_appealable">
                                            السماح للموظف بتقديم تظلم على هذا الانتهاك
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="appeal_days" class="form-label">عدد أيام التظلم المسموحة</label>
                                    <input type="number" class="form-control" id="appeal_days" name="appeal_days"
                                        value="{{ old('appeal_days', $violation->appeal_days ?? 5) }}" min="1"
                                        max="30" oninput="if(this.value > 30) this.value = 30;">
                                    <div class="form-text">
                                        عدد الأيام المسموحة للموظف لتقديم تظلم بعد تسجيل الانتهاك.
                                    </div>
                                </div>

                                <!-- ملاحظات -->
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3"
                                        placeholder="قم بإضافة بعض الملاحظات هنا  ....">{{ old('notes', $violation->notes) }}</textarea>
                                </div>


                            </div>

                            <div class="d-flex flex-row-reverse mt-4">
                                <button class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
