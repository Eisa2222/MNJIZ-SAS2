$('.task-complete-checkbox').on('change', function () {
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
                setTimeout(function () {
                    location.reload();
                }, 1500);
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
$('.step-complete-checkbox').on('change', function () {
    var checkbox = $(this);
    var stepId = checkbox.data("step-id");
    var isChecked = checkbox.is(":checked");

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
                setTimeout(function () {
                    location.reload();
                }, 1500);
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

$('.step-approval-btn').on('click', function () {
    var btn = $(this);
    var action = btn.data("action");
    var stepId = btn.data("step-id");

    if (action === "reject") {
        // Store the button reference in the modal's data
        $("#rejectReasonModal")
            .data("step-id", stepId)
            .data("button", btn);
        var modal = new bootstrap.Modal(document.getElementById("rejectReasonModal"));
        modal.show();
    } else {
        processStepApproval(stepId, action, null, btn);
    }
});

function processStepApproval(stepId, action, rejectReason, btn) {
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
                setTimeout(function () {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error("حدث خطأ أثناء تحديث حالة اعتماد الخطوة.");
            }
        }
    });
}

// Confirm reject button handler
$('#confirmRejectBtn').on('click', function () {
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

    // Disable the button while processing
    $(this).prop('disabled', true);

    processStepApproval(stepId, "reject", rejectReason, btn);
    modal.hide();
    $("#rejectReasonText").val("");

    // Re-enable the button
    $(this).prop('disabled', false);
});
