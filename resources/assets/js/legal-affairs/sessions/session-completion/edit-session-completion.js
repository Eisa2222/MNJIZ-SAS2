"use strict";

(function () {
    const select2 = $(".select2");

    // Wizard Validation
    const wizardValidation = document.querySelector("#wizard-validation");
    if (wizardValidation) {
        const wizardValidationForm =
            wizardValidation.querySelector("#form");
        const steps = wizardValidationForm.querySelectorAll(".content");
        const btnNextList = wizardValidationForm.querySelectorAll(".btn-next");
        const btnPrevList = wizardValidationForm.querySelectorAll(".btn-prev");
        const btnSubmit = wizardValidationForm.querySelector(".btn-submit");

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
            animation: true,
        });

        // Form Validation Instances
        const formValidations = [];

        // Initialize validation for each step
        steps.forEach((step, index) => {
            let validators = {};

            if (index === 0) {
                // Step 1: Basic Info
                validators = {
                    summary_report_status: {
                        validators: {
                            notEmpty: {
                                message: "التقرير الاجمالي مطلوب ",
                            },
                        },
                    },
                    last_objection_deadline: {
                        validators: {
                            callback: {
                                message: "تاريخ آخر مهلة للاعتراض مطلوب",
                                callback: function (input) {
                                    var summaryReportStatus = $(
                                        "#summary_report_status",
                                    ).val();

                                    // تحقق من قيمة summary_report_status
                                    if (summaryReportStatus == "substantive_ruling" || summaryReportStatus == "formal_ruling") {
                                        const lastObjectionDeadline =
                                            input.value;

                                        // التحقق من قيمة الحقل إذا كانت صالحة أو غير فارغة
                                        if (lastObjectionDeadline == "") {
                                            return false; // فشل التحقق إذا كان الحقل فارغاً أو غير صالح
                                        } else {
                                            return true; // النجاح في الحالات الأخرى
                                        }
                                    }

                                    return true;
                                },
                            },
                        },
                    },
                    session_type: {
                        validators: {
                            notEmpty: {
                                message: "نوع الجلسة مطلوب",
                            },
                        },
                    },
                    rule_type: {
                        validators: {
                            // notEmpty: {
                            //     message: "نوع الحكم مطلوب",
                            // },
                            callback: {
                                message: "نوع الحكم مطلوب",
                                callback: function (input) {
                                    var session_type = $("#session_type").val();

                                    // تحقق من قيمة summary_report_status
                                    if (session_type == "2") {
                                        const rule_type = input.value;

                                        // التحقق من قيمة الحقل إذا كانت صالحة أو غير فارغة
                                        if (rule_type == "") {
                                            return false; // فشل التحقق إذا كان الحقل فارغاً أو غير صالح
                                        } else {
                                            return true; // النجاح في الحالات الأخرى
                                        }
                                    }

                                    return true;
                                },
                            },
                        },
                    },
                    execution_format: {
                        validators: {
                            callback: {
                                message: "الصيغة التنفيذية مطلوبة",
                                callback: function (input) {
                                    var rule_type = $("#rule_type").val();

                                    // تحقق من قيمة summary_report_status
                                    if (rule_type == "1") {
                                        const execution_format = input.value;

                                        // التحقق من قيمة الحقل إذا كانت صالحة أو غير فارغة
                                        if (execution_format == "") {
                                            return false; // فشل التحقق إذا كان الحقل فارغاً أو غير صالح
                                        } else {
                                            return true; // النجاح في الحالات الأخرى
                                        }
                                    }

                                    return true;
                                },
                            },
                        },
                    },
                    // التاريخ المتوقع للتنفيذ اذا كان الصيغة التنفيذية تساوي لا
                    expected_execution_date: {
                        validators: {
                            callback: {
                                message: "التاريخ المتوقع للتنفيذ مطلوب ",
                                callback: function (input) {
                                    var execution_format = $(
                                        "input[name='execution_format']:checked",
                                    ).val();

                                    // تحقق من قيمة summary_report_status
                                    if (execution_format === "no") {
                                        const expected_execution_date =
                                            input.value;

                                        // التحقق من قيمة الحقل إذا كانت صالحة أو غير فارغة
                                        if (expected_execution_date == "") {
                                            return false; // فشل التحقق إذا كان الحقل فارغاً أو غير صالح
                                        } else {
                                            return true; // النجاح في الحالات الأخرى
                                        }
                                    }

                                    return true;
                                },
                            },
                        },
                    },
                    execution_minutes: {
                        validators: {
                            notEmpty: {
                                message: "قيمة دقائق التنفيذ مطلوبة",
                            },
                            integer: {
                                message:
                                    "يجب أن تكون قيمة دقائق التنفيذ عددًا صحيحًا.",
                            },
                        },
                    },
                };
            }

            if (index === 1) {
                // Step 2: Additional Info
            }

            const fv = FormValidation.formValidation(step, {
                fields: validators,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".col-md-6, .col-md-12, .col-12",
                        eleValidClass: "",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            }).on("core.form.valid", function () {
                if (index < steps.length - 1) {
                    validationStepper.next();
                } else {
                    var execution_format = $(
                        "input[name='execution_format']:checked",
                    ).val();

                    if (execution_format === "yes") {
                        Swal.fire({
                            title: "هل تريد إغلاق الدعوى",
                            text: "لقد قمت بتحديد صيغة تنفيذية هل تريد اغلاق الدعوى ؟ ",
                            icon: "warning",
                            showCancelButton: true, // يعرض زر الإلغاء
                            showConfirmButton: true, // يعرض زر التأكيد
                            showDenyButton: false, // لا يعرض زر الرفض
                            buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                            customClass: {
                                popup: "custom-popup", // تخصيص شكل النافذة
                                title: "custom-title", // تخصيص شكل العنوان
                                text: "custom-text", // تخصيص شكل النص
                                confirmButton: "btn btn-success custom-confirm", // تخصيص زر التأكيد
                                cancelButton: "btn btn-danger custom-cancel", // تخصيص زر الإلغاء
                            },
                            confirmButtonText: "نعم",
                            cancelButtonText: "لا",
                            reverseButtons: false, // لعكس ترتيب الأزرار
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // إذا أكد المستخدم، يتم تحديد الخيار "نعم"
                                // إضافة حقل مخفي لتحديد اختيار المستخدم
                                $("<input>")
                                    .attr({
                                        type: "hidden",
                                        name: "user_confirmation",
                                        value: "نعم",
                                    })
                                    .appendTo(wizardValidationForm);

                                // إرسال النموذج
                                btnSubmit.setAttribute('disabled', 'disabled');
                                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                                wizardValidationForm.submit();

                                toastr.info(
                                    "سيتم اغلاق الدعوى بعد استكمال الجلسة",
                                );
                            } else if (
                                result.dismiss === Swal.DismissReason.cancel
                            ) {
                                // إذا ألغى المستخدم، إزالة التحديد
                                // إضافة حقل مخفي لتحديد اختيار المستخدم
                                $("<input>")
                                    .attr({
                                        type: "hidden",
                                        name: "user_confirmation",
                                        value: "لا",
                                    })
                                    .appendTo(wizardValidationForm);

                                // إرسال النموذج
                                btnSubmit.setAttribute('disabled', 'disabled');
                                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                                wizardValidationForm.submit();
                            }
                        });
                    } else {
                        btnSubmit.setAttribute('disabled', 'disabled');
                        btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                        btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                        // إذا لم تكن القيمة "نعم"، يتم إرسال النموذج مباشرة
                        wizardValidationForm.submit();
                    }

                    // إرسال النموذج إذا كان في آخر خطوة
                }
            });

            formValidations.push(fv);
        });

        // Next buttons
        btnNextList.forEach((btn, index) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                formValidations[index].validate();
            });
        });

        // Previous buttons
        btnPrevList.forEach((btn, index) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                validationStepper.previous();
            });
        });

        // Initialize Select2
        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this
                    .select2({
                        placeholder: $this.data("placeholder") || "اختر خيارًا",
                        dropdownParent: $this.parent(),
                        language: "ar",
                        allowClear: true,
                    })
                    .on("change", function () {
                        const fieldName = $this.attr("name");
                        const currentIndex = validationStepper._currentIndex;
                        formValidations[currentIndex].revalidateField(
                            fieldName,
                        );
                    });
            });
        }

        $("#last_objection_deadline").on("dp.change", function (e) {
            formValidations[0].revalidateField("last_objection_deadline");
        });

        $("#expected_execution_date").on("dp.change", function (e) {
            formValidations[0].revalidateField("expected_execution_date");
        });

        // Submit button handler (optional if you want to handle via AJAX)
        btnSubmit.addEventListener("click", function (e) {
            e.preventDefault();
            // قم بتفعيل التحقق من النموذج بأكمله أو خطوة معينة إذا لزم الأمر
            formValidations[1].validate(); // Assuming step 2 is the last step
        });
    }
})();
