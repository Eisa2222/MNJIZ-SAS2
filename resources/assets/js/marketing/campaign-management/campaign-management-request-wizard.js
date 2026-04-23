'use strict';

(function () {
    const select2 = $('.select2');
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardValidationForm = wizardValidation.querySelector('#form');
    const steps = wizardValidationForm.querySelectorAll('.content');
    const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    const formValidations = [];

    steps.forEach((step, index) => {
        const validators = {
            campaign_name: {
                validators: {
                    notEmpty: { message: ' اسم الحملة  مطلوب' },
                }
            },

            content_type_id: {
                validators: {
                    notEmpty: { message: ' نوع الحملة مطلوب' },
                }
            },

            campaign_section_id: {
                validators: {
                    notEmpty: { message: '  قسم الحملة  مطلوب' },
                }
            },

            content_purpose_id: {
                validators: {
                    notEmpty: { message: ' الهدف من الحملة مطلوب' },
                }
            },

            social_id: {
                validators: {
                    notEmpty: { message: ' المنصة  مطلوبة' },
                }
            },

            status: {
                validators: {
                    notEmpty: { message: ' الحالة  مطلوبة' },
                }
            },

            budget: {
                validators: {
                    notEmpty: { message: ' الميزانية  مطلوبة' },
                }
            },

            start_date: {
                validators: {
                    notEmpty: { message: ' تاريخ  بدء الحملة مطلوب' },
                    date: {
                        format: 'YYYY-MM-DD',
                        message: 'صيغة التاريخ غير صحيحة'
                    }
                }
            },

            end_date: {
                validators: {
                    notEmpty: { message: ' تاريخ  نهاية الحملة مطلوب' },
                    date: {
                        format: 'YYYY-MM-DD',
                        message: 'صيغة التاريخ غير صحيحة'
                    }
                }
            },

            target_audience_id: {
                validators: {
                    notEmpty: { message: ' الجمهور المستهدف  مطلوب' },
                }
            },
        };

        const fv = FormValidation.formValidation(step, {
            fields: validators,
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: '.col-md-6, .col-md-12',
                    eleValidClass: '',
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            }
        }).on("core.form.valid", function () {
            // إذا كانت هناك خطوة تالية انتقل إليها
            if (index < steps.length - 1) {
                validationStepper.next();
            } else {
                // وإلا في آخر خطوة أرسل الفورم
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                wizardValidationForm.submit();
            }
        });

        formValidations.push(fv);
    });

    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            // يشغل تحقق الخطوة الأخيرة
            formValidations[steps.length - 1].validate();
        });
    }

    if (select2.length) {
        select2.each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            $this
                .select2({
                    placeholder: $this.data("placeholder") || "اختر خيارًا",
                    dropdownParent: $this.parent(),
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
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
})();
