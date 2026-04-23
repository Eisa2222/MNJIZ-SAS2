/**
 * Form Wizard Validation for Power of Attorney
 */

'use strict';

(function () {
    const select2 = $('.select2');

    // Wizard Validation
    const wizardValidation = document.querySelector('#wizard-validation');
    if (wizardValidation) {
        const wizardValidationForm = wizardValidation.querySelector('#template-form');
        const steps = wizardValidationForm.querySelectorAll('.content');
        const btnNextList = wizardValidationForm.querySelectorAll('.btn-next');
        const btnPrevList = wizardValidationForm.querySelectorAll('.btn-prev');
        const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
            animation: true
        });

        // Form Validation Instances
        const formValidations = [];

        // Initialize validation for each step
        steps.forEach((step, index) => {
            let validators = {};

            if (index === 0) { // Step 1: Basic Info
                validators = {
                    name: {
                        validators: {
                            notEmpty: {
                                message: 'اسم النموذج مطلوب.'
                            },
                            stringLength: {
                                min: 3,
                                max: 150,
                                message: 'يجب أن يكون اسم العرض بين 3 و 150 حرف.'
                            }
                        }
                    },
                    templates_type: {
                        validators: {
                            notEmpty: {
                                message: 'تحديد نوع النموذج مطلوب'
                            },
                        }
                    },
                };
            }

            if (index === 1) { // Step 2: Additional Info
                // validators = {
                //     content: {
                //         validators: {
                //             notEmpty: {
                //                 message: 'الحقل الخاص بالنموذج مطلوب'
                //             }
                //         }
                //     }
                // };
            }

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
            }).on('core.form.valid', function () {
                if (index < steps.length - 1) {
                    validationStepper.next();
                } else {
                    btnSubmit.setAttribute('disabled', 'disabled');
                    btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                    // إرسال النموذج إذا كان في آخر خطوة
                    wizardValidationForm.submit();
                }
            });

            formValidations.push(fv);
        });

        // Next buttons
        btnNextList.forEach((btn, index) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                formValidations[index].validate();
            });
        });

        // Previous buttons
        btnPrevList.forEach((btn, index) => {
            btn.addEventListener('click', function (e) {
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
                        placeholder: $this.data('placeholder') || 'اختر خيارًا',
                        dropdownParent: $this.parent(),
                        language: 'ar'
                    })
                    .on('change', function () {
                        const fieldName = $this.attr('name');
                        const currentIndex = validationStepper._currentIndex;
                        formValidations[currentIndex].revalidateField(fieldName);
                    });
            });
        }

        // Event listener for start_date field
        $('#start_date').on('dp.change', function (e) {
            formValidations[0].revalidateField('start_date');
        });

        // Submit button handler (optional if you want to handle via AJAX)
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            // قم بتفعيل التحقق من النموذج بأكمله أو خطوة معينة إذا لزم الأمر
            formValidations[2].validate(); // Assuming step 2 is the last step
        });
    }
})();
