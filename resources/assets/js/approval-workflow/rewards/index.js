'use strict';

$(function () {
    $('.select2').each(function () {
        const $this = $(this);
        $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: $this.data('placeholder'),
            allowClear: true,
            dropdownParent: $this.parent()
        });
    });

    $(document).on('preInit.dt', function (e, settings) {
        if (settings.sTableId === 'approval-rewards-table') {
            const dt = $('#' + settings.sTableId).DataTable();

            dt.on('preXhr.dt', (e, settings, data) => {
                data.employee_id = $('#filter-employee').val();
                data.reward_type = $('#filter-reward-type').val();
            });

            $('#filter-employee, #filter-reward-type').on('change', () => {
                dt.ajax.reload();
            });
        }
    });
});
