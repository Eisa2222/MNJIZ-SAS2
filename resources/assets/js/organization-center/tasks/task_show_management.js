$(document).ready(function () {
    // اكمال الخطوة
    $(".step-checkbox").on("change", function () {
        var checkbox = $(this);
        var stepId = checkbox.data("step-id");
        var isChecked = checkbox.is(":checked");
        var $stepRow = checkbox.closest("tr");

        $.ajax({
            url: "/tasks/steps/" + stepId + "/toggle-completion",
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
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
                    const newStatus = isChecked
                        ? '<span class="badge bg-success">مكتملة</span>'
                        : '<span class="badge bg-secondary">قيد الانتظار</span>';
                    $stepRow.find("td:nth-child(4)").html(newStatus);

                    // تحديث التواريخ والمدة
                    // $stepRow.find('td:nth-child(5)').text(
                    //     response.step_start_date || '-'
                    // );
                    // $stepRow.find('td:nth-child(6)').text(
                    //     response.step_end_date || '-'
                    // );
                    $stepRow
                        .find("td:nth-child(5)")
                        .text(response.duration || "-");
                    updateCompletionDetails(stepId, response);
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
            },
        });
    });

    // اعتماد/رفض الخطوة
    $(".btn-approve, .btn-reject").on("click", function () {
        var btn = $(this);
        var action = btn.hasClass("btn-approve") ? "approve" : "reject";
        var stepId = btn.data("step-id");
        var $stepRow = btn.closest("tr");

        if (action === "reject") {
            // فتح نافذة إدخال سبب الرفض
            $("#rejectReasonModal")
                .data("step-id", stepId)
                .data("step-row", $stepRow)
                .data("button", btn);
            var modal = new bootstrap.Modal(
                document.getElementById("rejectReasonModal"),
            );
            modal.show();
        } else {
            processStepApproval(stepId, action, null, $stepRow, btn);
        }
    });

    // تأكيد رفض الخطوة
    $("#confirmRejectBtn").on("click", function () {
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

        // تعطيل الزر أثناء المعالجة
        $(this).prop("disabled", true);

        processStepApproval(stepId, "reject", rejectReason, $stepRow, btn);
        modal.hide();
        $("#rejectReasonText").val("");

        // إعادة تفعيل الزر
        $(this).prop("disabled", false);
    });

    function processStepApproval(stepId, action, rejectReason, $stepRow, btn) {
        var data = {
            action: action,
        };

        if (rejectReason) {
            data.reject_reason = rejectReason;
        }

        // تعطيل الأزرار
        if (btn) {
            btn.prop("disabled", true);
        }
        $stepRow.find(".btn-approve, .btn-reject").prop("disabled", true);

        $.ajax({
            url: "/tasks/steps/" + stepId + "/toggle-approval",
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: data,
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);

                    // تحديث حالة الخطوة
                    const newStatus =
                        action === "approve"
                            ? '<span class="badge bg-success">معتمدة</span>'
                            : '<span class="badge bg-danger">مرفوضة</span>';
                    $stepRow.find("td:nth-child(4)").html(newStatus);

                    // تحديث التواريخ والمدة
                    $stepRow
                        .find("td:nth-child(5)")
                        .text(response.step_start_date || "-");
                    $stepRow
                        .find("td:nth-child(6)")
                        .text(response.step_end_date || "-");
                    $stepRow
                        .find("td:nth-child(5)")
                        .text(response.duration || "-");

                   

                     // تحديث حالة الأزرار - تعطيل الزر المضغوط وتفعيل الآخر
                if (action === "approve") {
                    $stepRow.find('.btn-approve').prop('disabled', true).addClass('disabled');
                    $stepRow.find('.btn-reject').prop('disabled', false).removeClass('disabled');
                } else if (action === "reject") {
                    $stepRow.find('.btn-reject').prop('disabled', true).addClass('disabled');
                    $stepRow.find('.btn-approve').prop('disabled', false).removeClass('disabled');
                }

                // تحديث جدول تفاصيل الإنجاز
                updateCompletionDetails(stepId, response);
                } else {
                    toastr.error(response.message);
                    // إعادة تفعيل الأزرار
                    $stepRow
                        .find(".btn-approve, .btn-reject")
                        .prop("disabled", false);
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error("حدث خطأ أثناء تحديث حالة اعتماد الخطوة.");
                }
                // إعادة تفعيل الأزرار
                $stepRow
                    .find(".btn-approve, .btn-reject")
                    .prop("disabled", false);
            },
        });
    }

    // تحديث تفاصيل الإنجاز
    function updateCompletionDetails(stepId, response) {
        // تحديث الصف المقابل في جدول تفاصيل الإنجاز
        const $completionRow = $(
            `.table.nested-steps-table:last tr[data-step-id="${stepId}"]`,
        );
        if ($completionRow.length) {
            const newStatus = getStatusBadge(response.status);
            $completionRow.find("td:nth-child(2)").html(newStatus);
            $completionRow
                .find("td:nth-child(3)")
                .text(response.step_start_date || "-");
            $completionRow
                .find("td:nth-child(4)")
                .text(response.step_end_date || "-");

            // تحديث المنجز إذا كان متوفراً في الاستجابة
            if (response.completed_by && response.completed_by.id) {
                const completedByHtml = `
                    <a href="/employees/${response.completed_by.id}">
                        <div class="d-flex align-items-center">
                            <img src="${response.completed_by.profile_picture || "/assets/img/avatars/1.png"}"
                                class="rounded-circle" width="25" alt="Avatar">
                            <span class="ms-2">${response.completed_by.name}</span>
                        </div>
                    </a>`;
                $completionRow.find("td:nth-child(5)").html(completedByHtml);
            } else {
                $completionRow.find("td:nth-child(5)").html("-");
            }

            $completionRow
                .find("td:nth-child(6)")
                .text(response.duration || "-");
        }
    }

    // دالة مساعدة للحصول على شارة الحالة
    function getStatusBadge(status) {
        const statusMap = {
            pending: ["bg-secondary", "قيد الانتظار"],
            in_progress: ["bg-primary", "قيد التنفيذ"],
            completed: ["bg-success", "مكتملة"],
            approved: ["bg-success", "معتمدة"],
            rejected: ["bg-danger", "مرفوضة"],
        };

        const [bgClass, text] = statusMap[status] || [
            "bg-secondary",
            "غير محدد",
        ];
        return `<span class="badge ${bgClass}">${text}</span>`;
    }
});

// لاظهار المكلفين في الخطوة
document.addEventListener("DOMContentLoaded", function () {
    // Handle button icon and text update
    const buttons = document.querySelectorAll(".assigned-users-btn");

    buttons.forEach((button) => {
        button.addEventListener("click", function () {
            const targetId = this.getAttribute("data-bs-target");
            const targetRow = document.querySelector(targetId);

            // Toggle all other open rows
            document
                .querySelectorAll(".assigned-users-row.show")
                .forEach((row) => {
                    if (row.id !== targetId.substring(1)) {
                        row.classList.remove("show");
                        const otherButton = document.querySelector(
                            `[data-bs-target="#${row.id}"]`,
                        );
                        otherButton.setAttribute("aria-expanded", "false");
                    }
                });
        });
    });
});
