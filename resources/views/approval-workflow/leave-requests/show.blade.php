@extends('layouts.layoutMaster')

@section('title', $item->employee->name . ' - طلب إجازة #' . $item->id)

@section('breadcrumb')
<li class="breadcrumb-item">
    <a>{{ Str::limit($item->leaveType->name, 25) }}</a>
</li>
@endsection

@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/approval-workflow/approval-manager.js',
'resources/assets/js/approval-workflow/approval-config.js',
'resources/assets/js/approval-workflow/leave-requests/leave-requests-show.js'])
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
    {{-- معاينة تفاصيل الطلب --}}
    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-file-text text-primary me-2"></i>
                    تفاصيل طلب
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            {{-- معاينة تفاصيل الطلب --}}
            <div class="col-12 col-md-12">
                <div class="mt-4">
                    <div class="card-body">
                        <div class="row g-0">
                            <div class="col-12">
                                <div class="table-responsive rounded-3 border">
                                    <table class="table table-striped table-hover mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold text-muted py-4 ps-4 pe-3 bg-light"
                                                    style="width: 35%;">
                                                    رقم الطلب
                                                </td>
                                                <td class="py-4 ps-3 pe-4 fw-semibold text-dark">
                                                    {{ $item->id }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted py-4 ps-4 pe-3 bg-light">
                                                    اسم الموظف
                                                </td>
                                                <td class="py-4 ps-3 pe-4 fw-semibold text-dark">
                                                    {{ $item->employee->name }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted py-4 ps-4 pe-3 bg-light">
                                                    نوع الإجازة
                                                </td>
                                                <td class="py-4 ps-3 pe-4 fw-semibold text-dark">
                                                    {{ $item->leaveType->name }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted py-4 ps-4 pe-3 bg-light">
                                                    عدد الأيام
                                                </td>
                                                <td class="py-4 ps-3 pe-4 fw-semibold text-dark">
                                                    {{ $item->days_count }} يوم
                                                </td>
                                            </tr>
                                            @if ($item->start_date->format('Y-m-d') ===
                                            $item->end_date->format('Y-m-d'))
                                            <tr>
                                                <td class="fw-bold text-muted py-4 ps-4 pe-3 bg-light">
                                                    تاريخ الإجازة
                                                </td>
                                                <td class="py-4 ps-3 pe-4 fw-semibold text-dark">
                                                    {{ $item->start_date->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                            @else
                                            <tr>
                                                <td class="fw-bold text-muted py-4 ps-4 pe-3 bg-light">
                                                    تاريخ البداية
                                                </td>
                                                <td class="py-4 ps-3 pe-4 fw-semibold text-dark">
                                                    {{ $item->start_date->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-muted py-4 ps-4 pe-3 bg-light">
                                                    تاريخ النهاية
                                                </td>
                                                <td class="py-4 ps-3 pe-4 fw-semibold text-dark">
                                                    {{ $item->end_date->format('d/m/Y') }}
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                {{-- قسم المرفقات - إضافة جديدة --}}
                                @if ($item->attachments && $item->attachments->count() > 0)
                                <div class="mt-4">
                                    <h6 class="mb-3">
                                        <i class="ti ti-paperclip text-primary me-2"></i>
                                        المرفقات
                                        <span class="badge bg-primary ms-2">{{ $item->attachments->count() }}</span>
                                    </h6>
                                    <div class="row g-2">
                                        @foreach ($item->attachments as $index => $attachment)
                                        <div class="col-12">
                                            <div class="d-flex align-items-center p-3 border rounded hover-shadow">
                                                <div class="avatar avatar-sm me-3">
                                                    <div
                                                        class="avatar-initial bg-primary-subtle text-primary rounded-circle">
                                                        <i class="ti ti-file-text"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-truncate" title="{{ $attachment->file_name }}">
                                                        {{ Str::limit($attachment->file_name, 25) }}
                                                    </h6>
                                                    <small class="text-muted">المرفق رقم {{ $index + 1 }}</small>
                                                </div>
                                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                {{-- نهاية قسم المرفقات --}}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- مراحل الاعتماد --}}
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
                    <div class="d-flex position-relative mb-4">
                        <div class="me-3 position-relative" style="z-index:2">
                            <div class="avatar avatar-md">
                                <div class="avatar-initial {{ $cfg['bg'] }} text-white rounded-circle
                                                {{ $stage['is_current'] ? 'border border-3 border-primary' : '' }}">
                                    <i class="ti {{ $cfg['icon'] }}"></i>
                                </div>
                            </div>
                            @unless ($isLast)
                            <div class="position-absolute bg-{{ $cfg['line'] }}"
                                style="left:50%;width:4px;height:60px;transform:translateX(-50%);z-index:1">
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
                                    data-action="revoke">إلغاء
                                    اعتماد</button>
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
                @if ($approvalLogs->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-clipboard-off ti-lg"></i>
                    <p>لا توجد سجلات اعتماد</p>
                </div>
                @else
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
                @endif
            </div>
        </div>
    </div>
</div>
@endsection