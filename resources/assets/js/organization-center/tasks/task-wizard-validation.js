'use strict';

(function () {
    const select2 = $('.select2');

    // Wizard Validation
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardForm = wizardValidation.querySelector('#form');
    const steps = wizardForm.querySelectorAll('.content');
    const btnNextList = wizardForm.querySelectorAll('.btn-next');
    const btnPrevList = wizardForm.querySelectorAll('.btn-prev');
    const btnSubmit = wizardForm.querySelector('.btn-submit');

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    const lastStepIndex = steps.length - 1;

    const formValidations = [];

    // إعداد التحقق لكل خطوة
    steps.forEach((step, index) => {
        let validators = {};

        if (index === 0) {
            // الخطوة الأولى: المعلومات الأساسية
            validators = {
                task_name: {
                    validators: {
                        notEmpty: { message: 'عنوان المهمة مطلوب.' },
                        stringLength: {
                            min: 3,
                            max: 150,
                            message: 'يجب أن يكون عنوان المهمة بين 3 و 150 حرف.'
                        }
                    }
                },

                due_date: {
                    validators: {
                        notEmpty: { message: 'تاريخ الاستحقاق مطلوب.' },
                        date: {
                            format: ['YYYY-MM-DD', 'iYYYY-iMM-iDD'],
                            message: 'التاريخ غير صالح. استخدم YYYY-MM-DD أو iYYYY-iMM-iDD.'
                        }
                    }
                },

                task_field: {
                    validators: {
                        notEmpty: { message: ' مجال المهمة مطلوب.' }
                    }
                },

                detailed_marketing_channel_id: {
                    validators: {
                        callback: {
                            message: "قناة التسويق التفصيلية مطلوبة",
                            callback: function (input) {
                                var marketingChannelVal =
                                    $("#marketing_id").val();
                                return (
                                    marketingChannelVal != "2" ||
                                    input.value !== ""
                                );
                            },
                        },
                    },
                },
                parent_customer_id: {
                    validators: {
                        callback: {
                            message: "العميل مطلوب",
                            callback: function (input) {
                                var marketingChannelVal =
                                    $("#marketing_id").val();
                                return (
                                    marketingChannelVal != "3" ||
                                    input.value !== ""
                                );
                            },
                        },
                    },
                },
                social_media_id: {
                    validators: {
                        callback: {
                            message: "موقع التواصل الاجتماعي مطلوب",
                            callback: function (input) {
                                var marketingChannelVal =
                                    $("#marketing_id").val();
                                return (
                                    marketingChannelVal != "6" ||
                                    input.value !== ""
                                );
                            },
                        },
                    },
                },

                priority: {
                    validators: {
                        notEmpty: { message: ' أولوية المهمة مطلوب.' }
                    }
                },

                'assigned_user_ids[]': {
                    validators: {
                        notEmpty: { message: ' المكلفين بالمهمة مطلوبين.' }
                    }
                },

                project_id: {
                    validators: {
                        callback: {
                            message: 'المشروع مطلوب',
                            callback(input) {
                                if ($('#task_field').val() === 'projects') {
                                    return input.value.trim() !== '';
                                }

                                return true;
                            }
                        }
                    }
                },

                lawsuit_id: {
                    validators: {
                        callback: {
                            message: 'الدعوى مطلوبة',
                            callback(input) {
                                if ($('#task_field').val() === 'lawsuits') {
                                    return input.value.trim() !== '';
                                }

                                return true;
                            }
                        }
                    }
                },

                marketing_id: {
                    validators: {
                        callback: {
                            message: 'قناة التسويق مطلوبة',
                            callback(input) {
                                if ($('#task_field').val() === 'sales') {
                                    return input.value.trim() !== '';
                                }

                                return true;
                            }
                        }
                    }
                },
            };
        } else if (index === 1) {
            validators = {
                step_counter_dummy: {
                    validators: {
                        callback: {
                            message: 'أضِف خطوة واحدة على الأقل',
                            callback: function () {
                                return $('.step-block').length > 0;
                            }
                        }
                    }
                }
            };
        }

        // إنشاء مثيل FormValidation
        const fv = FormValidation.formValidation(step, {
            fields: validators,
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    rowSelector: '.col-12, [class^="col-md-"]',
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
    });

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

    window.taskFormValidations = formValidations;
})();
