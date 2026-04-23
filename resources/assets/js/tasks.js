/* resources/assets/js/tasks.js */

/* --------------------------------------------------
   دوال التأكيد على حذف مهمة باستخدام SweetAlert2
----------------------------------------------------- */
function confirmDeleteTask(taskId) {
    Swal.fire({
        title: "تأكيد عملية الحذف", // عنوان النافذة
        text: "هل أنت متأكد من رغبتك في حذف المهمة؟ .", // رسالة توضيحية للمستخدم
        // text: "هل أنت متأكد من رغبتك في حذف المهمة؟ عند التأكيد، سيتم نقل المهمة إلى قائمة المهام المحذوفة وإزالتها من مهام المستخدمين في خدمات مايكروسوفت.", // رسالة توضيحية للمستخدم
        icon: "warning", // أيقونة التحذير
        showCancelButton: true, // عرض زر الإلغاء
        buttonsStyling: false,
        customClass: {
            popup: "custom-popup",
            title: "custom-title",
            text: "custom-text",
            confirmButton: "btn btn-success custom-confirm", // زر التأكيد
            cancelButton: "btn btn-danger custom-cancel", // زر الإلغاء
        },
        confirmButtonText: "تأكيد", // نص زر التأكيد
        cancelButtonText: "إلغاء", // نص زر الإلغاء
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // إرسال طلب حذف المهمة عبر Fetch API
                const response = await fetch(`/tasks/${taskId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                });
                const data = await response.json();

                if (data.success) {
                    toastr.success("تم حذف المهمة بنجاح.");
                    // إزالة العنصر المرتبط بالمهمة من الصفحة
                    $("#tasks-table").DataTable().ajax.reload(null, false);
                } else {
                    toastr.error("حدث خطأ أثناء حذف المهمة.");
                }
            } catch (error) {
                toastr.error("حدث خطأ أثناء حذف المهمة.");
            }
        }
    });
}

/* --------------------------------------------------
     دالة تحديث المهمة عند التعديل باستخدام AJAX
----------------------------------------------------- */
function editTask(taskId) {
    // فتح الشاشة الجانبية للتعديل
    var offcanvasElement = document.getElementById(
        `editTaskOffcanvas${taskId}`,
    );
    var bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);
    bsOffcanvas.show();
}

// دالة تحديث المهمة بعد التأكد من صحة نموذج التعديل
function updateTask(taskId) {
    let form = document.getElementById(`editTaskForm${taskId}`);

    // التحقق من صحة النموذج قبل الإرسال
    if (!form.checkValidity()) {
        form.classList.add("was-validated");
        toastr.error("يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.");
        return;
    }

    // إنشاء كائن FormData من النموذج
    let formData = new FormData(form);

    // إضافة الطريقة المناسبة للتحديث
    formData.append('_method', 'PUT');

    // تحقق من ملفات المرفقات وأضفها يدويًا إلى FormData إذا لزم الأمر
    const fileInput = document.getElementById(`new-attachments-${taskId}`);
    if (fileInput && fileInput.files.length > 0) {
        // حذف أي قيم موجودة مسبقاً لـ new_attachments
        if (formData.has('new_attachments[]')) {
            formData.delete('new_attachments[]');
        }

        // إضافة كل ملف بشكل منفصل
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('new_attachments[]', fileInput.files[i]);
        }
    }

    $.ajax({
        url: `/tasks/${taskId}`,
        type: "PUT", // استخدام POST مع _method=PUT
        data: formData,
        contentType: false, // مهم! لا تضبط نوع المحتوى
        processData: false, // مهم! لا تقم بمعالجة البيانات
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.success) {
                toastr.success(response.message);
                // إخفاء شاشة offcanvas للتعديل
                let offcanvasEl = document.getElementById(
                    `editTaskOffcanvas${taskId}`
                );
                let bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (bsOffcanvas) bsOffcanvas.hide();
                location.reload();
            } else {
                toastr.error(response.message || "حدث خطأ ما.");
            }
        },
        error: function (xhr) {
            console.error("Error:", xhr.responseText);
            toastr.error("حدث خطأ أثناء الحفظ.");
        }
    });
}

/* --------------------------------------------------
   دالة تهيئة نموذج التعديل
   يتم استدعاؤها عند ظهور شاشة التعديل (offcanvas)
----------------------------------------------------- */
function initializeEditForm(taskId) {
    const editOffcanvas = $(`#editTaskOffcanvas${taskId}`);

    // تهيئة عناصر Select2 داخل نموذج التعديل
    editOffcanvas.find(".select2").each(function () {
        $(this).select2({
            dropdownParent: editOffcanvas,
            dir: "rtl",
            templateResult: formatEmployeeOption,
            templateSelection: formatEmployeeSelection,
            escapeMarkup: function (markup) {
                return markup;
            },
        });
    });

    // معالجة زر "لنفسي"
    $(`#assign-myself-btn-edit-${taskId}`).on("click", function () {
        var currentUserId = $(this).data("user-id");
        $(`#assigned_user_id_edit${taskId}`)
            .val([currentUserId])
            .trigger("change");
    });

    // إدارة خيار الخطوات في نموذج التعديل
    const hasStepsCheckbox = editOffcanvas.find(`#hasSteps_edit${taskId}`);
    const stepsContainer = editOffcanvas.find(`#stepsContainer_edit${taskId}`);
    hasStepsCheckbox.off("change").on("change", function () {
        if (this.checked) {
            stepsContainer.show();
            if (stepsContainer.find(".step-block").length === 0) {
                addNewStep(taskId);
            }
            stepsContainer
                .find("input, select, textarea")
                .prop("disabled", false);
            stepsContainer
                .find('input[name="step_name[]"], select')
                .attr("required", "required");
        } else {
            stepsContainer.hide();
            stepsContainer
                .find("input, select, textarea")
                .prop("disabled", true);
            stepsContainer
                .find('input[name="step_name[]"], select')
                .removeAttr("required");
        }
    });

    // زر إضافة خطوة جديدة
    editOffcanvas
        .find(`#addStepButton_edit${taskId}`)
        .off("click")
        .on("click", function () {
            addNewStep(taskId);
        });

    // زر حذف الخطوة
    editOffcanvas.on("click", ".remove-step-btn", function () {
        const stepBlock = $(this).closest(".step-block");
        const container = stepBlock.parent();
        if (container.find(".step-block").length > 1) {
            stepBlock.remove();
        } else {
            stepBlock.remove();
            hasStepsCheckbox.prop("checked", false);
            stepsContainer.hide();
        }
        updateStepNumbers(taskId);
    });

    // ربط حدث "submit" للنموذج بحيث يتم التحقق من صحة البيانات قبل الإرسال
    const editForm = document.getElementById(`editTaskForm${taskId}`);
    $(editForm)
        .off("submit")
        .on("submit", function (e) {
            e.preventDefault();
            if (!editForm.checkValidity()) {
                editForm.classList.add("was-validated");
                toastr.error(
                    "يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.",
                );
                return;
            }
            updateTask(taskId);
        });
}

/* --------------------------------------------------
     دوال تهيئة Select2 مع تنسيق عرض صورة واسم الموظف
----------------------------------------------------- */
function formatEmployeeOption(employee) {
    if (!employee.id) return employee.text;
    var imageUrl = $(employee.element).data("image");
    var employeeName = employee.text;
    var imageHtml = imageUrl
        ? `<img src="${imageUrl}" alt="${employeeName}" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">`
        : `<div style="width: 30px; height: 30px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 30px; border-radius: 50%; margin-right: 10px;">${employeeName.charAt(0)}</div>`;
    return `<div class="d-flex align-items-center">${imageHtml}<span>${employeeName}</span></div>`;
}

function formatEmployeeSelection(employee) {
    if (!employee.id) return employee.text;
    var imageUrl = $(employee.element).data("image");
    var employeeName = employee.text;
    var imageHtml = imageUrl
        ? `<img src="${imageUrl}" alt="${employeeName}" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">`
        : `<div style="width: 20px; height: 20px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 20px; border-radius: 50%; margin-right: 5px;">${employeeName.charAt(0)}</div>`;
    return `<div class="d-flex align-items-center">${imageHtml}<span>${employeeName}</span></div>`;
}

// دالة تهيئة Select2 لأي عنصر
function initializeSelect2(selectElement) {
    // تحديد ما إذا كان العنصر هو مجال المهمة
    const isTaskField = $(selectElement).attr("id") === "task_field";

    $(selectElement).select2({
        placeholder: $(selectElement).data("placeholder"),
        allowClear: true,
        width: "100%",
        language: "ar",
        dir: "rtl",
        // استخدام دالة تنسيق مخصصة
        templateResult: function (state) {
            // للعناصر التي تحتوي على صور (مثل الموظفين)
            if (
                !isTaskField &&
                state.element &&
                $(state.element).data("image")
            ) {
                var imageUrl = $(state.element).data("image");
                var name = state.text;
                var imageHtml = imageUrl
                    ? `<img src="${imageUrl}" alt="${name}" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">`
                    : `<div style="width: 30px; height: 30px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 30px; border-radius: 50%; margin-right: 10px;">${name.charAt(0)}</div>`;
                return $(
                    `<div class="d-flex align-items-center">${imageHtml}<span>${name}</span></div>`,
                );
            }

            // للعناصر الأخرى (مثل مجال المهمة)
            return state.text;
        },
        templateSelection: function (state) {
            // للعناصر التي تحتوي على صور (مثل الموظفين)
            if (
                !isTaskField &&
                state.element &&
                $(state.element).data("image")
            ) {
                var imageUrl = $(state.element).data("image");
                var name = state.text;
                var imageHtml = imageUrl
                    ? `<img src="${imageUrl}" alt="${name}" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">`
                    : `<div style="width: 20px; height: 20px; background-color: #007bff; color: white; font-weight: bold; text-align: center; line-height: 20px; border-radius: 50%; margin-right: 5px;">${name.charAt(0)}</div>`;
                return $(
                    `<div class="d-flex align-items-center">${imageHtml}<span>${name}</span></div>`,
                );
            }

            // للعناصر الأخرى (مثل مجال المهمة)
            return state.text;
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        dropdownParent: $(selectElement).closest(".offcanvas-end"),
    });
}

// لتهيئة القائمة الخاصة بمجال المهمة
function initializeTaskFieldSelect2() {
    $("#task_field").select2({
        placeholder: "اختر مجال المهمة",
        allowClear: true,
        width: "100%",
        language: "ar",
        dir: "rtl",
        templateResult: function (state) {
            if (!state.id) return state.text;
            return state.text;
        },
        templateSelection: function (state) {
            if (!state.id) return state.text;
            return state.text;
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        dropdownParent: $("#addTaskOffcanvas"),
    });
}

/* --------------------------------------------------
     دالة تحديث أرقام الخطوات داخل الحاويات (سواء إضافة أو تعديل)
----------------------------------------------------- */
function updateStepNumbers(containerSelector) {
    $(containerSelector + " .step-block").each(function (index, element) {
        $(element)
            .find(".step-number")
            .text("الخطوة " + (index + 1));
        $(element).attr("data-index", index);
    });
}

/* --------------------------------------------------
     تهيئة إضافة وحذف خطوات نموذج تعديل المهمة
     يمكن استدعاؤها مع تمرير رقم المهمة (taskId)
----------------------------------------------------- */
function initializeEditSteps(taskId) {
    const containerSelector = "#stepsContainer_edit" + taskId;
    const addButtonSelector = "#addStepButton_edit" + taskId;

    function updateStepCounter() {
        return $(containerSelector + " .step-block").length || 0;
    }
    let stepCounter = updateStepCounter();

    function createNewStep() {
        let userOptions = "";
        const firstSelect = $(
            `#stepsContainer_edit${taskId} .step-block:first select`,
        );
        if (firstSelect.length > 0) {
            userOptions = firstSelect
                .find("option")
                .map(function () {
                    return `<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`;
                })
                .get()
                .join("");
        } else {
            const fallbackSelect = $(`#assigned_user_id_edit${taskId}`);
            if (fallbackSelect.length > 0) {
                userOptions = fallbackSelect
                    .find("option")
                    .map(function () {
                        return `<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`;
                    })
                    .get()
                    .join("");
            } else {
                console.error("لا يمكن إيجاد قائمة المستخدمين");
                return;
            }
        }
        const uniqueStepId = `step_${taskId}_${stepCounter}`;
        const stepHtml = `
        <div class="step-block mb-3 border p-3" data-index="${stepCounter}" id="${uniqueStepId}">
            <div class="step-header mb-2">
                <strong class="step-number">الخطوة ${stepCounter + 1}</strong>
            </div>
            <div class="row">
                <div class="col-9">
                    <label class="form-label">اسم الخطوة</label>
                    <input type="text" name="step_name[]" class="form-control" placeholder="اسم الخطوة" required>
                    <div class="invalid-feedback">اسم الخطوة مطلوب</div>
                </div>
                <div class="col-3">
                    <label class="form-label">تحتاج لاعتماد؟</label>
                    <span title="عند تفعيل الاعتماد سيظهر للمستخدم قبول او رفض وعند الرفض سيتم ارجاع المهمة لمراجعتها" style="color: var(--primary-color);">
                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                    </span>
                    <div class="form-check">
                        <input type="checkbox" name="needs_approval[${stepCounter}]" class="form-check-input" value="1">
                        <label class="form-check-label">نعم</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label mt-2">المكلفين</label>
                    <select name="step_assigned_user_ids[${stepCounter}][]" class="form-select-new" multiple required data-placeholder="اختر الموظفين">
                        ${userOptions}
                    </select>
                    <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                </div>
            </div>
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-step-btn">حذف الخطوة</button>
            </div>
        </div>
      `;
        return $(stepHtml)[0];
    }

    // استخدام التفويض للأحداث داخل الحاوية (لأن المحتوى ديناميكي)
    $(document).on("click", addButtonSelector, function () {
        const newStep = createNewStep();
        if (!newStep) return;
        $(this).before(newStep);
        let newSelect = newStep.querySelector(".form-select-new");
        $(newSelect)
            .removeClass("form-select-new")
            .addClass("form-select")
            .select2({
                placeholder: "اختر الموظفين",
                allowClear: true,
                width: "100%",
                language: "ar",
                dir: "rtl",
                templateResult: formatEmployeeOption,
                templateSelection: formatEmployeeSelection,
                escapeMarkup: function (markup) {
                    return markup;
                },
                dropdownParent: $(`#editTaskOffcanvas${taskId}`),
            });
        stepCounter++;
        updateStepNumbers(containerSelector);
    });

    // حذف خطوة
    $(document).on(
        "click",
        containerSelector + " .remove-step-btn",
        function () {
            const container = $(containerSelector);
            const stepBlock = $(this).closest(".step-block");
            if (container.children(".step-block").length > 1) {
                stepBlock.remove();
            } else {
                stepBlock.remove();
                container.hide();
                $(`#hasSteps_edit${taskId}`).prop("checked", false);
            }
            container.children(".step-block").each(function (index) {
                $(this)
                    .find(".step-number")
                    .text("الخطوة " + (index + 1));
                $(this).attr("data-index", index);
            });
            stepCounter = container.children(".step-block").length || 0;
        },
    );

    // تهيئة الخطوات الموجودة داخل الحاوية
    $(`${containerSelector} .step-block select`).each(function () {
        if (!$(this).hasClass("select2-hidden-accessible")) {
            $(this).select2({
                placeholder: "اختر الموظفين",
                allowClear: true,
                width: "100%",
                language: "ar",
                dir: "rtl",
                templateResult: formatEmployeeOption,
                templateSelection: formatEmployeeSelection,
                escapeMarkup: function (markup) {
                    return markup;
                },
                dropdownParent: $(`#editTaskOffcanvas${taskId}`),
            });
        }
    });
}

/* --------------------------------------------------
     تهيئة إضافة وحذف خطوات نموذج إضافة مهمة جديدة
----------------------------------------------------- */
function initializeAddTaskSteps() {
    const containerSelector = "#stepsContainer";
    const addButtonSelector = "#addStepButton";
    function updateStepCounter() {
        return $(containerSelector + " .step-block").length || 0;
    }
    let stepCounter = updateStepCounter();

    function createNewStep() {
        const userOptions = $("#assigned_user_id option")
            .map(function () {
                return `<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`;
            })
            .get()
            .join("");
        const stepHtml = `
        <div class="step-block mb-3 border p-3" data-index="${stepCounter}">
            <div class="step-header mb-2">
                <strong class="step-number">الخطوة ${stepCounter + 1}</strong>
            </div>
            <div class="row">
                <div class="col-9">
                    <label class="form-label">اسم الخطوة</label>
                    <input type="text" name="step_name[]" class="form-control" placeholder="اسم الخطوة" required>
                    <div class="invalid-feedback">اسم الخطوة مطلوب</div>
                </div>
                <div class="col-3">
                    <label class="form-label">تحتاج لاعتماد؟</label>
                    <span title="عند تفعيل الاعتماد سيظهر للمستخدم قبول او رفض" style="color: var(--primary-color);">
                        <i class="fa fa-question-circle fa-lg" aria-hidden="true"></i>
                    </span>
                    <div class="form-check">
                        <input type="checkbox" name="needs_approval[${stepCounter}]" class="form-check-input" value="1">
                        <label class="form-check-label">نعم</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label mt-2">المكلفين</label>
                    <select name="step_assigned_user_ids[${stepCounter}][]" class="form-select-new" multiple required data-placeholder="اختر الموظفين">
                        ${userOptions}
                    </select>
                    <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                </div>
            </div>
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-step-btn">حذف الخطوة</button>
            </div>
        </div>
      `;
        return $(stepHtml)[0];
    }

    $(addButtonSelector)
        .off("click")
        .on("click", function () {
            const newStep = createNewStep();
            this.parentNode.insertBefore(newStep, this);
            let newSelect = newStep.querySelector(".form-select-new");
            $(newSelect)
                .removeClass("form-select-new")
                .addClass("form-select")
                .select2({
                    placeholder: "اختر الموظفين",
                    allowClear: true,
                    width: "100%",
                    language: "ar",
                    dir: "rtl",
                    templateResult: formatEmployeeOption,
                    templateSelection: formatEmployeeSelection,
                    escapeMarkup: function (markup) {
                        return markup;
                    },
                    dropdownParent: $("#addTaskOffcanvas"),
                });
            stepCounter++;
        });

    $(containerSelector)
        .off("click", ".remove-step-btn")
        .on("click", ".remove-step-btn", function () {
            const container = $(containerSelector);
            const stepBlock = $(this).closest(".step-block");
            if (container.children(".step-block").length > 1) {
                stepBlock.remove();
            } else {
                stepBlock.remove();
                container.hide();
                $("#hasSteps").prop("checked", false);
            }
            container.children(".step-block").each(function (index) {
                $(this)
                    .find(".step-number")
                    .text("الخطوة " + (index + 1));
                $(this).attr("data-index", index);
            });
            stepCounter = container.children(".step-block").length || 0;
        });

    $(containerSelector + " .step-block select").each(function () {
        if (!$(this).hasClass("select2-hidden-accessible")) {
            $(this).select2({
                placeholder: "اختر الموظفين",
                allowClear: true,
                width: "100%",
                language: "ar",
                dir: "rtl",
                templateResult: formatEmployeeOption,
                templateSelection: formatEmployeeSelection,
                escapeMarkup: function (markup) {
                    return markup;
                },
                dropdownParent: $("#addTaskOffcanvas"),
            });
        }
    });
}

/* --------------------------------------------------
     إعادة تهيئة الأحداث بعد تحديث القائمة (عند تغيير الفلاتر)
----------------------------------------------------- */
function reinitializeDynamicElements() {
    $(".select2").each(function () {
        if (!$(this).data("select2")) {
            initializeSelect2(this);
        }
    });
    initializeAddTaskSteps();
}

/* --------------------------------------------------
     أحداث التهيئة عند تحميل الصفحة
----------------------------------------------------- */
$(document).ready(function () {
    $(document).on("shown.bs.offcanvas", ".offcanvas", function () {
        const taskId = this.id.replace("editTaskOffcanvas", "");
        if (taskId) {
            initializeEditForm(taskId);
        }
    });

    $("#tasks-table").on("draw.dt", function () {
        $(".edit-task").each(function () {
            const taskId = $(this).data("task-id");
            if (taskId) {
                initializeEditForm(taskId);
            }
        });
    });

    // تهيئة Select2 للعناصر
    $(".select2").each(function () {
        if ($(this).attr("id") === "task_field") {
            initializeTaskFieldSelect2();
        } else {
            initializeSelect2(this);
        }
    });

    // إعادة تهيئة مجال المهمة عند فتح النماذج
    $("#addTaskOffcanvas").on("shown.bs.offcanvas", function () {
        initializeTaskFieldSelect2();
    });

    $(document).on("shown.bs.offcanvas", ".offcanvas-edit", function () {
        // إعادة تهيئة عناصر المهمة في نموذج التعديل
        initializeTaskFieldSelect2();
    });
    initializeAddTaskSteps();
    // initializeStepCompleteEvents();
    // initializeStepApprovalButtonEvents();

    var popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]'),
    );
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            html: true,
            content: function () {
                var contentSelector = this.getAttribute("data-bs-content");
                return document.querySelector(contentSelector).innerHTML;
            },
        });
    });

    $("#hasSteps").on("change", function () {
        var stepsContainer = $("#stepsContainer");
        if ($(this).is(":checked")) {
            stepsContainer.show();
            stepsContainer
                .find("input, select, textarea")
                .prop("disabled", false);
            stepsContainer
                .find('input[name="step_name[]"], select')
                .attr("required", "required");
        } else {
            stepsContainer.hide();
            stepsContainer
                .find("input, select, textarea")
                .prop("disabled", true);
            stepsContainer
                .find('input[name="step_name[]"], select')
                .removeAttr("required");
        }
    });

    $("#addTaskForm").on("submit", function (e) {
        let form = document.getElementById(`addTaskForm`);

        // التحقق من صحة النموذج قبل الإرسال
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            toastr.error("يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.");
            return;
        }

        var selectVal = $("#assigned_user_id").val();
        var instance = $("#assigned_user_id").data("select2");
        var container = instance ? instance.$container : null;
        var valid = true;
        if (!selectVal || selectVal.length === 0) {
            if (container) container.addClass("is-invalid");
            valid = false;
        } else {
            if (container) container.removeClass("is-invalid");
        }
        $("#stepsContainer select[required]").each(function () {
            var val = $(this).val();
            var inst = $(this).data("select2");
            var selectContainer = inst ? inst.$container : null;
            if (!val || val.length === 0) {
                if (selectContainer) selectContainer.addClass("is-invalid");
                valid = false;
            } else {
                if (selectContainer) selectContainer.removeClass("is-invalid");
            }
        });
        if (!valid) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!document.getElementById("hasSteps").checked) {
            $(
                "#stepsContainer input, #stepsContainer select, #stepsContainer textarea",
            ).prop("disabled", true);
        } else {
            $(
                "#stepsContainer input, #stepsContainer select, #stepsContainer textarea",
            ).prop("disabled", false);
        }
    });

    $("#assigned_user_id").on("select2:select", function () {
        var inst = $(this).data("select2");
        var container = inst ? inst.$container : null;
        if (container) container.removeClass("is-invalid");
    });
    $("#assigned_user_id").on("select2:unselect", function () {
        var selectVal = $(this).val();
        var inst = $(this).data("select2");
        var container = inst ? inst.$container : null;
        if ((!selectVal || selectVal.length === 0) && container) {
            container.addClass("is-invalid");
        }
    });

    $(document).on("change", ".select2", function () {
        var selectVal = $(this).val();
        var inst = $(this).data("select2");
        var container = inst ? inst.$container : null;
        if ($(this).is("[required]")) {
            if (selectVal && selectVal.length > 0) {
                if (container) container.removeClass("is-invalid");
            } else {
                if (container) container.addClass("is-invalid");
            }
        } else {
            if (container) container.removeClass("is-invalid");
        }
    });

    $("#addTaskOffcanvas").on("shown.bs.offcanvas", function () {
        $(this)
            .find(".select2")
            .each(function () {
                initializeSelect2(this);
            });
    });
    $("#addTaskOffcanvas").on("hidden.bs.offcanvas", function () {
        $(this)
            .find(".select2")
            .each(function () {
                if ($(this).data("select2")) {
                    $(this).select2("destroy");
                }
            });
    });

    $(document).on("shown.bs.offcanvas", ".offcanvas-edit", function () {
        var taskId = $(this).attr("id").replace("editTaskOffcanvas", "");
        initializeEditSteps(taskId);
        $(`#hasSteps_edit${taskId}`)
            .off("change")
            .on("change", function () {
                const stepsContainer = $(`#stepsContainer_edit${taskId}`);
                if ($(this).is(":checked")) {
                    stepsContainer.show();
                    stepsContainer
                        .find("input, select, textarea")
                        .prop("disabled", false);
                    stepsContainer
                        .find('input[name="step_name[]"], select')
                        .attr("required", "required");
                } else {
                    stepsContainer.hide();
                    stepsContainer
                        .find("input, select, textarea")
                        .prop("disabled", true);
                    stepsContainer
                        .find('input[name="step_name[]"], select')
                        .removeAttr("required");
                }
            });
    });

    $("#assign-myself-btn-create").on("click", function () {
        $("#assigned_user_id").val([currentUserId]).trigger("change");
    });

    $(document).on("click", "[id^=assign-myself-btn-edit-]", function () {
        var taskId = $(this).attr("id").replace("assign-myself-btn-edit-", "");
        $("#assigned_user_id_edit" + taskId)
            .val([currentUserId])
            .trigger("change");
    });

});

/* --------------------------------------------------
     تفعيل التحقق من صحة النماذج باستخدام Bootstrap
----------------------------------------------------- */
(function () {
    "use strict";
    window.addEventListener(
        "load",
        function () {
            var forms = document.getElementsByClassName("needs-validation");
            Array.prototype.filter.call(forms, function (form) {
                form.addEventListener(
                    "submit",
                    function (event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add("was-validated");
                    },
                    false,
                );
            });
        },
        false,
    );
})();

let currentFilterType = null;

// دالة إضافة خطوة جديدة في نموذج التعديل
function addNewStep(taskId) {
    const container = $(`#stepsContainer_edit${taskId}`);
    const stepCount = container.find(".step-block").length;
    const userOptions = $(`#assigned_user_id_edit${taskId} option`)
        .map(function () {
            return `<option value="${this.value}" data-image="${$(this).data("image")}">${this.text}</option>`;
        })
        .get()
        .join("");

    const newStepHtml = `
        <div class="step-block mb-3 border p-3" data-index="${stepCount}">
            <div class="step-header mb-2">
                <strong class="step-number">الخطوة ${stepCount + 1}</strong>
            </div>
            <div class="row">
                <div class="col-9">
                    <label class="form-label">اسم الخطوة</label>
                    <input type="text" name="step_name[]" class="form-control" placeholder="اسم الخطوة" required>
                    <div class="invalid-feedback">اسم الخطوة مطلوب</div>
                </div>
                <div class="col-3">
                    <label class="form-label">تحتاج لاعتماد؟</label>
                    <span title="عند تفعيل الاعتماد سيظهر للمستخدم قبول او رفض" style="color: var(--primary-color);">
                        <i class="fa fa-question-circle fa-lg"></i>
                    </span>
                    <div class="form-check">
                        <input type="checkbox" name="needs_approval[${stepCount}]" class="form-check-input" value="1">
                        <label class="form-check-label">نعم</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label mt-2">المكلفين</label>
                    <select name="step_assigned_user_ids[${stepCount}][]" class="form-select select2-new" multiple required data-placeholder="اختر الموظفين">
                        ${userOptions}
                    </select>
                    <div class="invalid-feedback">الرجاء اختيار موظف واحد على الأقل</div>
                </div>
            </div>
            <div class="mt-2 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-step-btn">
                    حذف الخطوة
                </button>
            </div>
        </div>
    `;
    $(newStepHtml).insertBefore(container.find(`#addStepButton_edit${taskId}`));
    container.find(".select2-new").each(function () {
        $(this)
            .removeClass("select2-new")
            .select2({
                dropdownParent: $(`#editTaskOffcanvas${taskId}`),
                dir: "rtl",
                templateResult: formatEmployeeOption,
                templateSelection: formatEmployeeSelection,
                escapeMarkup: function (markup) {
                    return markup;
                },
            });
    });
    updateStepNumbers(taskId);
}

/* --------------------------------------------------
     لتعيين المتغيرات العامة
----------------------------------------------------- */
window.updateTask = updateTask;
window.confirmDeleteTask = confirmDeleteTask;

$(document).ready(function () {
    // Asegurarse de que todos los recursos estén completamente cargados
    $(window).on("load", function () {
        // Inicializar Select2 para elementos de filtro específicos
        $("#filter-priority, #filter-task-field, #filter-status").each(
            function () {
                // Destruir cualquier instancia existente de Select2
                if ($(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2("destroy");
                }

                // Inicializar con configuración optimizada
                $(this).select2({
                    placeholder: $(this).data("placeholder") || "اختر...",
                    allowClear: true,
                    width: "100%",
                    language: "ar",
                    dir: "rtl",
                    // Usar el body como padre del dropdown para evitar problemas de visualización
                    dropdownParent: $("body"),
                    // Mejorar el rendimiento
                    minimumResultsForSearch: 5,
                });
            },
        );
    });

    // Asegurar que los select2 estén visibles después de cualquier redibujado de tabla
    $("#tasks-table").on("draw.dt", function () {
        $(".select2-container").css("width", "100%");
    });

    // Manejar conflictos de z-index para asegurar que los dropdowns se muestren correctamente
    $(document).on("select2:open", function () {
        $(".select2-dropdown").css("z-index", 9999);
    });
});

// Añadir esto al final del archivo tasks.js para asegurar la correcta inicialización
// incluso cuando se carga la página dinámicamente
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        if ($.fn.select2) {
            $("#filter-priority, #filter-task-field, #filter-status").each(
                function () {
                    if (!$(this).data("select2")) {
                        $(this).select2({
                            placeholder:
                                $(this).data("placeholder") || "اختر...",
                            allowClear: true,
                            width: "100%",
                            language: "ar",
                            dir: "rtl",
                            dropdownParent: $("body"),
                        });
                    }
                },
            );
        }
    }, 500); // Pequeño retraso para asegurar que todos los recursos estén cargados
});
