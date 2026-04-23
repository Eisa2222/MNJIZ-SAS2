$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    $('#campaign-management-table').on('init.dt', function () {
        const table = $(this).DataTable();


        function colorRows() {
            table.rows({ page: 'current' }).every(function () {
                const data = this.data();
                if (data.campaign_section_color) {
                    const bg = data.campaign_section_color;
                    // حساب تباين YIQ
                    let hex = bg.replace('#', '');
                    let r = parseInt(hex.substr(0, 2), 16),
                        g = parseInt(hex.substr(2, 2), 16),
                        b = parseInt(hex.substr(4, 2), 16);
                    let yiq = (r * 299 + g * 587 + b * 114) / 1000;
                    const fg = yiq >= 128 ? '#000000' : '#FFFFFF';

                    // طبّق اللون
                    const rowNode = this.node();
                    $(rowNode).css('background-color', bg);
                    // لون الخط على كل خلية
                    $(rowNode).find('td').css('color', fg);

                    $(rowNode).find('a').css('color', fg);

                    $(rowNode).find('i').css('color', fg);

                }
            });
        }

        table.on('draw', colorRows);

        colorRows();

        function applyFilters() {
            const filters = {
                status: $('#filter-status').val() || '',
                type: $('#filter-type').val() || '',
                section: $('#filter-section').val() || ''
            };
            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.status) params.append('status', filters.status);
            if (filters.type) params.append('type', filters.type);
            if (filters.section) params.append('section', filters.section);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');
            table.ajax.url(newUrl).load();
        }

        $('#filter-status').on('change', applyFilters);
        $('#filter-type').on('change', applyFilters);
        $('#filter-section').on('change', applyFilters);
    });
});
