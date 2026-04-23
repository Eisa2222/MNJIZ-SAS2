'use strict';

(function () {
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardValidationForm = wizardValidation.querySelector('#form');
    const steps = wizardValidationForm.querySelectorAll('.content');
    const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    let formValidation;
    let counter = 0; // ✅ غيّر من 1 إلى 0

    // إنشاء FormValidation واحد فقط
    function initializeValidation() {
        const validators = {
            "name[]": {
                validators: {
                    notEmpty: { message: 'الاسم مطلوب' },
                    stringLength: {
                        min: 2,
                        max: 255,
                        message: 'الاسم يجب أن يكون بين 2 و 255 حرف'
                    }
                }
            },
            "file[]": {
                validators: {
                    notEmpty: { message: 'المرفق مطلوب' },
                    file: {
                        extension: 'pdf',
                        type: 'application/pdf',
                        maxSize: 10485760,
                        message: 'يجب أن يكون الملف من نوع PDF وحجمه أقل من 10 ميغابايت'
                    }
                }
            }
        };

        formValidation = FormValidation.formValidation(wizardValidationForm, {
            fields: validators,
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: '[class*="col-md-"]',
                    eleValidClass: '',
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            }
        }).on("core.form.valid", function () {
            btnSubmit.setAttribute('disabled', 'disabled');
            btnSubmit.dataset.oldText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

            wizardValidationForm.submit();
        });
    }

    // تهيئة التحقق
    initializeValidation();

    // زر الحفظ
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            formValidation.validate();
        });
    }

    // إضافة حقول جديدة
    $(document).on('click', '#addNew', function () {
        counter++; // الآن سيكون 1, 2, 3...

        const newFields = `
           <div class="row g-3 mt-3 additional-fields" id="fields-${counter}">
               <div class="col-md-6">
                   <label class="form-label" for="name_${counter}">
                       الاسم
                       <span class="text-danger">*</span>
                   </label>
                   <input type="text" name="name[]" id="name_${counter}" class="form-control">
               </div>

               <div class="col-md-5">
                   <label class="form-label" for="file_${counter}">
                       المرفق
                       <span class="text-danger">*</span>
                   </label>
                   <input type="file" name="file[]" id="file_${counter}" class="form-control" accept=".pdf">
                   <small class="text-muted">مسموح PDF فقط الحجم الأقصى 10 ميغابايت</small>
               </div>

               <div class="col-md-1 d-flex align-items-center justify-content-center">
                   <button type="button" class="btn btn-sm btn-danger remove-fields" data-target="fields-${counter}">
                       <i class="ti ti-trash"></i>
                   </button>
               </div>
           </div>
       `;

        // إضافة الحقول
        $('#addNew').closest('.d-flex').before(newFields);

        // انتظار حتى يتم إدراج العناصر في DOM
        setTimeout(() => {
            // إضافة التحقق للحقول الجديدة
            formValidation.addField(`name_${counter}`, {
                selector: `#name_${counter}`,
                validators: {
                    notEmpty: { message: 'الاسم مطلوب' },
                    stringLength: {
                        min: 2,
                        max: 255,
                        message: 'الاسم يجب أن يكون بين 2 و 255 حرف'
                    }
                }
            });

            formValidation.addField(`file_${counter}`, {
                selector: `#file_${counter}`,
                validators: {
                    notEmpty: { message: 'المرفق مطلوب' },
                    file: {
                        extension: 'pdf',
                        type: 'application/pdf',
                        maxSize: 10485760,
                        message: 'يجب أن يكون الملف من نوع PDF وحجمه أقل من 10 ميغابايت'
                    }
                }
            });

            console.log(`Added validation for name_${counter} and file_${counter}`);
        }, 100);
    });

    // حذف الحقول
    $(document).on('click', '.remove-fields', function () {
        const targetId = $(this).data('target');

        // إزالة التحقق
        $(`#${targetId} input[type="text"], #${targetId} input[type="file"]`).each(function () {
            const fieldId = $(this).attr('id');
            if (fieldId) {
                formValidation.removeField(fieldId);
            }
        });

        // حذف العنصر
        $('#' + targetId).remove();
    });
})();
