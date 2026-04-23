$(document).ready(function () {
    // تهيئة الفورم للتعديل أولاً
    initializeEditForm();
    // ثم ربط أحداث الفلترة
    bindFilterEvents();
});

// تهيئة الفورم للتعديل
// تحديث دالة initializeEditForm
function initializeEditForm() {
    const currentData = window.currentData;

    // تحديد التصنيف ومرجعية الأصل أولاً
    if (currentData.categoryId) {
        $('#category_id').val(currentData.categoryId).trigger('change');
    }

    // تحميل العهد مع العهدة الحالية
    if (currentData.categoryId) {
        setTimeout(function () {
            loadCustodyItems(); // هذا سيحمل العهد المتاحة + العهدة الحالية
        }, 200);
    }
}

// ربط أحداث الفلترة
function bindFilterEvents() {
    $('#category_id').on('change', function () {
        // تجنب إعادة التحميل إذا كانت هذه هي القيم الأولية
        const currentData = window.currentData;
        const categoryId = $('#category_id').val();

        // إذا كانت القيم هي نفس القيم الأولية، لا تحمل مرة أخرى
        if (categoryId === currentData.categoryId && $('#custody_item_id option[value="' + currentData.custodyItemId + '"]').length > 0) {
            return;
        }

        loadCustodyItems();
    });

    // مسح العهد عند مسح أي من الفلاتر
    $('#category_id').on('select2:clear', function () {
        clearCustodyItems();
    });
}

// تحميل العهد المتاحة
// تحديث دالة loadCustodyItems
function loadCustodyItems() {
    const categoryId = $('#category_id').val();

    if (!categoryId) {
        clearCustodyItems();
        return;
    }

    const custodySelect = $('#custody_item_id');
    const currentSelectedValue = custodySelect.val(); // القيمة المحددة حالياً

    // إذا لم تكن هناك قيمة محددة، استخدم القيمة من البيانات الأولية
    const includeCurrentValue = currentSelectedValue || window.currentData?.custodyItemId;

    custodySelect.prop('disabled', true);
    custodySelect.html('<option value="">جاري التحميل...</option>');

    $.ajax({
        url: appUrls.requestUrl,
        method: 'GET',
        data: {
            category_id: categoryId,
            include_current: includeCurrentValue // إرسال العهدة الحالية دائماً
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log('Response received:', response);
            populateCustodyItems(response.data || response, currentSelectedValue || window.currentData?.custodyItemId);
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', { xhr, status, error });
            toastr.error('حدث خطأ في تحميل العهد');
            custodySelect.html('<option value="">حدث خطأ في التحميل</option>');
        },
        complete: function () {
            custodySelect.prop('disabled', false);
        }
    });
}

// تعبئة قائمة العهد مع الحفاظ على التحديد
function populateCustodyItems(items, selectedValue) {
    const custodySelect = $('#custody_item_id');
    custodySelect.empty();

    custodySelect.append('<option value="">اختر العهدة</option>');

    if (items && items.length > 0) {
        items.forEach(function (item) {
            const isSelected = item.id == selectedValue ? 'selected' : '';
            const option = `<option value="${item.id}" data-serial="${item.serial_number || ''}" data-model="${item.model || ''}" ${isSelected}>
                ${item.name} ${item.serial_number ? '(' + item.serial_number + ')' : ''}
            </option>`;
            custodySelect.append(option);
        });

        // إذا كانت هناك قيمة محددة، حددها
        if (selectedValue) {
            custodySelect.val(selectedValue);
        }

        toastr.success(`تم العثور على ${items.length} عهدة متاحة`);
    } else {
        custodySelect.append('<option value="">لا توجد عهد متاحة</option>');
        toastr.info('لا توجد عهد متاحة للتصنيف المحدد');
    }

    custodySelect.trigger('change');
}

// مسح قائمة العهد
function clearCustodyItems() {
    const custodySelect = $('#custody_item_id');
    custodySelect.empty();
    custodySelect.append('<option value="">اختر العهدة</option>');
    custodySelect.trigger('change');
}
