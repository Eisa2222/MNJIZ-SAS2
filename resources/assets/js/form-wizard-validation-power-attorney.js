/**
 * Form Wizard Validation for Power of Attorney
 */

'use strict';

(function () {
    const select2 = $('.select2');

    // Wizard Validation
    const wizardValidation = document.querySelector('#wizard-validation');
    if (wizardValidation) {
        const wizardValidationForm = wizardValidation.querySelector('#power-form');
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

        // دالة لتحليل التواريخ وتحديد نوع التقويم
        function parseDate(dateStr) {
            // حاول التحليل كميلادي
            let gregorianDate = moment(dateStr, 'YYYY-MM-DD', true);
            if (gregorianDate.isValid()) {
                console.log(`Parsed as Gregorian: ${dateStr}`);
                return { date: gregorianDate, type: 'gregorian' };
            }

            // حاول التحليل كهجري
            let hijriDate = moment(dateStr, 'iYYYY-iMM-iDD', true);
            if (hijriDate.isValid()) {
                console.log(`Parsed as Hijri: ${dateStr}`);
                return { date: hijriDate, type: 'hijri' };
            }

            // إذا لم يكن صالحًا لأي منهما
            console.log(`Invalid date format: ${dateStr}`);
            return { date: null, type: null };
        }

        // Initialize validation for each step
        steps.forEach((step, index) => {
            let validators = {};

            if (index === 0) { // Step 1: Basic Info
                validators = {
                    power_name: {
                        validators: {
                            notEmpty: {
                                message: 'اسم الوكالة مطلوب.'
                            },
                            stringLength: {
                                min: 3,
                                max: 100,
                                message: 'يجب أن يكون اسم الوكالة بين 3 و 100 حرف.'
                            }
                        }
                    },
                    power_number: {
                        validators: {
                            notEmpty: {
                                message: 'رقم الوكالة مطلوب.'
                            },
                            stringLength: {
                                min: 5,
                                max: 50,
                                message: 'يجب أن يكون رقم الوكالة بين 5 و 50 حرف.'
                            },
                            // تحقق فريد عبر AJAX (إذا كنت ترغب في ذلك)
                            // remote: {
                            //     url: '/validate-power-number',
                            //     data: {
                            //         power_number: () => wizardValidationForm.querySelector('[name="power_number"]').value
                            //     },
                            //     message: 'رقم الوكالة مستخدم بالفعل.'
                            // }
                        }
                    },
                    'customers[]': {
                        validators: {
                            notEmpty: {
                                message: 'يجب اختيار عميل واحد على الاقل'
                            },
                            integer: {
                                message: 'يجب أن يكون العميل موجود بالفعل  .'
                            }
                        }
                    },
                    'agents[]': {
                        validators: {
                            notEmpty: {
                                message: 'يجب اختيار وكيل واحد على الاقل'
                            },
                            integer: {
                                message: 'يجب أن يكون  المحامي  موجود بالفعل.'
                            }
                        }
                    },
                    date_issued: {
                        validators: {
                            notEmpty: {
                                message: 'تاريخ الإصدار مطلوب.'
                            },
                            date: {
                                format: ['YYYY-MM-DD', 'iYYYY-iMM-iDD'], // قبول كلا التنسيقين
                                message: 'تاريخ الإصدار غير صالح. يجب أن يكون بتنسيق YYYY-MM-DD (ميلادي) أو iYYYY-iMM-iDD (هجري).'
                            }
                        }
                    },
                    date_expiry: {
                        validators: {
                            date: {
                                format: ['YYYY-MM-DD', 'iYYYY-iMM-iDD'], // قبول كلا التنسيقين
                                message: 'تاريخ الانتهاء غير صالح. يجب أن يكون بتنسيق YYYY-MM-DD (ميلادي) أو iYYYY-iMM-iDD (هجري).'
                            }
                        }
                    }
                };
            }

            if (index === 1) { // Step 2: Additional Info
                validators = {
                    status: {
                        validators: {
                            notEmpty: {
                                message: 'حالة الوكالة مطلوبة.'
                            },
                            choice: {
                                min: 1,
                                max: 1,
                                message: 'يرجى اختيار حالة واحدة فقط.',
                                choices: ['active', 'expired', 'revoked']
                            }
                        }
                    },
                    file_attachment: {
                        validators: {
                            file: {
                                extension: 'jpg,jpeg,png,pdf,doc,docx',
                                type: 'image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                maxSize: 30 * 1024 * 1024, // 30 MB
                                message: 'يجب أن يكون الملف من نوع JPG، JPEG، PNG، PDF، DOC، DOCX وحجمه لا يتجاوز 30MB.'
                            }
                        }
                    },
                    notes: {
                        validators: {
                            stringLength: {
                                max: 1000,
                                message: 'يجب ألا تتجاوز الملاحظات 1000 حرف.'
                            }
                        }
                    }
                };
            }

            const fv = FormValidation.formValidation(step, {
                fields: validators,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '[class^="col-md-"]',
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
                        language: 'ar'
                    })
                    .on('change', function () {
                        const fieldName = $this.attr('name');
                        const currentIndex = validationStepper._currentIndex;
                        formValidations[currentIndex].revalidateField(fieldName);
                    });
            });
        }

        $('#date_issued').on('dp.change', function (e) {
            formValidations[0].revalidateField('date_issued');
        });

        $('#date_expiry').on('dp.change', function (e) {
            formValidations[0].revalidateField('date_expiry');
        });

        // Submit button handler (optional if you want to handle via AJAX)
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            // قم بتفعيل التحقق من النموذج بأكمله أو خطوة معينة إذا لزم الأمر
            formValidations[1].validate(); // Assuming step 2 is the last step
        });
    }
})();
