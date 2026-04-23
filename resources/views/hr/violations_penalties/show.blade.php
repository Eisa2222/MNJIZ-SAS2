@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الانتهاك')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li>
        <a href="{{ route('hr.violations-penalties.index') }}">إدارة الانتهاكات و العقوبات</a>
    </li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل الإنتهاك</a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل الإنتهاك" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
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

        .appeal-form {
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
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
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
    <script>
        $(document).ready(function() {
            // تهيئة Select2
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
    </script>
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection
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

                            <tr>
                                <td class="fw-bold">الاجراءات</td>

                                <td>
                                    @can('تطبيق/إلغاء عقوبة او إنتهاك')
                                        <div class="d-flex justify-content-center">
                                            <div class="small">

                                                @if ($violation->appeal && $violation->appeal->status === 'approved')
                                                    <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center">
                                                        تم قبول التظلم، لا يمكن تطبيق أو إلغاء العقوبة
                                                    </div>
                                                @elseif($violation->appeal && $violation->appeal->status === 'pending')
                                                    <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-center">
                                                        التظلم قيد المراجعة، يجب الرد على التظلم أولًا
                                                    </div>
                                                @else
                                                    @if ($violation->status->value === 'pending')
                                                        <button type="button"
                                                            class="btn btn-sm btn-success btn-apply-penalty me-2"
                                                            data-id="{{ $violation->id }}">
                                                            تطبيق العقوبة
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger btn-cancel-penalty"
                                                            data-id="{{ $violation->id }}">
                                                            إلغاء العقوبة
                                                        </button>
                                                    @elseif($violation->status->value === 'under_appeal')
                                                        @if ($violation->getRemainingAppealDays() > 0)
                                                            <small class="text-danger">
                                                                لم تنتهي فترة التظلم
                                                            </small>
                                                        @else
                                                            <button type="button"
                                                                class="btn btn-sm btn-success btn-apply-penalty me-2"
                                                                data-id="{{ $violation->id }}">
                                                                تطبيق العقوبة
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger btn-cancel-penalty"
                                                                data-id="{{ $violation->id }}">
                                                                إلغاء العقوبة
                                                            </button>
                                                        @endif
                                                    @elseif($violation->status->value === 'approved')
                                                        <div
                                                            class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center fw-bold">
                                                            تم إعتماد العقوبة وسيتم تطبيقها تلقائيا على مرتب الموظف
                                                        </div>
                                                    @elseif($violation->status->value === 'executed')
                                                        <div
                                                            class="alert alert-success py-2 px-3 mb-0 d-flex align-items-center fw-bold">
                                                            تم تنفيذ العقوبة على مرتب الموظف
                                                        </div>
                                                    @elseif($violation->status->value === 'cancelled')
                                                        <p class="text-danger fw-bold">
                                                            تم إلغاء المخالفة
                                                        </p>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endcan
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-md-4">
            {{-- سجل النشاط --}}
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
                <!-- معلومات العقوبات المترتبة -->
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

                <!-- معلومات التظلم إذا وجد -->
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
                                    <!-- الرد على التظلم إذا تمت مراجعته -->
                                    <div class="border-1 border-light border-dashed mb-2"></div>

                                    <div class="mt-3">
                                        <p class="fw-bold d-flex align-items-center mb-2">
                                            <i class="ti ti-message-circle text-muted me-2"></i>
                                            الرد على التظلم
                                        </p>
                                        <div
                                            class="p-3 rounded bg-{{ $violation->appeal->status->value == 'approved' ? 'success' : 'danger' }} bg-opacity-10">
                                            {!! nl2br(e($violation->appeal->response)) !!}
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
                                @else
                                    <!-- نموذج الرد على التظلم إذا كان قيد المراجعة -->
                                    <div class="border-1 border-light border-dashed mb-2"></div>

                                    <div class="mt-3">
                                        <p class="fw-bold d-flex align-items-center mb-3">
                                            <i class="ti ti-message-circle text-muted me-2"></i>
                                            الرد على التظلم
                                        </p>
                                        <form id="appealForm"
                                            action="{{ route('hr.violations-penalties.appeal.respond', $violation->appeal->id) }}"
                                            method="POST" class="appeal-form">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="status" class="form-label">القرار <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control select2" data-placeholder="اختر القرار"
                                                    id="status" name="status" required>
                                                    <option value="">اختر القرار</option>
                                                    @foreach (['approved' => 'قبول التظلم', 'rejected' => 'رفض التظلم'] as $value => $label)
                                                        <option value="{{ $value }}" @selected(old('status') == $value)>
                                                            {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('status')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label for="response" class="form-label">الرد على التظلم <span
                                                        class="text-danger">*</span></label>
                                                <textarea class="form-control" id="response" name="response" rows="4" required>{{ old('response') }}</textarea>
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    الرد على التظلم
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- معلومات تنفيذ العقوبة إذا كانت مطبقة -->
                @if (in_array($violation->status->value, ['approved', 'executed']) && !is_null($violation->execution))
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
                                                {{ $violation->execution->notes }}
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

    <!-- Modal تطبيق العقوبة -->
    <div class="modal fade" id="applyPenaltyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد تطبيق العقوبة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <h6 class="mb-2">تفاصيل العقوبة:</h6>
                        <p><strong>الموظف:</strong> {{ $violation->employee->name ?? 'غير محدد' }}</p>
                        <p><strong>نوع الانتهاك:</strong> {{ $violation->violationType->description ?? 'غير محدد' }}</p>
                        <p><strong>العقوبة المقررة:</strong> {{ $violation->formatPenalty($violation->penalty_text) }}</p>
                    </div>

                    <form id="applyPenaltyForm">
                        <input type="hidden" id="violation_id" name="violation_id">

                        <div class="mb-3">
                            <label for="attachment" class="form-label">مرفق </label>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                        </div>

                        <div class="mb-3">
                            <label for="execution_notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control" id="execution_notes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="saveExecution">تأكيد التطبيق</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // تطبيق العقوبة
            $(document).on('click', '.btn-apply-penalty', function() {
                var id = $(this).data('id');
                $('#applyPenaltyModal').modal('show');
                $('#violation_id').val(id);
            });

            // حفظ تنفيذ العقوبة
            // في section('page-script')
            $('#saveExecution').on('click', function() {
                var id = $('#violation_id').val();
                var formData = new FormData(document.getElementById('applyPenaltyForm'));
                formData.append('_token', "{{ csrf_token() }}");

                // إضافة تحميل
                $(this).prop('disabled', true).text('جاري التطبيق...');

                console.log('Sending request to apply penalty for violation:', id);

                $.ajax({
                    url: "{{ route('hr.violations-penalties.apply-penalty', ['violation' => ':violation']) }}"
                        .replace(':violation', id),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response.success) {
                            $('#applyPenaltyModal').modal('hide');
                            toastr.success(response.message);
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            toastr.error(response.message || 'حدث خطأ أثناء تطبيق العقوبة');
                        }
                    },
                    error: function(xhr) {
                        console.log('Error response:', xhr);
                        console.log('Response text:', xhr.responseText);

                        var response = xhr.responseJSON;
                        if (response) {
                            console.log('Parsed response:', response);

                            if (response.debug_info) {
                                console.log('Debug info:', response.debug_info);
                            }

                            if (response.errors) {
                                $.each(response.errors, function(key, value) {
                                    toastr.error(Array.isArray(value) ? value[0] :
                                        value);
                                });
                            } else if (response.message) {
                                toastr.error(response.message);
                            } else {
                                toastr.error('حدث خطأ أثناء تطبيق العقوبة');
                            }
                        } else {
                            toastr.error('خطأ في الاتصال مع الخادم');
                        }
                    },
                    complete: function() {
                        $('#saveExecution').prop('disabled', false).text('تأكيد التطبيق');
                    }
                });
            });

            // إلغاء العقوبة
            $(document).on('click', '.btn-cancel-penalty', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'تأكيد إلغاء العقوبة',
                    text: 'هل أنت متأكد من رغبتك في إلغاء هذه العقوبة؟',
                    icon: 'warning',
                    showCancelButton: true,
                    showConfirmButton: true,
                    showDenyButton: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'btn btn-success custom-confirm',
                        cancelButton: 'btn btn-danger custom-cancel'
                    },
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                    reverseButtons: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('hr.violations-penalties.cancel-penalty', ['violation' => ':violation']) }}"
                                .replace(':violation', id),

                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success('تم إلغاء العقوبة');
                                    window.location.reload();
                                } else {
                                    toastr.error(response.message ||
                                        'حدث خطأ أثناء إلغاء العقوبة');
                                }
                            },
                            error: function(xhr) {
                                toastr.error('حدث خطأ أثناء إلغاء العقوبة');
                            }
                        });
                    }
                });
            });
        });


        $(document).ready(function() {
            $('#appealForm').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                const status = $('#status').val();

                if (status === 'approved') {
                    Swal.fire({
                        title: 'هل أنت متأكد من قبول التظلم؟',
                        text: 'بقبول التظلم، سيتم إلغاء المخالفة ولن يتم اقتطاع أي مبلغ من راتب الموظف.',
                        icon: 'warning',
                        showCancelButton: true,
                        showConfirmButton: true,
                        showDenyButton: false,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'custom-popup',
                            title: 'custom-title',
                            text: 'custom-text',
                            confirmButton: 'btn btn-success custom-confirm',
                            cancelButton: 'btn btn-danger custom-cancel'
                        },
                        confirmButtonText: 'تأكيد',
                        cancelButtonText: 'إلغاء',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else if (status === 'rejected') {
                    Swal.fire({
                        title: 'هل أنت متأكد من رفض التظلم؟',
                        text: 'برفض التظلم سيتم تطبيق العقوبة على الموظف.',
                        icon: 'warning',
                        showCancelButton: true,
                        showConfirmButton: true,
                        showDenyButton: false,
                        buttonsStyling: false,
                        customClass: {
                            popup: 'custom-popup',
                            title: 'custom-title',
                            text: 'custom-text small',
                            confirmButton: 'btn btn-success custom-confirm',
                            cancelButton: 'btn btn-danger custom-cancel'
                        },
                        confirmButtonText: 'تأكيد',
                        cancelButtonText: 'إلغاء',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    // إذا لم يُختر أي قرار
                    toastr.error('يرجى اختيار قرار (قبول أو رفض) قبل الإرسال.');
                }
            });
        });
    </script>
@endsection
