/**
 * Leave Request Form Wizard
 */

"use strict";

(function () {
    const select2 = $(".select2");

    // الحصول على العنصر الرئيسي للـ Wizard
    const wizardValidation = document.querySelector("#wizard-validation");

    if (wizardValidation) {
        // النموذج الرئيسي
        const wizardValidationForm = wizardValidation.querySelector(
            "#violations-penalties-form",
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

        // دالة مساعدة للتمرير إلى عنصر معين
        function scrollToElement(element, offset = 150) {
            if (!element) return;

            // الحصول على موضع العنصر
            const rect = element.getBoundingClientRect();
            const isInViewport = (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );

            // إذا كان العنصر خارج نطاق الرؤية، نقوم بالتمرير إليه
            if (!isInViewport) {
                const targetPosition = window.pageYOffset + rect.top - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        }

        // دالة للعثور على عنصر DOM الفعلي لحقل Select2
        function getSelect2Container(selectElement) {
            if (!selectElement) return null;
            const select2Id = $(selectElement).data('select2-id');
            if (!select2Id) return null;

            return document.querySelector(`.select2-container[data-select2-id="${select2Id}"]`) ||
                document.querySelector(`.select2-container--${select2Id}`);
        }

        // إنشاء التحقق لكل خطوة باستخدام FormValidation
        steps.forEach((step, index) => {
            const fv = FormValidation.formValidation(step, {
                fields: {
                    // في الخطوة الأولى (أو الوحيدة) نتحقق من الحقول التالية:
                    ...(index === 0 && {
                        user_id: {
                            validators: {
                                notEmpty: {
                                    message: "الموظف مطلوب",
                                },
                            },
                        },
                        violation_date: {
                            validators: {
                                notEmpty: {
                                    message: "تاريخ الإنتهاك مطلوب",
                                },
                            },
                        },
                        settings_violation_id: {
                            validators: {
                                notEmpty: {
                                    message: "نوع الإنتهاك مطلوب",
                                },
                            },
                        },
                    }),
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".col-md-6, .col-md-12",
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
            }).on("core.field.invalid", function (e) {
                // هذا الحدث يطلق عندما يكون الحقل غير صالح
                const field = e;

                // إذا كان الحقل هو select2
                if ($(field).hasClass('select2-hidden-accessible')) {
                    setTimeout(function () {
                        // الحصول على عنصر container الخاص بـ Select2
                        const select2Container = getSelect2Container(field);

                        // التمرير إلى عنصر container
                        if (select2Container) {
                            scrollToElement(select2Container);
                        }

                        // فتح القائمة المنسدلة
                        $(field).select2('focus');
                        $(field).select2('open');
                    }, 150);
                } else {
                    // للحقول العادية، نمرر إليها بعد تأخير صغير
                    setTimeout(function () {
                        scrollToElement(field);
                    }, 150);
                }
            });

            formValidations.push(fv);
        });

        // ربط حدث الضغط على زر "حفظ" بعملية التحقق للخطوة الحالية
        if (btnSubmit) {
            btnSubmit.addEventListener("click", function (e) {
                e.preventDefault();
                const currentIndex = validationStepper._currentIndex;
                formValidations[currentIndex].validate().then(function (status) {
                    if (status === 'Invalid') {
                        // عند وجود أخطاء، نبحث عن أول حقل غير صالح
                        const form = steps[currentIndex];
                        const invalidField = form.querySelector('.is-invalid');

                        if (invalidField) {
                            // إذا كان الحقل هو select2
                            if ($(invalidField).hasClass('select2-hidden-accessible')) {
                                // تأخير صغير للسماح للصفحة بالتحديث
                                setTimeout(function () {
                                    // الحصول على عنصر container الخاص بـ Select2
                                    const select2Container = getSelect2Container(invalidField);

                                    // التمرير إلى عنصر container
                                    if (select2Container) {
                                        scrollToElement(select2Container);
                                    }

                                    // فتح القائمة المنسدلة
                                    $(invalidField).select2('focus');
                                    $(invalidField).select2('open');
                                }, 150);
                            } else {
                                // للحقول العادية
                                setTimeout(function () {
                                    invalidField.focus();
                                    scrollToElement(invalidField);
                                }, 150);
                            }
                        }
                    }
                });
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

            // إضافة استماع لحدث التغيير في عناصر select2 لتشغيل إعادة التحقق
            $('.select2').on('change', function () {
                // الحصول على اسم الحقل
                const fieldName = $(this).attr('name');
                // الحصول على الخطوة الحالية
                const currentIndex = validationStepper._currentIndex;
                // إعادة التحقق من الحقل المحدد فقط
                if (fieldName) {
                    formValidations[currentIndex].revalidateField(fieldName);
                }
            });
        }
    }
})();
