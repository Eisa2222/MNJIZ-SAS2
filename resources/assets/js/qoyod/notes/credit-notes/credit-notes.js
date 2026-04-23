/* ============ وظائف إدارة البنود في جدول واحد مع ترويسة محسنة ============ */
let itemCounter = 0;
function initGlobalSelect2() {
    ['#contact_id', '#parent_id', '#inventory_id', '#status'].forEach(function (sel) {
        const $el = $(sel);
        if ($el.length && !$el.hasClass('select2-hidden-accessible')) {
            $el.select2({
                dropdownParent: $('#wizard-validation'),
                placeholder: ' ',
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // إظهار رسالة الفراغ في البداية
    const emptyMessage = document.getElementById('emptyMessage');
    if (emptyMessage) {
        emptyMessage.style.display = 'block';
    }

    addNewItem();
    // إضافة مستمع للزر الرئيسي
    const addButton = document.getElementById('addLineItem');
    if (addButton) {
        addButton.addEventListener('click', addNewItem);
    }
});

function addNewItem() {
    itemCounter++;
    const tbody = document.querySelector('#lineItemsTable tbody');
    const emptyMessage = document.getElementById('emptyMessage');

    // الصف الأول - البيانات الأساسية
    const row1 = document.createElement('tr');
    row1.setAttribute('data-item', itemCounter);
    row1.innerHTML = `
        <td rowspan="3" class="merged-number-cell">${itemCounter}</td>
        <td>
            <select id="product_id_${itemCounter}" name="line_items[${itemCounter}][product_id]"
           class="select2 form-select product-select" required>
       <option value="">اختر منتجاً</option>
       ${productOptions}
   </select>
        </td>
        <td>
            <input type="text" name="line_items[${itemCounter}][description]" 
                   class="form-control" placeholder="الوصف">
        </td>
        <td>
            <input type="number" name="line_items[${itemCounter}][quantity]" 
                   class="form-control text-center quantity-input" 
                   min="1" step="1" value="1" required>
        </td>
        <td>
            <input type="number" name="line_items[${itemCounter}][unit_price]" 
                   class="form-control text-center price-input" 
                   min="0" step="0.01" value="0.00" required>
        </td>
        <td>
            <div class="input-group">
                <input type="number" name="line_items[${itemCounter}][discount]" 
                       class="form-control text-center discount-input  mb-1" 
                       min="0" step="0.01" value="0.00" 
                       placeholder="قيمة الخصم">
                <select name="line_items[${itemCounter}][discount_type]" 
                        class="select2 form-select discount-type-select" style="max-width: 80px;">
                    <option value="amount" selected>قيمة</option>
                    <option value="percentage">نسبة</option>
                </select>
            </div>
        </td>
        <td rowspan="3" class="merged-action-cell">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove" 
                    onclick="removeItem(this)" title="حذف">
                <i class="ti ti-trash ti-xs"></i>
            </button>
        </td>
    `;

    // الصف الثاني - ترويسة الحقول المحسوبة (محسن ومُوضح أكثر)
    const row2 = document.createElement('tr');
    row2.setAttribute('data-item', itemCounter);
    row2.className = 'item-separator';
    row2.style.backgroundColor = '#f8f9fa';
    row2.style.borderTop = '2px solid #dee2e6';
    row2.innerHTML = `
        <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-percentage me-1" style="font-size: 0.75rem;"></i>
            نوع الضريبة
        </th>
        <th colspan="1" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-calculator me-1" style="font-size: 0.75rem;"></i>
            المبلغ قبل الضريبة
        </th>
        <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-receipt-tax me-1" style="font-size: 0.75rem;"></i>
            مبلغ الضريبة
        </th>
        <th colspan="2" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
            <i class="ti ti-coin me-1" style="font-size: 0.75rem;"></i>
            المبلغ الإجمالي
        </th>
    `;

    // الصف الثالث - النتائج والضرائب
    const row3 = document.createElement('tr');
    row3.setAttribute('data-item', itemCounter);
    row3.className = 'item-results';
    row3.style.borderBottom = '2px solid #dee2e6';
    row3.innerHTML = `
        <td style="padding: 10px;">
            <select name="line_items[${itemCounter}][tax_percent]" class="select2 form-select tax-select" 
                    style="font-size: 0.9rem;">
                <option value="15" data-rate="15">ضريبة 15%</option>
                <option value="0" data-rate="0">ضريبة 0%</option>
            </select>
        </td>
        <td colspan="1" class="text-center" style="padding: 10px;">
            <div class="subtotal-cell calculated-field" 
                 style="background-color: #e3f2fd; padding: 8px; border-radius: 4px; font-weight: 500; color: #1976d2;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${itemCounter}][subtotal]" value="0.00">
        </td>
        <td class="text-center" style="padding: 10px;">
            <div class="tax-amount-cell calculated-field" 
                 style="background-color: #fff3e0; padding: 8px; border-radius: 4px; font-weight: 500; color: #f57c00;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${itemCounter}][tax_amount]" value="0.00">
        </td>
        <td colspan="2" class="text-center" style="padding: 10px;">
            <div class="total-cell calculated-field fw-bold" 
                 style="background-color: #e8f5e8; padding: 8px; border-radius: 4px; font-weight: bold; color: #2e7d32;">
                0.00 <span class="icon-saudi_riyal"></span>
            </div>
             <input type="hidden" name="line_items[${itemCounter}][total]" value="0.00">
        </td>
    `;

    // إضافة صف فاصل بصري بين البنود
    const separatorRow = document.createElement('tr');
    separatorRow.setAttribute('data-item', itemCounter);
    separatorRow.className = 'item-visual-separator';
    separatorRow.innerHTML = `
        <td colspan="8" style="height: 20px; background-color: #ffffff; border: none;">
            <div style="text-align: center; color: #dee2e6; font-size: 1rem; line-height: 20px;">
                ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥
            </div>
        </td>
    `;

    // إضافة الصفوف إلى الجدول
    tbody.appendChild(row1);
    tbody.appendChild(row2);
    tbody.appendChild(row3);
    tbody.appendChild(separatorRow);

    // إخفاء رسالة الفراغ
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }

    // إضافة مستمعي الأحداث للبند الجديد
    addRowEventListeners(itemCounter);

    // إعادة تهيئة Select2 إذا كان متوفراً
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            dropdownParent: $('#wizard-validation'),
            placeholder: ' ',
            allowClear: true,
            width: '100%',
            language: 'ar',
            dir: 'rtl'
        });
    }

    // حساب المجاميع
    calculateTotals();
}

// جعل الدالة global للوصول إليها من HTML
window.removeItem = function (button) {
    const totalItems = new Set(
        Array.from(document.querySelectorAll('tr[data-item]')).map(row => row.getAttribute('data-item'))
    ).size;

    if (totalItems <= 1) return;
    const row = button.closest('tr');
    const itemNumber = row.getAttribute('data-item');

    if (!itemNumber) return;

    // البحث عن جميع الصفوف المرتبطة بهذا البند (بما في ذلك الفاصل البصري)
    const allRows = document.querySelectorAll(`tr[data-item="${itemNumber}"]`);

    // حذف جميع الصفوف المرتبطة
    allRows.forEach(r => {
        if (r.parentNode) {
            r.parentNode.removeChild(r);
        }
    });

    // إعادة ترقيم البنود
    renumberItems();

    // التحقق من وجود بنود وإظهار رسالة الفراغ إذا لزم الأمر
    checkEmptyState();

    // حساب المجاميع
    calculateTotals();
}

function renumberItems() {
    const tbody = document.querySelector('#lineItemsTable tbody');
    if (!tbody) return;

    const allRows = Array.from(tbody.children);
    let newCounter = 0;

    // تجميع الصفوف حسب البند
    const itemGroups = {};

    allRows.forEach(row => {
        const itemAttr = row.getAttribute('data-item');
        if (itemAttr) {
            if (!itemGroups[itemAttr]) {
                itemGroups[itemAttr] = [];
            }
            itemGroups[itemAttr].push(row);
        }
    });

    // إعادة ترقيم كل مجموعة
    Object.keys(itemGroups).forEach(oldItemNumber => {
        newCounter++;
        const rows = itemGroups[oldItemNumber];

        rows.forEach(row => {
            // تحديث data-item
            row.setAttribute('data-item', newCounter);

            // تحديث رقم البند في الخلية المدمجة
            const numberCell = row.querySelector('.merged-number-cell');
            if (numberCell) {
                numberCell.textContent = newCounter;
            }

            // تحديث أسماء الحقول
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('[')) {
                    const newName = name.replace(/\[\d+\]/, `[${newCounter}]`);
                    input.setAttribute('name', newName);
                }
            });
        });

        // إعادة ربط مستمعي الأحداث للبند المُعاد ترقيمه
        reattachEventListeners(newCounter);
    });

    // تحديث العداد العام
    itemCounter = newCounter;
}

// دالة جديدة لإعادة ربط مستمعي الأحداث
function reattachEventListeners(itemNumber) {
    // البحث عن الصفوف المرتبطة بهذا البند
    const itemRows = document.querySelectorAll(`tr[data-item="${itemNumber}"]`);

    if (itemRows.length < 3) return;

    const mainRow = itemRows[0]; // الصف الأول - البيانات الأساسية
    const resultsRow = itemRows[2]; // الصف الثالث - النتائج

    // إزالة مستمعي الأحداث القديمة أولاً
    const elements = mainRow.querySelectorAll('.quantity-input, .price-input, .discount-input, .product-select, .discount-type-select');
    const taxSelect = resultsRow.querySelector('.tax-select');

    // إزالة event listeners القديمة بنسخها وإعادة إدراجها
    elements.forEach(element => {
        const newElement = element.cloneNode(true);
        element.parentNode.replaceChild(newElement, element);
    });

    if (taxSelect) {
        const newTaxSelect = taxSelect.cloneNode(true);
        taxSelect.parentNode.replaceChild(newTaxSelect, taxSelect);
    }

    // إعادة إضافة مستمعي الأحداث الجديدة
    addRowEventListeners(itemNumber);
}

function checkEmptyState() {
    const tbody = document.querySelector('#lineItemsTable tbody');
    const emptyMessage = document.getElementById('emptyMessage');

    if (tbody && emptyMessage) {
        const hasRows = tbody.children.length > 0;
        emptyMessage.style.display = hasRows ? 'none' : 'block';
    }
}

function addRowEventListeners(itemNumber) {
    // البحث عن الصفوف المرتبطة بهذا البند
    const itemRows = document.querySelectorAll(`tr[data-item="${itemNumber}"]`);

    if (itemRows.length < 3) return;

    const mainRow = itemRows[0]; // الصف الأول - البيانات الأساسية
    const resultsRow = itemRows[2]; // الصف الثالث - النتائج

    // مستمع تغيير المنتج
    const productSelect = mainRow.querySelector('.product-select');
    if (productSelect) {
        productSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || '0.00';
            const priceInput = mainRow.querySelector('.price-input');
            if (priceInput) {
                priceInput.value = price;
                calculateItemTotal(itemNumber);
            }
        });
    }

    // مستمعي تغيير الحقول الحسابية
    const calculationInputs = mainRow.querySelectorAll('.quantity-input, .price-input, .discount-input');
    calculationInputs.forEach(input => {
        input.addEventListener('input', () => {
            validateDiscountInput(itemNumber);
            calculateItemTotal(itemNumber);
        });
        input.addEventListener('change', () => {
            validateDiscountInput(itemNumber);
            calculateItemTotal(itemNumber);
        });
    });

    // مستمع تغيير نوع الخصم
    const discountTypeSelect = mainRow.querySelector('.discount-type-select');
    if (discountTypeSelect) {
        discountTypeSelect.addEventListener('change', () => {
            validateDiscountInput(itemNumber);
            calculateItemTotal(itemNumber);
        });
    }

    // مستمع تغيير الضريبة - تم إصلاح المشكلة هنا
    const taxSelect = resultsRow.querySelector('.tax-select');
    if (taxSelect) {
        taxSelect.addEventListener('change', () => {
            calculateItemTotal(itemNumber);
        });
    }


}

// دالة التحقق من صحة الخصم
function validateDiscountInput(itemNumber) {
    const itemRows = document.querySelectorAll(`tr[data-item="${itemNumber}"]`);
    if (itemRows.length < 1) return;

    const mainRow = itemRows[0];
    const quantityInput = mainRow.querySelector('.quantity-input');
    const priceInput = mainRow.querySelector('.price-input');
    const discountInput = mainRow.querySelector('.discount-input');
    const discountTypeSelect = mainRow.querySelector('.discount-type-select');

    if (!quantityInput || !priceInput || !discountInput || !discountTypeSelect) return;

    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(priceInput.value) || 0;
    const discountValue = parseFloat(discountInput.value) || 0;
    const discountType = discountTypeSelect.value;

    const totalBeforeDiscount = quantity * unitPrice;

    // التحقق من القيم السالبة
    if (discountValue < 0) {
        discountInput.value = 0;
        showValidationMessage(discountInput, 'لا يمكن أن يكون الخصم بالسالب', 'error');
        return;
    }

    // التحقق بناءً على نوع الخصم
    if (discountType === 'percentage') {
        // التحقق من النسبة
        if (discountValue > 100) {
            discountInput.value = 100;
            showValidationMessage(discountInput, 'لا يمكن أن تتجاوز نسبة الخصم 100%', 'warning');
        }
    } else {
        // التحقق من القيمة
        if (discountValue > totalBeforeDiscount) {
            discountInput.value = totalBeforeDiscount.toFixed(2);
            showValidationMessage(discountInput, 'لا يمكن أن يتجاوز الخصم المبلغ الإجمالي', 'warning');
        }
    }
}

// دالة لإظهار رسائل التحقق
function showValidationMessage(element, message, type) {
    // إزالة الرسائل السابقة
    const existingMessage = element.parentNode.querySelector('.validation-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    // إنشاء رسالة جديدة
    const messageDiv = document.createElement('div');
    messageDiv.className = `validation-message small mt-1 text-${type === 'error' ? 'danger' : 'warning'}`;
    messageDiv.textContent = message;

    // إضافة الرسالة
    element.parentNode.appendChild(messageDiv);

    // إزالة الرسالة بعد 3 ثوانٍ
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 3000);
}

function calculateItemTotal(itemNumber) {
    const itemRows = document.querySelectorAll(`tr[data-item="${itemNumber}"]`);

    if (itemRows.length < 3) return;

    const mainRow = itemRows[0];
    const resultsRow = itemRows[2];

    // الحصول على القيم
    const quantityInput = mainRow.querySelector('.quantity-input');
    const priceInput = mainRow.querySelector('.price-input');
    const discountInput = mainRow.querySelector('.discount-input');
    const discountTypeSelect = mainRow.querySelector('.discount-type-select');
    const taxSelect = resultsRow.querySelector('.tax-select');

    if (!quantityInput || !priceInput || !discountInput || !discountTypeSelect || !taxSelect) {
        return;
    }

    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(priceInput.value) || 0;
    const discountValue = parseFloat(discountInput.value) || 0;
    const discountType = discountTypeSelect.value;

    // الحصول على نسبة الضريبة
    const selectedOption = taxSelect.options[taxSelect.selectedIndex];
    const taxRate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;


    // حساب المبلغ قبل الخصم
    const totalBeforeDiscount = quantity * unitPrice;

    // حساب الخصم
    let discountAmount;
    if (discountType === 'percentage') {
        discountAmount = totalBeforeDiscount * (discountValue / 100);
    } else {
        discountAmount = discountValue;
    }

    // التأكد من أن الخصم لا يتجاوز المبلغ الإجمالي
    discountAmount = Math.min(discountAmount, totalBeforeDiscount);

    let subtotal, taxAmount, total;


    // إذا كان السعر غير شامل للضريبة (الحالة العادية)
    subtotal = Math.max(0, totalBeforeDiscount - discountAmount);
    taxAmount = subtotal * (taxRate / 100);
    total = subtotal + taxAmount;

    // تحديث العرض في صف النتائج
    const subtotalCell = resultsRow.querySelector('.subtotal-cell');
    const taxAmountCell = resultsRow.querySelector('.tax-amount-cell');
    const totalCell = resultsRow.querySelector('.total-cell');

    // تحديث الحقول المخفية
    const subtotalHidden = resultsRow.querySelector('input[name$="[subtotal]"]');
    const taxHidden = resultsRow.querySelector('input[name$="[tax_amount]"]');
    const totalHidden = resultsRow.querySelector('input[name$="[total]"]');

    if (subtotalCell) subtotalCell.innerHTML = `${subtotal.toFixed(2)} <span class="icon-saudi_riyal"></span>`;
    if (taxAmountCell) taxAmountCell.innerHTML = `${taxAmount.toFixed(2)} <span class="icon-saudi_riyal"></span>`;
    if (totalCell) totalCell.innerHTML = `${total.toFixed(2)} <span class="icon-saudi_riyal"></span>`;

    if (subtotalHidden) subtotalHidden.value = subtotal.toFixed(2);
    if (taxHidden) taxHidden.value = taxAmount.toFixed(2);
    if (totalHidden) totalHidden.value = total.toFixed(2);

    // حساب المجاميع العامة
    calculateTotals();
}

function calculateTotals() {
    let totalSubtotal = 0;
    let totalTax = 0;
    let grandTotal = 0;

    // البحث عن جميع صفوف النتائج
    const resultRows = document.querySelectorAll('tr.item-results');

    resultRows.forEach(row => {
        const subtotalCell = row.querySelector('.subtotal-cell');
        const taxCell = row.querySelector('.tax-amount-cell');

        if (subtotalCell && taxCell) {
            const subtotalText = subtotalCell.textContent || '0.00 ';
            const taxText = taxCell.textContent || '0.00 ';

            // إزالة رمز العملة من النص
            const subtotal = parseFloat(subtotalText.replace(/[^\d.-]/g, '')) || 0;
            const tax = parseFloat(taxText.replace(/[^\d.-]/g, '')) || 0;

            totalSubtotal += subtotal;
            totalTax += tax;
        }
    });

    grandTotal = totalSubtotal + totalTax;

    // تحديث عرض المجاميع
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const taxDisplay = document.getElementById('taxDisplay');
    const totalDisplay = document.getElementById('totalDisplay');

    if (subtotalDisplay) subtotalDisplay.innerHTML = `${totalSubtotal.toFixed(2)} <span class="icon-saudi_riyal"></span>`;
    if (taxDisplay) taxDisplay.innerHTML = `${totalTax.toFixed(2)} <span class="icon-saudi_riyal"></span>`;
    if (totalDisplay) totalDisplay.innerHTML = `${grandTotal.toFixed(2)} <span class="icon-saudi_riyal"></span>`;
}

// مستمع jQuery للمنتجات (للتوافق مع الكود الموجود)
if (typeof $ !== 'undefined') {
    $(document).on('change', 'select[name$="[product_id]"]', function () {
        const price = $(this).find(':selected').data('price');
        if (price) {
            const row = $(this).closest('tr');
            const priceInput = row.find('.price-input');
            priceInput.val(price);

            // الحصول على رقم البند وحساب المجموع
            const itemNumber = row.data('item');
            if (itemNumber) {
                calculateItemTotal(itemNumber);
            }
        }
    });

    // مستمع إضافي للضريبة باستخدام jQuery للتأكد
    $(document).on('change', 'select[name$="[tax_percent]"]', function () {
        const row = $(this).closest('tr');
        const itemNumber = row.data('item');
        if (itemNumber) {
            calculateItemTotal(itemNumber);
        }
    });

    $(document).on('change', 'select[name$="[discount_type]"]', function () {
        const row = $(this).closest('tr');
        const itemNumber = row.data('item');
        if (itemNumber) {
            calculateItemTotal(itemNumber);
        }
    });

    // 
    // 
    // 
    // 

    // إضافة هذا الكود في نهاية ملف credit-notes.js (استبدل الجزء الخاص بجلب البنود)

    $(document).ready(function () {
        initGlobalSelect2();

        // مستمع تغيير العميل (إذا لم يكن موجوداً بالفعل)
        $('#contact_id').on('change', function () {
            let contactId = $(this).val();
            if (!contactId) {
                $('#parent_id').html('<option value="">اختر الفاتورة</option>').trigger('change');
                return;
            }

            $('#parent_id').html('<option selected disabled>جارٍ تحميل الفواتير...</option>');
            $('#loadingIndicator').show();

            // استخدم نفس الـ route الموجود في الكود الأصلي
            $.ajax({
                url: `/qoyod/invoices/get-invoices-by-customer/${contactId}`,
                type: 'GET',
                success: function (data) {
                    let options = '<option value="">اختر الفاتورة</option>';
                    $('#loadingIndicator').hide();

                    data.forEach(invoice => {
                        options +=
                            `<option value="${invoice.id}">فاتورة  ${invoice.reference}</option>`;
                    });
                    $('#parent_id').html(options).trigger('change');
                },
                error: function () {
                    $('#loadingIndicator').hide();
                    $('#parent_id').html(
                        '<option selected disabled>تعذر جلب الفواتير</option>').trigger(
                            'change');
                    toastr.error('فشل في جلب الفواتير لهذا العميل');
                }
            });
        });

        // مستمع تغيير الفاتورة المحددة - الكود الجديد
        $('#parent_id').on('change', function () {
            let invoiceId = $(this).val();

            console.log('تم اختيار فاتورة:', invoiceId); // للتأكد من أن المستمع يعمل

            if (!invoiceId) {
                // إذا لم يتم اختيار فاتورة، مسح البنود وإضافة بند فارغ
                clearAllLineItems();
                setTimeout(() => {
                    addNewItem();
                }, 100);
                return;
            }

            // إظهار مؤشر التحميل للبنود
            showLineItemsLoading();

            // استخدم route بسيط للاختبار
            const url = `/qoyod/invoices/${invoiceId}/line-items`;

            console.log('URL للطلب:', url); // للتأكد من الـ URL

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    console.log('استجابة الخادم الكاملة:', response);
                    console.log('نوع البيانات:', typeof response);

                    // التحقق من وجود البيانات بطرق مختلفة
                    let lineItems = null;

                    // الحالة الأولى: الاستجابة تحتوي على data
                    if (response && response.data && Array.isArray(response.data)) {
                        lineItems = response.data;
                        console.log('تم العثور على البيانات في response.data');
                    }
                    // الحالة الثانية: الاستجابة تحتوي على line_items مباشرة
                    else if (response && response.line_items && Array.isArray(response.line_items)) {
                        lineItems = response.line_items;
                        console.log('تم العثور على البيانات في response.line_items');
                    }
                    // الحالة الثالثة: الاستجابة تحتوي على invoice.line_items
                    else if (response && response.invoice && response.invoice.line_items && Array.isArray(response.invoice.line_items)) {
                        lineItems = response.invoice.line_items;
                        console.log('تم العثور على البيانات في response.invoice.line_items');
                    }
                    // الحالة الرابعة: الاستجابة هي array مباشرة
                    else if (response && Array.isArray(response)) {
                        lineItems = response;
                        console.log('الاستجابة هي array مباشرة');
                    }
                    // الحالة الخامسة: البحث العميق في البنية
                    else if (response) {
                        console.log('البحث عن البيانات في خصائص أخرى...');
                        console.log('جميع خصائص الاستجابة:', Object.keys(response));

                        // البحث في المستوى الأول
                        for (let key in response) {
                            if (Array.isArray(response[key]) && response[key].length > 0) {
                                const firstItem = response[key][0];
                                if (firstItem && (firstItem.product_id || firstItem.quantity || firstItem.unit_price)) {
                                    lineItems = response[key];
                                    console.log(`تم العثور على البيانات في response.${key}`);
                                    break;
                                }
                            }
                            // البحث في المستوى الثاني
                            else if (response[key] && typeof response[key] === 'object') {
                                console.log(`فحص ${key}:`, Object.keys(response[key]));
                                for (let subKey in response[key]) {
                                    if (Array.isArray(response[key][subKey]) && response[key][subKey].length > 0) {
                                        const firstSubItem = response[key][subKey][0];
                                        if (firstSubItem && (firstSubItem.product_id || firstSubItem.quantity || firstSubItem.unit_price || firstSubItem.item_id)) {
                                            lineItems = response[key][subKey];
                                            console.log(`تم العثور على البيانات في response.${key}.${subKey}`);
                                            break;
                                        }
                                    }
                                }
                                if (lineItems) break;
                            }
                        }
                    }

                    console.log('البيانات المستخرجة:', lineItems);
                    console.log('عدد البنود:', lineItems ? lineItems.length : 'undefined');

                    if (lineItems && Array.isArray(lineItems) && lineItems.length > 0) {
                        console.log('تم العثور على بنود:', lineItems.length);
                        console.log('البند الأول:', lineItems[0]);

                        // جلب البنود بنجاح
                        populateLineItemsFromInvoice(lineItems);
                        if (typeof toastr !== 'undefined') {
                            toastr.success(`تم جلب ${lineItems.length} بند من الفاتورة بنجاح`);
                        }
                    } else {
                        console.log('لم يتم العثور على بنود صالحة');
                        console.log('الاستجابة الكاملة:', JSON.stringify(response, null, 2));

                        // لا توجد بنود أو الاستجابة فارغة
                        clearAllLineItems();
                        setTimeout(() => {
                            addNewItem();
                        }, 100);
                        if (typeof toastr !== 'undefined') {
                            toastr.info('لا توجد بنود في هذه الفاتورة');
                        }
                    }
                },
                error: function (xhr) {
                    // خطأ في الجلب
                    console.error('خطأ AJAX:', xhr); // لرؤية تفاصيل الخطأ

                    clearAllLineItems();
                    setTimeout(() => {
                        addNewItem();
                    }, 100);

                    let errorMessage = 'فشل في جلب بنود الفاتورة';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'الـ route غير موجود - تحقق من إعداد الـ routes';
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    }

                    console.error('تفاصيل الخطأ:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText
                    });
                }
            });
        });
    });

    // دالة لإظهار مؤشر التحميل
    function showLineItemsLoading() {
        const tbody = document.querySelector('#lineItemsTable tbody');
        const emptyMessage = document.getElementById('emptyMessage');

        if (tbody) {
            tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-primary">
                        <i class="ti ti-loader ti-spin me-2" style="font-size: 1.5rem;"></i>
                        <span>جارٍ تحميل بنود الفاتورة...</span>
                    </div>
                </td>
            </tr>
        `;
        }

        if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    }

    // دالة لمسح جميع البنود
    // ========= clearAllLineItems (revised) =========
    function clearAllLineItems() {

        // 1) دمِّر فقط العناصر التي تمت تهيئتها فعلاً (لها كائن select2 فى الـ data)
        if ($.fn.select2) {
            $('.select2').filter(function () {
                return $(this).data('select2') !== undefined;   // أو $(this).hasClass('select2-hidden-accessible')
            }).select2('destroy');
        }

        // 2) امسح محتوى الجدول وباقى المعالجات
        const tbody = document.querySelector('#lineItemsTable tbody');
        const emptyMessage = document.getElementById('emptyMessage');

        if (tbody) tbody.innerHTML = '';
        itemCounter = 0;

        if (emptyMessage) emptyMessage.style.display = 'block';
        resetTotals();
    }


    // دالة لمسح المجاميع
    function resetTotals() {
        const subtotalDisplay = document.getElementById('subtotalDisplay');
        const taxDisplay = document.getElementById('taxDisplay');
        const totalDisplay = document.getElementById('totalDisplay');

        if (subtotalDisplay) subtotalDisplay.innerHTML = `0.00 <span class="icon-saudi_riyal"></span>`;
        if (taxDisplay) taxDisplay.innerHTML = `0.00 <span class="icon-saudi_riyal"></span>`;
        if (totalDisplay) totalDisplay.innerHTML = `0.00 <span class="icon-saudi_riyal"></span>`;
    }

    // دالة لملء البنود من بيانات الفاتورة
    function populateLineItemsFromInvoice(lineItems) {
        console.log('جاري ملء البنود:', lineItems);
        console.log('عدد البنود:', lineItems.length);

        // مسح البنود الموجودة
        clearAllLineItems();

        // إضافة كل بند من بنود الفاتورة
        lineItems.forEach((item, index) => {
            console.log(`البند ${index + 1}:`, item);

            // تحويل البيانات إلى الشكل المطلوب
            const formattedItem = {
                id: item.id || (index + 1),
                product_id: item.product_id || item.item_id || '',
                description: item.description || item.name || '',
                quantity: item.quantity || 1,
                unit_price: item.unit_price || item.price || '0.00',
                discount: item.discount || item.discount_amount || '0.00',
                discount_type: item.discount_type || 'amount',
                tax_percent: item.tax_percent || item.tax_rate || 15,
                subtotal: item.subtotal || item.net_amount || '0.00',
                tax_amount: item.tax_amount || item.tax || '0.00',
                total: item.total || item.gross_amount || '0.00'
            };

            console.log(`البند المُنسق ${index + 1}:`, formattedItem);
            addNewItemWithInvoiceData(formattedItem);
        });

        if ($.fn.select2) {
            // $('.select2').select2({
            //     dropdownParent: $('#wizard-validation'),
            //     placeholder: ' ',
            //     allowClear: true,
            //     width: '100%',
            //     language: 'ar',
            //     dir: 'rtl'
            // });
        }

        calculateTotals();
    }

    // دالة لإضافة بند جديد مع البيانات من الفاتورة
    function addNewItemWithInvoiceData(itemData) {
        console.log('إضافة بند بالبيانات:', itemData);

        // زيادة العداد
        itemCounter++;

        const tbody = document.querySelector('#lineItemsTable tbody');
        const emptyMessage = document.getElementById('emptyMessage');

        // إنشاء البند الجديد باستخدام نفس structure الموجود
        const newItemHtml = createLineItemRows(itemCounter, itemData);

        // إضافة البند إلى الجدول
        tbody.insertAdjacentHTML('beforeend', newItemHtml);

        // إخفاء رسالة الفراغ
        if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }

        // تعيين قيمة المنتج وإعداد البند
        setupNewItemWithData(itemCounter, itemData);

    }

    // دالة لإنشاء HTML الخاص بصفوف البند
    function createLineItemRows(counter, itemData = {}) {
        return `
        <tr data-item="${counter}">
            <td rowspan="3" class="merged-number-cell">${counter}</td>
            <td>
                <select id="product_id_${counter}" name="line_items[${counter}][product_id]" class="select2 form-select product-select" required>
                    <option value="">اختر منتجاً</option>
                    ${typeof productOptions !== 'undefined' ? productOptions : ''}
                </select>
            </td>
            <td>
                <input type="text" name="line_items[${counter}][description]" class="form-control" placeholder="الوصف" value="${itemData.description || ''}">
            </td>
            <td>
                <input type="number" name="line_items[${counter}][quantity]" class="form-control text-center quantity-input" min="1" step="1" value="${itemData.quantity || 1}" required>
            </td>
            <td>
                <input type="number" name="line_items[${counter}][unit_price]" class="form-control text-center price-input" min="0" step="0.01" value="${itemData.unit_price || '0.00'}" required>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" name="line_items[${counter}][discount]" class="form-control text-center discount-input mb-1" min="0" step="0.01" value="${itemData.discount || '0.00'}" placeholder="قيمة الخصم">
                    <select name="line_items[${counter}][discount_type]" class="select2 form-select discount-type-select" style="max-width: 80px;">
                        <option value="amount" ${itemData.discount_type === 'amount' ? 'selected' : ''}>قيمة</option>
                        <option value="percentage" ${itemData.discount_type === 'percentage' ? 'selected' : ''}>نسبة</option>
                    </select>
                </div>
            </td>
            <td rowspan="3" class="merged-action-cell">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove" onclick="removeItem(this)" title="حذف">
                    <i class="ti ti-trash ti-xs"></i>
                </button>
            </td>
        </tr>
        <tr data-item="${counter}" class="item-separator" style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
            <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-percentage me-1" style="font-size: 0.75rem;"></i>
                نوع الضريبة
            </th>
            <th colspan="1" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-calculator me-1" style="font-size: 0.75rem;"></i>
                المبلغ قبل الضريبة
            </th>
            <th style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-receipt-tax me-1" style="font-size: 0.75rem;"></i>
                مبلغ الضريبة
            </th>
            <th colspan="2" style="font-size: 0.85rem; color: #495057; font-weight: 600; text-align: center; padding: 8px;">
                <i class="ti ti-coin me-1" style="font-size: 0.75rem;"></i>
                المبلغ الإجمالي
            </th>
        </tr>
        <tr data-item="${counter}" class="item-results" style="border-bottom: 2px solid #dee2e6;">
            <td style="padding: 10px;">
                <select name="line_items[${counter}][tax_percent]" class="select2 form-select tax-select" style="font-size: 0.9rem;">
                    <option value="15" data-rate="15" ${(itemData.tax_percent == 15) ? 'selected' : ''}>ضريبة 15%</option>
                    <option value="0" data-rate="0" ${(itemData.tax_percent == 0) ? 'selected' : ''}>ضريبة 0%</option>
                </select>
            </td>
            <td colspan="1" class="text-center" style="padding: 10px;">
                <div class="subtotal-cell calculated-field" style="background-color: #e3f2fd; padding: 8px; border-radius: 4px; font-weight: 500; color: #1976d2;">
                    ${itemData.subtotal || '0.00'} <span class="icon-saudi_riyal"></span>
                </div>
                <input type="hidden" name="line_items[${counter}][subtotal]" value="${itemData.subtotal || '0.00'}">
            </td>
            <td class="text-center" style="padding: 10px;">
                <div class="tax-amount-cell calculated-field" style="background-color: #fff3e0; padding: 8px; border-radius: 4px; font-weight: 500; color: #f57c00;">
                    ${itemData.tax_amount || '0.00'} <span class="icon-saudi_riyal"></span>
                </div>
                <input type="hidden" name="line_items[${counter}][tax_amount]" value="${itemData.tax_amount || '0.00'}">
            </td>
            <td colspan="2" class="text-center" style="padding: 10px;">
                <div class="total-cell calculated-field fw-bold" style="background-color: #e8f5e8; padding: 8px; border-radius: 4px; font-weight: bold; color: #2e7d32;">
                    ${itemData.total || '0.00'} <span class="icon-saudi_riyal"></span>
                </div>
                <input type="hidden" name="line_items[${counter}][total]" value="${itemData.total || '0.00'}">
            </td>
        </tr>
        <tr data-item="${counter}" class="item-visual-separator">
            <td colspan="8" style="height: 20px; background-color: #ffffff; border: none;">
                <div style="text-align: center; color: #dee2e6; font-size: 1rem; line-height: 20px;">
                    ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥ ⬥
                </div>
            </td>
        </tr>
    `;
    }

    // دالة لإعداد البند الجديد مع البيانات
    function setupNewItemWithData(counter, itemData) {

        const $row = $(`tr[data-item="${counter}"]`);

        /* 1) ضع قيمة المنتج قبل التهيئة */
        if (itemData.product_id) {
            $row.find('.product-select').val(String(itemData.product_id));
        }

        /* 2) طبِّق Select2 على كل عنصر يحمل الكلاس .select2 داخل هذا الصف */
        $row.find('.select2').each(function () {
            const $sel = $(this);
            if (!$sel.hasClass('select2-hidden-accessible')) {
                $sel.select2({
                    dropdownParent: $('#wizard-validation'),
                    placeholder: ' ',
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl'
                });
            }
        });
        initGlobalSelect2();

        /* 3) أبلغ Select2 بتغيّر القيم (يشمل المنتج وغيره) */
        $row.find('.select2').trigger('change');

        /* باقى الإجراءات */
        addRowEventListeners(counter);
        calculateTotals();
    }



}   