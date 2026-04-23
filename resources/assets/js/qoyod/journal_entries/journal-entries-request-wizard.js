'use strict';

(function () {
    const select2 = $('#inventory_id');
    const wizardValidation = document.querySelector('#wizard-validation');
    if (!wizardValidation) return;

    const wizardValidationForm = wizardValidation.querySelector('#form');
    const steps = wizardValidationForm.querySelectorAll('.content');
    const btnSubmit = wizardValidationForm.querySelector('.btn-submit');

    const validationStepper = new Stepper(wizardValidation, {
        linear: true,
        animation: true
    });

    const formValidations = [];

    steps.forEach((step, index) => {
        const validators = {
            date: {
                validators: {
                    notEmpty: { message: 'التاريخ مطلوب' },
                }
            },

            inventory_id: {
                validators: {
                    notEmpty: { message: 'الموقع مطلوب' },
                }
            },
        };

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
        }).on("core.form.valid", function () {
            // إذا كانت هناك خطوة تالية انتقل إليها
            if (index < steps.length - 1) {
                validationStepper.next();
            } else {
                btnSubmit.setAttribute('disabled', 'disabled');
                btnSubmit.dataset.oldText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = 'جار المعالجة <span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> ';

                // وإلا في آخر خطوة أرسل الفورم
                wizardValidationForm.submit();
            }
        });

        formValidations.push(fv);
    });


    if (btnSubmit) {
        btnSubmit.addEventListener('click', function (e) {
            e.preventDefault();
            // يشغل تحقق الخطوة الأخيرة
            formValidations[steps.length - 1].validate();
        });
    }

    // Initialize Select2
    if (select2.length) {
        select2.each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            $this
                .select2({
                    placeholder: $this.data('placeholder') || ' ',
                    dropdownParent: $this.parent(),
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                });
        });
    }
})();


document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.querySelector('#linesTable tbody');
    const addBtn = document.getElementById('addLine');
    // ننسخ الصفّ الإفتراضي (بدون Select2 عليه)
    const templateRow = tbody.querySelector('tr.line').cloneNode(true);
    let lineIndex = 1;

    // نهيّئ الصفّ الأصلي
    initializeSelect2InRow(tbody.querySelector('tr.line'));

    function initializeSelect2InRow(row) {
        const selectEl = row.querySelector('.account-select');
        // إذا كان مركّباً مسبقاً ندمّره
        if ($(selectEl).hasClass('select2-hidden-accessible')) {
            $(selectEl).select2('destroy');
        }
        // نطبّق Select2
        $(selectEl).select2({
            placeholder: 'اختر الحساب',
            allowClear: true,
            width: '100%',
            language: 'ar',
            dir: 'rtl'
        });
    }

    function updateTotals() {
        let sumD = 0, sumC = 0;
        tbody.querySelectorAll('tr.line').forEach(row => {
            sumD += parseFloat(row.querySelector('.debit-input').value) || 0;
            sumC += parseFloat(row.querySelector('.credit-input').value) || 0;
        });

        document.getElementById('total-debit').textContent = sumD.toFixed(2);
        document.getElementById('total-credit').textContent = sumC.toFixed(2);

        const diff = Math.abs(sumD - sumC).toFixed(2);
        const existingError = document.getElementById('totalsError');
        if (sumD !== sumC) {
            if (!existingError) {
                const errDiv = document.createElement('div');
                errDiv.id = 'totalsError';
                errDiv.className = 'text-danger mt-2 small';
                // الرسالة الجديدة تتضمن مقدار الفرق
                errDiv.textContent = `القيد غير متوازن بمقدار ${diff}`;
                tbody.parentElement.appendChild(errDiv);
            } else {
                // لو كانت الرسالة موجودة، حدّث مقدار الفرق
                existingError.textContent = `القيد غير متوازن بمقدار ${diff}`;
            }
            submitBtn.disabled = true;
        } else {
            if (existingError) existingError.remove();
            submitBtn.disabled = false;
        }
    }



    addBtn.addEventListener('click', () => {
        // 1. انسخ الصفّ
        const newRow = templateRow.cloneNode(true);

        // 2. صفِّر القيم واضبط الاسماء (name) و الـ id
        newRow.querySelectorAll('input').forEach(inp => {
            inp.classList.contains('comment-input') ?
                inp.value = '' :
                inp.value = '0.00';
        });
        newRow.querySelector('select.account-select').value = '';

        newRow.querySelectorAll('input, select').forEach(el => {
            // حدث الاسم
            const oldName = el.getAttribute('name') || '';
            const newName = oldName.replace(/\[lines\]\[\d+\]/, `[lines][${lineIndex}]`);
            el.setAttribute('name', newName);
            // إذا كان لدى العنصر id، حدّثه بنفس الطريقة
            if (el.id) {
                el.id = el.id.replace(/\[lines\]\[\d+\]/, `[lines][${lineIndex}]`);
            }
        });

        // 3. أضف الصفّ أولاً إلى الواجهة
        tbody.appendChild(newRow);

        // 4. أعد تهيئة كل قوائم الحساب غير المركّبة
        tbody.querySelectorAll('select.account-select').forEach(sel => {
            const $sel = $(sel);
            if (!$sel.hasClass('select2-hidden-accessible')) {
                $sel.select2({
                    placeholder: 'اختر الحساب',
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                });
            }
        });

        lineIndex++;
        updateTotals();
    });

    // حذف الصف
    tbody.addEventListener('click', e => {
        if (e.target.closest('.remove-line')) {
            const rows = tbody.querySelectorAll('tr.line');
            if (rows.length > 1) {
                e.target.closest('tr').remove();
                updateTotals();
            }
        }
    });

    // إعادة حساب المجاميع عند الإدخال
    tbody.addEventListener('input', e => {
        if (e.target.matches('.debit-input, .credit-input')) {
            updateTotals();
        }
    });

    // الحساب الأوّلي
    updateTotals();
});
