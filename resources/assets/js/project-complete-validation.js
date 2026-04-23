'use strict';

(function () {
    const select2 = $('.select2');

    const wizardValidation = document.querySelector('#wizard-validation');
    if (typeof wizardValidation !== 'undefined' && wizardValidation !== null) {
        const wizardValidationForm = wizardValidation.querySelector('#wizard-validation-form');
        const wizardValidationFormStep1 = wizardValidationForm.querySelector('#account-details-validation');
        const wizardValidationFormStep2 = wizardValidationForm.querySelector('#details-validation');
        const wizardValidationNext = [].slice.call(wizardValidationForm.querySelectorAll('.btn-next'));
        const wizardValidationPrev = [].slice.call(wizardValidationForm.querySelectorAll('.btn-prev'));

        const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

        const validationStepper = new Stepper(wizardValidation, {
            linear: true
        });

        // الخطوة الأولى: معلومات المشروع
        const FormValidation1 = FormValidation.formValidation(wizardValidationFormStep1, {
            fields: {
                // project_name: {
                //     validators: {
                //         notEmpty: {
                //             message: 'اسم المشروع مطلوب'
                //         },
                //         stringLength: {
                //             min: 3,
                //             max: 255,
                //             message: 'يجب أن يكون اسم المشروع أكثر من 3 وأقل من 255 حرفًا'
                //         },
                //     }
                // },
                // employee_id: {
                //     validators: {
                //         notEmpty: {
                //             message: 'مدير المشروع مطلوب'
                //         },
                //         callback: {
                //             message: 'مدير المشروع غير صالح',
                //             callback: function (input) {
                //                 const value = input.value;
                //                 return value === '' || /^[0-9]+$/.test(value);
                //             }
                //         }
                //     }
                // },
                // start_date: {
                //     validators: {
                //         notEmpty: {
                //             message: 'تاريخ البدء مطلوب'
                //         },
                //         date: {
                //             format: 'YYYY-MM-DD',
                //             message: 'تاريخ البدء غير صالح'
                //         }
                //     }
                // },
                // contract_id: {
                //     validators: {
                //         notEmpty: {
                //             message: ' العقد مطلوب'
                //         },
                //     }
                // },
                // contractual_closure: {
                //     validators: {
                //         date: {
                //             format: 'YYYY-MM-DD',
                //             message: 'الإغلاق التعاقدي غير صالح'
                //         },
                //         callback: {
                //             message: 'الإغلاق التعاقدي يجب أن يكون بعد أو يساوي تاريخ البدء',
                //             callback: function (input) {
                //                 const startDate = $('#start_date').val();
                //                 const contractualClosure = input.value;
                //                 if (startDate && contractualClosure) {
                //                     return new Date(contractualClosure) >= new Date(startDate);
                //                 }
                //                 return true;
                //             }
                //         }
                //     }
                // },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.col-sm-6, .col-sm-12, .col-sm-4'
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            },
            init: instance => {
                instance.on('plugins.message.placed', function (e) {
                    // تحريك رسالة الخطأ خارج عنصر `input-group`
                    if (e.element.parentElement.classList.contains('input-group')) {
                        e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                    }
                });
            }
        }).on('core.form.valid', function () {
            // الانتقال إلى الخطوة التالية عندما تكون جميع الحقول في الخطوة الحالية صالحة
            validationStepper.next();
        });

        // الخطوة الثانية: تفاصيل المشروع
        const FormValidation2 = FormValidation.formValidation(wizardValidationFormStep2, {
            fields: {
                manager_user_id: {
                    validators: {
                        notEmpty: {
                            message: 'مدير المشروع مطلوب'
                        },
                        callback: {
                            message: 'مدير المشروع غير صالح',
                            callback: function (input) {
                                const value = input.value;
                                return value === '' || /^[0-9]+$/.test(value);
                            }
                        }
                    }
                },
                // 'team_members[]': {
                //     validators: {
                //         notEmpty: {
                //             message: "فريق المشروع مطلوب",
                //         },

                //     },
                // },
                claim_type: {
                    validators: {
                        notEmpty: {
                            message: 'نوع المطالبة  مطلوبة.'
                        }
                    }
                },
                total_claim: {
                    validators: {
                        numeric: {
                            message: 'إجمالي المطالبة يجب أن يكون رقمًا'
                        },
                        // notEmpty: {
                        //     message: 'القيمة الخاصة بالمطالبة المالية مطلوبة'
                        // },
                        callback: {
                            message: 'القيمة الخاصة بالمطالبة المالية مطلوبة',
                            callback: function (input) {
                                const claim_type = $('#claim_type').val();
                                const thisVal = input.value;
                                if (claim_type == 'financial' && thisVal == '') {
                                    return false;
                                }

                                return true;
                            }
                        }
                    },
                },
                non_financial_claim: {
                    validators: {
                        // notEmpty: {
                        //     message: 'القيمة الخاصة بالمطالبة غير المالية مطلوبة'
                        // },
                        callback: {
                            message: 'القيمة الخاصة بالمطالبة غير المالية مطلوبة',
                            callback: function (input) {
                                const claim_type = $('#claim_type').val();
                                const thisVal = input.value;
                                if (claim_type == 'non_financial' && thisVal == '') {
                                    return false;
                                }

                                return true;
                            }
                        }
                    },
                },
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.col-sm-6, .col-sm-12, .col-sm-4'
                }),
                autoFocus: new FormValidation.plugins.AutoFocus(),
                submitButton: new FormValidation.plugins.SubmitButton()
            },
            init: instance => {
                instance.on('plugins.message.placed', function (e) {
                    // تحريك رسالة الخطأ خارج عنصر `input-group`
                    if (e.element.parentElement.classList.contains('input-group')) {
                        e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                    }
                });
            }
        }).on('core.form.valid', function () {
            // إرسال النموذج عندما تكون جميع الخطوات صالحة
            btnSubmit.setAttribute('disabled', 'disabled');
            btnSubmit.dataset.oldText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

            wizardValidationForm.submit();
        });

        // تهيئة Select2
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
                        // إعادة التحقق من الحقل عند التغيير
                        switch (validationStepper._currentIndex) {
                            case 0:
                                FormValidation1.revalidateField($this.attr('name'));
                                break;
                            case 1:
                                FormValidation2.revalidateField($this.attr('name'));
                                break;
                            default:
                                break;
                        }
                    });
            });
        }

        // أزرار التالي
        wizardValidationNext.forEach(item => {
            item.addEventListener('click', () => {
                switch (validationStepper._currentIndex) {
                    case 0:
                        FormValidation1.validate();
                        break;
                    case 1:
                        FormValidation2.validate();
                        break;
                }
            });
        });

        // أزرار السابق
        wizardValidationPrev.forEach(item => {
            item.addEventListener('click', () => {
                validationStepper.previous();
            });
        });
    }
})();
