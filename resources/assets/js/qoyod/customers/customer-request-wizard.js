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
            name: {
                validators: {
                    notEmpty: { message: 'اسم العميل مطلوب' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون اسم العميل  بين 3 و 150 حرف.'
                    }
                }
            },

            organization: {
                validators: {
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون اسم المنشأة  بين 3 و 150 حرف.'
                    }
                }
            },

            status: {
                validators: {
                    notEmpty: { message: 'الحالة  مطلوبة' },
                }
            },

            email: {
                validators: {
                    emailAddress: {
                        message: "يرجى إدخال بريد إلكتروني صالح.",
                    },
                }
            },

            phone_number: {
                validators: {
                    regexp: {
                        regexp: /^\+?[0-9]{9,15}$/,
                        message: 'يرجى إدخال رقم هاتف صالح (9–15 رقم، اختياري علامة + في البداية).'
                    }
                }
            },

            secondary_phone_number: {
                validators: {
                    regexp: {
                        regexp: /^\+?[0-9]{9,15}$/,
                        message: 'يرجى إدخال رقم هاتف صالح (9–15 رقم، اختياري علامة + في البداية).'
                    }
                }
            },

            tax_number: {
                validators: {
                    regexp: {
                        // يقبل فقط سلسلة مكوّنة من 10 أرقام (0–9)
                        regexp: /^\+?[0-9]{15}$/,
                        message: 'يجب أن يتكوّن الرقم الضريبي من  15 أرقام صحيحة'
                    }
                }
            }


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
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                // وإلا في آخر خطوة أرسل الفورم
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

    // Initialize Select2
    if (select2.length) {
        select2.each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            $this
                .select2({
                    placeholder: $this.data('placeholder') || ' ',
                    dropdownParent: $this.parent(),
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                });
        });
    }
})();
