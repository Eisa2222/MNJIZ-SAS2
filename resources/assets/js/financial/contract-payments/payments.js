// إلغاء الدفع بواسطة SweetAlert2
$(function () {
    $('.select2').select2({ allowClear: true, dir: 'rtl' });


    $('#contract-paymen-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                status: $('#filter-status').val() || '',
                customer: $('#filter-customer').val() || '',
                contract: $('#filter-contract').val() || ''
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.status) params.append('status', filters.status);
            if (filters.customer) params.append('customer', filters.customer);
            if (filters.contract) params.append('contract', filters.contract);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-status').on('change', applyFilters);
        $('#filter-customer').on('change', applyFilters);
        $('#filter-contract').on('change', applyFilters);
    });



    $(document).on('click', '.cancel-payment-btn', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const $form = $btn.closest('form.cancel-payment-form');

        Swal.fire({
            title: 'تأكيد الإلغاء',
            text: 'هل أنت متأكد من إلغاء هذه الدفعة؟',
            icon: 'warning',
            showCancelButton: true,
            showConfirmButton: true,
            showDenyButton: false,
            buttonsStyling: false,
            customClass: {
                popup: 'custom-popup',
                title: 'custom-title',
                text: 'custom-text',
                confirmButton: 'btn btn-success custom-confirm',
                cancelButton: 'btn btn-danger custom-cancel'
            },
            confirmButtonText: 'تأكيد',
            cancelButtonText: 'إلغاء',
            reverseButtons: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $form.submit();
            }
        });
    });
});
