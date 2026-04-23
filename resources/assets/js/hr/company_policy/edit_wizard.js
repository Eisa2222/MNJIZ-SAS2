'use strict';

(function () {
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardValidationForm = wizardValidation.querySelector('#form');
    const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    // إنشاء FormValidation
    const formValidation = FormValidation.formValidation(wizardValidationForm, {
        fields: {
            name: {
                validators: {
                    notEmpty: { message: 'الاسم مطلوب' },
                    stringLength: {
                        min: 2,
                        max: 255,
                        message: 'الاسم يجب أن يكون بين 2 و 255 حرف'
                    }
                }
            },
            file: {
                validators: {
                    file: {
                        extension: 'pdf',
                        type: 'application/pdf',
                        maxSize: 10485760, // 10MB
                        message: 'يجب أن يكون الملف من نوع PDF وحجمه أقل من 10 ميغابايت'
                    }
                }
            }
        },
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                rowSelector: '[class*="col-md-"]',
                eleValidClass: '',
            }),
            autoFocus: new FormValidation.plugins.AutoFocus(),
            submitButton: new FormValidation.plugins.SubmitButton()
        }
    }).on("core.form.valid", function () {
        btnSubmit.setAttribute('disabled', 'disabled');
        btnSubmit.dataset.oldText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

        // إرسال النموذج
        wizardValidationForm.submit();
    });

    // زر التحديث
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            formValidation.validate();
        });
    }
})();
