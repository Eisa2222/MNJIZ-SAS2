/**
 * JavaScript for Clearance Certificates Approval Index Page
 * Initializes DataTable and handles filtering.
 */
'use strict';

$(function () {
    // Initialize select2 for filtering
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl',
        placeholder: $(this).data('placeholder'),
    });

    // Ensure the DataTable instance is ready
    $(document).ready(function () {
        const table = $('#approval-clearance-certificates-table').DataTable();

        if (!table) {
            console.error('DataTable instance not found for #approval-clearance-certificates-table');
            return;
        }

        // Add filter data to AJAX requests
        table.on('preXhr.dt', (e, settings, data) => {
            data.user = $('#filter-user').val();
        });

        // Re-draw the table when a filter changes
        $('#filter-user').on('change', () => {
            table.draw();
        });
    });
});
