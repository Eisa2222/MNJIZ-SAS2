{{-- <div class="tab-pane fade" id="taskSteps">
    <div class="row g-3">
        @foreach ($task->steps as $index => $item)
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="text-primary">الخطوة {{ $index + 1 }}</h6>
                        @if ($item->needs_approval)
                            <div>
                                <button class="btn btn-success">
                                    اعتماد
                                </button>
                                <button class="btn btn-danger">
                                    رفض
                                </button>
                            </div>
                        @else
                            <button class="btn btn-primary">
                                اكمال المهمة
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <h5 class="mx-2 fw-bold">{{ $item->name }}</h5>
                        <h6 class="mb-0 text-primary">المكلفين</h6>
                        <div class="row g-2 mt-2">
                            @if ($item->assignedUsers)
                                @foreach ($item->assignedUsers as $user)
                                    <div class="col-md-4">
                                        <a href="{{ route('account.employee.profile', $user->id) }}" class="mb-1">
                                            <div class="d-flex align-items-center user-name border p-2">
                                                <div class="avatar-wrapper me-4">
                                                    <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('assets/img/avatars/1.png') }}"
                                                        alt="Avatar" class="rounded-circle" width="40">
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <p class="mb-1 fw-semibold">{{ $user->name }}</p>
                                                    <span
                                                        class="fw-medium">{{ $user->getRoleNames()->implode(', ') }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <p class="mx-2">لم يتم إضافة مكلفين</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div> --}}


<div class="tab-pane fade" id="taskSteps">
    <div class="row g-3">
        @if ($task->steps->count() > 0)
            <div class="card">
                <div class="nested-steps-container mx-2 my-4">
                    <table class="table table-bordered nested-steps-table w-100 small">
                        <thead style="background: rgb(239 238 240 / 42%) ">
                            <tr>
                                <th>اكمال الخطوة</th>
                                <th>رقم الخطوة</th>
                                <th>وصف الخطوة</th>
                                <th>الحالة</th>
                                <th>تاريخ البدء</th>
                                <th>تاريخ الانتهاء</th>
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
                                                    <input class="form-check-input step-checkbox" type="checkbox"
                                                        id="stepCheckbox{{ $step->id }}"
                                                        {{ in_array($step->status, ['completed', 'approved']) ? 'checked' : '' }}
                                                        data-step-id="{{ $step->id }}">
                                                </div>
                                            @endif
                                        @endif

                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $step->name }}</td>
                                    @php
                                        $statusBadge = [
                                            'pending' => ['bg-secondary', 'قيد الانتظار'],
                                            'in_progress' => ['bg-primary', 'قيد التنفيذ'],
                                            'completed' => ['bg-success', 'مكتملة'],
                                            'approved' => ['bg-success', 'معتمدة'],
                                            'rejected' => ['bg-danger', 'مرفوضة'],
                                        ];

                                        $badgeClass = $statusBadge[$step->status][0] ?? 'bg-secondary';
                                        $badgeText = $statusBadge[$step->status][1] ?? 'غير محدد';
                                    @endphp
                                    <td>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $badgeText }}
                                        </span>
                                    </td>
                                    <td>{{ $step->step_start_date ? $step->step_start_date->format('Y-m-d H:i:s') : '-' }}
                                    </td>
                                    <td>{{ $step->step_end_date ? $step->step_end_date->format('Y-m-d H:i:s') : '-' }}
                                    </td>
                                    <td>{{ $step->duration ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
    <!-- موديل سبب الرفض -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectReasonModalLabel">سبب الرفض</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <textarea id="rejectReasonText" class="form-control" placeholder="أدخل سبب الرفض هنا"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmRejectBtn" class="btn btn-danger">رفض</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
 
</script>
