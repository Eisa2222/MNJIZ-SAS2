/**
 * وحدة اعتمادات مسيرات الرواتب
 * تعريف وظائف معالجة اعتمادات المسير والواجهة الخاصة بها
 */
var WpsApprovals = (function () {
    'use strict';

    /**
     * تهيئة الجدول وأحداث الأزرار
     * @param {Object} options - خيارات التهيئة
     */
    var init = function (options) {
        initDataTable(options);
        initApprovalEvents();
    };

    /**
     * تهيئة جدول البيانات
     * @param {Object} options - خيارات تهيئة الجدول
     */
    var initDataTable = function (options) {
        $('#table').DataTable({
            "createdRow": function (row, data, dataIndex) {
                $(row).find('td.table-ellipsis').each(function () {
                    var cellText = $(this).text();
                    $(this).html('<div class="cell-content"><span>' + cellText +
                        '</span></div>');
                });
            },
            processing: true,
            serverSide: true,
            ajax: {
                url: options.dataUrl,
            },
            columns: [{
                data: "",
                orderable: false,
                searchable: false
            },
            {
                data: 'employee_id',
                name: 'employee_id'
            },
            {
                data: 'basic',
                name: 'basic'
            },
            {
                data: 'transport',
                name: 'transport'
            },
            {
                data: 'housing',
                name: 'housing'
            },
            {
                data: 'other',
                name: 'other'
            },
            {
                data: 'insurance',
                name: 'insurance'
            },
            {
                data: 'deductions',
                name: 'deductions'
            },
            {
                data: 'incentives',
                name: 'incentives'
            },
            {
                data: 'net',
                name: 'net'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action'
            }
            ],
            order: [
                [1, 'asc']
            ],
            dom: '<"dt-toolbar"<' +
                '"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center"l>' +
                '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between"fB>>' +
                'rt' +
                '<"row mt-3"<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100"i>' +
                '<"pagination-wrapper overflow-auto w-100"p>>',
            buttons: [{
                extend: 'collection',
                className: 'btn btn-export btn-sm d-none',
                text: 'الإجراءات',
                buttons: [{
                    extend: 'copy',
                    text: 'نسخ'
                },
                {
                    extend: 'excel',
                    text: 'إكسل'
                }
                ]
            }],
            language: {
                url: options.languageUrl
            },
            orderCellsTop: true,
            columnDefs: [{
                className: "control",
                orderable: false,
                targets: 0,
                render: function (data, type, full, meta) {
                    return "";
                },
            }],
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            return "التفاصيل";
                        },
                    }),
                    type: "column",
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.title !== "" ?
                                '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' +
                                col.columnIndex + '"><td>' + col.title + ":</td> <td>" + col
                                    .data + "</td></tr>" :
                                "";
                        }).join("");
                        return data ? $('<table class="table"/><tbody />').append(data) : false;
                    },
                },
            },
        });
    };

    /**
     * تهيئة أحداث أزرار الاعتماد والرفض
     */
    var initApprovalEvents = function () {
        // زر الاعتماد أو الإلغاء
        $(document).on('click', '.approval-action', handleApprovalClick);

        // زر الرفض
        $(document).on('click', '.reject-action', handleRejectClick);
    };

    /**
     * معالجة حدث النقر على زر الاعتماد أو الإلغاء
     * @param {Event} e - حدث النقر
     */
    var handleApprovalClick = function (e) {
        e.preventDefault();
        var payrollId = $(this).data('id');
        var name = $(this).data('name');
        var action = $(this).data('action');
        var url, titleText, confirmBtn;

        if (action === 'approve') {
            url = approvalRoutes.approve.replace('__ID__', payrollId).replace('__NAME__', name);
            titleText = 'هل تريد اعتماد مسير الرواتب؟';
            confirmBtn = 'اعتماد';
        } else if (action === 'revoke') {
            url = approvalRoutes.revoke.replace('__ID__', payrollId).replace('__NAME__', name);
            titleText = 'هل تريد إلغاء الاعتماد؟';
            confirmBtn = 'إلغاء الاعتماد';
        }

        Swal.fire({
            title: titleText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: confirmBtn,
            cancelButtonText: 'إلغاء',
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                performApprovalAction(url);
            }
        });
    };

    /**
     * معالجة حدث النقر على زر الرفض
     * @param {Event} e - حدث النقر
     */
    var handleRejectClick = function (e) {
        e.preventDefault();
        var payrollId = $(this).data('id');
        var name = $(this).data('name');
        var url = approvalRoutes.reject.replace('__ID__', payrollId).replace('__NAME__', name);

        Swal.fire({
            title: 'أدخل سبب الرفض',
            input: 'textarea',
            inputPlaceholder: 'سبب الرفض...',
            showCancelButton: true,
            confirmButtonText: 'رفض',
            cancelButtonText: 'إلغاء',
            customClass: {
                confirmButton: 'btn btn-danger me-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false,
            inputValidator: (value) => {
                if (!value) return 'يجب إدخال سبب الرفض';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                performRejectAction(url, result.value);
            }
        });
    };

    /**
     * تنفيذ إجراء الاعتماد أو الإلغاء
     * @param {string} url - عنوان URL للإجراء
     */
    var performApprovalAction = function (url) {
        $.ajax({
            url: url,
            method: 'GET',
            success: function (response) {
                handleResponse(response);
            },
            error: function (xhr) {
                handleError(xhr);
            }
        });
    };

    /**
     * تنفيذ إجراء الرفض
     * @param {string} url - عنوان URL للإجراء
     * @param {string} reason - سبب الرفض
     */
    var performRejectAction = function (url, reason) {
        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            data: {
                rejection_reason: reason
            },
            success: function (response) {
                handleResponse(response);
            },
            error: function (xhr) {
                handleError(xhr);
            }
        });
    };

    /**
     * معالجة استجابة النجاح للإجراء
     * @param {Object} response - رد الخادم
     */
    var handleResponse = function (response) {
        if (response.success) {
            toastr.success(response.success);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            Swal.fire('خطأ', response.error, 'error');
        }
    };

    /**
     * معالجة خطأ الإجراء
     * @param {Object} xhr - كائن XMLHttpRequest
     */
    var handleError = function (xhr) {
        var errMsg = xhr.responseJSON && xhr.responseJSON.error ?
            xhr.responseJSON.error : 'حدث خطأ أثناء تنفيذ الإجراء.';
        Swal.fire('خطأ', errMsg, 'error');
    };

    // متغيرات عامة للوحدة
    var approvalRoutes = {};
    var csrfToken = '';

    /**
     * تعيين عناوين URL للإجراءات
     * @param {Object} routes - كائن يحتوي على عناوين URL للإجراءات
     */
    var setRoutes = function (routes) {
        approvalRoutes = routes;
    };

    /**
     * تعيين رمز CSRF
     * @param {string} token - رمز CSRF
     */
    var setCsrfToken = function (token) {
        csrfToken = token;
    };

    return {
        init: init,
        setRoutes: setRoutes,
        setCsrfToken: setCsrfToken
    };
})();

// Para asegurarnos de que está disponible globalmente
window.WpsApprovals = WpsApprovals;
