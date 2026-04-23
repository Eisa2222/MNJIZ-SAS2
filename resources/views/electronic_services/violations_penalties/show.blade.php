@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الانتهاك')


@section('breadcrumb')
    <li><a href="#">الخدمات الإلكترونية</a></li>
    <li><a href="{{ route('account.electronic-services.violations-penalties.index') }}">الإنتهاكات و العقوبات</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل الانتهاك </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الانتهاك" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

<style>
    /* تحسينات شكل الصفحة */
    .violation-id-badge {
        font-size: 0.95rem;
        min-width: 42px;
        text-align: center;
    }

    .card {
        transition: all 0.3s;
        border-radius: 8px;
    }

    .card-header {
        border-radius: 8px 8px 0 0 !important;
        background-color: #fdfdfd;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .penalties-table {
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .penalties-header {
        display: flex;
        background-color: rgba(var(--bs-primary-rgb), 0.05);
        font-weight: bold;
    }

    .penalties-body {
        display: flex;
    }

    .penalty-column {
        flex: 1;
        padding: 0.8rem;
        text-align: center;
        border-left: 1px solid rgba(0, 0, 0, 0.1);
    }

    .penalty-column:last-child {
        border-left: none;
    }

    .current-penalty {
        font-weight: bold;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-bottom: 3px solid var(--primary-color);
    }

    .penalty-content {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 2.5rem;
    }

    dl.row dd,
    dl.row dt {
        margin-bottom: 0.5rem;
        padding: 0.5rem 0;
    }

    .badge {
        font-size: 0.85rem;
        padding: 0.5rem 0.8rem;
    }


    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 1.5rem;
    }

    /* تحسين عرض الحالة */
    .status-badge {
        border-radius: 30px;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
</style>

@section('content')

    <div class="row g-3">
        <div class="col-12 col-md-8 mb-3">
            <div class="card">
                <div class="card-header py-4">

                    <h6 class="card-title mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل الإنتهاك
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped  small text-center">
                            <tr>
                                <td class="fw-bold">المرجع</td>
                                <td>{{ $violation->reference_number }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الموظف</td>
                                <td>
                                    <a href="{{ route('account.employee.profile', $violation->employee_id) }}">
                                        {{ $violation->employee->getRawNameAttribute() }}
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">تصنيف الإنتهاك</td>
                                <td>{{ $violation->violationType->category->name ?? 'غير محدد' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold"> الإنتهاك</td>
                                <td>{{ $violation->violationType->description ?? 'غير محدد' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold"> تكرار الإنتهاك</td>
                                <td>
                                    <span class="badge bg-label-primary rounded-pill fw-bold">المرة
                                        {{ $violation->occurrence }}</span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold"> تاريخ الإنتهاك</td>
                                <td> {{ $violation->violation_date ? $violation->violation_date : 'غير محدد' }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">حالة التظلم</td>
                                <td>
                                    @if ($violation->is_appealable)
                                        <div class="appeal-status text-success">
                                            <i class="ti ti-check-circle"></i>
                                            يسمح بالتظلم ({{ $violation->appeal_days }} أيام)
                                        </div>
                                    @else
                                        <div class="appeal-status text-danger fw-bold">
                                            <i class="ti ti-ban"></i>
                                            لا يسمح بالتظلم
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">المدة المتاحة للتظلم</td>
                                <td>
                                    @if ($violation->is_appealable)
                                        <div class="plan-statistics">
                                            <div class="progress rounded mb-1">
                                                <div class="progress-bar rounded" role="progressbar"
                                                    style="width: {{ $violation->getAppealProgressPercentage() }}%"
                                                    aria-valuenow="{{ $violation->getAppealProgressPercentage() }}"
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>

                                            @if ($violation->getRemainingAppealDays() > 0)
                                                <small>متبقي {{ $violation->getRemainingAppealDays() }}
                                                    {{ $violation->getRemainingAppealDays() === 1 ? 'يوم' : 'أيام' }}
                                                    للتظلم من أصل {{ $violation->appeal_days }}
                                                    {{ $violation->appeal_days === 1 ? 'يوم' : 'أيام' }}</small>
                                            @else
                                                <small class="text-danger">انتهت فترة التظلم</small>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>



                            <tr>
                                <td class="fw-bold">ملاحظات</td>
                                <td>
                                    {{ $violation->notes ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">الحالة</td>
                                <td>{{ $violation->status->label() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-logs text-warning me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    <ul class="timeline ">
                        <li class="timeline-item">
                            <span class="timeline-point bg-primary"></span>
                            <div class="timeline-event">
                                <small class="timeline-title text-capitalize">
                                    تمت الاضافة بتاريخ
                                </small>
                                <small class="text-muted d-block">
                                    {{ $violation->created_at }}
                                </small>
                                <small>
                                    <b>اضيف بواسطة</b>
                                    <a href="{{ route('account.employee.profile', $violation->created_by) }}">
                                        {{ $violation->createdBy->getRawNameAttribute() }}
                                    </a>
                                </small>
                            </div>
                        </li>

                        @if ($violation->updated_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-warning"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                        اخر تحديث بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $violation->updated_at }}
                                    </small>
                                    <small>
                                        <b>التعديل بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $violation->updated_by) }}">
                                            {{ $violation->updatedBy->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif

                        @if ($violation->reviewed_by)
                            <li class="timeline-item">
                                <span class="timeline-point bg-warning"></span>
                                <div class="timeline-event">
                                    <small class="timeline-title text-capitalize">
                                         تمت المراجعة بتاريخ
                                    </small>
                                    <small class="text-muted d-block">
                                        {{ $violation->reviewed_at }}
                                    </small>
                                    <small>
                                        <b>المراجعة بواسطة</b>
                                        <a href="{{ route('account.employee.profile', $violation->reviewed_by) }}">
                                            {{ $violation->reviewedBy->getRawNameAttribute() }}
                                        </a>
                                    </small>
                                </div>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-2">
        <div class="col-md-8">
            <div class="row g-2">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header d-flex align-items-center py-4">
                            <i class="ti ti-gavel text-warning me-2"></i>
                            <h6 class="card-title mb-0">العقوبات المترتبة</h6>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>
                        <div class="card-body p-4 small">
                            <div class="row">
                                <div class="col-12">
                                    <div class="penalties-table">
                                        <div class="penalties-header">
                                            <div class="penalty-column">المرة الأولى</div>
                                            <div class="penalty-column">المرة الثانية</div>
                                            <div class="penalty-column">المرة الثالثة</div>
                                            <div class="penalty-column">المرة الرابعة</div>
                                        </div>
                                        <div class="penalties-body">
                                            <div
                                                class="penalty-column {{ $violation->occurrence == 1 ? 'current-penalty' : '' }}">
                                                <div class="penalty-content">
                                                    {!! $violation->violationType->formatted_penalty_with_icon($violation->violationType->penalty_first) !!}
                                                </div>
                                            </div>
                                            <div
                                                class="penalty-column {{ $violation->occurrence == 2 ? 'current-penalty' : '' }}">
                                                <div class="penalty-content">
                                                    {!! $violation->violationType->formatted_penalty_with_icon($violation->violationType->penalty_second) !!}
                                                </div>
                                            </div>
                                            <div
                                                class="penalty-column {{ $violation->occurrence == 3 ? 'current-penalty' : '' }}">
                                                <div class="penalty-content">
                                                    {!! $violation->violationType->formatted_penalty_with_icon($violation->violationType->penalty_third) !!}
                                                </div>
                                            </div>
                                            <div
                                                class="penalty-column {{ $violation->occurrence == 4 ? 'current-penalty' : '' }}">
                                                <div class="penalty-content">
                                                    {!! $violation->violationType->formatted_penalty_with_icon($violation->violationType->penalty_fourth) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تقديم تظلم بتصميم محسّن -->
                @if ($violation->status->value == 'under_appeal' && $violation->getRemainingAppealDays() != 0)
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header d-flex align-items-center py-4">
                            <i class="ti ti-pencil text-warning me-2"></i>
                            <h6 class="card-title mb-0">تقديم تظلم</h6>
                        </div>
                        <div class="border-1 border-light border-dashed mb-2"></div>
                        <div class="card-body p-4">
                            <form action="{{ route('account.electronic-services.violations-penalties.appeal', $violation->id) }}"
                                method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="appeal_reason" class="form-label fw-semibold">سبب التظلم <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control  @error('appeal_reason') is-invalid @enderror" id="appeal_reason" name="appeal_reason"
                                        rows="5" placeholder="اشرح أسباب تظلمك من هذا الانتهاك..." required>{{ old('appeal_reason') }}</textarea>
                                    @error('appeal_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="text-danger mt-1 small">
                                        <i class="ti ti-info-circle me-1"></i> يرجى توضيح أسباب تظلمك بوضوح وتقديم أي
                                        معلومات
                                        إضافية قد تكون مفيدة.
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        تقديم التظلم
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif($violation->status->value == 'under_appeal' && $violation->getRemainingAppealDays() == 0)
                    <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm p-3">
                        <i class="ti ti-alert-triangle fs-5 me-2"></i>
                        <div>انتهت فترة التظلم المسموح بها لهذا الانتهاك.</div>
                    </div>
                @endif

                @if ($violation->appeal)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex justify-content-between py-4">
                                <div class="d-flex items-center">
                                    <i class="ti ti-scale text-warning me-2"></i>
                                    <h6 class="card-title mb-0">التظلم</h6>
                                </div>
                                <div>
                                    <span
                                        class="badge bg-{{ $violation->appeal->status->color() }}">{{ $violation->appeal->status->label() }}</span>
                                </div>
                            </div>
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <div class="card-body p-4 small">
                                <div class="mb-3">
                                    <p class="fw-bold d-flex align-items-center mb-2">
                                        <i class="ti ti-file-text text-muted me-2"></i>
                                        نص التظلم
                                    </p>
                                    <div class="alert alert-light border small p-3">
                                        {{ $violation->appeal->appeal_reason }}
                                    </div>
                                    <div class="d-flex justify-content-end text-muted small mt-2">
                                        <i class="ti ti-calendar me-1"></i> تاريخ تقديم التظلم:
                                        {{ $violation->appeal->created_at }}
                                    </div>
                                </div>
                                @if ($violation->appeal->status->value != 'pending')
                                    <div class="border-1 border-light border-dashed mb-2"></div>

                                    <div class="mt-3">
                                        <p class="fw-bold d-flex align-items-center mb-2">
                                            <i class="ti ti-message-circle text-muted me-2"></i>
                                            الرد على التظلم
                                        </p>
                                        <div
                                            class="p-3 rounded bg-{{ $violation->appeal->status->value == 'approved' ? 'success' : 'danger' }} bg-opacity-10">
                                            {{ $violation->appeal->response }}
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small mt-2">
                                            <div>
                                                <i class="ti ti-user me-1"></i>تمت المراجعة بواسطة :
                                                {{ $violation->appeal->reviewer->name ?? 'غير محدد' }}
                                            </div>

                                            <div>
                                                <i class="ti ti-calendar me-1"></i>تاريخ الرد على التظلم :

                                                {{ $violation->appeal->reviewed_at ?? 'غير محدد' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- معلومات تنفيذ العقوبة إذا كانت مطبقة -->
                @if ($violation->status->value == 'approved' && !is_null($violation->execution))
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header d-flex align-items-center py-4">
                                <i class="ti ti-scale text-success me-2"></i>
                                <h6 class="card-title mb-0">معلومات تنفيذ العقوبة </h6>
                            </div>
                            <div class="border-1 border-light border-dashed mb-2"></div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-1 small">

                                        <div class="mb-6">
                                            <p class="mb-2 fw-bold"> تاريخ التنفيذ</p>
                                            <p>{{ $violation->execution->execution_date ?? 'غير محدد' }}</p>
                                        </div>

                                        <div class="mb-6">
                                            <p class="mb-2 fw-bold">قيمة الخصم</p>
                                            <strong dir="ltr" class="text-danger">
                                                {{ number_format($violation->execution->deduction_amount, 2) }}
                                                <small class="sar">SAR</small>
                                            </strong>
                                        </div>

                                    </div>

                                    <div class="col-md-6 small">
                                        <div class="mb-6">
                                            <p class="mb-2 fw-bold"> العقوبة </p>
                                            <p class="text-danger fw-bold">
                                                {{ $violation->formatPenalty($violation->penalty_text) }}
                                            </p>
                                        </div>

                                        <div class="mb-6">
                                            <p class="mb-2 fw-bold">تم التنفيذ بواسطة</p>
                                            <p>
                                                {{ optional($violation->execution->executor)->name ?? 'غير محدد' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if ($violation->execution->notes)
                                        <div class="col-12">
                                            <div class="border-1 border-light border-dashed mb-2"></div>
                                            <p class="fw-bold d-flex align-items-center mb-2">
                                                <i class="ti ti-notes text-muted me-2"></i>
                                                ملاحظات التنفيذ
                                            </p>
                                            <div class="alert alert-light border small p-3">
                                                {!! nl2br(e($violation->execution->notes)) !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($violation->execution->attachment)
                                        <div class="col-12">
                                            <div class="border-1 border-light border-dashed mb-2"></div>
                                            <p class="fw-bold d-flex align-items-center mb-2">
                                                <i class="ti ti-paperclip text-muted me-2"></i>
                                                مرفق التنفيذ
                                            </p>
                                            <div class="alert alert-light border small p-3">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center flex-grow-1">

                                                        <div>
                                                            <span class="text-truncate d-block fw-medium"
                                                                style="max-width: 300px;">
                                                                {{ $violation->reference_number }} مرفق المخالفة
                                                            </span>
                                                            <small class="text-muted">
                                                                {{ $violation->execution->created_at ? $violation->execution->created_at->format('d/m/Y') : 'غير محدد' }}
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <a href="{{ Storage::disk('public')->url($violation->execution->attachment) }}"
                                                            class="btn btn-sm btn-outline-success"
                                                            download="{{ $violation->reference_number }}"
                                                            title="تحميل الملف">
                                                            <i class="ti ti-download me-1"></i>
                                                            تحميل
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>


@endsection
