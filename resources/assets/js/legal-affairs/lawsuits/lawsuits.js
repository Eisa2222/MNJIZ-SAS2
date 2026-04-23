$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    $('#lawsuits-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                project: $('#filter-project').val() || '',
                type: $('#filter-type').val() || '',
                court: $('#filter-court').val() || '',
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.project) params.append('project', filters.project);
            if (filters.type) params.append('type', filters.type);
            if (filters.court) params.append('court', filters.court);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-project').on('change', applyFilters);
        $('#filter-type').on('change', applyFilters);
        $('#filter-court').on('change', applyFilters);
    });
});
