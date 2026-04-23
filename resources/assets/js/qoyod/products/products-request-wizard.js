
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
            sku: {
                validators: {
                    notEmpty: { message: 'الرقم التسلسلي مطلوب' },
                    stringLength: {
                        min: 3, max: 50,
                        message: 'يجب أن يكون الرقم التسلسلي بين 3 و50 حرف / رقم.'
                    }
                }
            },
            name_ar: {
                validators: {
                    notEmpty: { message: 'الاسم بالعربية مطلوب' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون الاسم بالعربية بين 3 و150 حرف.'
                    }
                }
            },
            name_en: {
                validators: {
                    notEmpty: { message: 'الاسم بالإنجليزية مطلوب' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون الاسم بالإنجليزية بين 3 و150 حرف.'
                    }
                }
            },
            category_id: {
                validators: {
                    notEmpty: { message: 'الصنف مطلوب' },
                }
            },
            product_unit_type_id: {
                validators: {
                    notEmpty: { message: 'وحدة القياس مطلوبة' },
                }
            },
            buying_price: {
                validators: {
                    callback: {
                        message: 'سعر الشراء مطلوب',
                        callback: function (input) {
                            const currentType = getCurrentType();
                            if (currentType === 'Expense') {
                                const fieldValue = input.value.trim();
                                return fieldValue !== '';
                            }
                            return true;
                        },
                    },
                }
            },
            expense_account_id: {
                validators: {
                    callback: {
                        message: 'حساب المصروفات مطلوب',
                        callback: function (input) {
                            const currentType = getCurrentType();
                            if (currentType === 'Expense') {
                                const fieldValue = input.value.trim();
                                return fieldValue !== '';
                            }
                            return true;
                        },
                    },
                }
            },
            selling_price: {
                validators: {
                    callback: {
                        message: 'سعر البيع مطلوب',
                        callback: function (input) {
                            const currentType = getCurrentType();
                            if (currentType === 'Service') {
                                const fieldValue = input.value.trim();
                                return fieldValue !== '';
                            }
                            return true;
                        },
                    },
                }
            },
            sales_account_id: {
                validators: {
                    callback: {
                        message: 'حساب المبيعات مطلوب',
                        callback: function (input) {
                            const currentType = getCurrentType();
                            if (currentType === 'Service') {
                                const fieldValue = input.value.trim();
                                return fieldValue !== '';
                            }
                            return true;
                        },
                    },
                }
            },
            tax_id: {
                validators: {
                    notEmpty: { message: 'الضريبة مطلوبة' },
                }
            },
            special_tax_reason_id: {
                validators: {
                    callback: {
                        message: 'سبب الضريبة الخاصة مطلوب',
                        callback: function (input) {
                            const taxVal = $('#tax_id').val();
                            // إذا كان الضريبة Zero أو Exempt، فالحقل مطلوب
                            if (taxVal === '2' || taxVal === '3') {
                                const fieldValue = input.value.trim();
                                return fieldValue !== '';
                            }
                            return true;
                        },
                    },
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

    function toggleTypeFields() {
        const currentType = getCurrentType();

        const $buying_price = $('#buying_price');
        const $expense_account_id = $('#expense_account_id');
        const $selling_price = $('#selling_price');
        const $sales_account_id = $('#sales_account_id');

        // إخفاء جميع الحقول أولاً
        $('#wrapper_buying_price').addClass('d-none');
        $('#wrapper_expense_account_id').addClass('d-none');
        $('#wrapper_selling_price').addClass('d-none');
        $('#wrapper_sales_account_id').addClass('d-none');

        // إزالة required من جميع الحقول
        $buying_price.removeAttr("required");
        $expense_account_id.removeAttr("required");
        $selling_price.removeAttr("required");
        $sales_account_id.removeAttr("required");

        if (currentType === 'Service') {
            // الخدمة: إظهار سعر البيع وحساب المبيعات
            $('#wrapper_selling_price').removeClass('d-none');
            $('#wrapper_sales_account_id').removeClass('d-none');
            $selling_price.attr("required", "required");
            $sales_account_id.attr("required", "required");

            // تفريغ حقول المصروفات
            $buying_price.val("");
            $expense_account_id.val(null).trigger("change");
        }
        else if (currentType === 'Expense') {
            // المصروف: إظهار سعر الشراء وحساب المصروفات
            $('#wrapper_buying_price').removeClass('d-none');
            $('#wrapper_expense_account_id').removeClass('d-none');
            $buying_price.attr("required", "required");
            $expense_account_id.attr("required", "required");

            // تفريغ حقول الخدمات
            $selling_price.val("");
            $sales_account_id.val(null).trigger("change");
        }

        // إعادة التحقق من الحقول المتأثرة في جميع الخطوات
        revalidateTypeFields();
    }

    // دالة لإعادة التحقق من الحقول المتعلقة بالنوع
    function revalidateTypeFields() {
        const fieldsToRevalidate = ['buying_price', 'expense_account_id', 'selling_price', 'sales_account_id'];

        formValidations.forEach(fv => {
            fieldsToRevalidate.forEach(fieldName => {
                if (fv.getElements(fieldName).length > 0) {
                    fv.revalidateField(fieldName);
                }
            });
        });
    }

    function toggleSpecialTaxReason() {
        const taxVal = $('#tax_id').val();
        const wrapper = $('#wrapper_special_tax_reason_id');
        const select = $('#special_tax_reason_id');

        const savedValue = (typeof savedSpecialTaxReasonId !== 'undefined' && savedSpecialTaxReasonId) ? savedSpecialTaxReasonId : null;

        // تفريغ القائمة وإضافة الخيار الفارغ
        select.empty().append('<option value=""></option>');

        if (taxVal === '2') {
            // Zero
            wrapper.removeClass('d-none');
            select.prop('disabled', false).prop('required', true);

            // إضافة خيارات Zero
            zeroReasons.forEach(reason => {
                const isSelected = savedValue == reason.id ? 'selected' : '';
                select.append(`<option value="${reason.id}" ${isSelected}>${reason.name}</option>`);
            });
        }
        else if (taxVal === '3') {
            // Exempt
            wrapper.removeClass('d-none');
            select.prop('disabled', false).prop('required', true);

            // إضافة خيارات Exempt
            exemptReasons.forEach(reason => {
                const isSelected = savedValue == reason.id ? 'selected' : '';
                select.append(`<option value="${reason.id}" ${isSelected}>${reason.name}</option>`);
            });
        }
        else {
            // إخفاء الحقل وتعطيله
            wrapper.addClass('d-none');
            select.prop('disabled', true).prop('required', false).val('');
        }

        // تحديث Select2
        select.trigger('change.select2');

        // إعادة التحقق من حقل سبب الضريبة الخاصة
        formValidations.forEach(fv => {
            if (fv.getElements('special_tax_reason_id').length > 0) {
                fv.revalidateField('special_tax_reason_id');
            }
        });
    }

    // تهيئة الحقول عند تحميل الصفحة
    toggleTypeFields();
    toggleSpecialTaxReason();

    // عند تغيير نوع المنتج
    $('input[name="type"]').on('change', function () {
        toggleTypeFields();
    });

    // عند تغيير قيمة الضريبة
    $('#tax_id').on('change', function () {
        toggleSpecialTaxReason();
    });

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
