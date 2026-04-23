'use strict';

let validationStepper;
let formValidations = [];

$(function () {
    initializeWizard();
    initializeFormValidation();
    initializeQuestionsManagement();
    initializeMessageTemplate();
    initializeSelect2();
});

/**
 * تهيئة المعالج (Wizard)
 */
function initializeWizard() {
    const wizardElement = document.querySelector('#wizard-validation');
    if (wizardElement) {
        validationStepper = new Stepper(wizardElement, {
            linear: true,
            animation: true
        });
    }
}

/**
 * تهيئة التحقق من صحة النموذج
 */
function initializeFormValidation() {
    const form = $('#form');
    const steps = form.find('.content');

    // تهيئة validation لكل خطوة
    steps.each(function (index) {
        let validators = {};

        if (index === 0) { // الخطوة الأولى: المعلومات الأساسية
            validators = {
                type: {
                    validators: {
                        notEmpty: { message: 'نوع الاستبيان مطلوب' }
                    }
                },
                title: {
                    validators: {
                        notEmpty: { message: 'عنوان الاستبيان مطلوب' },
                        stringLength: {
                            min: 3,
                            max: 255,
                            message: 'عنوان الاستبيان يجب أن يكون بين 3 و 255 حرف'
                        }
                    }
                },
                message_template: {
                    validators: {
                        notEmpty: { message: 'قالب الرسالة مطلوب' },
                        stringLength: {
                            min: 10,
                            max: 500,
                            message: 'قالب الرسالة يجب أن يكون بين 10 و 500 حرف'
                        }
                    }
                },
                description: {
                    validators: {
                        stringLength: {
                            max: 1000,
                            message: 'الوصف يجب أن لا يتجاوز 1000 حرف'
                        }
                    }
                }
            };
        }

        const fv = FormValidation.formValidation(this, {
            fields: validators,
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.col-md-6, .col-md-12'
                }),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        }).on('core.form.valid', function () {
            if (index === 0) {
                // الانتقال للخطوة التالية
                validationStepper.next();
            } else {
                // إرسال النموذج
                submitForm();
            }
        });

        formValidations.push(fv);
    });

    // أزرار التالي
    $('.btn-next').on('click', function (e) {
        e.preventDefault();
        const currentIndex = validationStepper._currentIndex;
        formValidations[currentIndex].validate();
    });

    // أزرار السابق
    $('.btn-prev').on('click', function (e) {
        e.preventDefault();
        validationStepper.previous();
    });

    // زر الإرسال
    form.on('submit', function (e) {
        e.preventDefault();

        if (validateAllSteps()) {
            submitForm();
        }
    });
}

/**
 * تهيئة إدارة الأسئلة
 */
function initializeQuestionsManagement() {
    // إضافة سؤال جديد
    $('#addQuestionBtn').on('click', function () {
        addNewQuestion();
    });

    // التعامل مع الأحداث المفوضة للأسئلة
    $('#questionsContainer').on('click', '.remove-question', function () {
        removeQuestion($(this));
    });

    // التعامل مع خيارات الأسئلة
    $('#questionsContainer').on('click', '.add-option', function () {
        addNewOption($(this));
    });

    $('#questionsContainer').on('click', '.remove-option', function () {
        removeOption($(this));
    });
}

/**
 * إضافة سؤال جديد
 */
function addNewQuestion() {
    const template = $('#questionTemplate').html();
    const questionElement = $(template);

    // إضافة السؤال أولاً
    $('#questionsContainer').append(questionElement);
    $('#noQuestionsAlert').hide();

    // أضف خيارين افتراضيين
    addNewOption(questionElement.find('.add-option'), 'نعم');
    addNewOption(questionElement.find('.add-option'), 'لا');

    // إعادة ترقيم جميع الأسئلة
    reindexAllQuestions();

    questionElement.hide().fadeIn(300);
    questionElement.find('.question-text').focus();
}

/**
 * حذف سؤال
 */
function removeQuestion(button) {
    const questionItem = button.closest('.question-item');

    Swal.fire({
        title: 'تأكيد الحذف',
        text: 'هل تريد حذف هذا السؤال وجميع خياراته؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            questionItem.fadeOut(300, function () {
                $(this).remove();
                reindexAllQuestions(); // إعادة ترقيم بعد الحذف
                checkNoQuestions();
            });
        }
    });
}

/**
 * إضافة خيار جديد
 */
function addNewOption(button, defaultText = '') {
    const questionItem = button.closest('.question-item');
    const optionsList = questionItem.find('.options-list');

    const template = $('#optionTemplate').html();
    const optionElement = $(template);

    // ضع النص الافتراضي إن وُجد
    if (defaultText) {
        optionElement.find('.option-text').val(defaultText);
    }

    // أضف العنصر
    optionsList.append(optionElement);

    // إعادة ترقيم جميع الأسئلة والخيارات
    reindexAllQuestions();

    // مؤثر بصري وتركيز
    optionElement.hide().fadeIn(300);
    if (!defaultText) {
        optionElement.find('.option-text').focus();
    }
}

/**
 * حذف خيار
 */
function removeOption(button) {
    const optionItem = button.closest('.option-item');
    const optionsList = optionItem.parent();

    if (optionsList.children('.option-item').length <= 1) {
        Swal.fire({
            title: 'تنبيه',
            text: 'يجب أن يكون هناك خيار واحد على الأقل',
            icon: 'warning',
            confirmButtonText: 'موافق'
        });
        return;
    }

    optionItem.fadeOut(300, function () {
        $(this).remove();
        reindexAllQuestions(); // إعادة ترقيم بعد الحذف
    });
}

/**
 * إعادة ترقيم جميع الأسئلة والخيارات - هذه الدالة الأساسية
 */
function reindexAllQuestions() {
    $('#questionsContainer .question-item').each(function (questionIndex) {
        const $question = $(this);

        // تحديث رقم السؤال في العرض
        $question.find('.question-number').text(questionIndex + 1);

        // تحديث data attribute
        $question.attr('data-question-index', questionIndex);

        // تحديث اسم حقل السؤال
        $question.find('.question-text').attr('name', `questions[${questionIndex}][question_text]`);

        // إعادة ترقيم خيارات هذا السؤال
        $question.find('.options-list .option-item').each(function (optionIndex) {
            const $option = $(this);
            $option.find('.option-text').attr('name', `questions[${questionIndex}][options][${optionIndex}][option_text]`);
        });
    });

    console.log('تم إعادة ترقيم الأسئلة بنجاح'); // للتتبع
}

/**
 * فحص عدم وجود أسئلة
 */
function checkNoQuestions() {
    const questionsCount = $('#questionsContainer .question-item').length;
    if (questionsCount === 0) {
        $('#noQuestionsAlert').show();
    } else {
        $('#noQuestionsAlert').hide();
    }
}

/**
 * التحقق من صحة الأسئلة
 */
function validateQuestions() {
    const questions = $('#questionsContainer .question-item');
    const errors = [];

    if (questions.length === 0) {
        errors.push('يجب إضافة سؤال واحد على الأقل');
        return errors;
    }

    questions.each(function (index) {
        const questionText = $(this).find('.question-text').val().trim();

        if (!questionText) {
            errors.push(`السؤال رقم ${index + 1}: نص السؤال مطلوب`);
        }

        // التحقق من الخيارات
        const options = $(this).find('.option-item');
        if (options.length < 2) {
            errors.push(`السؤال رقم ${index + 1}: يجب إضافة خيارين على الأقل`);
        }

        let hasEmptyOption = false;
        options.each(function () {
            if (!$(this).find('.option-text').val().trim()) {
                hasEmptyOption = true;
            }
        });

        if (hasEmptyOption) {
            errors.push(`السؤال رقم ${index + 1}: جميع الخيارات يجب أن تحتوي على نص`);
        }
    });

    return errors;
}

/**
 * التحقق من جميع الخطوات
 */
function validateAllSteps() {
    // التحقق من الخطوة الأولى
    const step1Valid = formValidations[0] ? formValidations[0].validate() : Promise.resolve('Valid');

    // التحقق من الأسئلة
    const questionErrors = validateQuestions();

    if (questionErrors.length > 0) {
        Swal.fire({
            title: 'أخطاء في الأسئلة',
            html: '<ul class="text-start">' + questionErrors.map(error => `<li>${error}</li>`).join('') + '</ul>',
            icon: 'error',
            confirmButtonText: 'موافق'
        });
        return false;
    }

    return true;
}

/**
 * إرسال النموذج
 */
function submitForm() {
    const submitBtn = $('.btn-submit');

    // إظهار loading
    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>جاري الحفظ...');

    // طباعة البيانات للتتبع (يمكن حذفها لاحقاً)
    console.log('بيانات النموذج:', $('#form').serialize());

    // إرسال النموذج
    $('#form').off('submit').submit();
}

/**
 * تهيئة قالب الرسالة
 */
function initializeMessageTemplate() {
    // معاينة الرسالة
    $('#message_template').on('input', function () {
        updateMessagePreview();
    });

    // إضافة أزرار المتغيرات
    const variableButtons = `
        <div class="mt-2">
            <small class="text-muted">المتغيرات المتاحة:</small><br>
            <div class="btn-group-sm mt-1" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm insert-variable" data-variable="name">الاسم</button>
                <button type="button" class="btn btn-outline-secondary btn-sm insert-variable" data-variable="survey_url">رابط الاستبيان</button>
                <button type="button" class="btn btn-outline-secondary btn-sm insert-variable" data-variable="survey_title">عنوان الاستبيان</button>
            </div>
        </div>
    `;
    $('#message_template').parent().append(variableButtons);

    // إدراج المتغيرات
    $('.insert-variable').on('click', function () {
        const variable = $(this).data('variable');
        const textarea = $('#message_template');
        const cursorPos = textarea[0].selectionStart;
        const currentValue = textarea.val();

        const newValue = currentValue.slice(0, cursorPos) + `{${variable}}` + currentValue.slice(cursorPos);
        textarea.val(newValue);
        textarea.trigger('input');
        textarea.focus();
    });
}

/**
 * تحديث معاينة الرسالة
 */
function updateMessagePreview() {
    const template = $('#message_template').val();
    if (!template) return;

    const sampleData = {
        'name': 'اسم العميل',
        'survey_url': 'URL',
        'survey_title': $('#title').val() || 'استبيان العملاء'
    };

    let preview = template;
    Object.keys(sampleData).forEach(key => {
        const regex = new RegExp(`\\{${key}\\}`, 'g');
        preview = preview.replace(regex, sampleData[key]);
    });

    const length = preview.length;

    // إزالة معاينة سابقة
    $('.message-preview').remove();

    if (preview !== template) {
        const previewHtml = `
            <div class="message-preview mt-2">
                <small class="text-muted">معاينة الرسالة:</small>
                <div class="border rounded p-2 bg-light">
                    <small>${preview}</small>
                    <div class="text-end mt-1">
                        <small class="badge ${length > 160 ? 'bg-danger' : 'bg-success'}">${length} حرف</small>
                    </div>
                </div>
            </div>
        `;
        $('#message_template').parent().append(previewHtml);
    }
}

/**
 * تهيئة Select2
 */
function initializeSelect2() {
    $('.select2').each(function () {
        const $this = $(this);
        $this.wrap('<div class="position-relative"></div>');
        $this.select2({
            placeholder: $this.data('placeholder') || 'اختر خيارًا',
            dropdownParent: $this.parent(),
            dir: 'rtl'
        }).on('change', function () {
            const fieldName = $this.attr('name');
            const currentIndex = validationStepper._currentIndex;
            if (formValidations[currentIndex]) {
                formValidations[currentIndex].revalidateField(fieldName);
            }
        });
    });
}

/**
 * التحقق من التغييرات غير المحفوظة
 */
let hasUnsavedChanges = false;

$('#form').find('input, textarea, select').on('change input', function () {
    hasUnsavedChanges = true;
});

$('#form').on('submit', function () {
    hasUnsavedChanges = false;
});

$(window).on('beforeunload', function (e) {
    if (hasUnsavedChanges) {
        e.returnValue = 'لديك تغييرات غير محفوظة. هل تريد المغادرة دون حفظ؟';
        return e.returnValue;
    }
});
