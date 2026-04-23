$(function () {
    // 1) تهيئة Select2 للفلاتر
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl',
        placeholder: function () {
            return $(this).data('placeholder');
        },
    });

    // 2) انتظر تحميل الصفحة بالكامل
    $(document).ready(function () {
        // 3) احصل على instance الجدول الذي تم إنشاؤه بواسطة PHP
        const table = $('#approval-leave-requests-table').DataTable();

        if (!table) {
            console.error('DataTable instance not found for #approval-leave-requests-table');
            return;
        }

        // 4) ألحق منطق الفلاتر بحدث `preXhr` لإرسالها مع طلب AJAX
        table.on('preXhr.dt', (e, settings, data) => {
            data.filter_leave_type = $('#filter-leave-type').val();
            data.filter_employee = $('#filter-employee').val();
        });

        // 5) أعد تحميل الجدول عند تغيير الفلاتر
        $('#filter-leave-type, #filter-employee').on('change', () => {
            table.draw();
        });
    });
});
