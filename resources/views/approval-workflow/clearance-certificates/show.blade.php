@extends('layouts.layoutMaster')

{{-- العنوان --}}
@section('title', 'اعتماد طلب إخلاء طرف - ' . ($item->user->name ?? 'غير محدد'))

{{-- مسار التنقل --}}
@section('breadcrumb')
<li class="breadcrumb-item">
    <a>تفاصيل طلب {{ Str::limit($item->user->name ?? 'غير محدد', 25) }}</a>
</li>
@endsection

{{-- ملفات JS و CSS --}}
@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/approval-workflow/approval-config.js',
'resources/assets/js/approval-workflow/approval-manager.js',
'resources/assets/js/approval-workflow/clearance-certificates/show.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.routeBaseName = '{{ $routeBaseName }}';
    window.laravelRoutes = {
        approve: '{{ route($routeBaseName . '.approve', ':id') }}',
        reject: '{{ route($routeBaseName . '.reject', ':id') }}',
        revoke: '{{ route($routeBaseName . '.revoke', ':id') }}'
    };
</script>
@endsection


{{-- المحتوى الرئيسي للصفحة --}}
@section('content')
<div class="row g-3">
    <!-- ============================================================== -->
    <!-- العمود الأيمن: تفاصيل الطلب (تصميم الجدول)                      -->
    <!-- ============================================================== -->
    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-file-text text-primary me-2"></i>
                    تفاصيل طلب إخلاء الطرف
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body">
                <div class="table-responsive rounded-3 border">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light" style="width: 35%;">
                                    اسم الموظف
                                </td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $item->user->name ?? 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">
                                    تاريخ الطلب
                                </td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $item->created_at?->format('d/m/Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">
                                    سبب الطلب
                                </td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $item->reason ?? 'لا يوجد' }}
                                </td>
                            </tr>
                            @if ($item->notes)
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">
                                    ملاحظات إضافية
                                </td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $item->notes }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- العمود الأيسر: مراحل الاعتمادات (تصميم مطابق)                     -->
    <!-- ============================================================== -->
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-header py-4 bg-white">
                <div class="d-flex align-items-center">
                    <h6 class="card-title mb-0 fw-semibold">
                        <i class="ti ti-list-details text-primary me-2"></i>
                        مراحل الاعتمادات
                    </h6>
                </div>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body p-0 overflow-auto">
                @if (empty($approvalStages))
                <div class="p-4 text-center text-muted">
                    <i class="ti ti-clock-pause ti-lg"></i>
                    <p>لا توجد مراحل اعتماد</p>
                </div>
                @else
                <div class="p-4">
                    @foreach ($approvalStages as $i => $stage)
                    @php
                    // نفس منطق الكود في شاشة الاجازات تماماً
                    $status = $stage['status'] ?? 'pending';
                    $cfg = match ($status) {
                    'approved' => ['line' => 'success', 'bg' => 'bg-success', 'icon' => 'ti-check'],
                    'rejected' => ['line' => 'danger', 'bg' => 'bg-danger', 'icon' => 'ti-x'],
                    'pending' => ['line' => 'warning', 'bg' => 'bg-warning', 'icon' => 'ti-clock'],
                    default => [
                    'line' => 'secondary',
                    'bg' => 'bg-secondary',
                    'icon' => 'ti-dots',
                    ],
                    };
                    $isLast = $loop->last;

                    // شروط عرض الأزرار
                    $isCurrent = $stage['is_current'] ?? false;
                    $canAction = $isCurrent && ($stage['can_action'] ?? false);
                    $canApprove = $canAction && ($userPermissions['canApprove'] ?? false);
                    $canReject = $canAction && ($userPermissions['canReject'] ?? false);
                    $canRevoke =
                    ($stage['can_revoke'] ?? false) && ($userPermissions['canRevoke'] ?? false);
                    @endphp
                    <div class="d-flex position-relative {{ !$isLast ? 'mb-4' : '' }}">
                        <div class="me-3 position-relative" style="z-index:2">
                            <div class="avatar avatar-md">
                                <div
                                    class="avatar-initial {{ $cfg['bg'] }} text-white rounded-circle {{ $isCurrent ? 'border border-3 border-primary' : '' }}">
                                    <i class="ti {{ $cfg['icon'] }}"></i>
                                </div>
                            </div>
                            @unless ($isLast)
                            <div class="position-absolute bg-{{ $cfg['line'] }}"
                                style="left:50%; top:unset; transform:translateX(-50%); width:4px; height:55px; z-index:1;">
                            </div>
                            @endunless
                        </div>
                        <div class="flex-fill d-flex justify-content-between align-items-center px-3 py-2">
                            <div>
                                <span>المستوى {{ $stage['level'] ?? 'N/A' }}</span>
                                <h6 class="mb-0">{{ $stage['employee_name'] ?? 'غير محدد' }}</h6>
                            </div>
                            <div class="d-flex gap-1">
                                @if ($canRevoke)
                                <button class="btn btn-xs btn-outline-danger approval-action" data-id="{{ $item->id }}"
                                    data-action="revoke">إلغاء
                                    اعتماد</button>
                                @endif
                                @if ($canApprove)
                                <button class="btn btn-xs btn-outline-success approval-action" data-id="{{ $item->id }}"
                                    data-action="approve">اعتماد</button>
                                @endif
                                @if ($canReject)
                                <button class="btn btn-xs btn-outline-danger reject-action"
                                    data-id="{{ $item->id }}">رفض</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- سجل الاعتمادات (تصميم مطابق)                                    -->
    <!-- ============================================================== -->

    <div class="col-12 col-md-12">
        <div class="card">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-history text-primary me-2"></i>
                    سجل الاعتمادات
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body">
                @if ($approvalLogs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-clipboard-off ti-lg"></i>
                    <p>لا توجد سجلات اعتماد</p>
                </div>
                @else
                <ul class="timeline ms-2">
                    @foreach ($approvalLogs as $log)
                    @php
                    $ic = match ($log->action) {
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'revoked' => 'bg-warning',
                    default => 'bg-secondary',
                    };
                    @endphp
                    <li class="timeline-item pb-4">
                        <span class="timeline-point {{ $ic }}"></span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="mb-0">{{ $log->action_label ?? $log->action }}</h6>
                                <small class="text-muted">{{ $log->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-1"><strong>المعتمد:</strong>
                                {{ $log->employee->name ?? 'غير معروف' }}</p>
                            <p class="mb-1"><strong>المستوى:</strong> {{ $log->level }}</p>
                            @if ($log->signature_path)
                            <img src="{{ Storage::url($log->signature_path) }}" style="max-height:50px;" alt="توقيع">
                            @endif
                            @if ($log->action === 'rejected' && $log->reason)
                            <div class="alert alert-warning p-2 mt-2 small">{{ $log->reason }}</div>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection