'use strict';

(function () {
    const select2 = $('.select2');

    function clearSelectValue($select) {
        if ($select.val() !== null) {
            $select.val(null).trigger('change.select2');
        }
    }

    function clearElement($el) {
        if ($el.is('select')) {
            clearSelectValue($el);
        } else if ($el.is(':checkbox,:radio')) {
            $el.prop('checked', false);
        } else if ($el.attr('type') === 'file') {
            const $clone = $el.clone().val('');
            $el.replaceWith($clone);
            $el = $clone;
        } else {
            $el.val('');
        }

        return $el;
    }

    function clearFieldsInContainer(selector) {
        $(selector).find('input,select,textarea').each(function () {
            clearElement($(this));
        });
    }

    function updateFormVisibility(clear = true) {
        const publication_status = $('#publication_status').val();

        $('#published-fields-container, #scheduling-type-container, #scheduling-details-container').hide();
        if (clear) clearFieldsInContainer('#published-fields-container,#scheduling-type-container,#scheduling-details-container');

        if (publication_status === 'published') {
            $('#published-fields-container').show();
        } else if (publication_status === 'scheduled') {
            $('#scheduling-type-container').show();
            $('#scheduling-details-container').show();
            updateSchedulingUI(clear);
        }
    }

    function updateSchedulingUI(clear = true) {
        const publishType = $('#publish_type').val();
        const recurringType = $('#recurring_type').val();

        if (clear) {
            if (publishType !== 'one_time') clearFieldsInContainer('.auto-one-time');
            if (publishType !== 'recurring') clearFieldsInContainer('.auto-recurring');
            if (recurringType !== 'weekly') clearFieldsInContainer('.recurring-weekly');
            if (recurringType !== 'monthly') clearFieldsInContainer('.recurring-monthly');
        }

        $('.auto-one-time').toggle(publishType === 'one_time');
        $('.auto-recurring').toggle(publishType === 'recurring');

        if (publishType === 'recurring') {
            $('.recurring-weekly').toggle(recurringType === 'weekly');
            $('.recurring-monthly').toggle(recurringType === 'monthly');
        }
    }

    $('#publication_status').on('change', () => updateFormVisibility(true));
    $('#publish_type,#recurring_type').on('change', () => updateSchedulingUI(true));
    updateFormVisibility(false);

    const typesNeedFile = ['image', 'video', 'image_text', 'video_text'];

    function hasExistingMedia() {
        return $('#existing_media').length > 0;
    }

    function updateMediaUI(clear = true) {
        const type = $('#media_type').val();
        let $file = $('#media');
        const $text = $('#content_text');

        // إخفاء الجميع
        $('#media_wrapper, #content_text_wrapper').hide();

        if (clear) {
            $file = clearElement($file).prop('required', false);
            clearElement($text).prop('required', false);

            if (hasExistingMedia() && typesNeedFile.includes(type)) {
                $('#existing_media').remove();
            }
        } else {
            $file.prop('required', false);
            $text.prop('required', false);
        }

        switch (type) {
            case 'image':
                $('#media_label').text('الصورة');
                $('#media_wrapper').show();
                $file.prop('required', true);
                break;

            case 'video':
                $('#media_label').text('الفيديو');
                $('#media_wrapper').show();
                $file.prop('required', true);
                break;

            case 'text':
                $('#content_text_wrapper').show();
                $text.prop('required', true);
                break;

            case 'image_text':
                $('#media_label').text('الصورة');
                $('#media_wrapper, #content_text_wrapper').show();
                $file.prop('required', true);
                $text.prop('required', true);
                break;

            case 'video_text':
                $('#media_label').text('الفيديو');
                $('#media_wrapper, #content_text_wrapper').show();
                $file.prop('required', true);
                $text.prop('required', true);
                break;
        }
    }

    updateMediaUI(false);
    $('#media_type').on('change', () => updateMediaUI(true));

    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;
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

        const formValidations = [];

        steps.forEach((step, index) => {
            let validators = {};

            if (index === 0) {
                validators = {
                    content_type_id: {
                        validators: {
                            notEmpty: { message: ' نوع المحتوى مطلوب' },
                        }
                    },

                    publishing_pattern_id: {
                        validators: {
                            notEmpty: { message: ' نمط النشر  مطلوب' },
                        }
                    },

                    content_purpose_id: {
                        validators: {
                            notEmpty: { message: ' الهدف من المحتوى مطلوب' },
                        }
                    },

                    "socials[]": {
                        validators: {
                            notEmpty: { message: ' المنصات  مطلوبة' },
                        }
                    },
                    media_type: {
                        validators: {
                            notEmpty: { message: ' نوع الوسائط  مطلوب' },
                        }
                    },
                    publication_date: {
                        validators: {
                            notEmpty: { message: ' تاريخ النشر مطلوب' },
                            date: {
                                format: 'YYYY-MM-DD',
                                message: 'صيغة التاريخ غير صحيحة'
                            }
                        }
                    },

                    media: {
                        validators: {
                            callback: {
                                message: 'الملف مطلوب',
                                callback: () => {
                                    const t = $('#media_type').val();
                                    return typesNeedFile.includes(t)
                                        ? (hasExistingMedia() || $('#media').val().trim() !== '')
                                        : true;
                                }
                            }
                        }
                    },

                    content_text: {
                        validators: {
                            callback: {
                                message: 'النص مطلوب',
                                callback: () => {
                                    const t = $('#media_type').val();
                                    return ['text', 'image_text', 'video_text'].includes(t) ? $('#content_text').val().trim() !== '' : true;
                                }
                            }
                        }
                    },
                };
            }

            if (index === 1) {
                validators = {
                    publication_status: { validators: { notEmpty: { message: 'الحالة مطلوبة' } } },
                    publication_date: {
                        validators: { callback: { message: 'تاريخ النشر مطلوب', callback: (input) => $('#publication_status').val() === 'published' ? input.value.trim() !== '' : true } }
                    },
                    publish_type: {
                        validators: { callback: { message: 'نوع الجدولة مطلوب', callback: (input) => $('#publication_status').val() === 'scheduled' ? input.value.trim() !== '' : true } }
                    },
                    one_time_at: {
                        validators: { callback: { message: 'تاريخ ووقت النشر مطلوب', callback: (input) => ($('#publication_status').val() === 'scheduled' && $('#publish_type').val() === 'one_time') ? input.value.trim() !== '' : true } }
                    },
                    recurring_type: {
                        validators: { callback: { message: 'نمط التكرار مطلوب', callback: (input) => ($('#publication_status').val() === 'scheduled' && $('#publish_type').val() === 'recurring') ? input.value.trim() !== '' : true } }
                    },
                    publish_time: {
                        validators: { callback: { message: 'وقت النشر مطلوب', callback: (input) => ($('#publication_status').val() === 'scheduled' && $('#publish_type').val() === 'recurring') ? input.value.trim() !== '' : true } }
                    },
                    month_day: {
                        validators: { callback: { message: 'يوم الشهر مطلوب', callback: (input) => ($('#publication_status').val() === 'scheduled' && $('#publish_type').val() === 'recurring' && $('#recurring_type').val() === 'monthly') ? input.value.trim() !== '' : true } }
                    },
                    'week_days[]': {
                        validators: { callback: { message: 'يجب اختيار يوم واحد على الأقل', callback: () => ($('#publication_status').val() === 'scheduled' && $('#publish_type').val() === 'recurring' && $('#recurring_type').val() === 'weekly') ? $(wizardValidationForm).find('input[name="week_days[]"]:checked').length > 0 : true } }
                    },
                    start_date: {
                        validators: { callback: { message: 'تاريخ البدء مطلوب', callback: (input) => ($('#publication_status').val() === 'scheduled' && $('#publish_type').val() === 'recurring') ? input.value.trim() !== '' : true } }
                    },
                    end_date: {
                        validators: {
                            callback: {
                                message: 'تاريخ الانتهاء مطلوب ويجب أن يكون بعد تاريخ البدء',
                                callback: function (input) {
                                    if ($('#publication_status').val() === 'scheduled' && $('#publish_type').val() === 'recurring') {
                                        const startDate = $(wizardValidationForm).find('[name="start_date"]').val();
                                        return input.value.trim() !== '' && (startDate === '' || input.value >= startDate);
                                    }

                                    return true;
                                }
                            }
                        }
                    },
                }
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

                    wizardValidationForm.submit();
                }
            });

            formValidations.push(fv);
        });

        btnNextList.forEach((btn, index) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                formValidations[index].validate();
            });
        });

        btnPrevList.forEach((btn, index) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                validationStepper.previous();
            });
        });

        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>');
                $this
                    .select2({
                        placeholder: $this.data('placeholder') || 'اختر خيارًا',
                        dropdownParent: $this.parent(),
                        allowClear: true,
                        width: '100%',
                        language: 'ar',
                        dir: 'rtl'
                    })
                    .on('change', function () {
                        const fieldName = $this.attr('name');
                        const currentIndex = validationStepper._currentIndex;
                        formValidations[currentIndex].revalidateField(fieldName);
                    });
            });
        }

        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            formValidations[1].validate();
        });
    }
})();
