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
            name_ar: {
                validators: {
                    notEmpty: { message: 'اسم الحساب بالعربي مطلوب' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون اسم الحساب بالعربي  بين 3 و 150 حرف.'
                    }
                }
            },

            name_en: {
                validators: {
                    notEmpty: { message: 'اسم الحساب بالانجليزي مطلوب' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون اسم الحساب بالانجليزي  بين 3 و 150 حرف.'
                    }
                }
            },

            code: {
                validators: {
                    notEmpty: { message: ' الكود  مطلوب' },
                }
            },

            type: {
                validators: {
                    notEmpty: { message: 'النوع  مطلوب' },
                }
            },

            recieve_payments: {
                validators: {
                    notEmpty: { message: 'استقبال المدفوعات  مطلوب' },
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
                    placeholder: $this.data('placeholder') || 'اختر خيارًا',
                    dropdownParent: $this.parent(),
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                })
                .on('change', function () {
                    const fieldName = $this.attr('name');
                    const currentIndex = validationStepper._currentIndex;
                    formValidations[currentIndex].revalidateField(fieldName);
                });
        });
    }
})();
