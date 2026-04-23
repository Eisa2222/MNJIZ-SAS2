$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    $('#tasks-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                priority: $('#filter-priority').val() || '',
                field: $('#filter-field').val() || '',
                status: $('#filter-status').val() || '',
                employee: $('#filter-employee').val() || ''
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.priority) params.append('priority', filters.priority);
            if (filters.field) params.append('field', filters.field);
            if (filters.status) params.append('status', filters.status);
            if (filters.employee) params.append('employee', filters.employee);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-priority').on('change', applyFilters);
        $('#filter-field').on('change', applyFilters);
        $('#filter-status').on('change', applyFilters);
        $('#filter-employee').on('change', applyFilters);
    });
});
