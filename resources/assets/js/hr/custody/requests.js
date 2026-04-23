$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl',
        minimumResultsForSearch: 5,
    });

    $('#request-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                employee: $('#filter-employee').val() || '',
                status: $('#filter-status').val() || '',
                type: $('#filter-type').val() || '',
                item: $('#filter-item').val() || ''
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.employee) params.append('employee', filters.employee);
            if (filters.status) params.append('status', filters.status);
            if (filters.type) params.append('type', filters.type);
            if (filters.item) params.append('item', filters.item);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-employee').on('change', applyFilters);
        $('#filter-status').on('change', applyFilters);
        $('#filter-type').on('change', applyFilters);
        $('#filter-item').on('change', applyFilters);
    });
});