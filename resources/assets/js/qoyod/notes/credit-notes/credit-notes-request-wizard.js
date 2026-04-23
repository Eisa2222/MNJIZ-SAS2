
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

            parent_id: {
                validators: {
                    notEmpty: { message: 'فاتورة المبيعات الاصلية مطلوبة' },
                }
            },
            contact_id: {
                validators: {
                    notEmpty: { message: 'العميل مطلوب' },
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

             issuance_reason: {
                validators: {
                    notEmpty: { message: ' سبب الاصدار مطلوب' },
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