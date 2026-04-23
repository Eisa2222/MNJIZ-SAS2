
// 1) تهيئة Hijri DatePicker بعد تحميل السكريبت
function initializeHijriPicker() {
    $('.hijri-picker').hijriDatePicker({
        hijri: true,
        showSwitcher: true,
        useCurrent: false,
        showClear: true,
        showTodayButton: true,
        showClose: true,
        todayBtn: true,
        todayHighlight: true,
    });
}

function loadHijriPickerScript() {
    const script = document.createElement('script');
    script.src = '/assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js';
    script.onload = initializeHijriPicker;
    document.head.appendChild(script);
}

// 2) مسح محتوى محرّرات Quill وإعادة الحقول المخفية
function clearQuill() {
    ['editor-technical-offer', 'editor-financial-offer', 'editor-supplement-preamble', 'editor-supplement-declaration'].forEach(id => {
        if (window.quillEditors && window.quillEditors[id]) {
            window.quillEditors[id].clipboard.dangerouslyPasteHTML('');
            let hidden;
            switch (id) {
                case 'editor-technical-offer':
                    hidden = 'technical_offer';
                    break;
                case 'editor-financial-offer':
                    hidden = 'financial_offer';
                    break;
                case 'editor-supplement-preamble':
                    hidden = 'supplement_preamble';
                    break;
                case 'editor-supplement-declaration':
                    hidden = 'supplement_terms';
                    break;
            }

            if (hidden && document.getElementById(hidden)) {
                document.getElementById(hidden).value = '';
            }
        }
    });
}

// 3) إظهار/إخفاء حقل العقد الرئيسي بناءً على نوع العقد
function toggleMainContractField() {
    const type = $('#contract_type').val();

    $('#offer_id').val('').trigger('change.select2');

    if (type === 'supplementary') {
        // إذا كان عقد ملحق
        $('#main_contract_container').removeClass('d-none');
        $('#main_contract_id').attr('required', true);

        // إظهار حقول الملحق
        $('.supplement-field').removeClass('d-none');

        // تعطيل اختيار العرض الأصلي
        $('#offer_id').prop('disabled', true).trigger('change.select2');

        // مسح العملاء ومسؤولي العلاقات
        $('#customer_id, #relationship_manager_id')
            .empty()
            .prop('disabled', true)
            .append('<option value=""></option>');

        clearQuill();
    } else {
        // إذا كان عقد رئيسي
        $('#main_contract_container').addClass('d-none');
        $('#main_contract_id')
            .attr('required', false)
            .val('')
            .trigger('change.select2');

        // إخفاء حقول الملحق
        $('.supplement-field').addClass('d-none');

        // تمكين اختيار العرض الأصلي
        $('#offer_id')
            .prop('disabled', false)
            .trigger('change.select2');

        // إزالة الخيارات الديناميكية فقط
        $('#offer_id .dynamic-option').remove();

        // مسح العملاء ومسؤولي العلاقات
        $('#customer_id, #relationship_manager_id')
            .empty()
            .prop('disabled', true)
            .append('<option value=""></option>');

        clearQuill();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // اجعل moment متاحًا عالميًا إذا لزم الأمر
    window.moment = moment;

    // تهيئة Hijri Picker
    loadHijriPickerScript();

    // 4) تهيئة محرّرات Quill
    const editors = [
        { editorId: 'editor-technical-offer', hiddenInputId: 'technical_offer' },
        { editorId: 'editor-financial-offer', hiddenInputId: 'financial_offer' },
        { editorId: 'editor-supplement-preamble', hiddenInputId: 'supplement_preamble' },
        { editorId: 'editor-supplement-declaration', hiddenInputId: 'supplement_terms' },
    ];
    window.quillEditors = {};
    editors.forEach(item => {
        const el = document.getElementById(item.editorId);
        if (!el) return console.error(`Editor ${item.editorId} not found`);
        const quill = new Quill(`#${item.editorId}`, {
            theme: 'snow',
            placeholder: 'اكتب هنا...',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ indent: '-1' }, { indent: '+1' }],
                    [{ direction: 'rtl' }],
                    [{ size: ['small', false, 'large', 'huge'] }],
                    [{ align: [] }],
                    [{ color: [] }, { background: [] }],
                    ['link', 'image', 'video'],
                    ['clean'],
                ]
            }
        });
        const hidden = document.getElementById(item.hiddenInputId);
        // إذا كان هناك محتوى سابق
        if (hidden.value) {
            quill.clipboard.dangerouslyPasteHTML(hidden.value);
        }

        quill.on('text-change', () => {
            hidden.value = quill.root.innerHTML;
        });
        window.quillEditors[item.editorId] = quill;
    });

    // 5) عند تغيير العرض

    const $offer = $('#offer_id');
    const template = $offer.data('get-offer-details-route');

    $offer.on('change', function () {
        const id = $(this).val();
        if (!id) {
            // مسح الحقول ومحرّرات Quill
            $('#customer_id, #relationship_manager_id')
                .empty().prop('disabled', true)
                .append('<option value=""></option>');
            clearQuill();
            return;
        }

        // استبدل :id بالمعرّف الحقيقي
        const url = template.replace(':id', id);

        $.ajax({
            url,
            method: 'GET',
            dataType: 'json',
            success(data) {
                // املأ الحقول كما تريد…
                $('#customer_id').empty()
                    .append('<option value=""> العميل</option>')
                    .append(`<option value="${data.customer.id}" selected>${data.customer.name}</option>`)
                    .prop('disabled', true);

                $('#relationship_manager_id').empty()
                    .append('<option value=""> مسؤول العلاقات</option>')
                    .append(`<option value="${data.relationship_manager.id}" selected>${data.relationship_manager.name}</option>`)
                    .prop('disabled', true);

                if (data.technical_offer && window.quillEditors['editor-technical-offer']) {
                    window.quillEditors['editor-technical-offer']
                        .clipboard.dangerouslyPasteHTML(data.technical_offer);
                    $('#technical_offer').val(data.technical_offer);
                }

                if (data.financial_offer && window.quillEditors['editor-financial-offer']) {
                    window.quillEditors['editor-financial-offer']
                        .clipboard.dangerouslyPasteHTML(data.financial_offer);
                    $('#financial_offer').val(data.financial_offer);
                }
            },
            error(xhr, status, error) {
                console.error('Error fetching offer details:', error);
            }
        });
    });

    // 6) عند تغيير العقد الرئيسي
    $('#main_contract_id').on('change', function () {
        const mainId = $(this).val();
        if (!mainId) {
            $('#offer_id').val('').trigger('change.select2');
            $('#customer_id, #relationship_manager_id')
                .empty().prop('disabled', true).append('<option value=""></option>');
            clearQuill();
            return;
        }

        $.ajax({
            url: `/employees/operations-center/contracts/get-main-contract-details/${mainId}`,
            type: 'GET',
            dataType: 'json',
            success(data) {
                $('#offer_id .dynamic-option').remove();
                $('#offer_id')
                    .append(`<option class="dynamic-option" value="${data.offer.offer_id}" selected>${data.offer.offer_name}</option>`)
                    .trigger('change.select2');

                $('#customer_id')
                    .empty()
                    .append(`<option value="${data.customer.id}">${data.customer.name}</option>`)
                    .prop('disabled', true);

                $('#relationship_manager_id')
                    .empty()
                    .append(`<option value="${data.relationship_manager.id}">${data.relationship_manager.name}</option>`)
                    .prop('disabled', true);

                if (data.technical_offer && window.quillEditors['editor-technical-offer']) {
                    window.quillEditors['editor-technical-offer']
                        .clipboard.dangerouslyPasteHTML(data.technical_offer);
                    $('#technical_offer').val(data.technical_offer);
                }

                if (data.financial_offer && window.quillEditors['editor-financial-offer']) {
                    window.quillEditors['editor-financial-offer']
                        .clipboard.dangerouslyPasteHTML(data.financial_offer);
                    $('#financial_offer').val(data.financial_offer);
                }

                if (data.supplement_preamble && window.quillEditors['editor-supplement-preamble']) {
                    window.quillEditors['editor-supplement-preamble']
                        .clipboard.dangerouslyPasteHTML(data.supplement_preamble);
                    $('#supplement_preamble').val(data.supplement_preamble);
                }

                if (data.supplement_terms && window.quillEditors['editor-supplement-declaration']) {
                    window.quillEditors['editor-supplement-declaration']
                        .clipboard.dangerouslyPasteHTML(data.supplement_terms);
                    $('#supplement_terms').val(data.supplement_terms);
                }
            },
            error(xhr, status, error) {
                console.error('Error fetching main contract details:', error);
            }
        });
    });

    // 7) إضافة/حذف مرفقات إضافية
    let attachmentIndex = 0;
    $('#add-attachment').on('click', () => {
        const html = `
      <div class="row g-3 mb-3" data-index="${attachmentIndex}">
        <div class="col-md-6">
          <input type="text" name="additional_attachments[${attachmentIndex}][name]" class="form-control" placeholder="اسم المرفق" required>
        </div>
        <div class="col-md-5">
          <input type="file" name="additional_attachments[${attachmentIndex}][file]" class="form-control" required>
        </div>
        <div class="col-md-1">
          <button type="button" class="btn btn-sm btn-danger remove-attachment">
           <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>`;
        $('#additional-attachments-container').append(html);
        attachmentIndex++;
    });
    $(document).on('click', '.remove-attachment', function () {
        $(this).closest('.row').remove();
    });

    // 8) ضبط ظهور الحقل عند التحميل والموافقة على التغيير
    toggleMainContractField();

    $('#contract_type').on('change', toggleMainContractField);

    const $container = $('#payment-rows');

    function initSelect2(scope = document) {
        $(scope).find('.select2').select2({
            allowClear: true,
            width: '100%',
            language: 'ar',
            dir: 'rtl'
        });
    }

    initSelect2();

    function buildSelect(options, name, selectedValue, extraClasses = '') {
        const $sel = $(`<select name="${name}" class="form-select select2 ${extraClasses}" data-placeholder="اختر"></select>`);
        $sel.append('<option value=""></option>');
        options.forEach(opt => {
            const selected = opt.id === selectedValue ? ' selected' : '';
            $sel.append(`<option value="${opt.id}"${selected}>${opt.name}</option>`);
        });
        return $sel;
    }

    $('#add-payment-btn').on('click', function () {
        const idx = $container.find('.payment-row').length;

        const $row = $(`
        <div class="row payment-row g-3 align-items-end">
            <div class="col-md-3 p-2 calculation-col">
                <label class="form-label">طريقة الحساب</label>
            </div>
            <div class="col-md-3 p-2 payment-batch-col">
                <label class="form-label">نوع الدفعة</label>
            </div>
            <div class="col-md-3 p-2 percentage-col" id="percentage-col-${idx}" style="display:none;">
                <label class="form-label">النسبة %</label>
                <input type="text" name="payments[${idx}][percentage]" class="form-control">
            </div>
            <div class="col-md-3 p-2 amount-col" id="amount-col-${idx}">
                <label class="form-label">المبلغ</label>
                <input type="text" name="payments[${idx}][fixed_amount]" class="form-control">
            </div>
            <div class="col-md-2 p-2">
                <label class="form-label">تاريخ الاستحقاق</label>
                <input type="date" name="payments[${idx}][due_date]" class="form-control">
            </div>
            <div class="col-md-1 p-2">
                <button type="button" class="btn btn-sm btn-danger remove-payment-btn" title="حذف الدفعة">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
    `);

        // 2) ندرج select النوع
        const $calcSel = buildSelect(
            window.EnumOptions.calculationType,
            `payments[${idx}][calculation_type]`,
            'fixed',
            'form-select select2 payment-type'
        ).attr('data-index', idx);

        $row.find('.calculation-col').append($calcSel);

        const $batchSel = buildSelect(
            window.EnumOptions.paymentBatchType,
            `payments[${idx}][payment_batch_type]`,
            'fixed',
            'form-select select2'
        ).attr('data-index', idx);

        $row.find('.payment-batch-col').append($batchSel);

        $container.append($row);
        initSelect2($row);
    });

    $(document).on('click', '.remove-payment-btn', function () {
        const $row = $(this).closest('.payment-row');
        const oldIdx = parseInt($row.find('.payment-type').data('index'), 10);
        const fv = window.paymentStepFv;

        ['calculation_type', 'payment_batch_type', 'due_date', 'percentage', 'fixed_amount']
            .forEach(field => {
                const name = `payments[${oldIdx}][${field}]`;
                try {
                    fv.removeField(name);
                } catch (e) {
                }
            });

        $row.remove();

        window.addDynamicPaymentValidation(fv);

        try { fv.revalidateField('payment_validation'); } catch (_) { }
    });

    $container.on('change', '.payment-type', function () {
        const idx = $(this).data('index');
        const isPerc = $(this).val() === 'percentage';

        // إظهار/إخفاء الأعمدة فقط
        $(`#percentage-col-${idx}`).toggle(isPerc);
        $(`#amount-col-${idx}`).toggle(!isPerc);

        // إذا ما كان هذا أول تحميل، علّمنا وتجاهل المسح
        if (!$(this).data('initialized')) {
            $(this).data('initialized', true);
            return;
        }

        $(`input[name="payments[${idx}][percentage]"]`).val('');
        $(`input[name="payments[${idx}][fixed_amount]"]`).val('');
    });
});
