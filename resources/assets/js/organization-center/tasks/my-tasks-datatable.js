window.TasksDataTable = {
    init: function (table) {
        this.setupChildRowsToggle(table);
    },

    // setupChildRowsToggle: function (table) {
    //     // إضافة event listener للعمود الأول
    //     $("#tasks-table tbody").on("click", "td.details-control", function () {
    //         var tr = $(this).closest("tr");
    //         var row = table.row(tr);
    //         var icon = $(this).find("i");

    //         // التحقق من وجود خطوات
    //         if (!row.data().steps || row.data().steps.length === 0) {
    //             // إذا لم تكن هناك خطوات، لا تفعل شيئاً
    //             return;
    //         }

    //         if (row.child.isShown()) {
    //             // إخفاء الخطوات
    //             row.child.hide();
    //             tr.removeClass("shown");
    //             icon.removeClass("ti ti-arrow-big-up-line").addClass("ti ti-arrow-big-down-lines");
    //             icon.attr("title", "عرض الخطوات");
    //         } else {
    //             // عرض الخطوات
    //             var stepsHtml = TasksDataTable.formatSteps(row.data());
    //             row.child(stepsHtml).show();
    //             tr.addClass("shown");
    //             icon.removeClass("ti ti-arrow-big-down-lines").addClass("ti ti-arrow-big-up-line");
    //             icon.attr("title", "إخفاء الخطوات");
    //         }
    //     });
    // },

    setupChildRowsToggle: function (table) {
        // إضافة event listener للعمود الأول
        $("#tasks-table tbody").on("click", "td.details-control", function () {
            var tr = $(this).closest("tr");
            var row = table.row(tr);
            var icon = $(this).find("i");

            // التحقق من وجود خطوات
            if (!row.data().steps || row.data().steps.length === 0) {
                // إذا لم تكن هناك خطوات، لا تفعل شيئاً
                return;
            }

            if (row.child.isShown()) {
                // إخفاء الخطوات
                row.child.hide();
                tr.removeClass("shown");
                icon.removeClass("ti-arrow-big-up-line").addClass("ti-arrow-big-down-lines");
                icon.attr("title", "عرض الخطوات");
            } else {
                // عرض الخطوات
                var stepsHtml = TasksDataTable.formatSteps(row.data());
                row.child(stepsHtml).show();
                tr.addClass("shown");
                icon.removeClass("ti-arrow-big-down-lines").addClass("ti-arrow-big-up-line");
                icon.attr("title", "إخفاء الخطوات");
            }
        });
    },

    formatSteps: function (data) {
        if (!data.steps || data.steps.length === 0) {
            return '<div class="p-3 text-center"><em class="text-muted">لا توجد خطوات لهذه المهمة</em></div>';
        }

        var html = '<div class="nested-steps-container p-3">';
        html += '<div class="table-responsive">';
        html += '<table class="table table-bordered table-striped nested-steps-table">';
        html += '<thead class="table-primary">';
        html += '<tr>';
        html += '<th style="width: 50px;" class="text-center"> </th>';
        html += '<th style="width: 80px;">رقم الخطوة</th>';
        html += '<th>وصف الخطوة</th>';
        html += '<th style="width: 100px;">الحالة</th>';
        html += '<th style="width: 150px;">تاريخ البدء</th>';
        html += '<th style="width: 150px;">تاريخ الانتهاء</th>';
        html += '<th style="width: 100px;">المدة</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        for (var i = 0; i < data.steps.length; i++) {
            var step = data.steps[i];
            html += TasksDataTable.formatStepRow(step, i);
        }

        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div>';

        return html;
    },

    formatStepRow: function (step, index) {
        var actionHtml = this.formatStepAction(step);

        var html = '<tr>';
        html += '<td class="text-center">' + actionHtml + '</td>';
        html += '<td class="text-center"><span class="badge bg-light text-dark">' + (index + 1) + '</span></td>';
        html += '<td><strong>' + step.name + '</strong></td>';
        html += '<td><span class="badge ' + step.status_class + '">' + step.status_text + '</span></td>';
        html += '<td><small>' + (step.step_start_date || '<span class="text-muted">-</span>') + '</small></td>';
        html += '<td><small>' + (step.step_end_date || '<span class="text-muted">-</span>') + '</small></td>';
        html += '<td><small>' + (step.duration || '<span class="text-muted">-</span>') + '</small></td>';
        html += '</tr>';

        return html;
    },

    formatStepAction: function (step) {
        if (!step.action_data.has_permission) {
            return "<small>ليس لديك صلاحيات</small>";
        }

        if (step.action_data.needs_approval) {
            return this.formatApprovalButtons(step);
        }

        return this.formatCheckbox(step);
    },

    formatApprovalButtons: function (step) {
        var approveDisabled = step.action_data.status === "approved" ? "disabled" : "";
        var rejectDisabled = step.action_data.status === "rejected" ? "disabled" : "";

        return '<div class="step-approval-container small d-flex justify-content-center">' +
            '<button class="btn btn-sm btn-primary step-approval-btn mx-2" style="font-size:12px;" ' +
            'data-step-id="' + step.action_data.step_id + '" data-action="approve" ' + approveDisabled + '>اعتماد</button>' +
            '<button class="btn btn-sm btn-secondary step-approval-btn " style="font-size:12px;" ' +
            'data-step-id="' + step.action_data.step_id + '" data-action="reject" ' + rejectDisabled + '>رفض</button>' +
            '</div>';
    },

    formatCheckbox: function (step) {
        var checked = (step.action_data.status === "completed" || step.action_data.status === "approved") ? "checked" : "";

        return '<div class="px-1 custom-checkbox m-auto">' +
            '<input class="form-check-input custom-item step-complete-checkbox" type="checkbox" ' +
            'name="step_complete" id="step_complete_' + step.action_data.step_id + '" ' +
            'title="إكمال الخطوة" ' + checked + ' data-step-id="' + step.action_data.step_id + '">' +
            '</div>';
    }
};

// دالة للاستخدام في initComplete
window.initTasksDataTable = function (settings, json) {
    window.TasksDataTable.init(this.api());
};
