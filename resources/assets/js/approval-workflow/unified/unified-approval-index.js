$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl',
        placeholder: function () {
            return $(this).data('placeholder');
        },
    });

    $(document).ready(function () {
        const table = $('#unified-approvals-table').DataTable();

        if (!table) {
            console.error('DataTable instance not found for #unified-approvals-table');
            return;
        }

        table.on('preXhr.dt', (e, settings, data) => {
            data.filter_type = $('#filter-type').val();
            data.filter_employee = $('#filter-employee').val();
            data.filter_date_from = $('#filter-date-from').val();
            data.filter_date_to = $('#filter-date-to').val();
        });

        $('#filter-type, #filter-employee, #filter-date-from, #filter-date-to').on('change', () => {
            table.draw();
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const filterType = urlParams.get('filter_type');
    if (filterType) {
        $('#filter-type').val(filterType).trigger('change');
    }

    $(document).ajaxError(function (event, xhr, settings) {
        if (xhr.status === 419) {
            toastr.error('انتهت صلاحية الجلسة، يرجى تحديث الصفحة');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });
});