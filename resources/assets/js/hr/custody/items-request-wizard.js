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
                    notEmpty: { message: 'اسم الصنف مطلوب' },
                }
            },

            asset_category_id: {
                validators: {
                    notEmpty: { message: 'التصنيف مطلوب' },
                }
            },

            storage_location_id: {
                validators: {
                    notEmpty: { message: 'مرجعية الاصل مطلوبة' },
                }
            },

            use_status: {
                validators: {
                    notEmpty: { message: 'حالة الاستخدام مطلوبة' },
                }
            },

            // التحقق من الأرقام التسلسلية
            'serial_numbers[]': {
                validators: {
                    notEmpty: {
                        message: 'الرقم التسلسلي مطلوب'
                    },
                    callback: {
                        message: 'يجب أن تكون الأرقام التسلسلية فريدة وغير مكررة',
                        callback: function (value, validator, $field) {
                            // الحصول على جميع الأرقام التسلسلية
                            const serialInputs = document.querySelectorAll('input[name="serial_numbers[]"]');
                            const values = Array.from(serialInputs)
                                .map(input => input.value.trim())
                                .filter(val => val !== '');

                            // التحقق من وجود أرقام
                            if (values.length === 0) {
                                return {
                                    valid: false,
                                    message: 'يجب إدخال رقم تسلسلي واحد على الأقل'
                                };
                            }

                            // التحقق من عدم التكرار
                            const uniqueValues = [...new Set(values)];
                            if (uniqueValues.length !== values.length) {
                                return {
                                    valid: false,
                                    message: 'يجب أن تكون الأرقام التسلسلية فريدة وغير مكررة'
                                };
                            }

                            return { valid: true };
                        }
                    }
                }
            }
        };

        const fv = FormValidation.formValidation(step, {
            fields: validators,
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: '.col-md-6, .col-md-12, .col-12',
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
                    dir: 'rtl',
                    minimumResultsForSearch: 5,
                })
                .on("change", function () {
                    const fieldName = $this.attr("name");
                    const currentIndex = validationStepper._currentIndex;
                    formValidations[currentIndex].revalidateField(fieldName);
                });
        });
    }

    // متغيرات إدارة الأرقام التسلسلية
    let serialIndex = 1;

    // دالة إضافة رقم تسلسلي جديد
    window.addSerialNumber = function () {
        const container = document.getElementById('serialNumbersContainer');

        const newDiv = document.createElement('div');
        newDiv.className = 'serial-number-container mb-3';
        newDiv.innerHTML = `
            <div class="serial-number-header form-label">
                الرقم التسلسلي
                <span class="text-danger">*</span>
            </div>
            <div class="row g-2">
                <div class="col-md-11">
                    <input type="text" name="serial_numbers[]" class="form-control"
                           placeholder="أدخل الرقم التسلسلي" required>
                </div>
                <div class="col-md-1 d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-serial-btn"
                            onclick="removeSerialNumber(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(newDiv);
        serialIndex++;

        // إضافة التحقق للحقل الجديد
        const currentIndex = validationStepper._currentIndex;
        const newInput = newDiv.querySelector('input[name="serial_numbers[]"]');

        // إضافة event listener للتحقق عند التغيير
        newInput.addEventListener('input', function () {
            formValidations[currentIndex].revalidateField('serial_numbers[]');
        });

        showHideRemoveButtons();
    };

    // دالة حذف رقم تسلسلي (النسخة المبسطة)
    window.removeSerialNumber = function (button) {
        const container = button.closest('.serial-number-container');
        const allContainers = document.querySelectorAll('.serial-number-container');

        // تحقق من أن هذا ليس الحقل الأول
        const isFirstContainer = container === allContainers[0];

        // منع حذف الحقل الأول نهائياً
        if (isFirstContainer) {
            return;
        }

        // حذف الحقل
        container.remove();

        // إعادة التحقق بعد الحذف
        const currentIndex = validationStepper._currentIndex;
        formValidations[currentIndex].revalidateField('serial_numbers[]');

        showHideRemoveButtons();
    };

    // دالة إظهار/إخفاء أزرار الحذف (مبسطة)
    function showHideRemoveButtons() {
        const containers = document.querySelectorAll('.serial-number-container');

        containers.forEach((container, index) => {
            const removeBtn = container.querySelector('.remove-serial-btn');
            if (removeBtn) {
                // إخفاء زر الحذف للحقل الأول دائماً
                if (index === 0) {
                    removeBtn.style.display = 'none';
                } else {
                    removeBtn.style.display = 'inline-block';
                }
            }
        });
    }

    // تهيئة النموذج عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function () {
        // إعداد أزرار الحذف
        showHideRemoveButtons();

        // إضافة event listeners للحقول الموجودة
        const existingInputs = document.querySelectorAll('input[name="serial_numbers[]"]');
        existingInputs.forEach(input => {
            input.addEventListener('input', function () {
                const currentIndex = validationStepper._currentIndex;
                if (formValidations[currentIndex]) {
                    formValidations[currentIndex].revalidateField('serial_numbers[]');
                }
            });
        });

        // التحقق من وجود بيانات قديمة (في حالة الأخطاء)
        // ملاحظة: هذا الجزء يحتاج إلى تمرير البيانات من Blade
        if (window.oldSerialNumbers && window.oldSerialNumbers.length > 0) {
            const container = document.getElementById('serialNumbersContainer');
            const firstInput = container.querySelector('input[name="serial_numbers[]"]');

            // تعبئة الحقل الأول
            if (window.oldSerialNumbers[0]) {
                firstInput.value = window.oldSerialNumbers[0];
            }

            // إضافة باقي الحقول
            for (let i = 1; i < window.oldSerialNumbers.length; i++) {
                addSerialNumber();
                const inputs = container.querySelectorAll('input[name="serial_numbers[]"]');
                if (inputs[i] && window.oldSerialNumbers[i]) {
                    inputs[i].value = window.oldSerialNumbers[i];
                }
            }
        }
    });
})();
