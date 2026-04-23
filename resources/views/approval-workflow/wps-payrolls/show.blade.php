@extends('layouts.layoutMaster')

@section('title', 'اعتماد مسير رواتب - ' . $item->reference)

@section('breadcrumb')
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">تفاصيل مسير {{ $item->reference }}</a>
    <i class="ti ti-star favorite-icon" data-page-name="اعتماد مسير رواتب" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event,this)"></i>
</li>
@endsection

@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/approval-workflow/approval-config.js',
'resources/assets/js/approval-workflow/approval-manager.js',
'resources/assets/js/approval-workflow/wps-payrolls/show.js'])
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

@section('content')
<div class="row g-3 align-items-stretch">

    <!-- العمود الأيمن: تفاصيل مسير الرواتب -->
    <div class="col-12 col-md-8 d-flex">
        <div class="card flex-fill d-flex flex-column h-100">
            <div class="card-header py-4">
                <h6 class="card-title mb-0"><i class="ti ti-file-text text-primary me-2"></i>تفاصيل مسير الرواتب</h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body flex-fill overflow-auto">
                <div class="alert alert-primary" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-info-circle ti-lg me-2"></i>
                        <div>
                            يمكنك الاطلاع على التفاصيل الكاملة للمسير من <a
                                href="{{ route('hr.payrolls.wps.details.index', $item->id) }}" target="_blank"
                                class="alert-link fw-bold">هنا</a>.
                        </div>
                    </div>
                </div>
                <div class="table-responsive rounded-3 border mt-3">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light" style="width: 35%;">مرجع المسير
                                </td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">{{ $item->reference }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">تاريخ المسير</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">{{ $item->run_date?->format('F Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">إجمالي الاستحقاق</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ number_format($item->total_gross, 2) }} ر.س</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">صافي المبلغ</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ number_format($item->total_net, 2) }} ر.س</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">تاريخ الإنشاء</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $item->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- العمود الأيسر: مراحل الاعتمادات -->
    <div class="col-12 col-md-4 d-flex">
        <div class="card flex-fill d-flex flex-column h-100 shadow-sm">
            <div class="card-header py-4 bg-white">
                <div class="d-flex align-items-center">
                    <h6 class="card-title mb-0 fw-semibold">
                        <i class="ti ti-list-details text-primary me-2"></i>
                        مراحل الاعتمادات
                    </h6>
                </div>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body d-flex flex-column flex-fill overflow-auto p-0">
                @if (empty($approvalStages))
                <div class="d-flex flex-column align-items-center justify-content-center flex-fill p-4">
                    <div class="avatar avatar-lg mb-3">
                        <div class="avatar-initial bg-secondary-subtle text-muted rounded-circle"><i
                                class="ti ti-clock-pause ti-lg"></i></div>
                    </div>
                    <h6 class="text-muted mb-1">لا توجد مراحل اعتماد</h6>
                    <small class="text-muted text-center">لم يتم إرسال هذا المسير للاعتماد بعد</small>
                </div>
                @else
                <div class="p-4">
                    @foreach ($approvalStages as $stage)
                    @php
                    $status = $stage['status'] ?? 'pending';
                    $statusConfig = match ($status) {
                    'approved' => [
                    'lineColor' => 'success',
                    'bgClass' => 'bg-success',
                    'icon' => 'ti-check',
                    ],
                    'rejected' => [
                    'lineColor' => 'danger',
                    'bgClass' => 'bg-danger',
                    'icon' => 'ti-x',
                    ],
                    'pending' => [
                    'lineColor' => 'warning',
                    'bgClass' => 'bg-warning',
                    'icon' => 'ti-clock',
                    ],
                    default => [
                    'lineColor' => 'secondary',
                    'bgClass' => 'bg-secondary',
                    'icon' => 'ti-dots',
                    ],
                    };
                    $isLast = $loop->last;
                    $isCurrent = $stage['is_current'] ?? false;
                    $canAction = $isCurrent && ($stage['can_action'] ?? false);
                    $canApprove = $canAction && ($userPermissions['canApprove'] ?? false);
                    $canReject = $canAction && ($userPermissions['canReject'] ?? false);
                    $canRevoke =
                    ($stage['can_revoke'] ?? false) && ($userPermissions['canRevoke'] ?? false);
                    @endphp
                    <div class="d-flex position-relative {{ !$isLast ? 'mb-4' : '' }}">
                        <div class="me-3 position-relative" style="z-index:2;">
                            <div class="avatar avatar-md">
                                <div
                                    class="avatar-initial {{ $statusConfig['bgClass'] }} text-white rounded-circle border-3 {{ $isCurrent ? 'border-primary' : 'border-0' }}">
                                    <i class="ti {{ $statusConfig['icon'] }} ti-sm"></i>
                                </div>
                            </div>
                            @unless ($isLast)
                            <div class="position-absolute bg-{{ $statusConfig['lineColor'] }}"
                                style="left:50%; top: 100%; transform:translateX(-50%); width:2px; height:3.5rem; z-index:1;">
                            </div>
                            @endunless
                        </div>
                        <div class="flex-fill d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fw-semibold">المستوى {{ $stage['level'] ?? 'N/A' }}</span>
                                <h6 class="mb-0 fw-bold">{{ $stage['employee_name'] ?? 'غير محدد' }}</h6>
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
</div>
<div class="row mt-4">
    <div class="col-12">
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
                <div class="text-center py-5">
                    <i class="ti ti-clipboard-off text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2 mb-0">لا يوجد سجلات اعتماد بعد</p>
                </div>
                @else
                <ul class="timeline ms-2">
                    @foreach ($approvalLogs as $log)
                    @php
                    $logConfig = match ($log->action) {
                    'approved' => ['point_color' => 'bg-success'],
                    'rejected' => ['point_color' => 'bg-danger'],
                    'revoked' => ['point_color' => 'bg-warning'],
                    default => ['point_color' => 'bg-secondary'],
                    };
                    @endphp
                    <li class="timeline-item timeline-item-transparent pb-4">
                        <span class="timeline-point {{ $logConfig['point_color'] }}"></span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="timeline-title mb-0">{{ $log->action_label ?? $log->action }}</h6>
                                <small class="text-muted">{{ $log->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>المعتمد:</strong>
                                        {{ $log->employee->name ?? 'غير معروف' }} (المستوى
                                        {{ $log->level }})</p>
                                </div>
                                @if ($log->signature_path)
                                <div class="col-md-6 text-md-end">
                                    <div class="d-inline-block"><img src="{{ Storage::url($log->signature_path) }}"
                                            alt="التوقيع" class="border rounded"
                                            style="max-height: 40px; max-width: 100px;">
                                        <div class="small text-muted mt-1 text-center">التوقيع</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @if ($log->action === 'rejected' && $log->reason)
                            <div class="mt-3 alert alert-label-warning p-2 small mb-0"><strong>سبب
                                    الرفض:</strong> {{ $log->reason }}</div>
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