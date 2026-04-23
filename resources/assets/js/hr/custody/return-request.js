document.addEventListener('DOMContentLoaded', () => {
    const employeeEl = $('#employee_id');
    const parentEl = $('#parent_request_id');
    const loader = $('#loadingIndicator');
    const selectedId = window.appData.selectedParentRequestId || null;

    function loadAssignments(empId, selectedId = null) {
        parentEl.prop('disabled', true);
        loader.hide();

        if (!empId) {
            parentEl.empty()
                .append('<option value="">اختر أولاً الموظف</option>')
                .trigger('change');
            return;
        }

        parentEl.empty()
            .append('<option value="">جاري التحميل...</option>')
            .trigger('change');
        loader.show();

        $.ajax({
            url: window.appUrls.approvedRequests,
            data: { employee_id: empId },
        })
            .done(data => {
                parentEl.empty().append('<option value="">اختر طلب العهدة الأصلي</option>');
                if (data.length) {
                    data.forEach(item => {
                        parentEl.append(`<option value="${item.id}">${item.text}</option>`);
                    });

                    if (selectedId) {
                        parentEl.val(selectedId).trigger('change');
                    }
                } else {
                    toastr.info('لا توجد طلبات عهدة متاحة لهذا الموظف');
                }
            })
            .fail(() => {
                parentEl.empty()
                    .append('<option value="">خطأ في جلب البيانات</option>');
            })
            .always(() => {
                loader.hide();
                parentEl.prop('disabled', false).trigger('change');
            });
    }

    employeeEl.on('change', () => {
        loadAssignments(employeeEl.val(), null);
    });

    const initialEmp = employeeEl.val();
    if (initialEmp) {
        loadAssignments(initialEmp, selectedId);
    }

    employeeEl.select2({
        placeholder: 'اختر الموظف',
        allowClear: true,
        dir: 'rtl',
        minimumResultsForSearch: 5,
    });
    parentEl.select2({
        placeholder: 'اختر طلب العهدة الأصلي',
        allowClear: true,
        dir: 'rtl',
        minimumResultsForSearch: 5,
    });
});
