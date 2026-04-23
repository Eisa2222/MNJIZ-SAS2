/**
 * resources/assets/js/approval-workflow/approval-manager.js
 */

class ApprovalManager {
    constructor(routeBaseName, csrfToken = null) {
        this.routeBaseName = routeBaseName;
        this.csrfToken = csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.table = null;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        $(document).on('click', '.approve-btn', (e) => {
            const itemId = $(e.currentTarget).data('id');
            this.showApprovalConfirmation(itemId);
        });

        $(document).on('click', '.reject-btn', (e) => {
            const itemId = $(e.currentTarget).data('id');
            this.showRejectDialog(itemId);
        });

        $(document).on('click', '.revoke-btn', (e) => {
            const itemId = $(e.currentTarget).data('id');
            this.showRevokeConfirmation(itemId);
        });

        $(document).on('click', '.approval-action', (e) => {
            e.preventDefault();
            const itemId = $(e.currentTarget).data('id');
            const action = $(e.currentTarget).data('action');

            if (action === 'approve') {
                this.showApprovalConfirmation(itemId);
            } else if (action === 'revoke') {
                this.showRevokeConfirmation(itemId);
            }
        });

        $(document).on('click', '.reject-action', (e) => {
            e.preventDefault();
            const itemId = $(e.currentTarget).data('id');
            this.showRejectDialog(itemId);
        });
    }

    showApprovalConfirmation(itemId) {
        Swal.fire({
            title: 'تأكيد الاعتماد',
            text: 'هل أنت متأكد من اعتماد هذا الطلب؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، اعتماد',
            cancelButtonText: 'إلغاء',
            customClass: {
                confirmButton: 'btn btn-success me-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                this.performAction(itemId, 'approve');
            }
        });
    }

    showRejectDialog(itemId) {
        Swal.fire({
            title: 'رفض الطلب',
            input: 'textarea',
            inputLabel: 'سبب الرفض',
            inputPlaceholder: 'يرجى إدخال سبب الرفض...',
            inputAttributes: {
                'aria-label': 'سبب الرفض',
                'rows': 4,
                'maxlength': 1000
            },
            showCancelButton: true,
            confirmButtonText: 'رفض الطلب',
            cancelButtonText: 'إلغاء',
            customClass: {
                confirmButton: 'btn btn-danger me-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false,
            inputValidator: (value) => {
                if (!value || value.trim().length === 0) {
                    return 'يجب إدخال سبب الرفض';
                }
                if (value.length > 1000) {
                    return 'سبب الرفض لا يجب أن يتجاوز 1000 حرف';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.performAction(itemId, 'reject', result.value);
            }
        });
    }

    showRevokeConfirmation(itemId) {
        Swal.fire({
            title: 'إلغاء الاعتماد',
            text: 'هل أنت متأكد من إلغاء اعتماد هذا الطلب؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، إلغاء الاعتماد',
            cancelButtonText: 'إلغاء',
            customClass: {
                confirmButton: 'btn btn-warning me-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                this.performAction(itemId, 'revoke');
            }
        });
    }

    performAction(itemId, action, reason = null) {
        try {
            const url = this.buildActionUrl(itemId, action);
            const data = { _token: this.csrfToken };

            if (reason) {
                data.reason = reason;
            }

            this.showLoading();

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: (response) => {
                    this.hideLoading();
                    this.handleActionResponse(response);
                },
                error: (xhr) => {
                    this.hideLoading();
                    this.handleActionError(xhr);
                }
            });
        } catch (error) {
            toastr.error(error.message || 'خطأ في تكوين النظام');
        }
    }

    buildActionUrl(itemId, action) {
        if (window.laravelRoutes && window.laravelRoutes[action]) {
            return window.laravelRoutes[action].replace(':id', itemId);
        }

        if (typeof route !== 'undefined') {
            try {
                const routeName = `${this.routeBaseName}.${action}`;
                return route(routeName, { id: itemId });
            } catch (error) {
                throw new Error('لا يمكن إنشاء رابط الطلب');
            }
        }
        throw new Error('حدث خطأ غير متوقع');

    }

    handleActionResponse(response) {
        if (response.success) {
            toastr.success(response.message);
            this.refreshTable();
            this.updateStatistics();
        } else {
            toastr.error(response.message || 'حدث خطأ غير متوقع');
        }
    }

    handleActionError(xhr) {
        let message = 'حدث خطأ أثناء معالجة الطلب';

        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.status === 403) {
            message = 'ليس لديك الصلاحية لتنفيذ هذا الإجراء';
        } else if (xhr.status === 405) {
            message = 'طريقة الطلب غير مسموحة';
        } else if (xhr.status === 422) {
            message = 'البيانات المدخلة غير صحيحة';
        } else if (xhr.status === 404) {
            message = 'الطلب المطلوب غير موجود';
        } else if (xhr.status === 500) {
            message = 'خطأ في الخادم، يرجى المحاولة لاحقاً';
        }

        toastr.error(message);
    }

    showLoading() {
        Swal.fire({
            title: 'جاري المعالجة...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    hideLoading() {
        Swal.close();
    }

    refreshTable() {
        if (this.table) {
            this.table.ajax.reload(null, false);
        }
    }

    updateStatistics() {
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }

    setTable(table) {
        this.table = table;
    }

    static initForPage(routeBaseName) {
        const manager = new ApprovalManager(routeBaseName);
        window.approvalManager = manager;
        return manager;
    }
}

window.ApprovalManager = ApprovalManager;