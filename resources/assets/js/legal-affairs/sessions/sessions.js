$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    $('#sessions-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                status: $('#filter-status').val() || '',
                ranks: $('#filter-ranks').val() || '',
                type: $('#filter-type').val() || '',
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.status) params.append('status', filters.status);
            if (filters.ranks) params.append('ranks', filters.ranks);
            if (filters.type) params.append('type', filters.type);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-status').on('change', applyFilters);
        $('#filter-ranks').on('change', applyFilters);
        $('#filter-type').on('change', applyFilters);
    });
});
