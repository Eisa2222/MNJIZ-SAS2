"use strict";

(function () {
    const $select2 = $(".select2");
    const wizardEl = document.querySelector("#wizard-validation");
    if (!wizardEl) return;

    // تهيئة bs-stepper
    new Stepper(wizardEl, { linear: true });


    // FormValidation
    const form = wizardEl.querySelector("form");
    const fv = FormValidation.formValidation(form, {
        fields: {
            employee_id: {
                validators: { notEmpty: { message: "يرجى اختيار الموظف" } }
            },
            leave_type_id: {
                validators: { notEmpty: { message: "يرجى اختيار نوع الإجازة" } }
            },
            opening_balance: {
                validators: {
                    notEmpty: { message: "يرجى إدخال الرصيد" },
                    numeric: { message: "يجب أن يكون رقماً عشرياً" },
                    greaterThan: { min: 0.01, message: "يجب أن يكون أكبر من صفر" }
                }
            },
            effective_date: {
                validators: {
                    notEmpty: { message: "يرجى اختيار تاريخ السريان" },
                    date: { format: "YYYY-MM-DD", message: "صيغة التاريخ غير صحيحة" }
                }
            }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                rowSelector: ".col-md-4, .col-md-6, .col-12",
                eleValidClass: ""
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
            submitButton: new FormValidation.plugins.SubmitButton()
        }
    }).on("core.form.valid", () => {
        form.submit();
    });

    // زر ‟سابق‟ (لا يوجد خطوات أخرى)
    wizardEl.querySelector(".btn-prev").addEventListener("click", e => {
        e.preventDefault();
    });

    // init Select2 و revalidate
    if ($select2.length) {
        $select2.each(function () {
            const $el = $(this).select2({
                placeholder: $(this).data("placeholder") || "",
                dropdownParent: $(this).parent(),
                language: "ar"
            });
            $el.on("change", () => fv.revalidateField(this.name));
        });
    }

})();
