<!-- resources/views/violations/details.blade.php -->
@extends('layouts.layoutMaster')

@section('title', 'تفاصيل الانتهاك والعقوبة')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('hr.violations-penalties.index') }}">إدارة الانتهاكات والعقوبات</a>
    </li>
    <li class="breadcrumb-item active">تفاصيل الانتهاك #{{ $violation->id }}</li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- بطاقة معلومات الانتهاك -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">معلومات الانتهاك</h5>
                    <div>
                        <span
                            class="badge bg-{{ $violation->status == 'pending'
                                ? 'warning'
                                : ($violation->status == 'approved'
                                    ? 'info'
                                    : ($violation->status == 'under_appeal'
                                        ? 'warning'
                                        : ($violation->status == 'executed'
                                            ? 'success'
                                            : 'danger'))) }}">
                            {{ $violation->status == 'pending'
                                ? 'معلق'
                                : ($violation->status == 'approved'
                                    ? 'معتمد'
                                    : ($violation->status == 'under_appeal'
                                        ? 'قيد التظلم'
                                        : ($violation->status == 'executed'
                                            ? 'مطبق'
                                            : 'ملغي'))) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">رقم الانتهاك:</dt>
                                <dd class="col-sm-8">#{{ $violation->id }}</dd>

                                <dt class="col-sm-4">الموظف:</dt>
                                <dd class="col-sm-8">{{ $violation->user->name ?? 'غير محدد' }}</dd>

                                <dt class="col-sm-4">تصنيف الانتهاك:</dt>
                                <dd class="col-sm-8">{{ $violation->violation->category->name ?? 'غير محدد' }}</dd>

                                <dt class="col-sm-4">نوع الانتهاك:</dt>
                                <dd class="col-sm-8">{{ $violation->violation->description ?? 'غير محدد' }}</dd>

                                <dt class="col-sm-4">تاريخ الانتهاك:</dt>
                                <dd class="col-sm-8">
                                    {{ $violation->violation_date ? $violation->violation_date->format('Y-m-d') : 'غير محدد' }}
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">عدد مرات التكرار:</dt>
                                <dd class="col-sm-8">المرة {{ $violation->occurrence }}</dd>

                                <dt class="col-sm-4">العقوبة:</dt>
                                <dd class="col-sm-8"><strong>{{ $violation->penalty_text }}</strong></dd>

                                <dt class="col-sm-4">يسمح بالتظلم:</dt>
                                <dd class="col-sm-8">
                                    @if ($violation->is_appealable)
                                        <span class="badge bg-success">نعم ({{ $violation->appeal_days }} أيام)</span>
                                    @else
                                        <span class="badge bg-danger">لا</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">المراجع:</dt>
                                <dd class="col-sm-8">{{ $violation->reviewer->name ?? 'غير محدد' }}</dd>

                                <dt class="col-sm-4">تاريخ التسجيل:</dt>
                                <dd class="col-sm-8">{{ $violation->created_at->format('Y-m-d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- ملاحظات الانتهاك -->
                    @if ($violation->notes)
                        <div class="mt-3">
                            <h6 class="mb-2">ملاحظات:</h6>
                            <div class="alert alert-secondary">
                                {!! nl2br(e($violation->notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- معلومات التظلم إذا وجد -->
            @if ($violation->appeals->isNotEmpty())
                @php $appeal = $violation->appeals->first(); @endphp
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">معلومات التظلم</h5>
                        <div>
                            <span
                                class="badge bg-{{ $appeal->status == 'pending' ? 'warning' : ($appeal->status == 'approved' ? 'success' : 'danger') }}">
                                {{ $appeal->status == 'pending' ? 'قيد المراجعة' : ($appeal->status == 'approved' ? 'مقبول' : 'مرفوض') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="mb-2">نص التظلم:</h6>
                            <div class="alert alert-secondary">
                                {!! nl2br(e($appeal->appeal_reason)) !!}
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                            <div>
                                <i class="fas fa-calendar-alt me-1"></i> تاريخ تقديم التظلم:
                                {{ $appeal->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        @if ($appeal->status != 'pending')
                            <!-- الرد على التظلم إذا تمت مراجعته -->
                            <hr>
                            <div class="mt-3">
                                <h6 class="mb-2">الرد على التظلم:</h6>
                                <div class="alert alert-{{ $appeal->status == 'approved' ? 'success' : 'danger' }}">
                                    {!! nl2br(e($appeal->response)) !!}
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i> تمت المراجعة بواسطة:
                                    {{ $appeal->reviewer->name ?? 'غير محدد' }} -
                                    <i class="fas fa-calendar-alt me-1"></i> بتاريخ:
                                    @if(is_object($appeal->response_date))
                                    {{ $appeal->response_date->format('Y-m-d H:i') }}
                                @else
                                    {{ $appeal->response_date ?? 'غير محدد' }}
                                @endif                                </div>
                            </div>
                        @else
                            <!-- نموذج الرد على التظلم إذا كان قيد المراجعة -->
                            <hr>
                            <div class="mt-3">
                                <h6 class="mb-2">الرد على التظلم:</h6>
                                <form action="{{ route('hr.violations-penalties.appeal.respond', $appeal->id) }}" method="POST">

                                    @csrf
                                    @method('PUT')
                                    <div class="mb-3">
                                        <label for="status" class="form-label">القرار <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="">اختر القرار</option>
                                            <option value="approved">قبول التظلم</option>
                                            <option value="rejected">رفض التظلم</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="response" class="form-label">الرد على التظلم <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" id="response" name="response" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">حفظ الرد</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- معلومات تنفيذ العقوبة إذا كانت مطبقة -->
            @if ($violation->status == 'executed' && $violation->executions->isNotEmpty())
                @php $execution = $violation->executions->first(); @endphp
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">معلومات تنفيذ العقوبة</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">تاريخ التنفيذ:</dt>
                            {{-- <dd class="col-sm-9">{{ $execution->execution_date->format('Y-m-d') }}</dd> --}}

                            <dt class="col-sm-3">نوع التنفيذ:</dt>
                            <dd class="col-sm-9">
                                @if ($execution->execution_type == 'warning')
                                    إنذار
                                @elseif($execution->execution_type == 'deduction')
                                    خصم مالي
                                @elseif($execution->execution_type == 'suspension')
                                    إيقاف عن العمل
                                @else
                                    أخرى
                                @endif
                            </dd>

                            @if ($execution->deduction_amount)
                                <dt class="col-sm-3">قيمة الخصم:</dt>
                                <dd class="col-sm-9">{{ $execution->deduction_amount }}</dd>
                            @endif

                            @if ($execution->deduction_days)
                                <dt class="col-sm-3">عدد أيام الخصم/الإيقاف:</dt>
                                <dd class="col-sm-9">{{ $execution->deduction_days }} يوم</dd>
                            @endif

                            <dt class="col-sm-3">تم التنفيذ بواسطة:</dt>
                            <dd class="col-sm-9">{{ optional($execution->executor)->name ?? 'غير محدد' }}</dd>

                            @if ($execution->notes)
                                <dt class="col-sm-3">ملاحظات:</dt>
                                <dd class="col-sm-9">{!! nl2br(e($execution->notes)) !!}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <!-- بطاقة الإجراءات المتاحة -->
        <div class="col-lg-4">
            <div class="card mb-4 sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header">
                    <h5 class="card-title">الإجراءات المتاحة</h5>
                </div>
                <div class="card-body">
                    <div class="action-buttons">
                        <!-- رابط العودة للقائمة -->
                        <a href="{{ route('hr.violations-penalties.index') }}"
                            class="btn btn-outline-secondary btn-block mb-2">
                            <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                        </a>

                        <!-- زر تعديل الانتهاك -->
                        @if (in_array($violation->status, ['pending', 'approved']))
                            <a href="{{ route('hr.violations-penalties.edit', $violation->id) }}"
                                class="btn btn-outline-primary btn-block mb-2">
                                <i class="fas fa-edit me-1"></i> تعديل الانتهاك
                            </a>
                        @endif

                        <!-- زر تطبيق العقوبة -->
                        @if (in_array($violation->status, ['pending', 'approved']) )
                            @php
                                $canApply = true;
                                $disableReason = '';

                                // التحقق من فترة التظلم
                                if ($violation->is_appealable) {
                                    $creationDate = $violation->created_at;
                                    $appealDays = $violation->appeal_days;
                                    $deadlineDate = $creationDate->copy()->addDays($appealDays);

                                    if (now()->lt($deadlineDate) && $violation->appeals->isEmpty()) {
                                        $canApply = false;
                                        $disableReason =
                                            'لا يمكن تطبيق العقوبة قبل انتهاء فترة التظلم المسموحة. يمكن تطبيق العقوبة بعد تاريخ ' .
                                            $deadlineDate->format('Y-m-d');
                                    }
                                }

                                // التحقق من وجود تظلم قيد المراجعة
                                if (
                                    $violation->appeals->isNotEmpty() &&
                                    $violation->appeals->first()->status == 'pending'
                                ) {
                                    $canApply = false;
                                    $disableReason = 'لا يمكن تطبيق العقوبة بينما التظلم قيد المراجعة.';
                                }
                            @endphp

                            @if ($canApply)
                                <button type="button" class="btn btn-success btn-block mb-2" data-bs-toggle="modal"
                                    data-bs-target="#applyPenaltyModal">
                                    <i class="fas fa-check me-1"></i> تطبيق العقوبة
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-block mb-2" disabled>
                                    <i class="fas fa-check me-1"></i> تطبيق العقوبة
                                </button>
                                <div class="alert alert-warning small mb-2">
                                    <i class="fas fa-info-circle me-1"></i> {{ $disableReason }}
                                </div>
                            @endif
                        @endif

                        <!-- زر إلغاء العقوبة -->
                        @if ($violation->status != 'cancelled' )
                            <button type="button" class="btn btn-danger btn-block mb-2" data-bs-toggle="modal"
                                data-bs-target="#cancelPenaltyModal">
                                <i class="fas fa-times me-1"></i> إلغاء العقوبة
                            </button>
                        @endif

                        <!-- زر حذف الانتهاك -->
                        <button type="button" class="btn btn-outline-danger btn-block mb-2" data-bs-toggle="modal"
                            data-bs-target="#deleteViolationModal">
                            <i class="fas fa-trash me-1"></i> حذف الانتهاك
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal تطبيق العقوبة -->
    <div class="modal fade" id="applyPenaltyModal" tabindex="-1" aria-labelledby="applyPenaltyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applyPenaltyModalLabel">تأكيد تطبيق العقوبة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <h6 class="mb-2">تفاصيل العقوبة:</h6>
                        <div>
                            <p><strong>الموظف:</strong> {{ $violation->user->name ?? 'غير محدد' }}</p>
                            <p><strong>نوع الانتهاك:</strong> {{ $violation->violation->description ?? 'غير محدد' }}</p>
                            <p><strong>العقوبة المقررة:</strong> {{ $violation->penalty_text }}</p>
                        </div>
                    </div>

                    <form id="applyPenaltyForm" action="{{ route('hr.violations-penalties.apply-penalty', $violation->id) }}"
                        method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="execution_date" class="form-label">تاريخ التنفيذ</label>
                            <input type="date" class="form-control" id="execution_date" name="execution_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="execution_notes" class="form-label">ملاحظات</label>
                            <textarea class="form-control" id="execution_notes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" form="applyPenaltyForm" class="btn btn-primary">تأكيد التطبيق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إلغاء العقوبة -->
    <div class="modal fade" id="cancelPenaltyModal" tabindex="-1" aria-labelledby="cancelPenaltyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelPenaltyModalLabel">تأكيد إلغاء العقوبة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <p>هل أنت متأكد من رغبتك في إلغاء هذه العقوبة؟</p>
                    </div>

                    <form id="cancelPenaltyForm" action="{{ route('hr.violations-penalties.cancel-penalty', $violation->id) }}"
                        method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="cancel_reason" class="form-label">سبب الإلغاء</label>
                            <textarea class="form-control" id="cancel_reason" name="reason" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                    <button type="submit" form="cancelPenaltyForm" class="btn btn-danger">تأكيد الإلغاء</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal حذف الانتهاك -->
    <div class="modal fade" id="deleteViolationModal" tabindex="-1" aria-labelledby="deleteViolationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteViolationModalLabel">تأكيد حذف الانتهاك</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <p>هل أنت متأكد من رغبتك في حذف هذا الانتهاك بشكل نهائي؟</p>
                        <p><strong>تحذير:</strong> لا يمكن التراجع عن هذا الإجراء.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">تراجع</button>
                    <form action="{{ route('hr.violations-penalties.destroy', $violation->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">تأكيد الحذف</button>
                </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <style>
        /* تنسيق أزرار الإجراءات */
        .btn-block {
            display: block;
            width: 100%;
        }
    </style>

    <script>
        $(document).ready(function() {
            // تنسيق التاريخ
            $('#execution_date').val(new Date().toISOString().split('T')[0]);

            // تفعيل sweetalert2 للتأكيدات
            $('.btn-delete-violation').on('click', function() {
                Swal.fire({
                    title: 'تأكيد حذف الانتهاك',
                    text: 'هل أنت متأكد من رغبتك في حذف هذا الانتهاك؟ لا يمكن التراجع عن هذا الإجراء.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم، احذف الانتهاك',
                    cancelButtonText: 'تراجع',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).closest('form').submit();
                    }
                });
            });
        });
    </script>
@endsection
