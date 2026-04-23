
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

    const ProductType = {
        PRODUCT: 'Product',
        SERVICE: 'Service',
        EXPENSE: 'Expense',
        RAW_MATERIAL: 'RawMaterial',
        RECIPE: 'Recipe'
    };

    // دالة للحصول على النوع المختار حالياً
    function getCurrentType() {
        if (typeof currentProductType !== 'undefined') {
            return currentProductType;
        } else {
            return $('input[name="type"]:checked').val() || null;
        }
    }

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    const formValidations = [];

    steps.forEach((step, index) => {
        const validators = {
            ar_name: {
                validators: {
                    notEmpty: { message: 'الاسم بالعربية مطلوب' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون الاسم بالعربية بين 3 و150 حرف.'
                    }
                }
            },
            name: {
                validators: {
                    notEmpty: { message: 'الاسم بالإنجليزية مطلوب' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون الاسم بالإنجليزية بين 3 و150 حرف.'
                    }
                }
            },
            account_id: {
                validators: {
                    notEmpty: { message: 'حساب المخزون مطلوب' },
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
                    placeholder: $this.data('placeholder') || ' ',
                    dropdownParent: $this.parent(),
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
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
