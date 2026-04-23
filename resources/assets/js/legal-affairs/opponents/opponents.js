$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    $('#opponents-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                region: $('#filter-region').val() || '',
                type: $('#filter-type').val() || '',
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.region) params.append('region', filters.region);
            if (filters.type) params.append('type', filters.type);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-region').on('change', applyFilters);
        $('#filter-type').on('change', applyFilters);
    });
});
