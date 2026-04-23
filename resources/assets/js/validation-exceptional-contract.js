'use strict';

(function () {
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardValidationForm = wizardValidation.querySelector('#form');
    const steps = wizardValidationForm.querySelectorAll('.content');
    const btnNextList = wizardValidationForm.querySelectorAll('.btn-next');
    const btnPrevList = wizardValidationForm.querySelectorAll('.btn-prev');
    const btnSubmit = wizardValidationForm.querySelector('.btn-submit');
    const isEditMode = wizardValidation.dataset.mode === 'edit';

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    const formValidations = [];

    steps.forEach((step, index) => {
        // إعداد الفاليديشن لكل خطوة (مثال للـ step 0)
        const validators = index === 0 ? {
            contract_name: {
                validators: {
                    notEmpty: { message: 'اسم العقد الإستثنائي مطلوب.' },
                    stringLength: {
                        min: 3, max: 150,
                        message: 'يجب أن يكون اسم العقد الإستثنائي بين 3 و 150 حرف.'
                    }
                }
            },
            employee_id: {
                validators: { notEmpty: { message: 'المكلف مطلوب.' } }
            },
            customer_id: {
                validators: { notEmpty: { message: 'العميل مطلوب.' } }
            }
        } : {};

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
        }).on('core.form.valid', function () {
            // إذا لم تكن آخر خطوة، انتقل للخطوة التالية
            if (index < steps.length - 1) {
                validationStepper.next();
                return;
            }

            // هنا نحن في آخر خطوة: عرض رسالة التأكيد
            if (isEditMode) {
                // في صفحة التعديل عرض التأكيد
                Swal.fire({
                    title: 'تنبيه',
                    text: 'سيتم إعادة طلب الاعتمادات من جديد. هل تريد المتابعة؟',
                    icon: 'warning',
                    showCancelButton: true,
                    showConfirmButton: true,
                    showDenyButton: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'btn btn-success custom-confirm',
                        cancelButton: 'btn btn-danger custom-cancel'
                    },
                    confirmButtonText: 'تأكيد',
                    cancelButtonText: 'إلغاء',
                }).then((result) => {
                    if (result.isConfirmed) {
                        btnSubmit.setAttribute('disabled', 'disabled');
                        btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                        btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                        wizardValidationForm.submit();
                    }
                });
            } else {
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                // صفحة الإضافة ترسل فوراً بدون تأكيد
                wizardValidationForm.submit();
            }
        });

        formValidations.push(fv);
    });

    // أزرار التالي
    btnNextList.forEach((btn, idx) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            formValidations[idx].validate();
        });
    });

    // أزرار السابق
    btnPrevList.forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            validationStepper.previous();
        });
    });

    // زر الإرسال (في حال أردت معالجة يدوية)
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            // يشغل تحقق الخطوة الأخيرة
            formValidations[steps.length - 1].validate();
        });
    }

    // تهيئة Select2 كما في السابق...
    const select2 = $('.select2');
    if (select2.length) {
        select2.each(function () {
            const $this = $(this);
            $this.wrap('<div class="position-relative"></div>')
                .select2({
                    placeholder: $this.data('placeholder') || ' ',
                    dropdownParent: $this.parent(),
                    language: 'ar'
                })
                .on('change', function () {
                    const field = $this.attr('name');
                    const idx = validationStepper._currentIndex;
                    formValidations[idx].revalidateField(field);
                });
        });
    }
})();
