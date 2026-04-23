$('#tasks-table').on('change', '.task-complete-checkbox', function () {
    console.log(44);
    const $checkbox = $(this);
    const taskId = $checkbox.data('task-id');
    const isChecked = $checkbox.is(':checked');

    const url = appUrls.taskComplate.replace(':task', taskId);

    $.ajax({
        url: url,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            status: isChecked ? 'completed' : 'cancel_completion'
        },
        success: function (response) {
            if (response.success) {
                toastr.success(response.message);
                $('#tasks-table').DataTable().draw(true);
            } else {
                toastr.error(response.message);
                // إعادة تعيين حالة الـ checkbox بناءً على النتيجة
                $checkbox.prop('checked', !isChecked);
            }
        },
        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error("حدث خطأ أثناء تحديث الحالة.");
            }

            // إعادة تعيين حالة الـ checkbox عند حدوث خطأ
            $checkbox.prop('checked', !isChecked);
        }
    });
});

// اكمال الخطوات
$('#tasks-table').on('change', '.step-complete-checkbox', function () {
    var checkbox = $(this);
    var stepId = checkbox.data("step-id");
    var isChecked = checkbox.is(":checked");
    var $stepRow = checkbox.closest('tr');

    const url = appUrls.stepComplate.replace(':stepId', stepId);

    $.ajax({
        url: url,
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            isChecked: isChecked,
        },
        beforeSend: function () {
            checkbox.prop("disabled", true);
        },
        success: function (response) {
            if (response.success) {
                toastr.success(response.message);
                const newStatus = isChecked ?
                    '<span class="badge bg-success">مكتملة</span>' :
                    '<span class="badge bg-primary"> قيد التنفيذ	</span>';
                $stepRow.find('td:nth-child(4)').html(newStatus);
                // تحديث التواريخ والمدة
                $stepRow.find('td:nth-child(5)').html(
                    `<small>${response.step_start_date}</small>`);
                $stepRow.find('td:nth-child(6)').html(
                    `<small>${response.step_end_date}</small>`);
                $stepRow.find('td:nth-child(7)').html(
                    `<small>${response.duration}</small>`);
            } else {
                toastr.error(response.message);
                checkbox.prop("checked", !isChecked);
            }
        },
        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error("حدث خطأ أثناء تحديث حالة الخطوة.");
            }

            checkbox.prop("checked", !isChecked);
        },
        complete: function () {
            checkbox.prop("disabled", false);
        }
    });
});

// دالة اعتماد/رفض الخطوة
// Step approval/rejection click handler
$('#tasks-table').on('click', '.step-approval-btn', function () {
    var btn = $(this);
    var action = btn.data("action");
    var stepId = btn.data("step-id");
    var $stepRow = btn.closest('tr');

    if (action === "reject") {
        // Store the button reference in the modal's data
        $("#rejectReasonModal")
            .data("step-id", stepId)
            .data("step-row", $stepRow)
            .data("button", btn);
        var modal = new bootstrap.Modal(document.getElementById("rejectReasonModal"));
        modal.show();
    } else {
        processStepApproval(stepId, action, null, $stepRow, btn);
    }
});

function processStepApproval(stepId, action, rejectReason, $stepRow, btn) {
    var taskId = btn.data("task-id"); // تأكد من إضافة هذا في HTML

    var data = {
        action: action
    };

    if (rejectReason) {
        data.reject_reason = rejectReason;
    }

    // Disable the relevant buttons
    if (btn) {
        btn.prop("disabled", true);
    }

    $stepRow.find('.step-approval-btn').prop("disabled", true);

    const url = appUrls.stepApproval.replace(':stepId', stepId);

    $.ajax({
        url: url,

        method: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: data,
        success: function (response) {
            if (response.success) {
                toastr.success(response.message);

                // Update step status
                const newStatus = action === "approve" ?
                    '<span class="badge bg-success">معتمدة</span>' :
                    '<span class="badge bg-danger">مرفوضة</span>';
                $stepRow.find('td:nth-child(4)').html(newStatus);

                // تحديث التواريخ والمدة
                $stepRow.find('td:nth-child(5)').html(
                    `<small>${response.step_start_date}</small>`);
                $stepRow.find('td:nth-child(6)').html(
                    `<small>${response.step_end_date}</small>`);
                $stepRow.find('td:nth-child(7)').html(
                    `<small>${response.duration}</small>`);

                // Update approval buttons
                $stepRow.find('.step-approval-btn[data-action="approve"]')
                    .prop('disabled', action === "approve");
                $stepRow.find('.step-approval-btn[data-action="reject"]')
                    .prop('disabled', action === "reject");

                // Update status text if it exists
                var $statusText = $stepRow.find('.step-status');
                if ($statusText.length) {
                    $statusText.text(action === "approve" ? "معتمدة" : "مرفوضة");
                }
            } else {
                toastr.error(response.message);
                // Re-enable buttons on error
                $stepRow.find('.step-approval-btn').prop("disabled", false);
            }
        },
        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error("حدث خطأ أثناء تحديث حالة اعتماد الخطوة.");
            }

            // Re-enable buttons on error
            $stepRow.find('.step-approval-btn').prop("disabled", false);
        }
    });
}

// Confirm reject button handler
$('#confirmRejectBtn').off('click').on('click', function () {
    var modalEl = document.getElementById("rejectReasonModal");
    var modal = bootstrap.Modal.getInstance(modalEl);
    var modalObj = $("#rejectReasonModal");
    var stepId = modalObj.data("step-id");
    var $stepRow = modalObj.data("step-row");
    var btn = modalObj.data("button");
    var rejectReason = $("#rejectReasonText").val();

    if ($.trim(rejectReason) === "") {
        toastr.warning("يرجى إدخال سبب الرفض.");
        return;
    }

    // Disable the button while processing
    $(this).prop('disabled', true);

    processStepApproval(stepId, "reject", rejectReason, $stepRow, btn);
    modal.hide();
    $("#rejectReasonText").val("");

    // Re-enable the button
    $(this).prop('disabled', false);
});
