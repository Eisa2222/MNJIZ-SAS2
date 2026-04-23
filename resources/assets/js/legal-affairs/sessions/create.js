"use strict";

(function () {
    const select2 = $(".select2");

    // Wizard Validation
    const wizardValidation = document.querySelector("#wizard-validation");
    if (wizardValidation) {
        // تصحيح: استخدام ID الصحيح للـ form
        const wizardValidationForm = wizardValidation.querySelector("#form");

        if (!wizardValidationForm) {
            console.error('Form with ID #power-form not found');
            return;
        }

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
                // Step 1: Basic Info - تعديل حسب الحقول الموجودة فعلياً
                validators = {
                    project_id: {
                        validators: {
                            notEmpty: {
                                message: "المشروع مطلوب",
                            },
                        },
                    },
                    lawsuit_id: {
                        validators: {
                            notEmpty: {
                                message: "الدعوى مطلوبة",
                            },
                        },
                    },
                    'assigned_to[]': {
                        validators: {
                            notEmpty: {
                                message: "المكلفين مطلوبون",
                            },
                        },
                    },
                    entity_ranks_id: {
                        validators: {
                            notEmpty: {
                                message: "درجة الجهة مطلوبة",
                            },
                        },
                    },
                    session_date: {
                        validators: {
                            notEmpty: {
                                message: "التاريخ مطلوب",
                            },
                        },
                    },
                    session_time: {
                        validators: {
                            notEmpty: {
                                message: "الوقت مطلوب",
                            },
                        },
                    },
                };
            }

            if (index === 1) {
                // Step 2: Additional Info
                validators = {
                    // يمكنك إضافة حقول للخطوة الثانية إذا لزم الأمر
                    notes: {
                        validators: {
                            // اختياري - لا حاجة للتحقق
                        },
                    },
                };
            }

            const fv = FormValidation.formValidation(step, {
                fields: validators,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '[class^="col-md-"]',
                        eleValidClass: '',
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton()
                }
            }).on('core.form.valid', function () {
                if (index < steps.length - 1) {
                    validationStepper.next();
                } else {
                    // إرسال النموذج إذا كان في آخر خطوة
                    btnSubmit.setAttribute('disabled', 'disabled');
                    btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                    wizardValidationForm.submit();
                }
            });

            formValidations.push(fv);
        });

        // Next buttons
        btnNextList.forEach((btn, index) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();

                // التحقق من صحة الخطوة الحالية
                if (formValidations[index]) {
                    formValidations[index].validate();
                }
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
                        dir: "rtl",
                        allowClear: true,
                        width: '100%'
                    })
                    .on("change", function () {
                        const fieldName = $this.attr("name");
                        const currentIndex = validationStepper._currentIndex;

                        // التحقق من وجود التحقق للخطوة الحالية
                        if (formValidations[currentIndex]) {
                            formValidations[currentIndex].revalidateField(fieldName);
                        }
                    });
            });
        }

        $("#session_date").on("dp.change", function (e) {
            formValidations[0].revalidateField("session_date");
        });

        // Submit button handler
        if (btnSubmit) {
            btnSubmit.addEventListener('click', function (e) {
                e.preventDefault();

                // التحقق من الخطوة الأخيرة قبل الإرسال
                const lastStepIndex = formValidations.length - 1;
                if (formValidations[lastStepIndex]) {
                    formValidations[lastStepIndex].validate();
                }
            });
        }
    }
})();
