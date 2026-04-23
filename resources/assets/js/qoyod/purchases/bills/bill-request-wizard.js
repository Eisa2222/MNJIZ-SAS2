
'use strict';

(function () {
    const select2 = $('.select2');
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardValidationForm = wizardValidation.querySelector('#form');
    const steps = wizardValidationForm.querySelectorAll('.content');
    const btnNextList = wizardValidationForm.querySelectorAll('.btn-next');
    const btnPrevList = wizardValidationForm.querySelectorAll('.btn-prev');
    const btnSubmit = wizardValidationForm.querySelector('.btn-submit');


    window.validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    const formValidations = [];

    steps.forEach((step, index) => {
        const validators = {
            reference: {
                validators: {
                    notEmpty: { message: 'المرجع مطلوب' },
                    stringLength: {
                        min: 3, max: 50,
                        message: 'يجب أن يكون المرجع  بين 3 و50 حرف / رقم.'
                    }
                }
            },

            contact_id: {
                validators: {
                    notEmpty: { message: 'المورد مطلوب' },
                }
            },

            issue_date: {
                validators: {
                    notEmpty: { message: 'تاريخ الإصدار مطلوب' },
                    date: {
                        format: 'YYYY-MM-DD',
                        message: 'صيغة التاريخ غير صحيحة'
                    }
                }
            },

            due_date: {
                validators: {
                    notEmpty: { message: 'تاريخ الإستحقاق  مطلوب' },
                    date: {
                        format: 'YYYY-MM-DD',
                        message: 'صيغة التاريخ غير صحيحة'
                    },
                    callback: {
                        message: 'تاريخ الإستحقاق  يجب أن يكون بعد تاريخ الإصدار',
                        callback: function (input) {
                            const issueDate = document.querySelector('[name="issue_date"]').value;
                            const expiryDate = input.value;
                            return !issueDate || !expiryDate || new Date(expiryDate) >= new Date(issueDate);
                        }
                    }
                }
            },

            status: {
                validators: {
                    notEmpty: { message: ' الحالة مطلوبة' },
                }
            },

            inventory_id: {
                validators: {
                    notEmpty: { message: ' الموقع مطلوب' },
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
            if (index < steps.length - 1) {
                validationStepper.next();
            } else {
                wizardValidationForm.requestSubmit();
            }
        });

        formValidations.push(fv);
    });

    btnNextList.forEach((btn, index) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            formValidations[index].validate();
        });
    });

    btnPrevList.forEach((btn, index) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            validationStepper.previous();
        });
    });

    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            formValidations[steps.length - 1].validate();
        });
    }



    // تهيئة Select2
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
                })
                .on('change', function () {
                    const fieldName = $this.attr('name');
                    const currentIndex = validationStepper._currentIndex;
                    formValidations[currentIndex].revalidateField(fieldName);
                });
        });
    }



})();