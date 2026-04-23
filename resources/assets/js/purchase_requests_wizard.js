/**
 * Leave Request Form Wizard (خطوتين فقط)
 */

"use strict";

(function () {
    const select2 = $(".select2");

    // الحصول على العنصر الرئيسي للـ Wizard
    const wizardValidation = document.querySelector("#wizard-validation");

    if (wizardValidation) {
        // النموذج الرئيسي
        const wizardValidationForm = wizardValidation.querySelector(
            "#purchase-requests-form",
        );
        // جميع خطوات النموذج (div.content)
        const steps = wizardValidationForm.querySelectorAll(".content");
        // زر الإرسال (الذي سيعمل كزر التحقق في آخر خطوة)
        const btnSubmit = wizardValidationForm.querySelector(".btn-submit");
        // أزرار الرجوع
        const btnPrevList = wizardValidationForm.querySelectorAll(".btn-prev");

        // تهيئة bs-stepper
        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
        });

        // مصفوفة لتخزين كائنات التحقق لكل خطوة
        const formValidations = [];

        // إنشاء التحقق لكل خطوة باستخدام FormValidation
        steps.forEach((step, index) => {
            const fv = FormValidation.formValidation(step, {
                fields: {
                    // في الخطوة الأولى (أو الوحيدة) نتحقق من الحقول التالية:
                    ...(index === 0 && {
                        item_name: {
                            validators: {
                                notEmpty: {
                                    message: "اسم الطلب مطلوب",
                                },
                            },
                        },
                        purchase_category_id: {
                            validators: {
                                notEmpty: {
                                    message: "تصنيف الطلب مطلوب ",
                                },
                            },
                        },
                    }),
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".col-md-4, .col-md-12",
                        eleValidClass: "",
                    }),
                    autoFocus: new FormValidation.plugins.AutoFocus(),
                    submitButton: new FormValidation.plugins.SubmitButton(),
                },
            }).on("core.form.valid", function () {
                // عند نجاح التحقق في الخطوة الحالية:
                // إذا كانت هذه آخر خطوة (أو النموذج يحتوي على خطوة واحدة)
                if (index === steps.length - 1) {
                    btnSubmit.setAttribute('disabled', 'disabled');
                    btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                    wizardValidationForm.submit();
                } else {
                    // الانتقال للخطوة التالية إذا كانت هناك خطوات أخرى
                    validationStepper.next();
                }
            });

            formValidations.push(fv);
        });

        // ربط حدث الضغط على زر "حفظ" بعملية التحقق للخطوة الحالية
        if (btnSubmit) {
            btnSubmit.addEventListener("click", function (e) {
                e.preventDefault();
                const currentIndex = validationStepper._currentIndex;
                formValidations[currentIndex].validate();
            });
        }

        // ربط حدث أزرار "السابق" للانتقال للخلف
        btnPrevList.forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                validationStepper.previous();
            });
        });

        // تهيئة الـ Select2
        if (select2.length) {
            $('.select2').select2({
                placeholder: function () {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });
        }
    }
})();
