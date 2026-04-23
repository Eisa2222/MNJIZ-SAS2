@extends('layouts.layoutMaster')

@section('title', 'تفاصيل المهمة')
@section('breadcrumb')
    <li><a href="#">مركز تنظيم الاعمال</a></li>

    <li><a href="{{ route('organization-center.tasks.index') }}">المهام</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">تفاصيل المهمة </a>
        <i class="ti ti-star favorite-icon" data-page-name="تفاصيل المهمة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/organization-center/tasks/task_show_management.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title  mb-0">
                        <i class="ti ti-list-details text-warning me-2"></i>
                        تفاصيل المهمة
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-striped small text-center">
                            <tr>
                                <td class="fw-bold" style="width: 30%;">عنوان المهمة</td>
                                <td class="text-start">{{ $task->task_name }}</td>
                            </tr>

                            <tr>
                                <td class="fw-bold">
                                    تاريخ الاستحقاق
                                </td>
                                <td class="text-start">
                                    <span class="badge bg-danger-subtle text-danger">
                                        {{ \Carbon\Carbon::parse($task->due_date . ' ' . $task->due_time)->format('Y-m-d H:i') }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">مجال المهمة</td>
                                <td class="text-start">
                                    <span
                                        class="badge bg-{{ $task->task_field->color() }}">{{ $task->task_field->label() }}</span>
                                </td>
                            </tr>

                            @if ($task->offer_id && $task->offer)
                                <tr>
                                    <td class="fw-bold">
                                        العرض
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.offers.show', $task->offer_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>{{ $task->offer->offer_name }}
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->contract_id && $task->contract)
                                <tr>
                                    <td class="fw-bold">
                                        العقد
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.contracts.show', $task->contract_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>{{ $task->contract->contract_name }}
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->project_id && $task->project)
                                <tr>
                                    <td class="fw-bold">
                                        المشروع
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('projects.show', $task->project_id) }}"
                                            class="text-decoration-none">
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="ti ti-external-link me-1"></i>{{ $task->project->project_name }}
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->lawsuit_id && $task->lawsuit)
                                <tr>
                                    <td class="fw-bold">
                                        الدعوى
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('legal-affairs.lawsuits.show', $task->lawsuit_id) }}"
                                            class="text-decoration-none">
                                            <span class="badge bg-purple-subtle text-purple">
                                                <i class="ti ti-external-link me-1"></i>{{ $task->lawsuit->name }}
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->session_id && $task->session)
                                <tr>
                                    <td class="fw-bold">
                                        الجلسة
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('legal-affairs.sessions.show', $task->session_id) }}"
                                            class="text-decoration-none">
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="ti ti-external-link me-1"></i>{{ $task->session->session_name }}
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->clearance_certificate_id && $task->clearanceCertificate)
                                <tr>
                                    <td class="fw-bold">
                                        إخلاء الطرف
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.clearance-certificates.show', $task->clearance_certificate_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>الطلب
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif


                            @if ($task->advance_id && $task->advance)
                                <tr>
                                    <td class="fw-bold">
                                        طلب سلفة
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.advances.show', $task->advance_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>الطلب
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->reward_id && $task->reward)
                                <tr>
                                    <td class="fw-bold">
                                        طلب مكافأة
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.rewards.show', $task->reward_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>الطلب
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->deduction_id && $task->deduction)
                                <tr>
                                    <td class="fw-bold">
                                        طلب خصم
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.deductions.show', $task->deduction_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>الطلب
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->content_management_id && $task->contentManagement)
                                <tr>
                                    <td class="fw-bold">
                                        طلب نشر محتوى
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.content.show', $task->content_management_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>الطلب
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif


                            @if ($task->custody_id && $task->custody)
                                <tr>
                                    <td class="fw-bold">
                                        طلب عهدة
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.custodies.show', $task->custody_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>الطلب
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->leave_id && $task->leave)
                                <tr>
                                    <td class="fw-bold">
                                        طلب إجازة
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('approval-workflow.leave-requests.show', $task->leave_id) }}"
                                            class="text-decoration-none">
                                            <span>
                                                <i class="ti ti-external-link me-1"></i>الطلب
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td class="fw-bold">
                                    أولوية المهمة
                                </td>
                                <td class="text-start">
                                    <span
                                        class="badge bg-{{ $task->priority->color() }}">{{ $task->priority->label() }}</span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">
                                    حالة المهمة
                                </td>
                                <td class="text-start">
                                    <span
                                        class="badge bg-{{ $task->status->color() }}">{{ $task->status->label() }}</span>
                                </td>

                            </tr>

                            <tr>
                                <td class="fw-bold">
                                    المكلفين
                                </td>
                                <td class="text-start">
                                    @if ($task->assignedUsers && $task->assignedUsers->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($task->assignedUsers as $user)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <img src="{{ $user->employee->profile_picture ? asset('storage/' . $user->employee->profile_picture) : asset('assets/img/avatars/1.png') }}"
                                                        alt="{{ $user->employee->name }}" class="rounded-circle me-1"
                                                        style="width: 16px; height: 16px;">
                                                    {{ $user->employee->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">لا يوجد مكلفين</span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">
                                    منشئ المهمة
                                </td>
                                <td class="text-start">
                                    @if ($task->createdBy)
                                        <span class="badge bg-primary-subtle text-primary">
                                            <img src="{{ $task->createdBy->employee->profile_picture ? asset('storage/' . $task->createdBy->employee->profile_picture) : asset('assets/img/avatars/1.png') }}"
                                                alt="{{ $task->createdBy->employee->name }}" class="rounded-circle me-1"
                                                style="width: 16px; height: 16px;">
                                            {{ $task->createdBy->employee->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">تلقائيا من النظام</span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-bold">
                                    تاريخ الإنشاء
                                </td>
                                <td class="text-start">
                                    <span class="badge bg-success-subtle text-success">
                                        {{ $task->created_at->format('Y-m-d H:i') }}
                                    </span>
                                    <small class="text-muted ms-2">
                                        ({{ $task->created_at->diffForHumans() }})
                                    </small>
                                </td>
                            </tr>

                            @if ($task->task_start_date)
                                <tr>
                                    <td class="fw-bold">
                                        تاريخ البداية
                                    </td>
                                    <td class="text-start">
                                        <span class="badge bg-primary-subtle text-primary">
                                            {{ \Carbon\Carbon::parse($task->task_start_date)->format('Y-m-d H:i') }}
                                        </span>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->completed_at)
                                <tr>
                                    <td class="fw-bold">
                                        تاريخ الإكمال
                                    </td>
                                    <td class="text-start">
                                        <span class="badge bg-success-subtle text-success">
                                            {{ \Carbon\Carbon::parse($task->completed_at)->format('Y-m-d H:i') }}
                                        </span>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->steps && $task->steps->count() > 0)
                                <tr>
                                    <td class="fw-bold">
                                        عدد الخطوات
                                    </td>
                                    <td class="text-start">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning-subtle text-warning me-2">
                                                {{ $completedSteps }} من {{ $totalSteps }}
                                            </span>
                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar"
                                                    style="width: {{ $progressPercentage }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $progressPercentage }}%</small>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->attachments && $task->attachments->count() > 0)
                                <tr>
                                    <td class="fw-bold">
                                        المرفقات
                                    </td>
                                    <td class="text-start">
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <i class="ti ti-files me-1"></i>{{ $task->attachments->count() }} ملف
                                        </span>
                                    </td>
                                </tr>
                            @endif

                            @if ($task->description)
                                <tr>
                                    <td class="fw-bold align-top">
                                        الوصف
                                    </td>
                                    <td class="text-start">
                                        <div class="">
                                            {{ $task->description }}
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td class="fw-bold align-top">
                                    الاجراءات
                                </td>
                                <td>
                                    <div>
                                        @if ($task->isReturnedAndCanRespond())
                                            <button type="button" class="py-1 btn btn-primary btn-sm"
                                                onclick="confirmReassign({{ $task->id }})">
                                                رد على الإرجاع
                                            </button>
                                        @elseif ($task->getStatusMessage())
                                            <span class="badge fw-bold rounded-pill bg-danger">
                                                {{ $task->getStatusMessage() }}
                                            </span>
                                        @elseif ($task->canShowActions())
                                            @if ($task->canIncomplete())
                                                <button type="button" class="py-1 btn btn-danger btn-sm"
                                                    onclick="confirmIncomplete({{ $task->id }})">
                                                    إلغاء الإكمال
                                                </button>
                                            @elseif ($task->canComplete())
                                                <button type="button" class="py-1 btn btn-primary btn-sm"
                                                    onclick="confirmComplete({{ $task->id }})">
                                                    إكمال المهمة
                                                </button>
                                            @endif

                                            @if ($task->canReturn())
                                                <button type="button" class="py-1 btn btn-danger btn-sm"
                                                    onclick="confirmReturn({{ $task->id }})">
                                                    إرجاع المهمة
                                                </button>
                                            @endif
                                        @else
                                            <span class="badge fw-bold rounded-pill bg-secondary">
                                                لا توجد إجراءات متاحة
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>

            @if ($task->steps->count() > 0)
                <div class="card mt-5">
                    <div class="card-header">
                        <h6 class="fw-bold mb-0">تفاصيل الخطوات</h6>
                    </div>
                    <div class="card-body px-5">
                        <div class="row">
                            <div class="nested-steps-container ">
                                <table class="table table-bordered nested-steps-table w-100 small">
                                    <thead style="background: rgb(239 238 240 / 42%) ">
                                        <tr>
                                            <th>اكمال الخطوة</th>
                                            <th>وصف الخطوة</th>
                                            <th>المكلفين</th>
                                            <th>الحالة</th>
                                            <th>المدة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($task->steps->sortBy('step_order') as $index => $step)
                                            <tr class="">
                                                <td class="text-center">

                                                    @if ($task->status == 'completed')
                                                        <small>المهمة مكتملة</small>
                                                    @elseif($task->status == 'rejected')
                                                        <small>تم رفض الاعتماد</small>
                                                    @else
                                                        @if ($step->needs_approval)
                                                            <div class="approval-buttons">
                                                                <button
                                                                    class="btn btn-approve btn-sm btn-primary step-approval-btn mb-2 w-100  {{ $step->status === 'approved' ? 'disabled' : '' }}"
                                                                    data-step-id="{{ $step->id }}">
                                                                    اعتماد
                                                                </button>
                                                                <button
                                                                    class="btn btn-reject btn-sm btn-secondary step-approval-btn w-100 {{ $step->status === 'rejected' ? 'disabled' : '' }}"
                                                                    data-step-id="{{ $step->id }}">
                                                                    رفض
                                                                </button>
                                                            </div>
                                                        @else
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input step-checkbox"
                                                                    type="checkbox" id="stepCheckbox{{ $step->id }}"
                                                                    {{ in_array($step->status, ['completed', 'approved']) ? 'checked' : '' }}
                                                                    data-step-id="{{ $step->id }}">
                                                            </div>
                                                        @endif
                                                    @endif

                                                </td>
                                                <td class="fw-semibold">{{ $step->name }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary assigned-users-btn"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#assignedUsers{{ $step->id }}"
                                                        aria-expanded="false">
                                                        {{-- <i class="ti ti-users me-1"></i> --}}
                                                        عرض المكلفين ({{ $step->assignedUsers->count() }})
                                                    </button>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $step->status->color() }}">{{ $step->status->label() }}</span>
                                                </td>
                                                {{-- <td>{{ $step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-' }}
                                                    </td> --}}
                                                {{-- <td>{{ $step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-' }}
                                                    </td> --}}
                                                <td>{{ $step->duration ?: '-' }}</td>
                                            </tr>
                                            <tr class="collapse assigned-users-row"
                                                id="assignedUsers{{ $step->id }}">
                                                <td colspan="8">
                                                    <div class="p-3">
                                                        <h6 class="mb-3">المكلفين بالخطوة</h6>
                                                        <div class="row g-3">
                                                            @foreach ($step->assignedUsers as $user)
                                                                <div class="col-md-4">
                                                                    <a href="{{ route('account.employee.profile', $user->employee->id) }}"
                                                                        class="text-decoration-none">
                                                                        <div
                                                                            class="d-flex align-items-center p-2 border rounded hover-bg">
                                                                            <div class="avatar-wrapper me-1">
                                                                                <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('assets/img/avatars/1.png') }}"
                                                                                    class="rounded-circle" width="40"
                                                                                    height="40"
                                                                                    alt="{{ $user->employee->name }}">
                                                                            </div>
                                                                            <div>
                                                                                <h6 class="mb-0 small">
                                                                                    {{ $user->employee->name }}
                                                                                </h6>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @if ($step->status === 'rejected' && $step->reject_reason)
                                                <tr>
                                                    <td>
                                                        <small class="text-danger text-nowrap"><i
                                                                class="ti ti-alert-circle me-1"></i>سبب الرفض</small>

                                                    </td>
                                                    <td colspan="5" class="text-danger">
                                                        {{ $step->reject_reason }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- موديل سبب الرفض -->
                        <div class="modal fade" id="rejectReasonModal" tabindex="-1"
                            aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="rejectReasonModalLabel">سبب الرفض</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="إغلاق"></button>
                                    </div>
                                    <div class="modal-body">
                                        <textarea id="rejectReasonText" class="form-control" placeholder="أدخل سبب الرفض هنا"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="confirmRejectBtn" class="btn btn-danger">رفض</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">إلغاء</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-header py-4">
                    <h6 class="card-title  mb-0">
                        <i class="ti ti-progress text-warning me-2"></i>
                        تفاصيل الإنجاز
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body px-5">
                    <div class="table-responsive">
                        <table class="table table-bordered nested-steps-table w-100 small">
                            <thead style="background: rgb(239 238 240 / 42%) ">
                                <tr>
                                    <th>العنصر</th>
                                    <th>الحالة</th>
                                    <th>تاريخ البداية</th>
                                    <th>تاريخ الإنجاز</th>
                                    <th>المنجز</th>
                                    <th>المدة</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- المهمة الرئيسية --}}
                                <tr>
                                    <td><strong>المهمة الرئيسية</strong></td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $task->status->color() }}">{{ $task->status->label() }}</span>
                                    </td>
                                    <td>{{ $task->task_start_date ? $task->task_start_date->format('Y-m-d H:i:s') : '-' }}
                                    </td>
                                    <td>{{ $task->task_end_date ? $task->task_end_date->format('Y-m-d H:i:s') : '-' }}
                                    </td>
                                    <td>
                                        @if ($task->status->value === 'completed' && $task->completedBy)
                                            <a
                                                href="{{ route('account.employee.profile', $task->completedBy->employee->id) }}">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $task->completedBy->employee->profile_picture
                                                        ? asset('storage/' . $task->completedBy->employee->profile_picture)
                                                        : asset('assets/img/avatars/1.png') }}"
                                                        class="rounded-circle" width="25" alt="Avatar">
                                                    <span class="ms-2">{{ $task->completedBy->employee->name }}</span>
                                                </div>
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $task->duration ?? '-' }}</td>
                                </tr>

                                {{-- الخطوات --}}
                                @foreach ($task->steps as $step)
                                    <tr data-step-id="{{ $step->id }}">
                                        <td>{{ $step->name }}</td>
                                        {{-- <td>الخطوة {{ $loop->iteration }}: {{ $step->name }}</td> --}}

                                        <td>
                                            <span
                                                class="badge bg-{{ $step->status->color() }}">{{ $step->status->label() }}</span>

                                        </td>

                                        <td>{{ $step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-' }}
                                        </td>
                                        <td>{{ $step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-' }}
                                        </td>
                                        <td>
                                            @if ($step->completed_by)
                                                <a
                                                    href="{{ route('account.employee.profile', $step->completedBy->employee->id) }}">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $step->completedBy->employee->profile_picture
                                                            ? asset('storage/' . $step->completedBy->employee->profile_picture)
                                                            : asset('assets/img/avatars/1.png') }}"
                                                            class="rounded-circle" width="25" alt="Avatar">
                                                        <span
                                                            class="ms-2">{{ $step->completedBy->employee->name }}</span>
                                                    </div>
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $step->duration ?: '-' }}</td>
                                    </tr>
                                    @if ($step->status === 'rejected' && $step->reject_reason)
                                        <tr>
                                            <td>
                                                <small class="text-danger text-nowrap"><i
                                                        class="ti ti-alert-circle me-1"></i>سبب الرفض</small>

                                            </td>
                                            <td colspan="5" class="text-danger">
                                                {{ $step->reject_reason }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



            {{-- تفاصيل الإسناد / الإرجاع --}}
            @if ($task->routings->count())
                <div class="card mt-3">
                    <div class="card-header py-4">
                        <h6 class="card-title mb-0">
                            <i class="ti ti-repeat text-warning me-2"></i>
                            تفاصيل الإسناد / الإرجاع
                        </h6>
                    </div>

                    <div class="border-1 border-light border-dashed mb-2"></div>
                    <div class="card-body px-5">
                        <div class="table-responsive">
                            <table class="table table-bordered w-100 small">
                                <thead style="background: rgb(239 238 240 / 42%)">
                                    <tr>
                                        <th>الإجراء</th>
                                        <th>من</th>
                                        <th>السبب / الملاحظة</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($task->routings->sortByDesc('created_at') as $routing)
                                        @php
                                            // جلب اللون والوسم من enum (أو اعمل ماب بسيط إن لم تستخدم enum)
                                            $badgeClass = $routing->action->color(); // primary / warning …
                                            $badgeText = $routing->action->label(); // إسناد / إرجاع
                                        @endphp
                                        <tr>
                                            {{-- الإجراء --}}
                                            <td>
                                                <span class="badge bg-{{ $badgeClass }}">
                                                    {{ $badgeText }}
                                                </span>
                                            </td>

                                            {{-- من --}}
                                            <td>
                                                <a href="{{ route('account.employee.profile', $routing->fromUser->employee->id) }}"
                                                    class="text-decoration-none">
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $routing->fromUser->employee->profile_picture
                                                            ? asset('storage/' . $routing->fromUser->employee->profile_picture)
                                                            : asset('assets/img/avatars/1.png') }}"
                                                            class="rounded-circle" width="25" alt="Avatar">
                                                        <span
                                                            class="ms-2">{{ $routing->fromUser->employee->name }}</span>
                                                    </div>
                                                </a>
                                            </td>


                                            {{-- السبب --}}
                                            <td>{{ $routing->reason ?: '-' }}</td>

                                            {{-- التاريخ --}}
                                            <td>
                                                {{ $routing->created_at->format('Y-m-d H:i') }}
                                                <small class="text-muted d-block">
                                                    ({{ $routing->created_at->diffForHumans() }})
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif


        </div>

        <div class="col-12 col-md-4">

            {{-- المرفقات  --}}
            <div class="card">
                <div class="card-header py-4">
                    <h6 class="card-title  mb-0">
                        <i class="ti ti-paperclip text-warning me-2"></i>
                        المرفقات
                    </h6>
                </div>

                <div class="border-1 border-light border-dashed mb-2"></div>
                <div class="card-body">
                    @if ($task->attachments && $task->attachments->count() > 0)
                        <div class="row g-2">
                            @foreach ($task->attachments as $index => $attachment)
                                @php
                                    $fileExtension = pathinfo($attachment->file_name, PATHINFO_EXTENSION);
                                    $iconClass = match (strtolower($fileExtension)) {
                                        'pdf' => 'ti ti-file-type-pdf text-danger',
                                        'doc', 'docx' => 'ti ti-file-type-doc text-primary',
                                        'xls', 'xlsx' => 'ti ti-file-type-xlsx text-success',
                                        'jpg', 'jpeg', 'png', 'gif' => 'ti ti-photo text-info',
                                        default => 'ti ti-file text-secondary',
                                    };

                                    // إضافة رقم المرفق
                                    $attachmentNumber = $index + 1;
                                    $displayName = 'المرفق رقم ' . $attachmentNumber;

                                    // تنسيق حجم الملف
                                    $fileSize = $attachment->file_size;
                                    $formattedSize = '';

                                    if ($fileSize < 1024) {
                                        $formattedSize = $fileSize . ' بايت';
                                    } elseif ($fileSize < 1024 * 1024) {
                                        $formattedSize = round($fileSize / 1024, 2) . ' كيلوبايت';
                                    } elseif ($fileSize < 1024 * 1024 * 1024) {
                                        $formattedSize = round($fileSize / (1024 * 1024), 2) . ' ميجابايت';
                                    } else {
                                        $formattedSize = round($fileSize / (1024 * 1024 * 1024), 2) . ' جيجابايت';
                                    }
                                @endphp

                                <div class="col-12">
                                    <div class="card border attachment-card shadow-sm mb-2">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center">

                                                <div class="attachment-icon me-2"
                                                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="{{ $iconClass }} fs-3"></i>
                                                </div>

                                                <div class="attachment-details">
                                                    <h6 class="mb-0 fs-6">
                                                        <span>{{ $displayName }}</span>
                                                        <small class="text-muted">- {{ $formattedSize }}</small>
                                                    </h6>
                                                </div>

                                                <div class="ms-auto">
                                                    <div class="btn-group">
                                                        <!-- زر العرض -->
                                                        <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                            target="_blank" class="btn btn-sm btn-outline-info me-1"
                                                            title="عرض الملف">
                                                            <i class="ti ti-eye fs-6"></i>
                                                        </a>

                                                        <!-- زر التنزيل -->
                                                        <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                            download="{{ $attachment->file_name }}"
                                                            class="btn btn-sm btn-outline-primary" title="تنزيل الملف">
                                                            <i class="ti ti-download fs-6"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <small class="text-muted">لا توجد مرفقات</small>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header py-4">
                    <h6 class="card-title mb-0">
                        <i class="ti ti-logs text-warning me-2"></i>
                        سجل النشاطات
                    </h6>
                </div>
                <div class="border-1 border-light border-dashed mb-2"></div>

                <div class="card-body">
                    @if ($task->tasksLogs->count())
                        <ul class="timeline ">
                            @foreach ($task->tasksLogs as $log)
                                <li class="timeline-item">
                                    <span class="timeline-point "></span>
                                    <div class="timeline-event">
                                        <small class="timeline-title text-capitalize">
                                            {{ $log->message }}
                                        </small>
                                        @if ($log->user)
                                            <small class="text-muted d-block">بواسطة:
                                                <a href="{{ route('account.employee.profile', $log->user->employee->id) }}"
                                                    class="text-black">
                                                    {{ $log->user->employee->name }}</a>
                                            </small>
                                        @else
                                            <small class="text-muted d-block">بواسطة:
                                                تلقائيا من النظام
                                            </small>
                                        @endif

                                        <small class="text-muted">{{ $log->created_at->format('Y-m-d H:i A') }}</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">لا يوجد سجلات بعد.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>



@endsection


@section('page-script')
    <script>
        window.appUrls = {
            taskComplate: "{{ route('organization-center.tasks.toggle-completion', ':task') }}",
            taskReturn: "{{ route('organization-center.tasks.return', ':task') }}",
            taskReassign: "{{ route('organization-center.tasks.reassign', ':task') }}",


            stepComplate: "{{ route('organization-center.tasks.steps.toggle-completion', ':stepId') }}",
            stepApproval: "{{ route('organization-center.tasks.steps.toggle-approval', ':stepId') }}",

        };



        // اكمال المهمة
        function confirmComplete(taskId) {
            Swal.fire({
                title: 'تأكيد إكمال المهمة',
                text: 'هل أنت متأكد من أنك تريد إكمال هذه المهمة؟',
                icon: 'question',
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
                    toggleComplete(taskId);
                }
            });
        }

        function toggleComplete(taskId) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const url = appUrls.taskComplate.replace(':task', taskId);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: 'completed'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        new Audio("/assets/mp3/alert.mp3").play();
                        toastr.success(data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showErrorAlert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorAlert('حدث خطأ أثناء تحديث حالة المهمة');
                });
        }

        function confirmIncomplete(taskId) {
            Swal.fire({
                title: 'تأكيد إلغاء إكمال المهمة',
                text: 'هل أنت متأكد من أنك تريد إلغاء إكمال هذه المهمة؟',
                icon: 'question',
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
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const url = appUrls.taskComplate.replace(':task', taskId);

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                status: 'pending'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                new Audio("/assets/mp3/alert.mp3").play();
                                toastr.success(data.message);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                showErrorAlert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showErrorAlert('حدث خطأ أثناء تحديث حالة المهمة');
                        });
                }
            });
        }

        // ارجاع المهمة

        /* إرجاع المهمة مع سبب */
        function confirmReturn(taskId) {
            Swal.fire({
                title: 'تأكيد إرجاع المهمة',
                text: 'يرجى توضيح  سبب الإرجاع ثم الضغط على إرسال.',
                input: 'textarea',
                inputPlaceholder: 'سبب إرجاع المهمة…',
                inputAttributes: {
                    'aria-label': 'سبب الإرجاع'
                },
                showCancelButton: true,
                confirmButtonText: 'إرسال',
                cancelButtonText: 'إلغاء',
                showLoaderOnConfirm: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'custom-popup',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'btn btn-success custom-confirm',
                    cancelButton: 'btn btn-danger custom-cancel'
                },
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('أدخل سبب الإرجاع أولاً');
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    sendReturn(taskId, result.value); // result.value = سبب الإرجاع
                }
            });
        }

        function sendReturn(taskId, reason) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = appUrls.taskReturn.replace(':task', taskId); // يمكن استعمال نفس المسار

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: 'returned', // أو 'pending' إذا كان هذا هو المتّفق عليه
                        return_reason: reason // حقل جديد يرسَل إلى السيرفر
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        new Audio('/assets/mp3/alert.mp3').play();
                        toastr.success(data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showErrorAlert(data.message);
                    }
                })
                .catch(() => showErrorAlert('حدث خطأ أثناء إرجاع المهمة'));
        }


        function confirmReassign(taskId) {
            Swal.fire({
                title: 'إعادة إسناد المهمة',
                html: `

            <textarea id="reassignNote" class="form-control" placeholder="ملاحظة اختيارية"></textarea>
        `,
                showCancelButton: true,
                confirmButtonText: 'إسناد',
                cancelButtonText: 'إلغاء',
                focusConfirm: false,
                buttonsStyling: false,
                customClass: {
                    popup: 'custom-popup',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'btn btn-success custom-confirm',
                    cancelButton: 'btn btn-danger custom-cancel'
                },
                preConfirm: () => {
                    return {
                        note: document.getElementById('reassignNote').value
                    };
                }
            }).then(({
                value,
                isConfirmed
            }) => {
                if (isConfirmed) {
                    sendReassign(taskId, value.note);
                }
            });
        }

        function sendReassign(taskId, note) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = appUrls.taskReassign.replace(':task', taskId);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        note: note
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        new Audio('/assets/mp3/alert.mp3').play();
                        toastr.success(data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showErrorAlert(data.message);
                    }
                })
                .catch(() => showErrorAlert('حدث خطأ أثناء إعادة الإسناد'));
        }


        function showErrorAlert(message) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ!',
                text: message,
                confirmButtonText: 'حسناً',
                customClass: {
                    popup: 'custom-popup',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'btn btn-success custom-confirm',
                    cancelButton: 'btn btn-danger custom-cancel'
                },
                buttonsStyling: false
            });
        }
    </script>
@endsection
