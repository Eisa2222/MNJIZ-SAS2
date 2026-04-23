'use strict';

$(function () {
    // تهيئة Select2
    $('.select2').each(function () {
        const $this = $(this);
        $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: $this.data('placeholder'),
            allowClear: true,
            dropdownParent: $this.parent()
        });
    });

    // انتظر حتى يتم تهيئة DataTable
    $(document).on('preInit.dt', function (e, settings) {
        if (settings.sTableId === 'approval-advances-table') {
            const dt = $('#' + settings.sTableId).DataTable();

            // إضافة بيانات الفلاتر مع كل طلب AJAX
            dt.on('preXhr.dt', (e, settings, data) => {
                data.employee_id = $('#filter-employee').val();
                data.advance_type = $('#filter-advance-type').val();
            });

            // إعادة رسم الجدول عند تغيير الفلاتر
            $('#filter-employee, #filter-advance-type').on('change', () => {
                dt.ajax.reload();
            });
        }
    });
});
