/**
 * Form Wizard Validation for Power of Attorney
 */

'use strict';

(function () {
    const select2 = $('.select2');

    // 2) حافظ على reference للعناصر
    const $unit = $('#duration_unit');
    const $from = $('#duration_from');
    const $to = $('#duration_to');
    const form = $('#violation-form');

    // 3) دالة تحديث placeholder
    function updatePlaceholders() {
        const unit = $unit.val();
        let sufMin = '',
            sufMax = '';
        if (unit === 'minutes') {
            sufMin = 'دقيقة';
            sufMax = 'دقائق';
        }

        if (unit === 'hours') {
            sufMin = 'ساعة';
            sufMax = 'ساعات';
        }

        if (unit === 'days') {
            sufMin = 'يوم';
            sufMax = 'أيام';
        }

        $from.attr('placeholder', `مثلاً 1 ${sufMin}`);
        $to.attr('placeholder', `مثلاً 3 ${sufMax}`);
    }

    // 4) استدعاء أولي وربط الـ event
    updatePlaceholders();
    $unit.on('select2:select change', updatePlaceholders);

    // Wizard Validation
    const wizardValidation = document.querySelector('#wizard-validation');
    if (wizardValidation) {
        const wizardValidationForm = wizardValidation.querySelector('#violation-form');
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
                    violation_type: {
                        validators: {
                            notEmpty: {
                                message: 'نوع المخالفة  مطلوب .'
                            },
                        }
                    },
                    description: {
                        validators: {
                            notEmpty: {
                                message: 'وصف  المخالفة  مطلوب .'
                            },
                        }
                    },
                    duration_unit: {
                        validators: {
                            callback: {
                                message: 'وحدة المدة مطلوبة',
                                callback: function (input) {
                                    const violationType = document.getElementById('violation_type').value;
                                    if (violationType && violationType !== 'other') {
                                        return input.value !== '';
                                    }

                                    return true;
                                }
                            }
                        }
                    },
                    duration_from: {
                        validators: {
                            callback: {
                                message: 'المدة من مطلوبة',
                                callback: function (input) {
                                    const violationType = document.getElementById('violation_type').value;
                                    if (violationType && violationType !== 'other') {
                                        return input.value !== '';
                                    }

                                    return true;
                                }
                            }
                        }
                    },
                    penalty_first: {
                        validators: {
                            notEmpty: {
                                message: ' الجزاء أول مرة  مطلوب .'
                            },
                        }
                    },
                    settings_violation_category_id: {
                        validators: {
                            notEmpty: {
                                message: '   تصنيف المخالفة  مطلوب .'
                            },
                        }
                    },
                };
            }

            const fv = FormValidation.formValidation(step, {
                fields: validators,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap5: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.col-md-6, .col-md-12,.col-md-4',
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
                        width: '100%',
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
            formValidations[0].validate();
        });
    }

    $('#violation_type').on('select2:select', function (e) {
        const selectedType = e.params.data.id;
        toggleDurationFieldsJQuery(selectedType);
    });

    // دالة jQuery للإظهار/الإخفاء
    function toggleDurationFieldsJQuery(selectedType) {
        const durationFieldContainers = {
            unit: $('#duration_unit').closest('.col-md-4'),
            from: $('#duration_from').closest('.col-md-4'),
            to: $('#duration_to').closest('.col-md-4')
        };

        if (selectedType === 'other') {
            // إخفاء مع تأثير انسيابي
            durationFieldContainers.unit.fadeOut(300);
            durationFieldContainers.from.fadeOut(300);
            durationFieldContainers.to.fadeOut(300);

            // تفريغ القيم
            $('#duration_unit').val('').trigger('change');
            $('#duration_from').val('');
            $('#duration_to').val('');

            // إزالة required
            $('#duration_unit, #duration_from').removeAttr('required');
        } else {
            // إظهار مع تأثير انسيابي
            durationFieldContainers.unit.fadeIn(300);
            durationFieldContainers.from.fadeIn(300);
            durationFieldContainers.to.fadeIn(300);
        }
    }

    // تطبيق عند التحميل
    toggleDurationFieldsJQuery($('#violation_type').val());
})();
