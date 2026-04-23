@extends('layouts.layoutMaster')

@section('title', 'تعديل بيانات الطلب')

@section('breadcrumb')
<li><a href="#">الخدمات الإلكترونية</a></li>
<li><a href="{{ route('account.electronic-services.leave-requests.index') }}">طلبات الإجازات</a></li>
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">تعديل بيانات الطلب</a>
    <i class="ti ti-star favorite-icon" data-page-name="تعديل بيانات الطلب" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

@section('vendor-style')
@vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/electronic-services/leave-requests/leave-request-wizard.js'])
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
                            <span class="bs-stepper-title">بيانات الطلب</span>
                            <span class="bs-stepper-subtitle">نوع الإجازة، التواريخ، المرفقات</span>
                        </span>
                    </button>
                </div>
            </div>
            <div class="bs-stepper-content">
                <form id="leave-request-form"
                    action="{{ route('account.electronic-services.leave-requests.update', $leaveRequest->id) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <input type="hidden" id="weekly_days_off" value="{{ json_encode($weeklyDaysOff) }}">
                    <div id="request-info" class="content">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="leave_type_id" class="form-label">نوع الإجازة</label>
                                <select id="leave_type_id" name="leave_type_id" class="form-select select2" required
                                    data-placeholder="اختر نوع الإجازة">
                                    <option value=""></option>
                                    @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}" data-leave-unit-type="{{ $type->leave_unit_type }}"
                                        data-count-weekends="{{ $type->count_weekends }}" {{ old('leave_type_id',
                                        $leaveRequest->leave_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">تاريخ البداية</label>
                                <input type="date" id="start_date" name="start_date" class="form-control"
                                    value="{{ old('start_date', $leaveRequest->start_date?->format('Y-m-d')) }}"
                                    {{-- min="{{ now()->format('Y-m-d') }}" --}}
                                    >
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">تاريخ النهاية</label>
                                <input type="date" id="end_date" name="end_date" class="form-control"
                                    value="{{ old('end_date', $leaveRequest->end_date?->format('Y-m-d')) }}"
                                    {{-- min="{{ now()->format('Y-m-d') }}" --}}
                                    >
                            </div>
                            <div class="col-md-6">
                                <label for="calculated_days" class="form-label">عدد الأيام
                                    <i id="days-tooltip" class="fa fa-question-circle text-primary"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="جاري تحميل المعلومات..."></i>
                                </label>
                                <input type="text" id="calculated_days" name="calculated_days" class="form-control bg-light"
                                    readonly value="{{ old('calculated_days', $leaveRequest->ذ) }}">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label for="reason" class="form-label">ملاحظات</label>
                                <textarea id="reason" name="reason" rows="3"
                                    class="form-control">{{ old('reason', $leaveRequest->reason) }}</textarea>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">المرفقات</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- عرض المرفقات الحالية -->
                                        @if ($leaveRequest->attachments && $leaveRequest->attachments->count() > 0)
                                        <div class="table-responsive mb-3">
                                            <table class="table table-hover border">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 50px" class="text-center">#</th>
                                                        <th>اسم المرفق</th>
                                                        <th style="width: 130px" class="text-center">الإجراءات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($leaveRequest->attachments as $index => $attachment)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="ti ti-file-text text-primary me-2"></i>
                                                                <span>{{ $attachment->file_name }}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex justify-content-center gap-2">
                                                                <a href="{{ Storage::url($attachment->file_path) }}"
                                                                    target="_blank"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    title="عرض المرفق">
                                                                    <i class="ti ti-eye"></i>
                                                                </a>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-danger toggle-removal"
                                                                    data-attachment-id="{{ $attachment->id }}"
                                                                    title="حذف المرفق">
                                                                    <i class="ti ti-trash"></i>
                                                                </button>
                                                                <input type="checkbox" class="d-none"
                                                                    name="remove_attachments[]"
                                                                    value="{{ $attachment->id }}"
                                                                    id="remove_attachment_{{ $attachment->id }}">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @else
                                        <div class="text-center py-4 rounded mb-3">
                                            <i class="ti ti-file-off text-secondary mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-muted mb-0">لا توجد مرفقات لهذا الطلب</p>
                                        </div>
                                        @endif

                                        <!-- إضافة مرفقات جديدة -->
                                        <div id="additional-attachments-container"></div>
                                        <button type="button" id="add-attachment" class="btn btn-primary mt-2">
                                            إضافة مرفق
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="ti ti-arrow-left ti-xs me-2"></i>السابق
                            </button>
                            <button class="btn btn-primary btn-submit">حفظ</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection