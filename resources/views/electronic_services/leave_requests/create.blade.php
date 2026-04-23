@extends('layouts.layoutMaster')

@section('title', 'طلب إجازة جديد')

@section('breadcrumb')
<li><a href="#">الخدمات الإلكترونية</a></li>
<li><a href="{{ route('account.electronic-services.leave-requests.index') }}">طلبات الإجازات </a></li>
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#"> طلب إجازة جديد</a>
    <i class="ti ti-star favorite-icon" data-page-name="طلب إجازة جديد" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

{{-- Vendor Styles --}}
@section('vendor-style')
@vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.scss', 'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
@vite(['resources/assets/vendor/libs/bs-stepper/bs-stepper.js', 'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

{{-- Page Scripts --}}
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
                <form id="leave-request-form" action="{{ route('account.electronic-services.leave-requests.store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="weekly_days_off" value="{{ json_encode($weeklyDaysOff) }}">
                    <div id="request-info" class="content">
                        {{-- الصف الأول --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="leave_type_id">نوع الإجازة</label>
                                <select id="leave_type_id" name="leave_type_id" class="form-select select2"
                                    data-placeholder="اَختر نوع الإجازة" required>
                                    <option value=""></option>
                                    @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}" data-leave-unit-type="{{ $type->leave_unit_type }}"
                                        data-count-weekends="{{ $type->count_weekends }}" {{
                                        old('leave_type_id')==$type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="start_date">تاريخ البداية</label>
                                <input type="date" id="start_date" name="start_date" class="form-control"
                                    value="{{ old('start_date') }}" {{-- min="{{ now()->format('Y-m-d') }}" --}}>
                            </div>
                        </div>

                        {{-- الصف الثاني --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="end_date">تاريخ النهاية</label>
                                <input type="date" id="end_date" name="end_date" class="form-control"
                                    value="{{ old('end_date') }}" {{-- min="{{ now()->format('Y-m-d') }}" --}}>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="calculated_days">عدد الأيام
                                    <i id="days-tooltip" class="fa fa-question-circle text-primary"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="جاري تحميل المعلومات..."></i>
                                </label>
                                <input type="text" id="calculated_days" name="calculated_days" class="form-control"
                                    readonly disabled>
                            </div>
                        </div>

                        {{-- الملاحظات --}}
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label" for="reason">ملاحظات</label>
                                <textarea id="reason" name="reason" class="form-control"
                                    rows="3">{{ old('reason') }}</textarea>
                            </div>
                        </div>


                        <!-- عرض المهام المسندة للموظف -->
                        {{-- @if($employeeTasks['has_tasks'])
                        <div class="col-12 mb-4">
                            <div class="card border-warning shadow-sm">
                                <div class="card-header bg-warning bg-opacity-10 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="card-title mb-1 text-warning d-flex align-items-center">
                                                <i class="ti ti-alert-triangle me-2"></i>
                                                لديك {{ $employeeTasks['tasks_count'] }} {{
                                                $employeeTasks['tasks_count'] == 1 ? 'مهمة مسندة' : 'مهام مسندة' }} إليك
                                            </h5>
                                            <small class="text-muted d-block">
                                                يرجى مراجعة المهام التالية قبل تقديم طلب الإجازة
                                                @if($employeeTasks['high_priority_count'] > 0)
                                                <span class="badge bg-danger ms-2">
                                                    {{ $employeeTasks['high_priority_count'] }} عالية الأولوية
                                                </span>
                                                @endif
                                                @if($employeeTasks['overdue_count'] > 0)
                                                <span class="badge bg-danger ms-2">
                                                    {{ $employeeTasks['overdue_count'] }} متأخرة
                                                </span>
                                                @endif
                                            </small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-warning" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#tasksCollapse"
                                            aria-expanded="true" aria-controls="tasksCollapse">
                                            <i class="ti ti-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="collapse show" id="tasksCollapse">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="border-0 ps-4">المهمة</th>
                                                        <th class="border-0 text-center">الأولوية</th>
                                                        <th class="border-0 text-center">تاريخ الاستحقاق</th>
                                                        <th class="border-0 text-center">الحالة</th>
                                                        <th class="border-0 pe-4">المرتبطة بـ</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($employeeTasks['tasks'] as $task)
                                                    @php
                                                    $isOverdue = $task->due_date && $task->due_date < now();
                                                        $isToday=$task->due_date && $task->due_date->isToday();
                                                        $isTomorrow = $task->due_date && $task->due_date->isTomorrow();
                                                        @endphp
                                                        <tr
                                                            class="{{ $isOverdue ? 'table-danger' : ($isToday ? 'table-warning' : '') }}">
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-start">
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-semibold text-body">{{
                                                                            $task->task_name }}</div>
                                                                        @if($task->description)
                                                                        <small class="text-muted d-block mt-1">
                                                                            {{ Str::limit($task->description, 60) }}
                                                                        </small>
                                                                        @endif
                                                                    </div>
                                                                    @if($isOverdue)
                                                                    <i class="ti ti-alert-circle text-danger ms-2"
                                                                        title="مهمة متأخرة"></i>
                                                                    @elseif($isToday)
                                                                    <i class="ti ti-clock text-warning ms-2"
                                                                        title="مستحقة اليوم"></i>
                                                                    @elseif($isTomorrow)
                                                                    <i class="ti ti-clock-hour-3 text-info ms-2"
                                                                        title="مستحقة غداً"></i>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge bg-{{ $task->priority->color() }} fw-normal">
                                                                    {{ $task->priority->label() }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($task->due_date)
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <span
                                                                        class="{{ $isOverdue ? 'text-danger fw-bold' : ($isToday ? 'text-warning fw-semibold' : 'text-body') }}">
                                                                        {{ $task->due_date->format('Y-m-d') }}
                                                                    </span>
                                                                    <small class="text-muted">
                                                                        @if($isOverdue)
                                                                        متأخر {{ $task->due_date->diffForHumans() }}
                                                                        @elseif($isToday)
                                                                        اليوم
                                                                        @elseif($isTomorrow)
                                                                        غداً
                                                                        @else
                                                                        {{ $task->due_date->diffForHumans() }}
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                                @else
                                                                <span class="text-muted">غير محدد</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($task->status)
                                                                <span class="badge bg-{{ 
                                                    $task->status->value === 'pending' ? 'secondary' : 
                                                    ($task->status->value === 'in_progress' ? 'primary' : 'success')
                                                }} fw-normal">
                                                                    {{ $task->status->label() }}
                                                                </span>
                                                                @else
                                                                <span class="badge bg-secondary fw-normal">غير
                                                                    محدد</span>
                                                                @endif
                                                            </td>
                                                            <td class="pe-4">
                                                                <div class="d-flex align-items-center">
                                                                    @if($task->offer)
                                                                    <i class="ti ti-file-text text-primary me-1"></i>
                                                                    <small class="text-muted">عرض: {{
                                                                        Str::limit($task->offer->name ?? 'غير محدد', 20)
                                                                        }}</small>
                                                                    @elseif($task->contract)
                                                                    <i
                                                                        class="ti ti-file-contract text-success me-1"></i>
                                                                    <small class="text-muted">عقد: {{
                                                                        Str::limit($task->contract->name ?? 'غير محدد',
                                                                        20) }}</small>
                                                                    @elseif($task->project)
                                                                    <i class="ti ti-briefcase text-info me-1"></i>
                                                                    <small class="text-muted">مشروع: {{
                                                                        Str::limit($task->project->name ?? 'غير محدد',
                                                                        20) }}</small>
                                                                    @elseif($task->lawsuit)
                                                                    <i class="ti ti-gavel text-danger me-1"></i>
                                                                    <small class="text-muted">قضية: {{
                                                                        Str::limit($task->lawsuit->name ?? 'غير محدد',
                                                                        20) }}</small>
                                                                    @elseif($task->session)
                                                                    <i
                                                                        class="ti ti-calendar-event text-warning me-1"></i>
                                                                    <small class="text-muted">جلسة: {{
                                                                        Str::limit($task->session->name ?? 'غير محدد',
                                                                        20) }}</small>
                                                                    @elseif($task->powerOfAttorney)
                                                                    <i
                                                                        class="ti ti-file-certificate text-secondary me-1"></i>
                                                                    <small class="text-muted">وكالة: {{
                                                                        Str::limit($task->powerOfAttorney->name ?? 'غير
                                                                        محدد', 20) }}</small>
                                                                    @else
                                                                    <i class="ti ti-clipboard text-muted me-1"></i>
                                                                    <small class="text-muted">مهمة عامة</small>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="border-top">
                                            <div class="alert alert-info border-0 rounded-0 mb-0">
                                                <div class="d-flex align-items-start">
                                                    <i class="ti ti-info-circle me-2 mt-1 text-info"></i>
                                                    <div>
                                                        <strong class="text-info">تنبيه هام:</strong>
                                                        <span class="text-body">
                                                            تأكد من تنسيق هذه المهام مع زملائك أو مديرك المباشر قبل
                                                            البدء في الإجازة لضمان استمرارية العمل.
                                                        </span>
                                                        @if($employeeTasks['overdue_count'] > 0)
                                                        <div class="mt-2">
                                                            <small class="text-danger fw-semibold">
                                                                <i class="ti ti-alert-triangle me-1"></i>
                                                                يوجد لديك {{ $employeeTasks['overdue_count'] }} {{
                                                                $employeeTasks['overdue_count'] == 1 ? 'مهمة متأخرة' :
                                                                'مهام متأخرة' }}،
                                                                يُنصح بإنجازها قبل تقديم طلب الإجازة.
                                                            </small>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif --}}

                        {{-- المرفقات --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label">المرفقات</label>
                                <div id="additional-attachments-container"></div>
                                <button type="button" id="add-attachment" class="btn btn-primary mt-2">
                                    إضافة مرفق
                                </button>
                            </div>
                        </div>

                        {{-- أزرار التنقل --}}
                        <div class="d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
                                <span class="d-sm-inline d-none">السابق</span>
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