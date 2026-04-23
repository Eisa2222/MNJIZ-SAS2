
$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    $('#activity-log-table tbody').on('click', '.view-details', function () {
        var id = $(this).data('id');
        $.ajax({
            url: appUrls.action.replace(':id', id),
            method: 'GET',
            success: function (response) {
                $('#detailsModal .modal-body').html(response.html);
                $('#detailsModal').modal('show');
            },
            error: function (xhr) {
                // Swal.fire('خطأ', 'حدث خطأ أثناء جلب التفاصيل.', 'error');
                Swal.fire({
                    text: 'حدث خطأ أثناء جلب التفاصيل.',
                    icon: 'error',
                    showCancelButton: false, // يعرض زر الإلغاء
                    showConfirmButton: false, // يعرض زر التأكيد
                    showDenyButton: false, // لا يعرض زر الرفض
                    buttonsStyling: false, // لتعطيل التنسيق الافتراضي للأزرار
                })
            }
        });
    });

    $('#activity-log-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                user: $('#filter-user').val() || '',
                from: $('#filter-from').val() || '',
                to: $('#filter-to').val() || ''
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.user) params.append('user', filters.user);
            if (filters.from) params.append('from', filters.from);
            if (filters.to) params.append('to', filters.to);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-user').on('change', applyFilters);
        $('#filter-from').on('change', applyFilters);
        $('#filter-to').on('change', applyFilters);
    });
});
