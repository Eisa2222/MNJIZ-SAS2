
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


    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    const formValidations = [];

    steps.forEach((step, index) => {
        const validators = {
            reference: {
                validators: {
                    notEmpty: { message: 'رقم المرجع مطلوب' },
                    stringLength: {
                        min: 3, max: 50,
                        message: 'يجب أن يكون رقم المرجع بين 3 و50 حرف / رقم.'
                    }
                }
            },
            contact_type: {
                validators: {
                    notEmpty: { message: 'الجهة مطلوبة' },
                }
            },
            customer_id: {
                validators: {
                    callback: {
                        message: 'العميل مطلوب',
                        callback: function (input) {
                            // اجعل حقل customer_id مطلوبًا فقط إذا كانت الجهة "عميل"
                            return $('#contact_type').val() !== 'عميل' || input.value.trim() !== '';
                        }
                    },
                },
            },
            supplier_id: {
                validators: {
                    callback: {
                        message: 'المورد مطلوب',
                        callback: function (input) {
                            // اجعل حقل supplier_id مطلوبًا فقط إذا كانت الجهة "مورد"
                            return $('#contact_type').val() !== 'مورد' || input.value.trim() !== '';
                        }
                    },
                },
            },
            account_id: {
                validators: {
                    notEmpty: { message: 'الحساب مطلوب' },
                }
            },
            kind: {
                validators: {
                    notEmpty: { message: 'النوع مطلوب' },
                }
            },
            amount: {
                validators: {
                    notEmpty: { message: 'المبلغ مطلوب' },
                }
            },
            date: {
                validators: {
                    notEmpty: { message: 'التاريخ مطلوب' },
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
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                wizardValidationForm.submit();
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
                    placeholder: $this.data('placeholder') || 'اختر خيارًا',
                    dropdownParent: $this.parent(),
                    language: 'ar',
                    allowClear: true,
                })
                .on('change', function () {
                    const fieldName = $this.attr('name');
                    const currentIndex = validationStepper._currentIndex;
                    formValidations[currentIndex].revalidateField(fieldName);
                });
        });
    }


    // تقييد الإدخال للأرقام فقط
    document.querySelectorAll("input.numeric-only").forEach((input) => {
        input.addEventListener("input", function () {
            let sanitized = input.value.replace(/\D/g, "");
            const max = input.getAttribute("maxlength");
            if (max) {
                sanitized = sanitized.slice(0, max);
            }
            input.value = sanitized;
        });
    });

})();
