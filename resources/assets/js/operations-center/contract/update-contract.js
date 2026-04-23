function loadHijriDatePicker() {
    const script = document.createElement('script');
    script.src = '/assets/hijri-date/js/bootstrap-hijri-datetimepickermin.js';
    script.onload = initializeHijriPicker;
    document.head.appendChild(script);
}

function initializeHijriPicker() {
    $(".hijri-picker").hijriDatePicker({
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

// دالة موحدة لمسح جميع محررات Quill
function clearAllQuillEditors() {
    ['editor-technical-offer', 'editor-financial-offer', 'editor-supplement-preamble', 'editor-supplement-declaration'].forEach(id => {
        if (window.quillEditors && window.quillEditors[id]) {
            window.quillEditors[id].clipboard.dangerouslyPasteHTML('');
            let hiddenFieldId;
            switch (id) {
                case 'editor-technical-offer':
                    hiddenFieldId = 'technical_offer';
                    break;
                case 'editor-financial-offer':
                    hiddenFieldId = 'financial_offer';
                    break;
                case 'editor-supplement-preamble':
                    hiddenFieldId = 'supplement_preamble';
                    break;
                case 'editor-supplement-declaration':
                    hiddenFieldId = 'supplement_terms';
                    break;
            }

            if (hiddenFieldId && document.getElementById(hiddenFieldId)) {
                document.getElementById(hiddenFieldId).value = '';
            }
        }
    });
}

// 2) Quill editors
function initQuillEditors(initials) {
    window.quillEditors = {};
    initials.forEach(item => {
        const el = document.getElementById(item.editorId);
        if (!el) {
            console.warn(`Editor ${item.editorId} not found`);
            return;
        }

        const quill = new Quill(`#${item.editorId}`, {
            theme: 'snow',
            placeholder: 'اكتب هنا...',
            modules: {
                toolbar: item.toolbar || [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ direction: 'rtl' }],
                    [{ size: ['small', false, 'large', 'huge'] }],
                    [{ align: [] }],
                    ['link', 'image', 'video'], ['clean']
                ]
            }
        });
        if (item.initialContent) {
            quill.clipboard.dangerouslyPasteHTML(item.initialContent);
        }

        // set hidden input on load
        document.getElementById(item.hiddenInputId).value = quill.root.innerHTML;
        // update on change
        quill.on('text-change', () => {
            document.getElementById(item.hiddenInputId).value = quill.root.innerHTML;
        });
        window.quillEditors[item.editorId] = quill;
    });
}

// 3) Toggle main-contract (محدّث ليدعم حقول الملحق)
function toggleMainContractField(isUser) {
    const type = $('#contract_type').val();
    if (type === 'supplementary') {
        $('#main_contract_container').removeClass('d-none');
        $('#main_contract_id').attr('required', true);

        // إظهار حقول الملحق
        $('.supplement-field').removeClass('d-none');

        $('#offer_id').prop('disabled', true).trigger('change.select2');
        if (isUser) {
            $('#offer_id').val('').trigger('change.select2');
            $('#customer_id,#relationship_manager_id')
                .empty().prop('disabled', true).append('<option value=""></option>');
            clearAllQuillEditors();
        }
    } else {
        $('#main_contract_container').addClass('d-none');
        $('#main_contract_id').attr('required', false).val('').trigger('change.select2');

        // إخفاء حقول الملحق
        $('.supplement-field').addClass('d-none');

        $('#offer_id').prop('disabled', false).trigger('change.select2');
        $('#offer_id .dynamic-option').remove();
        if (isUser) {
            $('#offer_id').val('').trigger('change.select2');
            $('#customer_id,#relationship_manager_id')
                .empty().prop('disabled', true).append('<option value=""></option>');
            clearAllQuillEditors();
        }
    }
}

// 4) AJAX عند تغيير العرض
function bindOfferChange() {
    $('#offer_id').on('change', function () {
        const offerId = $(this).val();
        const template = $(this).attr('data-get-offer-details-route');
        if (!offerId || !template) {
            clearOfferFields();
            return;
        }

        const url = template.replace(':id', offerId);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                // customer
                $('#customer_id')
                    .empty().append(`<option>${data.customer.name}</option>`)
                    .prop('disabled', true);
                // rel-manager
                $('#relationship_manager_id')
                    .empty().append(`<option>${data.relationship_manager.name}</option>`)
                    .prop('disabled', true);

                // تحديث العرض الفني
                if (window.quillEditors['editor-technical-offer']) {
                    const techHtml = data.technical_offer || '';
                    window.quillEditors['editor-technical-offer']
                        .clipboard.dangerouslyPasteHTML(techHtml);
                    $('#technical_offer').val(techHtml);
                }

                // تحديث العرض المالي
                if (window.quillEditors['editor-financial-offer']) {
                    const finHtml = data.financial_offer || '';
                    window.quillEditors['editor-financial-offer']
                        .clipboard.dangerouslyPasteHTML(finHtml);
                    $('#financial_offer').val(finHtml);
                }
            },
            error: function (xhr) {
                console.error('Error fetching offer details:', xhr.status);
            }
        });
    });
}

function clearOfferFields() {
    $('#customer_id,#relationship_manager_id').empty();
    clearAllQuillEditors();
}

// 5) AJAX عند تغيير العقد الرئيسي (محدّث ليدعم حقول الملحق)
function bindMainContractChange() {
    $('#main_contract_id').on('change', function () {
        const id = $(this).val();
        if (!id) {
            clearMainContractFields();
            return;
        }

        $.getJSON(`/employees/operations-center/contracts/get-main-contract-details/${id}`, data => {
            // append offer
            $('#offer_id .dynamic-option').remove();
            $('#offer_id')
                .append(`<option class="dynamic-option" value="${data.offer.offer_id}">${data.offer.offer_name}</option>`)
                .val(data.offer.offer_id).trigger('change').trigger('change.select2');

            // customer & rel-manager
            $('#customer_id').empty().append(`<option>${data.customer.name}</option>`).prop('disabled', true);
            $('#relationship_manager_id').empty().append(`<option>${data.relationship_manager.name}</option>`).prop('disabled', true);

            // تحديث العرض الفني
            if (window.quillEditors['editor-technical-offer'] && data.technical_offer) {
                window.quillEditors['editor-technical-offer'].clipboard.dangerouslyPasteHTML(data.technical_offer);
                $('#technical_offer').val(data.technical_offer);
            }

            // تحديث العرض المالي
            if (window.quillEditors['editor-financial-offer'] && data.financial_offer) {
                window.quillEditors['editor-financial-offer'].clipboard.dangerouslyPasteHTML(data.financial_offer);
                $('#financial_offer').val(data.financial_offer);
            }

            // تحديث حقول الملحق إذا كانت متاحة
            if (window.quillEditors['editor-supplement-preamble'] && data.supplement_preamble) {
                window.quillEditors['editor-supplement-preamble'].clipboard.dangerouslyPasteHTML(data.supplement_preamble);
                $('#supplement_preamble').val(data.supplement_preamble);
            }

            if (window.quillEditors['editor-supplement-declaration'] && data.supplement_terms) {
                window.quillEditors['editor-supplement-declaration'].clipboard.dangerouslyPasteHTML(data.supplement_terms);
                $('#supplement_terms').val(data.supplement_terms);
            }
        }).fail(err => console.error(err));
    });
}

function clearMainContractFields() {
    $('#offer_id').val('').trigger('change').trigger('change.select2');
    $('#customer_id,#relationship_manager_id').empty().prop('disabled', true);
}

// 6) إضافة/حذف مرفقات
function bindAttachments() {
    let idx = 0;
    $('#attachment-fields').on('click', '#add-attachment', () => {
        const html = `
      <div class="row g-3 mb-3" data-index="${idx}">
        <div class="col-md-6">
          <input type="text" name="additional_attachments[${idx}][name]" class="form-control" placeholder="اسم المرفق" required>
        </div>
        <div class="col-md-5">
          <input type="file" name="additional_attachments[${idx}][file]" class="form-control" required>
        </div>
        <div class="col-md-1">
          <button type="button" class="btn btn-sm btn-danger remove-attachment">
           <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>`;
        $(html).insertBefore($('#add-attachment').closest('.mt-4'));
        idx++;
    });
    $('#attachment-fields').on('click', '.remove-attachment', function () {
        $(this).closest('.row').remove();
    });
    $('#attachment-fields').on('click', '.remove-existing-attachment', function () {
        const id = $(this).data('id');
        $('#power-form').append(`<input type="hidden" name="delete_attachments[]" value="${id}">`);
        $(this).closest('.existing-attachment').remove();
    });
}

// 7) وتشغيل الكل عند التحميل
document.addEventListener('DOMContentLoaded', () => {
    loadHijriDatePicker();

    // تصحيح أسماء المحررات والمتغيرات
    initQuillEditors([
        {
            editorId: 'editor-technical-offer',
            hiddenInputId: 'technical_offer',
            initialContent: window.initialTechnical || ''
        },
        {
            editorId: 'editor-financial-offer',
            hiddenInputId: 'financial_offer',
            initialContent: window.initialFinancial || ''
        },
        {
            editorId: 'editor-supplement-preamble', // تصحيح الاسم
            hiddenInputId: 'supplement_preamble',
            initialContent: window.supplementPreamble || '' // استخدام المتغير الصحيح
        },
        {
            editorId: 'editor-supplement-declaration', // تصحيح الاسم
            hiddenInputId: 'supplement_terms',
            initialContent: window.supplementDeclaration || '' // استخدام المتغير الصحيح
        }
    ]);

    toggleMainContractField(false);
    $('#contract_type').on('change', () => toggleMainContractField(true));

    bindOfferChange();
    bindMainContractChange();
    bindAttachments();

    // باقي الكود للدفعات...
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
        <div class="row payment-row g-3 align-items-end mb-3">
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

        $(`#percentage-col-${idx}`).toggle(isPerc);
        $(`#amount-col-${idx}`).toggle(!isPerc);

        if (!$(this).data('initialized')) {
            $(this).data('initialized', true);
            return;
        }

        $(`input[name="payments[${idx}][percentage]"]`).val('');
        $(`input[name="payments[${idx}][fixed_amount]"]`).val('');
    });
});
