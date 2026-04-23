@extends('layouts.layoutMaster')

@section('title', 'اعتماد طلب عهدة - ' . ($item->employee->name ?? 'غير محدد'))

@section('breadcrumb')
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">تفاصيل طلب {{ Str::limit($item->employee->name ?? 'غير محدد', 25) }}</a>
    <i class="ti ti-star favorite-icon" data-page-name="اعتماد طلب عهدة" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/approval-workflow/approval-config.js',
'resources/assets/js/approval-workflow/approval-manager.js', 'resources/assets/js/approval-workflow/custodies/show.js'])
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

    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header py-4">

                <h6 class="card-title mb-0">
                    <i class="ti ti-list-details text-warning me-2"></i>
                    تفاصيل الطلب
                </h6>
            </div>

            <div class="border-1 border-light border-dashed mb-2"></div>

            <div class="card-body px-5">
                <div class="table-responsive">
                    <table class="table table-striped  small text-center">
                        <tr>
                            <td class="fw-bold">الاصل</td>
                            <td class="fw-bold">{{ $item->item->name }}</td>
                        </tr>

                        <tr>
                            <td class="fw-bold">الرقم التسلسلي</td>
                            <td>{{ $item->item->serial_number }}</td>
                        </tr>

                        <tr>
                            <td class="fw-bold">التصنيف</td>
                            <td>{{ $item->item->assetCategory->name }}</td>
                        </tr>

                        <tr>
                            <td class="fw-bold">مرجعية الاصل</td>
                            <td>{{ $item->item->storageLocation->name }}</td>
                        </tr>

                        <tr>
                            <td class="fw-bold">نوع الطلب</td>
                            <td class="fw-bold">{{ $item->request_type->label() }}</td>
                        </tr>


                        @if ($item->return_status)
                        <tr>
                            <td class="fw-bold">سبب الارجاع</td>
                            <td class="text-danger">{{ $item->return_status->label() }}</td>
                        </tr>
                        @endif


                        <tr>
                            <td class="fw-bold">الحالة</td>
                            <td>{{ $item->status->label() }}</td>
                        </tr>

                        <tr>
                            <td class="fw-bold">ملاحظات</td>
                            <td>{{ $item->notes ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                    <small class="text-muted text-center">لم يتم إنشاء مراحل اعتماد لهذا الطلب بعد</small>
                </div>
                @else
                <div class="p-4">
                    @foreach ($approvalStages as $index => $stage)
                    @php
                    $status = $stage['status'] ?? 'pending';
                    $statusConfig = match ($status) {
                    'approved' => [
                    'lineColor' => 'success',
                    'bgClass' => 'bg-success',
                    'textClass' => 'text-white',
                    'icon' => 'ti-check',
                    ],
                    'rejected' => [
                    'lineColor' => 'danger',
                    'bgClass' => 'bg-danger',
                    'textClass' => 'text-white',
                    'icon' => 'ti-x',
                    ],
                    'pending' => [
                    'lineColor' => 'warning',
                    'bgClass' => 'bg-warning',
                    'textClass' => 'text-white',
                    'icon' => 'ti-clock',
                    ],
                    default => [
                    'lineColor' => 'secondary',
                    'bgClass' => 'bg-secondary',
                    'textClass' => 'text-white',
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
                    <div class="d-flex position-relative mb-8">
                        <!-- دائرة الحالة -->
                        <div class="me-3 position-relative" style="z-index:2;">
                            <div class="avatar avatar-md">
                                <div
                                    class="avatar-initial {{ $statusConfig['bgClass'] }} {{ $statusConfig['textClass'] }} rounded-circle border-3 {{ $isCurrent ? 'border-primary' : 'border-0' }}">
                                    <i class="ti {{ $statusConfig['icon'] }} ti-sm"></i>
                                </div>
                            </div>
                            @unless ($isLast)
                            <div class="position-absolute bg-{{ $statusConfig['lineColor'] }}"
                                style="left:50%; top:unset; transform:translateX(-50%); width:4px; height:55px; z-index:1;">
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
                    'approved' => ['icon' => 'ti-circle-check', 'point_color' => 'bg-success'],
                    'rejected' => ['icon' => 'ti-circle-x', 'point_color' => 'bg-danger'],
                    'revoked' => ['icon' => 'ti-arrow-back-up', 'point_color' => 'bg-warning'],
                    default => ['icon' => 'ti-circle-dotted', 'point_color' => 'bg-secondary'],
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
                                    <div class="d-inline-block">
                                        <img src="{{ Storage::url($log->signature_path) }}" alt="التوقيع"
                                            class="border rounded" style="max-height: 40px; max-width: 100px;">
                                        <div class="small text-muted mt-1 text-center">التوقيع</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @if ($log->action === 'rejected' && $log->reason)
                            <div class="mt-3 alert alert-label-warning p-2 small mb-0">
                                <strong>سبب الرفض:</strong> {{ $log->reason }}
                            </div>
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