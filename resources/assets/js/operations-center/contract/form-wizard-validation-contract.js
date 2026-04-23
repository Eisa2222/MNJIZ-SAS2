'use strict';

(function () {
    const select2 = $('.select2');

    // Wizard Validation
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardForm = wizardValidation.querySelector('#power-form');
    const steps = wizardForm.querySelectorAll('.content');
    const btnNextList = wizardForm.querySelectorAll('.btn-next');
    const btnPrevList = wizardForm.querySelectorAll('.btn-prev');
    const btnSubmit = wizardForm.querySelector('.btn-submit');

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    // حساب مواقع الخطوات ديناميكياً
    const paymentStepIndex = steps.length - 1;    // فهرس الخطوة الرابعة (دفعات)
    const lastStepIndex = steps.length - 1;    // فهرس آخر خطوة (Submit)

    // مصفوفة حفظ مثيلات التحقق لكل خطوة
    const formValidations = [];

    // دالة لإضافة تحقق ديناميكي لصفوف الدفعات
    function addDynamicPaymentValidation(fv) {
        const paymentRows = document.querySelectorAll('.payment-row');

        paymentRows.forEach((row, idx) => {
            // حساب نوع الدفع
            const calcType = row.querySelector(`[name="payments[${idx}][calculation_type]"]`);
            if (calcType) {
                fv.addField(calcType.name, {
                    validators: {
                        notEmpty: { message: ' طريقة الحساب مطلوب لكل قسط.' }
                    }
                });
            }

            const paymentBatch = row.querySelector(`[name="payments[${idx}][payment_batch_type]"]`);
            if (paymentBatch) {
                fv.addField(paymentBatch.name, {
                    validators: {
                        notEmpty: { message: 'نوع الدفعة مطلوب لكل قسط.' }
                    }
                });
            }

            // تاريخ الاستحقاق
            const dueDate = row.querySelector(`[name="payments[${idx}][due_date]"]`);
            if (dueDate) {
                fv.addField(dueDate.name, {
                    validators: {
                        notEmpty: { message: 'تاريخ الاستحقاق مطلوب لكل قسط.' },
                        date: {
                            format: 'YYYY-MM-DD',
                            message: 'التاريخ غير صالح. استخدم صيغة YYYY-MM-DD.'
                        }
                    }
                });
            }

            // النسبة
            const percentage = row.querySelector(`[name="payments[${idx}][percentage]"]`);
            if (percentage) {
                fv.addField(percentage.name, {
                    validators: {
                        callback: {
                            message: 'حقل النسبة مطلوب إذا اخترت النسبة.',
                            callback(input) {
                                if (calcType && calcType.value === 'percentage') {
                                    return input.value.trim() !== '';
                                }

                                return true;
                            }
                        },
                        numeric: { message: 'النسبة يجب أن تكون رقماً صالحاً.' },
                        between: {
                            min: 0,
                            max: 100,
                            message: 'النسبة يجب أن تكون بين 0 و 100.'
                        }
                    }
                });
            }

            // المبلغ الثابت
            const fixedAmount = row.querySelector(`[name="payments[${idx}][fixed_amount]"]`);
            if (fixedAmount) {
                fv.addField(fixedAmount.name, {
                    validators: {
                        callback: {
                            message: 'حقل المبلغ مطلوب إذا اخترت مبلغ ثابت.',
                            callback(input) {
                                if (calcType && calcType.value === 'fixed') {
                                    return input.value.trim() !== '';
                                }

                                return true;
                            }
                        },
                        numeric: { message: 'المبلغ يجب أن يكون رقماً صالحاً.' },
                    }
                });
            }
        });
    }

    // إعداد التحقق لكل خطوة
    steps.forEach((step, index) => {
        let validators = {};

        if (index === 0) {
            // الخطوة الأولى: المعلومات الأساسية
            validators = {
                contract_name: {
                    validators: {
                        notEmpty: { message: 'اسم العقد مطلوب.' },
                        stringLength: {
                            min: 3,
                            max: 150,
                            message: 'يجب أن يكون اسم العقد بين 3 و 150 حرف.'
                        }
                    }
                },
                contract_type: {
                    validators: {
                        notEmpty: { message: 'نوع العقد مطلوب.' }
                    }
                },
                main_contract_id: {
                    validators: {
                        callback: {
                            message: 'العقد الرئيسي مطلوب',
                            callback(input) {
                                if ($('#contract_type').val() === 'supplementary') {
                                    return input.value.trim() !== '';
                                }

                                return true;
                            }
                        }
                    }
                },
                contract_manager_id: {
                    validators: {
                        integer: { message: 'بيانات مسؤول العقد غير صحيحة' }
                    }
                },
                offer_id: {
                    validators: {
                        notEmpty: { message: 'العرض مطلوب.' },
                        integer: { message: 'بيانات العرض غير صحيحة' }
                    }
                },
                contract_status_id: {
                    validators: {
                        integer: { message: 'بيانات حالة العقد غير صحيحة' }
                    }
                },
                contract_start_date: {
                    validators: {
                        notEmpty: { message: 'تاريخ بداية العقد مطلوب.' },
                        date: {
                            format: ['YYYY-MM-DD', 'iYYYY-iMM-iDD'],
                            message: 'التاريخ غير صالح. استخدم YYYY-MM-DD أو iYYYY-iMM-iDD.'
                        }
                    }
                },
                contract_end_date: {
                    validators: {
                        date: {
                            format: ['YYYY-MM-DD', 'iYYYY-iMM-iDD'],
                            message: 'التاريخ غير صالح. استخدم YYYY-MM-DD أو iYYYY-iMM-iDD.'
                        }
                    }
                },
                expected_closure_date: {
                    validators: {
                        notEmpty: { message: 'تاريخ الإغلاق المتوقع مطلوب.' },
                        date: {
                            format: ['YYYY-MM-DD', 'iYYYY-iMM-iDD'],
                            message: 'التاريخ غير صالح. استخدم YYYY-MM-DD أو iYYYY-iMM-iDD.'
                        }
                    }
                }
            };
        } else if (index === 1 || index === 2) {
            // الخطوة الثانية والثالثة: يمكن إضافة تحقق لاحقاً
            validators = {};
        }

        // إنشاء مثيل FormValidation
        const fv = FormValidation.formValidation(step, {
            fields: validators,
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: '.col-md-6, .col-md-12, .payment-row',
                    eleValidClass: ''
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            }
        }).on('core.form.valid', () => {
            if (index < lastStepIndex) {
                validationStepper.next();
            } else {
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                wizardForm.submit();
            }
        });

        formValidations.push(fv);

        // بعد إنشاء التحقق للخطوة الرابعة، أضف الحقول الديناميكية مباشرة
        if (index === paymentStepIndex) {
            addDynamicPaymentValidation(fv);
        }
    });

    // عند إضافة دفعة جديدة
    $(document).on('click', '#add-payment-btn', function () {
        addDynamicPaymentValidation(formValidations[paymentStepIndex]);
    });

    // عند تغيير نوع الدفع في أي قسط
    $(document).on('change', '.payment-type', function () {
        const idx = $(this).data('index');
        const fv = formValidations[paymentStepIndex];
        if (fv) {
            fv.revalidateField(`payments[${idx}][percentage]`);
            fv.revalidateField(`payments[${idx}][fixed_amount]`);
        }
    });

    // ----------------------------
    // الإضافة الجديدة لإزالة الأخطاء فور اختيار أي قيمة من قوائم الدفعات
    $(document).on('change', '.payment-row select', function () {
        const fieldName = $(this).attr('name');
        formValidations[paymentStepIndex].revalidateField(fieldName);
    });
    // ----------------------------

    /* ------------------------------------------------------------------
   مراقبة أى تغيير يطرأ على حقل النسبة لإجمالى الدفعات (%)
-------------------------------------------------------------------*/
    function recalcPercentTotal() {
        let total = 0;
        $('.payment-row').each(function () {
            const $row = $(this);
            const type = $row.find('.payment-type').val();
            const perc = parseFloat($row.find('input[name$="[percentage]"]').val()) || 0;
            if (type === 'percentage') {
                total += perc;
            }
        });

        // أظهر الإجمالى للمستخدم (اختياري)
        $('#percentage-total-badge').text(total.toFixed(2) + '%');

        // إذا تجاوز 100٪ نمنع الاستمرار
        const $nextBtn = $('.btn-next, .btn-submit');       // كل أزرار المتابعة
        if (total > 100) {
            $nextBtn.attr('disabled', true);
            toastr.error('إجمالي نسب الدفعات تجاوز 100٪. عدِّل القيم.');
        } else {
            $nextBtn.removeAttr('disabled');
        }
    }

    //-- ربط الدالة بكل الأحداث التى تغيّر الإجمالى
    $(document)
        .on('keyup change', '.payment-row input[name$="[percentage]"], .payment-type', recalcPercentTotal)
        .on('click', '#add-payment-btn, .remove-payment-btn', () => setTimeout(recalcPercentTotal, 50));

    // أزرار Next
    btnNextList.forEach((btn, index) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            formValidations[index].validate();
        });
    });

    // أزرار Previous
    btnPrevList.forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            validationStepper.previous();
        });
    });

    // زر Submit
    btnSubmit.addEventListener('click', function (e) {
        e.preventDefault();

        formValidations[lastStepIndex].validate();
    });

    // تهيئة Select2
    if (select2.length) {
        select2.each(function () {
            const $this = $(this).wrap('<div class="position-relative"></div>').parent();
            $(this)
                .select2({
                    placeholder: $(this).data('placeholder') || ' ',
                    dropdownParent: $this,
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                })
                .on('change', function () {
                    const fieldName = $(this).attr('name');
                    const currentIndex = validationStepper._currentIndex;
                    if (formValidations[currentIndex]) {
                        formValidations[currentIndex].revalidateField(fieldName);
                    }
                });
        });
    }

    // أحداث التاريخ
    ['contract_start_date', 'contract_end_date', 'expected_closure_date'].forEach((id) => {
        $(`#${id}`).on('dp.change', () => {
            formValidations[0].revalidateField(id);
        });
    });

    window.paymentStepFv = formValidations[paymentStepIndex];
    window.addDynamicPaymentValidation = addDynamicPaymentValidation;
})();
