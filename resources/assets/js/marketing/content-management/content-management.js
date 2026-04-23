
$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    $('#content-management-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                status: $('#filter-publication-status').val() || '',
                type: $('#filter-type').val() || '',
                pattern: $('#filter-pattern').val() || ''
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.status) params.append('status', filters.status);
            if (filters.type) params.append('type', filters.type);
            if (filters.pattern) params.append('pattern', filters.pattern);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-publication-status').on('change', applyFilters);
        $('#filter-type').on('change', applyFilters);
        $('#filter-pattern').on('change', applyFilters);
    });
});