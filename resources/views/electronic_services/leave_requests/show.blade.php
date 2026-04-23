@extends('layouts.layoutMaster')

@section('title', 'تفاصيل طلب الإجازة - ' . ($leaveRequest->employee->name ?? 'غير محدد'))

@section('breadcrumb')
<li><a href="#">الخدمات الإلكترونية</a></li>
<li><a href="{{ route('account.electronic-services.leave-requests.index') }}">طلبات الإجازات</a></li>
<li class="breadcrumb-item d-flex align-items-center">
    <a href="#">تفاصيل طلب {{ Str::limit($leaveRequest->employee->name ?? 'غير محدد', 25) }}</a>
    <i class="ti ti-star favorite-icon" data-page-name="تفاصيل طلب الإجازة" data-page-url="{{ url()->current() }}"
        onclick="toggleFavorite(event, this)"></i>
</li>
@endsection

@section('toastr')
@vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
<!-- ============================================================== -->
<!-- القسم العلوي: تفاصيل الطلب والمرفقات ومراحل الاعتماد           -->
<!-- ============================================================== -->
<div class="row g-3 align-items-stretch">

    <!-- العمود الأيمن: تفاصيل طلب الإجازة -->
    <div class="col-12 col-md-8 d-flex">
        <div class="card flex-fill h-100">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-calendar-event text-primary me-2"></i>
                    تفاصيل طلب الإجازة
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body flex-fill d-flex flex-column">
                <div class="table-responsive rounded-3 border">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light" style="width: 35%;">الموظف</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $leaveRequest->employee->name ?? 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">نوع الإجازة</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $leaveRequest->leaveType->name ?? 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">تاريخ البداية</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $leaveRequest->start_date?->format('d/m/Y') ?? 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">تاريخ النهاية</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $leaveRequest->end_date?->format('d/m/Y') ?? 'غير محدد' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">عدد الأيام</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $leaveRequest->days_count }} يوم
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">الحالة</td>
                                <td class="py-3 ps-3 pe-4">
                                    <span class="badge bg-{{ $leaveRequest->status->color() }} fw-semibold">
                                        {{ $leaveRequest->status->label() }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">تاريخ الطلب</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">
                                    {{ $leaveRequest->created_at?->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @if ($leaveRequest->reason)
                            <tr>
                                <td class="fw-bold text-muted py-3 ps-4 pe-3 bg-light">ملاحظات</td>
                                <td class="py-3 ps-3 pe-4 fw-semibold text-dark">{{ $leaveRequest->reason }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- العمود الأيسر: المرفقات ومراحل الاعتماد -->
    <div class="col-12 col-md-4 d-flex flex-column">

        <!-- قسم المرفقات -->
        @if ($leaveRequest->attachments && $leaveRequest->attachments->count() > 0)
        <div class="card shadow-sm mb-3 flex-fill">
            <div class="card-header py-4">
                <h6 class="card-title mb-0">
                    <i class="ti ti-paperclip text-primary me-2"></i>
                    المرفقات
                    <span class="badge bg-primary ms-2">{{ $leaveRequest->attachments->count() }}</span>
                </h6>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body flex-fill d-flex flex-column">
                <div class="row g-2 flex-grow-1">
                    @foreach ($leaveRequest->attachments as $index => $attachment)
                    <div class="col-12">
                        <div class="d-flex align-items-center p-3 border rounded hover-shadow">
                            <div class="avatar avatar-sm me-3">
                                <div class="avatar-initial bg-primary-subtle text-primary rounded-circle">
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
        </div>
        @endif

        <!-- قسم مراحل الاعتمادات -->
        <div class="card shadow-sm flex-fill">
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
                        <div class="avatar-initial bg-secondary-subtle text-muted rounded-circle">
                            <i class="ti ti-clock-pause ti-lg"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1">لا توجد مراحل اعتماد</h6>
                    <small class="text-muted text-center">لم يتم إنشاء مراحل اعتماد لهذا الطلب بعد</small>
                </div>
                @else
                <div class="p-4">
                    @foreach ($approvalStages as $stage)
                    @php
                    $statusConfig = $stage['config'];
                    $isCurrent = $stage['is_current'] ?? false;
                    $isLast = $loop->last;
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
                        <div class="flex-fill d-flex align-items-center">
                            <div>
                                <span class="fw-semibold">المستوى {{ $stage['level'] ?? 'N/A' }}</span>
                                <h6 class="mb-0 fw-bold">{{ $stage['employee_name'] ?? 'غير محدد' }}</h6>
                                @if ($isCurrent)
                                @endif
                                @if (isset($stage['action_date']) && $stage['action_date'])
                                <small class="text-muted d-block">{{ $stage['action_date'] }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- قسم سجل النشاطات الأساسية (إنشاء/تعديل الطلب) -->
        <div class="card shadow-sm mt-3 flex-fill">
            <div class="card-header py-4 bg-white">
                <div class="d-flex align-items-center">
                    <h6 class="card-title mb-0 fw-semibold">
                        <i class="ti ti-history text-primary me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
            </div>
            <div class="border-1 border-light border-dashed mb-2"></div>
            <div class="card-body flex-fill d-flex flex-column">
                <ul class="timeline ms-2 flex-grow-1">
                    <!-- إنشاء الطلب -->
                    <li class="timeline-item timeline-item-transparent pb-4">
                        <span class="timeline-point bg-primary"></span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="timeline-title mb-0">تم إنشاء الطلب</h6>
                                <small class="text-muted">{{ $leaveRequest->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-0 text-muted">
                                <strong>بواسطة:</strong> {{ $leaveRequest->createdBy->name ?? 'غير معروف' }}
                            </p>
                        </div>
                    </li>

                    <!-- آخر تحديث -->
                    @if ($statusInfo['hasUpdates'])
                    <li class="timeline-item timeline-item-transparent pb-4">
                        <span class="timeline-point bg-warning"></span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="timeline-title mb-0">آخر تحديث</h6>
                                <small class="text-muted">{{ $leaveRequest->updated_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-0 text-muted">
                                <strong>بواسطة:</strong> {{ $leaveRequest->updatedBy->name ?? 'غير معروف' }}
                            </p>
                        </div>
                    </li>
                    @endif

                    <!-- تغيير الحالة -->
                    @if ($statusInfo['showStatusChange'])
                    <li class="timeline-item timeline-item-transparent">
                        <span class="timeline-point bg-{{ $leaveRequest->status->color() }}"></span>
                        <div class="timeline-event">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="timeline-title mb-0">تم {{ $leaveRequest->status->label() }}</h6>
                                <small class="text-muted">{{ $leaveRequest->updated_at?->format('d/m/Y H:i') }}</small>
                            </div>
                            @if (isset($statusInfo['statusMessage']))
                            <div class="alert {{ $statusInfo['alertClass'] }} p-2 small mb-0">
                                <i class="ti {{ $statusInfo['statusIcon'] }} me-1"></i>
                                {{ $statusInfo['statusMessage'] }}
                            </div>
                            @endif
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection