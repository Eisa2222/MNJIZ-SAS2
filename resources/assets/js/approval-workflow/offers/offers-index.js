$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl',
        placeholder: $(this).data('placeholder'),
    });

    $(document).ready(function () {
        const table = $('#approval-offers-table').DataTable();

        if (!table) {
            console.error('DataTable instance not found for #approval-offers-table');
            return;
        }

        table.on('preXhr.dt', (e, settings, data) => {
            data.customer = $('#filter-customer').val();
            data.employee = $('#filter-employee').val();
        });

        $('#filter-customer, #filter-employee').on('change', () => {
            table.draw();
        });
    });
});


















