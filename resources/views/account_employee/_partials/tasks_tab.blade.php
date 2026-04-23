<div class="tab-pane fade" id="tasks_tab">
    <div class="card">
        <div class="card-header py-4">

            <h6 class="card-title mb-0">
                <i class="ti ti-checklist text-warning me-2"></i>
                المهام
            </h6>
        </div>

        <div class="border-1 border-light border-dashed mb-2"></div>

        <div class="card-body">
            <div class="card-body px-0 py-0" id="task-list">
                @if ($tasks->count() > 0)
                    @foreach ($tasks as $item)
                        <div class="d-flex border p-2 align-items-center rounded-3 mb-2 small"
                            id="taskInfo_{{ $item->id }}">

                            <a href="{{ route('organization-center.tasks.show', $item->id) }}"
                                class="border-end px-2 me-10 small w-50">
                                {{ \Illuminate\Support\Str::limit($item->task_name, 100, '...') }}
                            </a>

                            <span class="badge bg-{{ $item->status->color() }}">
                                {{ $item->status->label() }}
                            </span>

                            <div class="ms-auto d-flex align-items-center" dir="rtl">
                                <div class="px-1">
                                    <input class="form-check-input custom-item task-complete-checkbox" type="checkbox"
                                        name="task_completed" id="task_{{ $item->id }}" title="إكمال المهمة"
                                        {{ $item->status->value == 'complete' ? 'checked' : '' }}
                                        {{ !$item->assignedUsers->contains('id', auth()->id()) || $item->status->value !== 'in_progress' ? 'disabled' : '' }}
                                        data-task-id="{{ $item->id }}">
                                </div>
                            </div>
                        </div>

                        <div class="task-steps ms-4 mb-4" id="taskSteps_{{ $item->id }}">
                            @php
                                $inProgressSteps = $item->steps
                                    ->filter(function ($step) {
                                        return $step->status->value === 'in_progress' &&
                                            $step->assignedUsers->contains('id', auth()->id());
                                    })
                                    ->sortBy('step_order');
                            @endphp

                            @if ($inProgressSteps->count() > 0)
                                <div class="steps-timeline">
                                    @foreach ($inProgressSteps as $step)
                                        <div
                                            class="d-flex justify-content-between border p-2 align-items-center rounded-3 mb-2 small">

                                            <div class="step-number d-flex">
                                                <span class="badge rounded-pill bg-warning">
                                                    {{ $step->step_order }}
                                                </span>
                                                <!-- Step Details -->
                                                <div class="step-details ">
                                                    <span
                                                        class="step-name fw-bold small mx-2">{{ $step->name }}</span>

                                                </div>
                                            </div>

                                            <span class="badge bg-{{ $step->status->color() }}">
                                                {{ $step->status->label() }}
                                            </span>

                                            @if ($step->needs_approval)
                                                <div class="d-flex justify-content-between">

                                                    <button class="btn btn-sm btn-primary step-approval-btn mx-2"
                                                        style="font-size:12px;" data-step-id="{{ $step->id }}"
                                                        data-action="approve" title="اعتماد">
                                                        <small>اعتماد</small>
                                                    </button>

                                                    <button class="btn btn-sm btn-secondary step-approval-btn"
                                                        style="font-size:12px;" data-step-id="{{ $step->id }}"
                                                        data-action="reject">
                                                        <small>رفض</small>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="px-1"><small>
                                                        <input
                                                            class="form-check-input custom-item step-complete-checkbox"
                                                            type="checkbox" name="step_complete"
                                                            id="step_complete_{{ $step->id }}" title="إكمال الخطوة"
                                                            data-step-id="{{ $step->id }}">
                                                    </small></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-center my-3">
                        <p class="text-muted">لا يوجد مهام حاليا </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
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
{{-- <script>
    // كود معالجة أزرار الاعتماد والرفض
    $(document).ready(function() {
        // معالجة نقر زر الاعتماد أو الرفض
        $('.step-approval-btn').on('click', function() {
            var btn = $(this);
            var action = btn.data("action");
            var stepId = btn.data("step-id");

            if (action === "reject") {
                // تخزين مرجع الزر في بيانات الموديل
                $("#rejectReasonModal")
                    .data("step-id", stepId)
                    .data("button", btn);
                var modal = new bootstrap.Modal(document.getElementById("rejectReasonModal"));
                modal.show();
            } else {
                processStepApproval(stepId, action, null, btn);
            }
        });

        // معالجة نقر زر تأكيد الرفض داخل الموديل
        $('#confirmRejectBtn').on('click', function() {
            var modalEl = document.getElementById("rejectReasonModal");
            var modal = bootstrap.Modal.getInstance(modalEl);
            var modalObj = $("#rejectReasonModal");
            var stepId = modalObj.data("step-id");
            var btn = modalObj.data("button");
            var rejectReason = $("#rejectReasonText").val();

            if ($.trim(rejectReason) === "") {
                toastr.warning("يرجى إدخال سبب الرفض.");
                return;
            }

            // تعطيل الزر أثناء المعالجة
            $(this).prop('disabled', true);

            processStepApproval(stepId, "reject", rejectReason, btn);

            // إخفاء الموديل ومسح القيمة
            modal.hide();
            $("#rejectReasonText").val("");

            // إعادة تمكين الزر
            $(this).prop('disabled', false);
        });

        // معالجة الإرسال للخادم
        function processStepApproval(stepId, action, rejectReason, btn) {
            // تعطيل الأزرار أثناء المعالجة
            btn.prop('disabled', true);

            var data = {
                action: action,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            if (rejectReason) {
                data.reject_reason = rejectReason;
            }

            $.ajax({
                url: "/tasks/steps/" + stepId + "/toggle-approval",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: data,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);

                        // تحديث العنصر في الواجهة
                        var stepItem = btn.closest('.step-item');

                        if (action === "approve") {
                            stepItem.find('.step-status').removeClass('text-warning').addClass(
                                'text-success').text('معتمدة');
                        } else if (action === "reject") {
                            stepItem.find('.step-status').removeClass('text-warning').addClass(
                                'text-danger').text('مرفوض');
                        }

                        // إعادة تحميل الصفحة بعد فترة قصيرة
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(response.message);
                        // إعادة تمكين الأزرار في حالة الخطأ
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("حدث خطأ أثناء تحديث حالة اعتماد الخطوة.");
                    }
                    // إعادة تمكين الأزرار في حالة الخطأ
                    btn.prop('disabled', false);
                }
            });
        }


    });

    $('.task-complete-checkbox').off('change').on('change', function() {
        var checkbox = $(this);
        var taskId = checkbox.data('task-id');
        var isChecked = checkbox.is(':checked');

        // إرسال طلب AJAX لتحديث حالة المهمة
        $.ajax({
            url: `/tasks/${taskId}/toggle-completion`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status: isChecked ? 'completed' : 'in_progress'
            },
            beforeSend: function() {
                // تعطيل الـ checkbox أثناء المعالجة
                checkbox.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // تشغيل الصوت
                    var completeSound = new Audio(
                        "{{ asset('assets/mp3/alert.mp3') }}");
                    completeSound.play();
                    // عرض رسالة النجاح
                    toastr.success(response.message);

                    location.reload();
                } else {
                    // في حال لم تنجح العملية
                    toastr.error(response.message);
                    checkbox.prop('checked', !isChecked); // عكس الحالة
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    // عرض رسالة الخطأ من الرد
                    toastr.error(xhr.responseJSON.message || 'لا يمكنك إكمال هذه المهمة.');
                } else if (xhr.status === 404) {
                    toastr.error('المهمة غير موجودة.');
                } else {
                    toastr.error('حدث خطأ أثناء تحديث حالة المهمة.');
                }
                checkbox.prop('checked', !isChecked); // عكس الحالة
            },
            complete: function() {
                checkbox.prop('disabled', false); // إعادة تمكين الـ checkbox
            }
        });
    });

    $('.step-complete-checkbox').off('change').on('change', function() {

        var checkbox = $(this);
        var stepId = checkbox.data("step-id");
        var isChecked = checkbox.is(":checked");


        $.ajax({
            url: "/tasks/steps/" + stepId + "/toggle-completion",
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                isChecked: isChecked,
            },
            beforeSend: function() {
                checkbox.prop("disabled", true);
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();

                } else {
                    toastr.error(response.message);
                    checkbox.prop("checked", !isChecked);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error("حدث خطأ أثناء تحديث حالة الخطوة.");
                }
                checkbox.prop("checked", !isChecked);
            },
            complete: function() {
                checkbox.prop("disabled", false);
            }
        });
    });
</script> --}}
