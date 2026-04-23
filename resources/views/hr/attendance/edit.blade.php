@extends('layouts.layoutMaster')

@section('title', 'تعديل سجل حضور')

@section('breadcrumb')
    <li><a href="#">الموارد البشرية</a></li>
    <li><a href="{{ route('attendances.index') }}">الحضور والانصراف</a></li>
    <li class="breadcrumb-item d-flex align-items-center">
        <a href="#">تعديل سجل حضور</a>
        <i class="ti ti-star favorite-icon"
           data-page-name="تعديل سجل حضور"
           data-page-url="{{ url()->current() }}"
           onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/@form-validation/form-validation.scss',
        'resources/assets/vendor/libs/animate-css/animate.scss',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
    ])
    <style>
        /* لإظهار نجمة حقل مطلوب */
        .required label:after {
            content: " *";
            color: #dc3545;
        }
    </style>
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/bs-stepper/bs-stepper.js',
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/@form-validation/popular.js',
        'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
        'resources/assets/vendor/libs/@form-validation/auto-focus.js',
        'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
    ])
@endsection

@section('page-script')
    @vite(['resources/assets/js/attendance-update-wizard.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="wizard-validation" class="bs-stepper mt-2">
                <div class="bs-stepper-header">
                    <div class="step" data-target="#attendance-info">
                        <button type="button" class="step-trigger">
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label mt-1">
                                <span class="bs-stepper-title">بيانات الحضور</span>
                                <span class="bs-stepper-subtitle">البيانات الأساسية</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bs-stepper-content">
                    <form id="attendance-edit-form"
                          action="{{ route('attendances.update', $attendance->id) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div id="attendance-info" class="content">
                            {{-- حقول مخفية للبيانات الأساسية --}}
                            <input type="hidden" name="user_id"              value="{{ $attendance->user_id }}">
                            <input type="hidden" name="scheduled_start_time" value="{{ $attendance->scheduled_start_time ?? '08:00:00' }}">
                            <input type="hidden" name="scheduled_end_time"   value="{{ $attendance->scheduled_end_time   ?? '16:00:00' }}">

                            {{-- حذف حقول حساب الدقائق حيث سيتم التعامل معها في الـ backend --}}

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="employee_name">الموظف</label>
                                    <input type="text"
                                           id="employee_name"
                                           class="form-control"
                                           value="{{ $attendance->user->name ?? 'غير معروف' }}"
                                           disabled>
                                </div>

                                {{-- الحقل الظاهر (معطَّل) + الحقل المخفي للإرسال --}}
                                <div class="col-md-6">
                                    <label class="form-label" for="date_display">تاريخ الحضور</label>

                                    <input type="date"
                                           id="date_display"
                                           class="form-control"
                                           value="{{ $attendance->date->format('Y-m-d') }}"
                                           disabled>

                                    <input type="hidden"
                                           name="date"
                                           value="{{ $attendance->date->format('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4 required">
                                    <label class="form-label" for="day_status">
                                        حالة الحضور
                                        <i id="day-status-tooltip"
                                           class="fa fa-question-circle text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="حالة حضور الموظف في هذا اليوم"></i>
                                    </label>

                                    <select id="day_status"
                                            name="day_status"
                                            class="form-select select2"
                                            required>
                                        <option value="present" {{ $attendance->day_status === 'present' ? 'selected' : '' }}>حضور</option>
                                        <option value="absent"  {{ $attendance->day_status === 'absent'  ? 'selected' : '' }}>غياب</option>
                                        <option value="leave"   {{ $attendance->day_status === 'leave'   ? 'selected' : '' }}>إجازة</option>
                                    </select>
                                </div>

                                <div id="check_in_container" class="col-md-4">
                                    <label class="form-label" for="check_in_time">
                                        وقت الدخول
                                        <i id="check-in-tooltip"
                                           class="fa fa-question-circle text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="وقت تسجيل دخول الموظف"></i>
                                    </label>

                                    <input type="time"
                                           id="check_in_time"
                                           name="check_in_time"
                                           class="form-control"
                                           value="{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '' }}"
                                           {{ $attendance->day_status !== 'present' ? 'disabled' : '' }}
                                           {{ $attendance->day_status === 'present' ? 'required' : '' }}>
                                </div>

                                <div id="check_out_container" class="col-md-4">
                                    <label class="form-label" for="check_out_time">
                                        وقت الخروج
                                        <i id="check-out-tooltip"
                                           class="fa fa-question-circle text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="وقت تسجيل خروج الموظف"></i>
                                    </label>

                                    <input type="time"
                                           id="check_out_time"
                                           name="check_out_time"
                                           class="form-control"
                                           value="{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '' }}"
                                           {{ $attendance->day_status !== 'present' ? 'disabled' : '' }}
                                           {{ $attendance->day_status === 'present' ? 'required' : '' }}>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-12 required">
                                    <label class="form-label" for="edit_reason">
                                        سبب التحديث
                                        <i id="edit-reason-tooltip"
                                           class="fa fa-question-circle text-primary"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top"
                                           title="سبب تحديث بيانات الحضور"></i>
                                    </label>

                                    <textarea id="edit_reason"
                                              name="edit_reason"
                                              class="form-control"
                                              rows="3"
                                              required
                                              placeholder="أدخل سبب ..."></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button class="btn btn-label-secondary btn-prev" disabled>
                                    <i class="ti ti-arrow-right ti-xs me-2"></i>السابق
                                </button>
                                <button class="btn btn-primary btn-submit">تحديث</button>
                            </div>
                        </div> {{-- /content --}}
                    </form>
                </div> {{-- /bs-stepper-content --}}
            </div>
        </div>
    </div>
@endsection
