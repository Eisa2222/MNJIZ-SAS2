$(function () {
    $(document).on('change', '[id^="select-all"]', function () {
        var table = $(this).closest('.dataTables_wrapper').find('table');
        var isChecked = this.checked;

        table.find('tbody input.row-checkbox').prop('checked', isChecked);
    });

    $(document).on('change', 'input.row-checkbox', function () {
        var table = $(this).closest('table');
        var wrapper = table.closest('.dataTables_wrapper');
        var selectAll = wrapper.find('[id^="select-all"]');

        var allBoxes = table.find('tbody input.row-checkbox');
        var checkedBoxes = allBoxes.filter(':checked');

        selectAll.prop('checked', allBoxes.length === checkedBoxes.length);
        selectAll.prop('indeterminate', checkedBoxes.length > 0 && checkedBoxes.length < allBoxes.length);
    });
});