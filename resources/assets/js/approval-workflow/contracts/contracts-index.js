$(function () {
    // 1) تهيئة Select2 للفلاتر
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl',
        placeholder: $(this).data('placeholder'),
    });

    // 2) انتظر تحميل الصفحة بالكامل
    $(document).ready(function () {
        // 3) احصل على instance الجدول الذي تم إنشاؤه بواسطة PHP
        const table = $('#approval-contracts-table').DataTable();

        if (!table) {
            console.error('DataTable instance not found for #approval-contracts-table');
            return;
        }

        // 4) ألحق منطق الفلاتر بحدث `preXhr` لإرسالها مع طلب AJAX
        table.on('preXhr.dt', (e, settings, data) => {
            data.customer = $('#filter-customer').val();
            // data.manager = $('#filter-manager').val(); // إذا تم تفعيله
        });

        // 5) أعد تحميل الجدول عند تغيير الفلتر
        $('#filter-customer').on('change', () => {
            table.draw();
        });
    });
});
