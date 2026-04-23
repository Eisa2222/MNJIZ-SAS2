/**
 * Form Wizard Validation for Power of Attorney
 */

'use strict';

(function () {
    const select2 = $('.select2');

    // Wizard Validation
    const wizardValidation = document.querySelector('#wizard-validation');
    if (wizardValidation) {
        const wizardValidationForm = wizardValidation.querySelector('#form');
        const steps = wizardValidationForm.querySelectorAll('.content');
        const btnNextList = wizardValidationForm.querySelectorAll('.btn-next');
        const btnPrevList = wizardValidationForm.querySelectorAll('.btn-prev');
        const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

        const validationStepper = new Stepper(wizardValidation, {
            linear: true,
            animation: true
        });

        // Form Validation Instances
        const formValidations = [];

        // Initialize validation for each step
        steps.forEach((step, index) => {
            let validators = {};

            if (index === 0) { // Step 1: Basic Info
                validators = {
                    expected_start_date: {
                        validators: {
                            notEmpty: {
                                message: 'تاريخ البداية المتوقع  مطلوب.'
                            }
                        }
                    },
                    expected_end_date: {
                        validators: {
                            notEmpty: {
                                message: 'تاريخ النهاية المتوقع  مطلوب.'
                            }
                        }
                    },
                    customer_id: {
                        validators: {
                            notEmpty: {
                                message: 'العميل مطلوب.'
                            },
                            integer: {
                                message: 'يجب أن يكون معرف العميل عددًا صحيحًا.'
                            }
                        }
                    },
                    relationship_manager_id: {
                        validators: {
                            notEmpty: {
                                message: 'مسؤول العلاقات مطلوب.'
                            },
                            integer: {
                                message: 'يجب أن يكون معرف المسؤول عددًا صحيحًا.'
                            }
                        }
                    },
                    start_date: {
                        validators: {
                            notEmpty: {
                                message: 'تاريخ بداية العرض مطلوب.'
                            }
                        }
                    },

                };
            }

            if (index === 1) { // Step 2: Additional Info
                // validators = {
                //     status: {
                //         validators: {
                //             notEmpty: {
                //                 message: 'حالة الوكالة مطلوبة.'
                //             },
                //             choice: {
                //                 min: 1,
                //                 max: 1,
                //                 message: 'يرجى اختيار حالة واحدة فقط.',
                //                 choices: ['active', 'expired', 'revoked']
                //             }
                //         }
                //     },
                //     file_attachment: {
                //         validators: {
                //             file: {
                //                 extension: 'jpg,jpeg,png,pdf,doc,docx',
                //                 type: 'image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                //                 maxSize: 2048 * 1024, // 2 MB
                //                 message: 'يجب أن يكون الملف من نوع JPG، JPEG، PNG، PDF، DOC، DOCX وحجمه لا يتجاوز 2MB.'
                //             }
                //         }
                //     },
                //     notes: {
                //         validators: {
                //             stringLength: {
                //                 max: 1000,
                //                 message: 'يجب ألا تتجاوز الملاحظات 1000 حرف.'
                //             }
                //         }
                //     }
                // };
            }

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
                if (index < steps.length - 1) {
                    validationStepper.next();
                } else {
                    btnSubmit.setAttribute('disabled', 'disabled');
                    btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                    btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                    // إرسال النموذج إذا كان في آخر خطوة
                    wizardValidationForm.submit();
                }
            });

            formValidations.push(fv);
        });

        // Next buttons
        btnNextList.forEach((btn, index) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                formValidations[index].validate();
            });
        });

        // Previous buttons
        btnPrevList.forEach((btn, index) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                validationStepper.previous();
            });
        });

        // Initialize Select2
        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this
                    .select2({
                        placeholder: $this.data('placeholder') || 'اختر خيارًا',
                        dropdownParent: $this.parent(),
                        language: 'ar',
                        allowClear: true,
                    })
                    .on('change', function () {
                        const fieldName = $this.attr('name');
                        const currentIndex = validationStepper._currentIndex;
                        formValidations[currentIndex].revalidateField(fieldName);
                    });
            });
        }

        // Event listener for start_date field
        $('#start_date').on('dp.change', function (e) {
            formValidations[0].revalidateField('start_date');
        });


        // Submit button handler (optional if you want to handle via AJAX)
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            // قم بتفعيل التحقق من النموذج بأكمله أو خطوة معينة إذا لزم الأمر
            formValidations[1].validate(); // Assuming step 2 is the last step
        });
    }



})();

// عند تغيير مدير المشروع، نقوم بمنع هذا المستخدم من الظهور في فريق المشروع
var $managerSelect = $('#manager_user_id');
var $teamSelect = $('#team_members');

function updateTeamList() {
    var managerId = $managerSelect.val();
    var selectedTeam = $teamSelect.val() || [];

    // تمكين جميع الخيارات أولاً
    $teamSelect.find('option').prop('disabled', false);

    if (managerId) {
        // إذا كان المدير المختار ضمن الفريق، إزالته من القيم المختارة
        $teamSelect.find('option').each(function () {
            var teamUserId = $(this).data('user_id');
            if (teamUserId == managerId) {
                // إذا كان المدير مختار في الفريق
                if (selectedTeam.includes($(this).val())) {
                    // إزالة المدير من القيم المختارة
                    selectedTeam = selectedTeam.filter((v) => v != $(this).val());
                }
                // تعطيل خيار المدير
                $(this).prop('disabled', true);
            }
        });
    }

    // إعادة تعيين القيم المختارة بعد التعديل
    $teamSelect.val(selectedTeam).trigger('change.select2');
}

$managerSelect.on('change', function () {
    updateTeamList();
});

// استدعاء التحديث عند تحميل الصفحة إذا لزم الأمر
updateTeamList();

// منع إدخال قيم سالبة أو غير رقمية في المطالبة المالية
$('#financial_claim').on('input', function () {
    let value = parseFloat(this.value);
    if (isNaN(value) || value < 0) {
        this.value = '';
    }
});
