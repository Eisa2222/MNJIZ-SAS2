"use strict";

(function () {
    /* ============= المتغيرات الأساسية ============= */
    const wizardValidation = document.querySelector("#wizard-validation");
    if (!wizardValidation) return;

    const wizardValidationForm = wizardValidation.querySelector("#attendance-edit-form");
    const steps = wizardValidationForm.querySelectorAll(".content");
    const btnSubmit = wizardValidationForm.querySelector(".btn-submit");
    const btnPrevList = wizardValidationForm.querySelectorAll(".btn-prev");
    const validationStepper = new Stepper(wizardValidation, { linear: true });

    const select2 = $(".select2");

    /* ============= دوال مساعدة ============= */
    // تأكد من أن وقت الخروج بعد الدخول
    function validateTimes() {
        const status = document.querySelector('#day_status')?.value;
        const inVal = document.querySelector('#check_in_time')?.value;
        const outVal = document.querySelector('#check_out_time')?.value;

        if (status === 'present' && inVal && outVal) {
            const inTime = new Date('2000-01-01T' + inVal + ':00');
            const outTime = new Date('2000-01-01T' + outVal + ':00');
            if (outTime < inTime) {
                toastr.error('وقت الخروج يجب أن يكون بعد وقت الدخول');
                return false;
            }
        }

        return true;
    }

    // تفريغ حقول الوقت
    function clearTimeFields() {
        const inInp = document.querySelector('#check_in_time');
        const outInp = document.querySelector('#check_out_time');

        if (inInp && outInp) {
            inInp.value = '';
            outInp.value = '';
        }
    }

    // تفعيل/تعطيل حقول الوقت حسب حالة الحضور
    function toggleTimeFields() {
        const status = document.querySelector('#day_status')?.value;
        const inInp = document.querySelector('#check_in_time');
        const outInp = document.querySelector('#check_out_time');
        const inContainer = document.querySelector('#check_in_container');
        const outContainer = document.querySelector('#check_out_container');

        if (!inInp || !outInp) return;

        if (status === 'present') {
            // تفعيل الحقول في حالة الحضور
            inInp.disabled = false;
            outInp.disabled = false;

            // إضافة السمة required لكن بدون إعادة التحقق الفوري
            inInp.setAttribute('required', 'required');
            outInp.setAttribute('required', 'required');

            // إضافة فئة required للحاويات للتنسيق
            if (inContainer) inContainer.classList.add('required');
            if (outContainer) outContainer.classList.add('required');
        } else {
            // تعطيل الحقول في حالات أخرى وتفريغها
            inInp.disabled = true;
            outInp.disabled = true;

            // تفريغ القيم عند التبديل من حضور إلى غياب أو إجازة
            clearTimeFields();

            // إزالة السمة required - هذا مهم للتحقق الصحيح عند الإرسال
            inInp.removeAttribute('required');
            outInp.removeAttribute('required');

            // إزالة فئة required من الحاويات
            if (inContainer) inContainer.classList.remove('required');
            if (outContainer) outContainer.classList.remove('required');

            // إعادة تقييم تحقق الحقل - هذا ضروري لإزالة أي رسائل خطأ
            const current = validationStepper._currentIndex;
            if (formValidations[current]) {
                formValidations[current].updateFieldStatus('check_in_time', 'Valid');
                formValidations[current].updateFieldStatus('check_out_time', 'Valid');
            }
        }
    }

    // تحقّق إضافي قبل الإرسال
    function validateTimeInputs() {
        const status = document.querySelector('#day_status')?.value;
        const inVal = document.querySelector('#check_in_time')?.value;
        const outVal = document.querySelector('#check_out_time')?.value;

        // فقط تحقق من قيم الوقت إذا كانت الحالة "حضور"
        if (status === 'present') {
            if (!inVal) {
                toastr.warning('يجب تحديد وقت الدخول في حالة الحضور');
                return false;
            }

            if (!outVal) {
                toastr.warning('يجب تحديد وقت الخروج في حالة الحضور');
                return false;
            }

            return validateTimes();
        }

        // إذا كانت الحالة غير "حضور"، نعتبرها صحيحة دائماً
        return true;
    }

    // تم الاستغناء عن هذه الدالة وتنفيذ وظيفتها مباشرة في أحداث المستمعين

    /* ============= إعداد FormValidation لكل خطوة ============= */
    const formValidations = [];

    steps.forEach((step, index) => {
        const fv = FormValidation.formValidation(step, {
            fields: {
                ...(index === 0 && {
                    day_status: {
                        validators: { notEmpty: { message: 'الرجاء اختيار حالة الحضور' } }
                    },
                    // إبقاء التحقق من حقول الوقت ولكن فقط في حالة الحضور
                    // وتعديل الشروط بطريقة محكمة
                    check_in_time: {
                        validators: {
                            notEmpty: {
                                // الشرط المحسن - نتأكد من أنها لا تكون مطلوبة إلا في حالة الحضور
                                enabled: function () {
                                    const status = document.querySelector('#day_status')?.value;
                                    return status === 'present';
                                },
                                message: 'وقت الدخول مطلوب'
                            }
                        }
                    },
                    check_out_time: {
                        validators: {
                            notEmpty: {
                                // الشرط المحسن - نتأكد من أنها لا تكون مطلوبة إلا في حالة الحضور
                                enabled: function () {
                                    const status = document.querySelector('#day_status')?.value;
                                    return status === 'present';
                                },
                                message: 'وقت الخروج مطلوب'
                            }
                        }
                    },
                    edit_reason: {
                        validators: {
                            notEmpty: { message: 'الرجاء إدخال سبب التحديث' },
                            stringLength: { max: 255, message: 'يجب ألا يتجاوز سبب التحديث 255 حرفًا' }
                        }
                    }
                })
            },
            plugins: {
                // تغيير سلوك الـ Trigger لمنع التحقق الفوري عند التغيير
                trigger: new FormValidation.plugins.Trigger({
                    event: {
                        // تطبيق التحقق على حقل السبب فقط عند الكتابة
                        edit_reason: 'input',
                        // لا نريد أن تكون أحداث التحقق لحقول الوقت آلية
                        // day_status: 'change',
                        // check_in_time: 'change',
                        // check_out_time: 'change'
                    },
                    // تفعيل خاصية عدم الفحص حتى يتم النقر على زر الإرسال
                    global: false
                }),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: '.col-md-6, .col-md-12, .col-md-4',
                    eleValidClass: ''
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            }
        }).on('core.form.valid', () => {
            // استخدام validateTimeInputs فقط، ولا داعي للتحقق من modifyFormBeforeSubmit هنا،
            // حيث تم التعامل معه في مستمع الـ submit
            if (validateTimeInputs()) {
                (index === steps.length - 1) ? wizardValidationForm.submit() : validationStepper.next();
            }
        });

        formValidations.push(fv);

        /* -------- أحداث خاصة بالخطوة الأولى -------- */
        if (index === 0) {
            const statusSel = document.querySelector('#day_status');
            const inInp = document.querySelector('#check_in_time');
            const outInp = document.querySelector('#check_out_time');

            // الحالة المبدئية
            setTimeout(() => {
                toggleTimeFields();
                // نزيل التحقق الآلي عند بدء التحميل
            }, 200);

            // تغيّر وقت الدخول
            inInp?.addEventListener('change', () => {
                validateTimes();
                // لا نقوم بإعادة التحقق هنا للحفاظ على سلاسة التجربة
            });

            // تغيّر وقت الخروج
            outInp?.addEventListener('change', () => {
                validateTimes();
                // لا نقوم بإعادة التحقق هنا للحفاظ على سلاسة التجربة
            });

            // تغيّر حالة الحضور
            statusSel?.addEventListener('change', () => {
                toggleTimeFields();

                // إعادة تهيئة كاملة لنموذج التحقق عند تغيير الحالة
                // هذا ضروري لإزالة رسائل الخطأ السابقة
                setTimeout(() => {
                    const current = validationStepper._currentIndex;
                    if (formValidations[current]) {
                        formValidations[current].resetForm(true);

                        // تحديث حالة حقول الوقت إلى صالحة للحالات غير "حضور"
                        if (statusSel.value !== 'present') {
                            formValidations[current].updateFieldStatus('check_in_time', 'Valid');
                            formValidations[current].updateFieldStatus('check_out_time', 'Valid');
                        }
                    }
                }, 50);
            });

            // Tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
    });

    /* ============= أزرار التالى/السابق ============= */
    btnSubmit?.addEventListener('click', e => {
        e.preventDefault();
        formValidations[validationStepper._currentIndex].validate();
    });
    btnPrevList.forEach(btn => btn.addEventListener('click', e => { e.preventDefault(); validationStepper.previous(); }));

    /* ============= تهيئة Select2 ============= */
    if (select2.length) {
        select2.each(function () {
            const $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            $this.select2({ placeholder: $this.data('placeholder') || 'اختر خيارًا', dropdownParent: $this.parent(), language: 'ar' })
                .on('change', function () {
                    const field = $this.attr('name');
                    const current = validationStepper._currentIndex;

                    if (field === 'day_status') {
                        toggleTimeFields();

                        // إعادة تهيئة كاملة لنموذج التحقق
                        setTimeout(() => {
                            if (formValidations[current]) {
                                formValidations[current].resetForm(true);

                                // تحديث حالة حقول الوقت إلى صالحة للحالات غير "حضور"
                                if ($this.val() !== 'present') {
                                    formValidations[current].updateFieldStatus('check_in_time', 'Valid');
                                    formValidations[current].updateFieldStatus('check_out_time', 'Valid');
                                }
                            }
                        }, 50);
                    }
                });
        });
    }

    /* ============= قبل إرسال النموذج ============= */
    wizardValidationForm.addEventListener('submit', function (e) {
        const status = document.querySelector('#day_status')?.value;

        // تأكيد تفريغ الحقول عند التقديم إذا كانت الحالة ليست حضور
        if (status !== 'present') {
            clearTimeFields();

            // تحديث حالة حقول الوقت للتأكد من صحتها
            const current = validationStepper._currentIndex;
            if (formValidations[current]) {
                formValidations[current].updateFieldStatus('check_in_time', 'Valid');
                formValidations[current].updateFieldStatus('check_out_time', 'Valid');
            }
        } else {
            // حالة الحضور - تأكد من صحة القيم
            const inVal = document.querySelector('#check_in_time')?.value;
            const outVal = document.querySelector('#check_out_time')?.value;

            if (!inVal || !outVal || !validateTimes()) {
                e.preventDefault();
                return false;
            }
        }
    });

    /* ============= تشغيل ابتدائي ============= */
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            toggleTimeFields();
        }, 300);
    });
})();
