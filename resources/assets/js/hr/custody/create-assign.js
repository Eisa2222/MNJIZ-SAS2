$(document).ready(function () {
    bindFilterEvents();
});

// ربط أحداث الفلترة
function bindFilterEvents() {
    $('#category_id, #location_id').on('change', function () {
        loadCustodyItems();
    });

    // مسح العهد عند مسح أي من الفلاتر
    $('#category_id, #location_id').on('select2:clear', function () {
        clearCustodyItems();
    });
}

// تحميل العهد المتاحة
function loadCustodyItems() {
    const categoryId = $('#category_id').val();
    const locationId = $('#location_id').val();

    if (!categoryId || !locationId) {
        clearCustodyItems();
        return;
    }

    const custodySelect = $('#custody_item_id');
    custodySelect.prop('disabled', true);
    custodySelect.html('<option value="">جاري التحميل...</option>');

    $.ajax({
        url: appUrls.requestUrl,
        method: 'GET',
        data: {
            category_id: categoryId,
            location_id: locationId,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            populateCustodyItems(response.data || response);
        },
        error: function (xhr, status, error) {
            toastr.error('حدث خطأ في تحميل العهد');
            custodySelect.html('<option value="">حدث خطأ في التحميل</option>');
        },
        complete: function () {
            custodySelect.prop('disabled', false);
        }
    });
}

function populateCustodyItems(items) {
    const custodySelect = $('#custody_item_id');
    custodySelect.empty();

    custodySelect.append('<option value="">اختر العهدة</option>');

    if (items && items.length > 0) {
        items.forEach(function (item) {
            const option = `<option value="${item.id}" data-serial="${item.serial_number || ''}" data-model="${item.model || ''}">
                ${item.name} ${item.serial_number ? '(' + item.serial_number + ')' : ''}
            </option>`;
            custodySelect.append(option);
        });

        toastr.success(`تم العثور على ${items.length} عهدة متاحة`);
    } else {
        custodySelect.append('<option value="">لا توجد عهد متاحة</option>');
        toastr.info('لا توجد عهد متاحة للتصنيف والمرجعية المحددة');
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
