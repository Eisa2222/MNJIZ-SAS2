'use strict';

(function () {
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
            unit_name: {
                validators: {
                    notEmpty: { message: 'الوحدة مطلوبة' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن تكون الوحدة  بين 3 و 150 حرف.'
                    }
                }
            },
            unit_representation: {
                validators: {
                    notEmpty: { message: 'طريقة العرض مطلوبة' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن تكون طريقة العرض بين 3 و 150 حرف.'
                    }
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
})();
