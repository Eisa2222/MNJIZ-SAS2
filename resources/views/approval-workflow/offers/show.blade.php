@extends('layouts.layoutMaster')

@section('title', $item->offer_name . ' - إدارة الاعتمادات')

@section('breadcrumb')
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">{{ Str::limit($item->offer_name, 25) }}</a>
    <i class="ti ti-star favorite-icon" data-page-name="اعتماد العرض" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/approval-workflow/approval-manager.js',
'resources/assets/js/approval-workflow/approval-config.js',
'resources/assets/js/approval-workflow/offers/offers-show.js'])
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
<div class="row g-3 ">
    <!-- العمود الأيسر: محتوى العرض -->
    <div class="col-12 col-md-8 d-flex">
        <div class="card flex-fill d-flex flex-column ">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-file-text text-primary me-2"></i>
                    معاينة محتوى العرض
                </h6>
            </div>

            <div class="border-1 border-light border-dashed mb-2"></div>

            <div class="card-body flex-fill overflow-auto">
                <div class="offer-preview">
                    {!! $processedContent !!}
                </div>
            </div>
        </div>
    </div>
    <!-- العمود الأيمن: مراحل الاعتمادات -->
    <div class="col-12 col-md-4 ">
        <div class="card flex-fill d-flex flex-column  shadow-sm">
            <div class="card-header py-4 bg-white">
                <div class="d-flex align-items-center">
                    <h6 class="card-title mb-0 fw-semibold">
                        <i class="ti ti-list-details text-primary me-2"></i>
                        مراحل الاعتمادات
                    </h6>
                </div>
            </div>
            <!-- الخط المتقطع تحت العنوان -->
            <div class="border-1 border-light border-dashed mb-2"></div>

            <div class="card-body d-flex flex-column flex-fill overflow-auto p-0">
                @if (count($approvalStages) === 0)
                <div class="d-flex flex-column align-items-center justify-content-center flex-fill p-4">
                    <div class="avatar avatar-lg mb-3">
                        <div class="avatar-initial bg-secondary-subtle text-muted rounded-circle">
                            <i class="ti ti-clock-pause ti-lg"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">لا توجد معلومات اعتماد</h6>
                    <small class="text-muted text-center">لم يتم إنشاء مراحل اعتماد بعد</small>
                </div>
                @else
                <div class="p-4">
                    @foreach ($approvalStages as $index => $stage)
                    @php
                    $statusConfig = match ($stage['status']) {
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
                    $isLast = $index === count($approvalStages) - 1;
                    $isCurrentLevel = $stage['is_current'];
                    $showRevoke = $stage['can_revoke'] && $userPermissions['canRevoke'];
                    $showApprove = $stage['can_action'] && $userPermissions['canApprove'];
                    $showReject = $stage['can_action'] && $userPermissions['canReject'];
                    $isCurrent = $stage['is_current'];
                    @endphp

                    <div class="d-flex position-relative mb-8">
                        <!-- دائرة الحالة -->
                        <div class="me-3 position-relative" style="z-index:2;">
                            <div class="avatar avatar-md">
                                <div
                                    class="avatar-initial {{ $statusConfig['bgClass'] }} {{ $statusConfig['textClass'] }}
                                                   rounded-circle border-3 {{ $isCurrent ? 'border-primary' : 'border-0' }}">
                                    <i class="ti {{ $statusConfig['icon'] }} ti-md"></i>
                                </div>
                            </div>

                            @unless ($isLast)
                            <div class="position-absolute bg-{{ $statusConfig['lineColor'] }}"
                                style="left:50%; top:unset; transform:translateX(-50%); width:4px; height:55px; z-index:1;">
                            </div>
                            @endunless
                        </div>

                        <!-- محتوى المرحلة -->
                        <div class="flex-fill d-flex align-items-center justify-content-between px-3 py-2">
                            <div>
                                <span class="fw-semibold">المستوى {{ $stage['level'] }}</span>
                                <h6 class="mb-0 fw-bold">{{ $stage['employee_name'] }}</h6>
                            </div>
                            <div class="d-flex gap-1">
                                @if ($showRevoke)
                                <button class="btn btn-xs btn-outline-danger approval-action" data-id="{{ $item->id }}"
                                    data-action="revoke">
                                    إلغاء اعتماد
                                </button>
                                @endif

                                @if ($showApprove)
                                <button class="btn btn-xs btn-outline-success approval-action" data-id="{{ $item->id }}"
                                    data-action="approve">
                                    اعتماد
                                </button>
                                @endif

                                @if ($showReject)
                                <button class="btn btn-xs btn-outline-danger reject-action" data-id="{{ $item->id }}">
                                    رفض
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div class="card my-3">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-history text-primary me-2"></i>
                    سجل الاعتمادات
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>

            <div class="card-body">
                @if ($approvalLogs->count() > 0)
                <ul class="timeline">
                    @foreach ($approvalLogs as $log)
                    @php
                    $iconClass = match ($log->action) {
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'revoked' => 'bg-warning',
                    default => 'bg-secondary',
                    };

                    @endphp
                    <li class="timeline-item">
                        <span class="timeline-point {{ $iconClass }}"></span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="timeline-title mb-0">{{ $log->action_label }}</h6>
                                <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <strong>المعتمد:</strong> {{ $log->employee->name ?? 'غير معروف' }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>المستوى:</strong> {{ $log->level }}
                                    </p>
                                </div>

                                @if ($log->signature_path)
                                <div class="col-md-6 text-end">
                                    <div class="d-inline-block">
                                        <img src="{{ Storage::url($log->signature_path) }}" alt="التوقيع"
                                            class="border rounded" style="max-height: 50px; max-width: 100px;">
                                        <div class="small text-muted mt-1 text-center">التوقيع</div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if ($log->action === 'rejected' && $log->reason)
                            <div class="mt-3">
                                <div class="alert alert-warning alert-sm mb-0">
                                    <strong>سبب الرفض:</strong> {{ $log->reason }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-5">
                    <i class="ti ti-clipboard-off text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2 mb-0">لا يوجد سجلات اعتماد بعد</p>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection