/**
 * Leave Type  Wizard
 */
"use strict";

(function () {
    /*
    |--------------------------------------------------------------------------
    | Variable Declarations
    |--------------------------------------------------------------------------
    | تعريف جميع المتغيرات المستخدمة في النموذج.
    */
    const $select2 = $(".select2");
    const wizardEl = document.querySelector("#wizard-validation");
    if (!wizardEl) return;

    const form = wizardEl.querySelector("#leave-type-form");
    const steps = form.querySelectorAll(".content");
    const btnNextList = form.querySelectorAll(".btn-next");
    const btnPrevList = form.querySelectorAll(".btn-prev");
    const btnSubmit = form.querySelector(".btn-submit");

    /*
    |--------------------------------------------------------------------------
    | Global Leave Elements
    |--------------------------------------------------------------------------
    | العناصر الخاصة بالإجازة العامة وحقول التواريخ.
    */
    const isGlobalCB = document.getElementById("is_global");
    const globalLeaveInfo = document.getElementById("globalLeaveInfo");
    const dateFields = document.getElementById("dateFields");
    const manualDays = document.getElementById("manualDays");
    const startDateEl = document.getElementById("start_date");
    const endDateEl = document.getElementById("end_date");
    const daysCalcEl = document.getElementById("daysCalculated");
    const daysInput = document.getElementById("days");

    /*
    |--------------------------------------------------------------------------
    | Attachment Elements
    |--------------------------------------------------------------------------
    | العناصر الخاصة بالمرفقات المطلوبة للإجازة.
    */
    const attachmentsCB = document.getElementById("has_attachments");
    const attachmentDescRow = document.getElementById("attachmentDescRow");
    const attachmentDescInput = document.getElementById("attachment_description");

    /*
    |--------------------------------------------------------------------------
    | Stepper Initialization
    |--------------------------------------------------------------------------
    | تهيئة مكون bs-stepper للتنقل بين المراحل.
    */
    const validationStepper = new Stepper(wizardEl, {
        linear: true,
        animation: true
    });

    // FormValidation instances for each step
    const formValidations = [];

    /*
    |--------------------------------------------------------------------------
    | Pre-Global State
    |--------------------------------------------------------------------------
    | حفظ الحالة السابقة لـ isGlobal
    */
    let prevGlobal = isGlobalCB.checked;

    /*
    |--------------------------------------------------------------------------
    | Days Calculation Function
    |--------------------------------------------------------------------------
    | حساب عدد الأيام بين التاريخين للإجازات العامة.
    */
    function updateCalculatedDays() {
        if (!isGlobalCB.checked) {
            daysCalcEl.value = "";
            return;
        }
        const sd = startDateEl.value, ed = endDateEl.value;
        if (sd && ed && ed >= sd) {
            const diff = (new Date(ed) - new Date(sd)) / (1000 * 60 * 60 * 24);
            const totalDays = diff + 1;
            daysCalcEl.value = totalDays;
            // نقل القيمة المحسوبة إلى حقل days
            daysInput.value = totalDays;
        } else {
            daysCalcEl.value = "";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Global Leave Toggle Function
    |--------------------------------------------------------------------------
    | تبديل ظهور الحقول حسب نوع الإجازة (عامة أو عادية).
    | - الإجازة العامة: تحتاج تحديد فترة زمنية (من تاريخ - إلى تاريخ)
    | - الإجازة العادية: تحتاج فقط عدد الأيام، مع مسح القيمة عند الانتقال من عامة إلى عادية.
    */
    function toggleGlobalLeaveFields() {
        const isGlobal = isGlobalCB.checked;

        // —————— تعطيل/إلغاء تعطيل حقل “تنطبق على” ——————
        // بافتراض أنك تستخدم jQuery و Select2
        const $genderSelect = $('#gender_applicability');

        if (isGlobal) {
            // حدّد القيمة على كلا الجنسين ثم عطّل الحقل
            $genderSelect.val('both').trigger('change');
            $genderSelect.prop('disabled', true);
        } else {
            // فعّل الحقل للسماح بالاختيار من جديد
            $genderSelect.prop('disabled', false);
        }
        // ——————————————————————————————————————————

        // باقي الدالة كما هي:
        dateFields.style.display = isGlobal ? "flex" : "none";
        manualDays.style.display = isGlobal ? "none" : "flex";

        if (isGlobal) {
            daysInput.removeAttribute("required");
            startDateEl.setAttribute("required", "required");
            endDateEl.setAttribute("required", "required");
        } else {
            daysInput.setAttribute("required", "required");
            startDateEl.removeAttribute("required");
            endDateEl.removeAttribute("required");
            if (prevGlobal) { /* مسح القيم المحسوبة… */ }
        }

        prevGlobal = isGlobal;
        updateCalculatedDays();

        if (validationStepper._currentIndex === 1) {
            formValidations[1].revalidateField("days");
            formValidations[1].revalidateField("start_date");
            formValidations[1].revalidateField("end_date");
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Attachment Toggle Function
    |--------------------------------------------------------------------------
    | تبديل ظهور حقل وصف المرفقات عند تفعيل خيار المرفقات.
    */
    function toggleAttachmentFields() {
        if (attachmentsCB.checked) {
            attachmentDescRow.style.display = "block";
            attachmentDescInput.setAttribute("required", "required");
        } else {
            attachmentDescRow.style.display = "none";
            attachmentDescInput.removeAttribute("required");
            attachmentDescInput.value = "";
        }
        // إعادة التحقق فقط في الخطوة الثالثة
        if (validationStepper._currentIndex === 2) {
            formValidations[2].revalidateField("attachment_description");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Form Validation Setup
    |--------------------------------------------------------------------------
    | إعداد قواعد التحقق لكل مرحلة من مراحل النموذج.
    */
    steps.forEach((step, index) => {
        let validators = {};

        // المرحلة الأولى: البيانات الأساسية
        if (index === 0) {
            validators = {
                name: {
                    validators: {
                        notEmpty: { message: "اسم نوع الإجازة مطلوب" },
                        stringLength: { max: 255, message: "لا يمكن أن يزيد عن 255 حرف" }
                    }
                },
                gender_applicability: {
                    validators: { notEmpty: { message: "حدد تنطبق على من" } }
                },
                leave_unit_type: {
                    validators: { notEmpty: { message: "يجب تحديد وحدة احتساب الإجازة" } }
                },
                advance_notice_days: {
                    validators: {
                        integer: { message: "يجب أن يكون عدد صحيح" },
                        greaterThan: { min: 0, inclusive: true, message: "يجب أن تكون ≥ 0" }
                    }
                }
            };
        }
        // المرحلة الثانية: الإعدادات والشروط
        else if (index === 1) {
            validators = {
                days: {
                    validators: {
                        notEmpty: {
                            message: "حدد عدد الأيام",
                            enabled: function () {
                                // فقط مطلوب للإجازات العادية
                                return !isGlobalCB.checked;
                            }
                        },
                        callback: {
                            message: "حدد عدد الأيام",
                            callback: function (input) {
                                if (isGlobalCB.checked) {
                                    return true; // للإجازة العامة، الحقل غير مطلوب
                                }
                                // للإجازة العادية، يجب أن يكون الحقل له قيمة
                                return input.value !== "" && input.value !== null && input.value !== undefined;
                            }
                        }
                    }
                },
                start_date: {
                    validators: {
                        callback: {
                            message: "حدد تاريخ البداية",
                            callback: () => !isGlobalCB.checked || !!startDateEl.value
                        }
                    }
                },
                end_date: {
                    validators: {
                        callback: {
                            message: "حدد تاريخ النهاية ويجب أن تكون ≥ البداية",
                            callback: () => !isGlobalCB.checked || (!!endDateEl.value && endDateEl.value >= startDateEl.value)
                        }
                    }
                },
                max_requests: {
                    validators: {
                        integer: { message: "يجب أن يكون عدد صحيح" },
                        greaterThan: { min: 0, inclusive: true, message: "يجب أن تكون ≥ 0" }
                    }
                },
                service_years_threshold: {
                    validators: {
                        integer: { message: "يجب أن يكون عدد صحيح" },
                        greaterThan: { min: 0, inclusive: true, message: "يجب أن تكون ≥ 0" }
                    }
                },
                days_after_threshold: {
                    validators: {
                        integer: { message: "يجب أن يكون عدد صحيح" },
                        greaterThan: { min: 0, inclusive: true, message: "يجب أن تكون ≥ 0" }
                    }
                }
            };
        }
        // المرحلة الثالثة: التفاصيل المتقدمة
        else if (index === 2) {
            validators = {
                min_service_years: {
                    validators: {
                        integer: { message: "يجب أن يكون عدد صحيح" },
                        greaterThan: { min: 0, inclusive: true, message: "يجب أن تكون ≥ 0" }
                    }
                },
                attachment_description: {
                    validators: {
                        callback: {
                            message: "حدد نوع المرفقات المطلوبة",
                            callback: input => !attachmentsCB.checked || input.value.trim() !== ""
                        }
                    }
                }
            };
        }

        const fv = FormValidation.formValidation(step, {
            fields: validators,
            plugins: {
                excluded: new FormValidation.plugins.Excluded({ exclude: ['disabled', 'hidden'] }),
                trigger: new FormValidation.plugins.Trigger({ event: { field: 'blur' } }),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: ".col-md-4, .col-md-6, .col-12",
                    eleValidClass: "",
                    eleInvalidClass: "is-invalid",
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            }
        }).on("core.form.valid", function () {
            if (index < steps.length - 1) {
                validationStepper.next();
            } else {
                if (isGlobalCB.checked) {
                    updateCalculatedDays();
                }
                form.submit();
            }
        });

        formValidations.push(fv);
    });

    /*
    |--------------------------------------------------------------------------
    | Navigation Event Handlers
    |--------------------------------------------------------------------------
    | معالجات أحداث أزرار التنقل بين المراحل.
    */
    btnNextList.forEach((btn, idx) =>
        btn.addEventListener("click", e => {
            e.preventDefault();

            // تحقق خاص لحقل الأيام في المرحلة الثانية
            if (idx === 1 && !isGlobalCB.checked) {
                const daysValue = daysInput.value;

                // التحقق من أن الحقل فارغ فعلاً
                if (!daysValue || daysValue === "" || daysValue.trim() === "") {
                    // إظهار رسالة الخطأ يدوياً
                    const daysField = daysInput.closest('.col-md-6');
                    daysInput.classList.add('is-invalid');

                    // إضافة رسالة خطأ إذا لم تكن موجودة
                    let errorDiv = daysField.querySelector('.invalid-feedback');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        daysField.appendChild(errorDiv);
                    }
                    errorDiv.textContent = 'حدد عدد الأيام';

                    // وضع الفوكس على الحقل
                    daysInput.focus();

                    return false; // منع الانتقال للخطوة التالية
                } else {
                    // إزالة رسالة الخطأ إذا كانت القيمة صحيحة
                    daysInput.classList.remove('is-invalid');
                }
            }

            formValidations[idx].validate();
        })
    );
    btnPrevList.forEach(btn =>
        btn.addEventListener("click", e => { e.preventDefault(); validationStepper.previous(); })
    );

    /*
    |--------------------------------------------------------------------------
    | Form Event Listeners
    |--------------------------------------------------------------------------
    | معالجات أحداث حقول النموذج المختلفة.
    */
    isGlobalCB.addEventListener("change", toggleGlobalLeaveFields);
    attachmentsCB.addEventListener("change", toggleAttachmentFields);

    [startDateEl, endDateEl].forEach(el =>
        el.addEventListener("change", () => {
            updateCalculatedDays();
            if (validationStepper._currentIndex === 1) {
                formValidations[1].revalidateField("days");
                formValidations[1].revalidateField(el.id);
            }
        })
    );

    daysInput.addEventListener("input", () => {
        // إزالة رسالة الخطأ عند الكتابة
        if (daysInput.value && daysInput.value.trim() !== "") {
            daysInput.classList.remove('is-invalid');

            // إزالة رسالة الخطأ أيضاً
            const daysField = daysInput.closest('.col-md-6');
            const errorDiv = daysField.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        }

        if (validationStepper._currentIndex === 1) {
            formValidations[1].revalidateField("days");
        }
    });



    attachmentDescInput.addEventListener("input", () => {
        if (validationStepper._currentIndex === 2) {
            formValidations[2].revalidateField("attachment_description");
        }
    });

    btnSubmit.addEventListener("click", e => {
        e.preventDefault();
        formValidations[2].validate();
    });

    /*
    |--------------------------------------------------------------------------
    | Select2 Initialization
    |--------------------------------------------------------------------------
    | تهيئة Select2 لحقول الاختيار مع دعم اللغة العربية.
    */
    if ($select2.length) {
        $select2.each(function () {
            const $el = $(this).select2({
                placeholder: $(this).data("placeholder") || "",
                dropdownParent: $(this).parent(),
                language: "ar"
            });
            $el.on("change", () => {
                const idx = validationStepper._currentIndex;
                const field = this.name;
                if (formValidations[idx]) {
                    formValidations[idx].revalidateField(field);
                }
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Initial Setup
    |--------------------------------------------------------------------------
    | تهيئة الحقول عند تحميل الصفحة.
    */
    toggleGlobalLeaveFields();
    toggleAttachmentFields();

})();
