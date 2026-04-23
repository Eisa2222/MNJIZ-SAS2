@extends('layouts.layoutMaster')

@section('title', 'تفاصيل طلب الإجازة')

@section('breadcrumb')
    <li><a href="#">طلبات الإعتماد</a></li>
    <li><a href="{{ route('accreditation-requests.leave-requests.index') }}">إعتماد الإجازات</a>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل طلب الإجازة </a>
        <i class="ti ti-star favorite-icon" data-page-name="طلبات الإعتماد للتصديق" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

{{-- Vendor Styles --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection

{{-- Toastr --}}
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

{{-- SweetAlert2 CDN --}}
@section('sweetalert-cdn')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

{{-- Page Scripts --}}
@section('page-script')
    @vite(['resources/assets/js/forms-editors.js', 'resources/assets/js/extended-ui-sweetalert2.js'])
    @yield('sweetalert-cdn')
    <script>
        $(document).ready(function() {
            if (window.location.hash) {
                $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
            }
            $('.nav-tabs a').on('shown.bs.tab', function(e) {
                history.pushState(null, null, e.target.hash);
            });


            $('.approval-action').on('click', function(e) {
                e.preventDefault();
                let requestId = $(this).data('id');
                let action = $(this).data('action');
                let field = $(this).data('name') || 'approver1_approved';
                let url = "";
                let titleText = "";
                let confirmBtnText = "";

                if (action === 'approve') {
                    url = "{{ route('accreditation-requests.leave-requests.approve', [':id', ':field']) }}";
                    url = url.replace(':id', requestId).replace(':field', field);
                    titleText = "هل تريد اعتماد هذا الطلب؟";
                    confirmBtnText = "اعتماد";
                } else if (action === 'revoke') {
                    url = "{{ route('accreditation-requests.leave-requests.revoke', [':id', ':field']) }}";
                    url = url.replace(':id', requestId).replace(':field', field);
                    titleText = "هل تريد إلغاء الاعتماد لهذا الطلب؟";
                    confirmBtnText = "إلغاء الاعتماد";
                }
                Swal.fire({
                    title: titleText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmBtnText,
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.success);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    Swal.fire('خطأ', response.error, 'error');
                                }
                            },
                            error: function(xhr) {
                                let errMsg = (xhr.responseJSON && xhr.responseJSON
                                        .error) ?
                                    xhr.responseJSON.error :
                                    'حدث خطأ أثناء تنفيذ الإجراء.';
                                Swal.fire('خطأ', errMsg, 'error');
                            }
                        });
                    }
                });
            });

            // زر رفض الطلب
            $('.reject-action').on('click', function(e) {
                e.preventDefault();
                let requestId = $(this).data('id');
                let field = $(this).data('name') || 'approver1_approved';
                let url = "{{ route('accreditation-requests.leave-requests.reject', [':id', ':field']) }}";
                url = url.replace(':id', requestId).replace(':field', field);
                Swal.fire({
                    title: 'أدخل سبب الرفض',
                    input: 'textarea',
                    inputPlaceholder: 'سبب الرفض...',
                    showCancelButton: true,
                    confirmButtonText: 'رفض',
                    cancelButtonText: 'إلغاء',
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false,
                    inputValidator: (value) => {
                        if (!value) return 'يجب إدخال سبب الرفض';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: {
                                rejection_reason: result.value
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.success);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    Swal.fire('خطأ', response.error, 'error');
                                }
                            },
                            error: function(xhr) {
                                let errMsg = (xhr.responseJSON && xhr.responseJSON
                                        .error) ?
                                    xhr.responseJSON.error :
                                    'حدث خطأ أثناء تنفيذ الإجراء.';
                                Swal.fire('خطأ', errMsg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <style>
            .nav-tabs .nav-link,
            .nav-pills .nav-link {
                justify-content: start;
                margin-bottom: 10px;
            }

            .nav-tabs .nav-link.active,
            .nav-tabs .nav-link.active:hover,
            .nav-tabs .nav-link.active:focus {
                box-shadow: none;
                border-left: 5px solid;
            }
        </style>
        <!-- تبويبات جانبية -->
        <div class="col-12 col-md-3 col-xl-3">
            <div>
                <ul class="nav nav-tabs flex-column border-bottom-0">
                    <!-- تبويب تفاصيل الطلب -->
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold active m-0" data-bs-toggle="tab" href="#leaveDetails">
                            <i class="ti ti-settings ti-sm me-1"></i>
                            <span class="align-middle">تفاصيل الطلب</span>
                        </a>
                    </li>
                    <!-- تبويب سجل الاعتمادات -->
                    <li class="card rounded-0 mb-3 nav-item shadow-none">
                        <a class="py-3 nav-link fw-bold m-0" data-bs-toggle="tab" href="#approvalsLog">
                            <i class="ti ti-history ti-sm me-1"></i>
                            <span class="align-middle">سجل الاعتمادات</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="col-12 col-md-9 col-xl-9 order-1 order-md-0 mb-5">
            <div class="tab-content p-0">
                {{-- تبويب تفاصيل الطلب --}}
                <div class="tab-pane fade show active" id="leaveDetails">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="text-primary fw-bold mb-0">تفاصيل طلب الإجازة</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th>رقم الطلب</th>
                                            <th>نوع الإجازة</th>
                                            <th>الموظف</th>
                                            <th>تاريخ البداية</th>
                                            <th>تاريخ النهاية</th>
                                            <th>عدد أيام الطلب</th>
                                            {{-- <th>سبب الإجازة</th> --}}
                                            {{-- <th>المرفقات</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- صف بيانات الطلب (بدون تعديل) --}}
                                        <tr>
                                            <td class="text-center">{{ $leaveRequest->id }}</td>
                                            <td class="text-center">{{ $leaveRequest->leaveType->name }}</td>
                                            <td class="text-center">{{ $leaveRequest->employee->name }}</td>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d') }}</td>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('Y-m-d') }}</td>
                                            <td class="text-center">{{ number_format($leaveRequest->days_count) }}</td>
                                            {{-- <td class="text-center">—</td> --}}
                                        </tr>
                                        {{-- صف الرصيد السنوي (يظهر فقط إذا لدينا رصيد) --}}
                                        @if (isset($balance))
                                            <tr class="table-light">
                                                <td colspan="7" class="text-center p-3">
                                                    <div class="row justify-content-center">
                                                        <div class="col-auto">
                                                            <strong class="mx-2">الرصيد الكلي :</strong>
                                                            <span
                                                                class="mx-1">{{ rtrim(rtrim(number_format($balance->total_days, 2, '.', ','), '0'), '.') }}</span>
                                                            أيام
                                                        </div>
                                                        <div class="col-auto">
                                                            <strong class="mx-2">المستخدم :</strong>
                                                            <span
                                                                class="mx-1">{{ rtrim(rtrim(number_format($balance->used_days, 2, '.', ','), '0'), '.') }}</span>
                                                            أيام
                                                        </div>
                                                        <div class="col-auto">
                                                            <strong class="mx-2">المتبقي :</strong>
                                                            <span
                                                                class="mx-1 {{ $balance->remaining_days < 0 ? 'text-danger fw-bold' : '' }}">
                                                                {{ $balance->remaining_days < 0 ? '-' : '' }}{{ rtrim(rtrim(number_format(abs($balance->remaining_days), 2, '.', ','), '0'), '.') }}
                                                            </span> أيام
                                                        </div>
                                                        {{-- <div class="col-auto">
                                                            <strong class="mx-2">المطلوب:</strong>
                                                            <span
                                                                class="mx-1">{{ rtrim(rtrim(number_format($leaveRequest->days_count, 2, '.', ','), '0'), '.') }}</span>
                                                            أيام
                                                        </div> --}}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        {{-- صف المرفقات (بدون تعديل) --}}
                                        @if ($leaveRequest->attachments->count())
                                            <tr>
                                                <td colspan="7">
                                                    <strong>المرفقات:</strong>
                                                    <ul class="list-unstyled mb-0 mt-2">
                                                        @foreach ($leaveRequest->attachments as $att)
                                                            <li>
                                                                <a href="{{ Storage::url($att->file_path) }}"
                                                                    target="_blank" class="text-decoration-none">
                                                                    {{ $att->file_name }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    {{-- بطاقة مراحل الاعتمادات --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="text-primary fw-bold mb-0">مراحل الاعتمادات</h5>
                        </div>
                        <div class="card-body">
                            @if (count($approvers) === 0)
                                <p class="text-muted">لا توجد معلومات اعتماد بعد.</p>
                            @else
                                <div class="d-flex justify-content-between align-items-center w-100 position-relative">
                                    @foreach ($approvers as $index => $approver)
                                        @php
                                            // جلب تفاصيل المعتمد
                                            $employee = \App\Models\Hr\Employees\Employees::find($approver['id']);
                                            $approverName = $employee ? $employee->name : 'غير متوفر';
                                            // حالة الاعتماد (باستخدام بيانات الطلب)
                                            $status = $leaveRequest->{$approver['field']} ?? 'pending';
                                            // تحديد لون الدائرة والأيقونة
                                            $circleColor = '';
                                            $icon = '';
                                            if ($status === 'approved') {
                                                $circleColor = 'bg-success';
                                                $icon = '<i class="ti ti-check"></i>';
                                            } elseif ($status === 'rejected') {
                                                $circleColor = 'bg-danger';
                                                $icon = '<i class="ti ti-x"></i>';
                                            } else {
                                                $circleColor = 'bg-secondary';
                                                $icon = '<i class="ti ti-clock"></i>';
                                            }
                                        @endphp
                                        <div class="d-flex flex-column align-items-center position-relative">
                                            <div class="rounded-circle {{ $circleColor }} d-flex align-items-center justify-content-center text-white"
                                                style="width: 50px; height: 50px;">
                                                {!! $icon !!}
                                            </div>
                                            <span class="mt-2 text-center">{{ $approverName }}</span>
                                        </div>
                                        @if (!$loop->last)
                                            <div class="flex-grow-1 bg-{{ $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'secondary') }}"
                                                style="height: 2px; margin: auto;"></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- أزرار الاعتماد والرفض والإلغاء تحت مراحل الاعتمادات --}}
                    @if (auth()->user()->hasRole('Admin'))
                        @php
                            $itemForLeave = \App\Models\Item::where('type', 'leave')->first();
                            $hasApprovers =
                                $itemForLeave &&
                                ($itemForLeave->approver1_id ||
                                    $itemForLeave->approver2_id ||
                                    $itemForLeave->approver3_id);
                        @endphp
                        @if ($leaveRequest->status !== 'rejected' && $hasApprovers)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                                        @if ($leaveRequest->status === 'pending')
                                            <button class="btn btn-primary approval-action"
                                                data-id="{{ $leaveRequest->id }}"
                                                data-name="{{ $currentApproval['field'] ?? 'approver1_approved' }}"
                                                data-action="approve">
                                                <i class="ti ti-check me-1"></i> اعتماد الطلب
                                            </button>
                                        @endif
                                        @if ($leaveRequest->status === 'pending')
                                            <button class="btn btn-danger reject-action" data-id="{{ $leaveRequest->id }}"
                                                data-name="{{ $currentApproval['field'] ?? 'approver1_approved' }}">
                                                <i class="ti ti-x me-1"></i> رفض الطلب
                                            </button>
                                        @endif
                                        @if ($leaveRequest->status === 'approved')
                                            <button class="btn btn-warning approval-action"
                                                data-id="{{ $leaveRequest->id }}"
                                                data-name="{{ $lastApprovedField ?? 'approver1_approved' }}"
                                                data-action="revoke">
                                                <i class="ti ti-rotate me-1"></i> إلغاء الاعتماد
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        @if ($canApprove || $canReject || $canRevoke)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                                        @if ($canApprove)
                                            <button class="btn btn-primary approval-action"
                                                data-id="{{ $leaveRequest->id }}"
                                                data-name="{{ $currentApproval['field'] ?? 'approver1_approved' }}"
                                                data-action="approve">
                                                <i class="ti ti-check me-1"></i> اعتماد الطلب
                                            </button>
                                        @endif
                                        @if ($canReject)
                                            <button class="btn btn-danger reject-action" data-id="{{ $leaveRequest->id }}"
                                                data-name="{{ $currentApproval['field'] ?? 'approver1_approved' }}">
                                                <i class="ti ti-x me-1"></i> رفض الطلب
                                            </button>
                                        @endif
                                        @if ($canRevoke && $lastApprovedField)
                                            <button class="btn btn-warning approval-action"
                                                data-id="{{ $leaveRequest->id }}"
                                                data-name="{{ $lastApprovedField ?? 'approver1_approved' }}"
                                                data-action="revoke">
                                                <i class="ti ti-rotate me-1"></i> إلغاء الاعتماد
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                    {{-- نهاية قسم المراحل --}}
                </div>

                {{-- تبويب سجل الاعتمادات --}}
                <div class="tab-pane fade" id="approvalsLog">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="text-primary fw-bold mb-0">سجل الاعتمادات</h5>
                        </div>
                        <div class="card-body">
                            @if ($leaveRequest->approvalLogs->count())
                                <ul class="timeline">
                                    @foreach ($leaveRequest->approvalLogs as $log)
                                        @php
                                            $iconClass = 'bg-secondary';
                                            if ($log->action === 'approved') {
                                                $iconClass = 'bg-success';
                                            } elseif ($log->action === 'rejected') {
                                                $iconClass = 'bg-danger';
                                            } elseif ($log->action === 'revoked') {
                                                $iconClass = 'bg-warning';
                                            }
                                        @endphp
                                        <li class="timeline-item">
                                            <span class="timeline-point {{ $iconClass }}"></span>
                                            <div class="timeline-event">
                                                <h6 class="timeline-title text-capitalize">
                                                    @if ($log->action === 'created')
                                                        تم الإنشاء
                                                    @elseif ($log->action === 'auto_approved')
                                                        تم الاعتماد التلقائي
                                                    @elseif ($log->action === 'approved')
                                                        تم الاعتماد
                                                    @elseif($log->action === 'rejected')
                                                        تم الرفض
                                                    @elseif($log->action === 'revoked')
                                                        تم إلغاء الاعتماد
                                                    @elseif($log->action === 'updated')
                                                        تم التحديث
                                                    @else
                                                        {{ $log->action }}
                                                    @endif
                                                </h6>
                                                <p class="mb-1">
                                                    بواسطة: {{ optional($log->employee)->name ?? '---' }}
                                                </p>
                                                @if ($log->action === 'rejected' && $log->reason)
                                                    <p class="text-muted">سبب الرفض: {{ $log->reason }}</p>
                                                @endif
                                                <small class="text-muted">{{ $log->created_at }}</small>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">لا يوجد سجلات اعتماد بعد.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div> <!-- End Tab Content -->
        </div>
    </div>
@endsection
