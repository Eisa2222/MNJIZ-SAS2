

$(function () {
    $('.select2').select2({
        allowClear: true,
        dir: 'rtl'
    });

    // filter initialization
    $('#employees-table').on('init.dt', function () {
        const table = $(this).DataTable();

        function applyFilters() {
            const filters = {
                hr_status_id: $('#filter-hr_status_id').val() || '',
                role: $('#filter-role').val() || '',
                insurance_status: $('#filter-insurance_status').val() || ''
            };

            const baseUrl = window.location.pathname;
            const params = new URLSearchParams();

            if (filters.hr_status_id) params.append('hr_status_id', filters.hr_status_id);
            if (filters.role) params.append('role', filters.role);
            if (filters.insurance_status) params.append('insurance_status', filters.insurance_status);

            const newUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

            table.ajax.url(newUrl).load();
        }

        $('#filter-hr_status_id').on('change', applyFilters);
        $('#filter-role').on('change', applyFilters);
        $('#filter-insurance_status').on('change', applyFilters);
    });
});


function confirmArchive(url) {
    Swal.fire({
        title: 'نقل إلى الأرشيف',
        text: "هل أنت متأكد من نقل هذا الموظف إلى الأرشيف؟",
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
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;

        $.ajax({
            url: url,
            type: 'PUT',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'جاري المعالجة...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showCancelButton: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                    },
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            success(res) {
                Swal.close();
                toastr.success(res.success);
                $('#employees-table').DataTable()
                    .ajax.reload(null, false);
            },
            error(xhr) {
                Swal.close();
                const msg = xhr.responseJSON?.error || 'حدث خطأ غير متوقع';
                toastr.error(msg);
            }
        });
    });

}


function confirmRestore(url) {
    Swal.fire({
        title: 'استعادة من الأرشيف',
        text: "هل أنت متأكد من استعادة هذا الموظف من الأرشيف؟",
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
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;

        $.ajax({
            url: url,
            type: 'PUT',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'جاري المعالجة...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    showCancelButton: false,
                    customClass: {
                        popup: 'custom-popup',
                        title: 'custom-title',
                        text: 'custom-text',
                    },
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            success(res) {
                Swal.close();
                toastr.success(res.success);
                $('#employees-table').DataTable()
                    .ajax.reload(null, false);
            },
            error(xhr) {
                Swal.close();
                const msg = xhr.responseJSON?.error || 'حدث خطأ غير متوقع';
                toastr.error(msg);
            }
        });
    });

}

function sendPasswordResetFirstNotification(url) {
    Swal.fire({
        title: "هل ترغب  في ارسال رسالة إعادة تعيين كلمة المرور لهذا الموظف؟",
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
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'جاري المعالجة...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        showCancelButton: false,
                        customClass: {
                            popup: 'custom-popup',
                            title: 'custom-title',
                            text: 'custom-text',
                        },
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                },


                success: function (response) {
                    Swal.close();
                    toastr.success(response.success, 'تم ارسال الرسلة بنجاح');
                },
                error: function (xhr) {
                    Swal.close();
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message, 'خطأ !');
                    } else {
                        toastr.error('حدث خطأ أثناء إرسال الرابط. يرجى المحاولة لاحقاً.',
                            'خطأ!');
                    }


                }
            });
        }
    });
}

// تجعل الدوال متاحة globally
window.confirmArchive = confirmArchive;
window.confirmRestore = confirmRestore;
window.sendPasswordResetFirstNotification = sendPasswordResetFirstNotification;