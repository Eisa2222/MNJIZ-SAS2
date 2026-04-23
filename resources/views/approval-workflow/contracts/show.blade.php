@extends('layouts.layoutMaster')

@section('title', $item->contract_name . ' - إدارة الاعتمادات')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a>{{ Str::limit($item->contract_name, 25) }}</a>
</li>
@endsection

@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/approval-workflow/approval-manager.js',
'resources/assets/js/approval-workflow/approval-config.js',
'resources/assets/js/approval-workflow/contracts/contracts-show.js'])
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
<div class="row g-3">
    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-file-text text-primary me-2"></i>
                    معاينة محتوى العقد
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body overflow-auto">
                {!! $processedContent !!}
            </div>
        </div>
    </div>
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
                @if (count($approvalStages) === 0)
                <div class="p-4 text-center text-muted">
                    <i class="ti ti-clock-pause ti-lg"></i>
                    <p>لا توجد مراحل اعتماد</p>
                </div>
                @else
                <div class="p-4">
                    @foreach ($approvalStages as $i => $stage)
                    @php
                    $cfg = match ($stage['status']) {
                    'approved' => ['line' => 'success', 'bg' => 'bg-success', 'icon' => 'ti-check'],
                    'rejected' => ['line' => 'danger', 'bg' => 'bg-danger', 'icon' => 'ti-x'],
                    'pending' => ['line' => 'warning', 'bg' => 'bg-warning', 'icon' => 'ti-clock'],
                    default => [
                    'line' => 'secondary',
                    'bg' => 'bg-secondary',
                    'icon' => 'ti-dots',
                    ],
                    };
                    $isLast = $i === count($approvalStages) - 1;
                    @endphp
                    <div class="d-flex position-relative mb-8">
                        <div class="me-3 position-relative" style="z-index:2">
                            <div class="avatar avatar-md">
                                <div
                                    class="avatar-initial {{ $cfg['bg'] }} text-white rounded-circle border-3 {{ $stage['is_current'] ? 'border-primary' : '' }}">
                                    <i class="ti {{ $cfg['icon'] }}"></i>
                                </div>
                            </div>
                            @unless ($isLast)
                            <div class="position-absolute bg-{{ $cfg['line'] }}"
                                style="left:50%; top:unset; transform:translateX(-50%); width:4px; height:60px; z-index:1;">
                            </div>
                            @endunless
                        </div>
                        <div class="flex-fill d-flex justify-content-between align-items-center px-3 py-2">
                            <div>
                                <span>المستوى {{ $stage['level'] }}</span>
                                <h6>{{ $stage['employee_name'] }}</h6>
                            </div>
                            <div class="d-flex gap-1">
                                @if ($stage['can_revoke'] && $userPermissions['canRevoke'])
                                <button class="btn btn-xs btn-outline-danger approval-action" data-id="{{ $item->id }}"
                                    data-action="revoke">إلغاء اعتماد</button>
                                @endif
                                @if ($stage['can_action'] && $userPermissions['canApprove'])
                                <button class="btn btn-xs btn-outline-success approval-action" data-id="{{ $item->id }}"
                                    data-action="approve">اعتماد</button>
                                @endif
                                @if ($stage['can_action'] && $userPermissions['canReject'])
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

{{-- سجل الاعتمادات --}}
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
                @if ($approvalLogs->count())
                <ul class="timeline">
                    @foreach ($approvalLogs as $log)
                    @php
                    $ic = match ($log->action) {
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'revoked' => 'bg-warning',
                    default => 'bg-secondary',
                    };
                    @endphp
                    <li class="timeline-item">
                        <span class="timeline-point {{ $ic }}"></span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between mb-2">
                                <h6>{{ $log->action_label }}</h6>
                                <small>{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p><strong>المعتمد:</strong> {{ $log->employee->name }}</p>
                            <p><strong>المستوى:</strong> {{ $log->level }}</p>
                            @if ($log->signature_path)
                            <img src="{{ Storage::url($log->signature_path) }}" style="max-height:50px;" alt="توقيع">
                            @endif
                            @if ($log->action === 'rejected' && $log->reason)
                            <div class="alert alert-warning mt-2">{{ $log->reason }}</div>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-clipboard-off" style="font-size:3rem"></i>
                    <p>لا توجد سجلات اعتماد</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection