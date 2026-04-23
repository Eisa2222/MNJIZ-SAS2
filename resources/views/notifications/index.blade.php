@extends('layouts.layoutMaster')

@section('title', 'الإشعارات')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/form-validation.js'])
    <script>
        $(document).ready(function() {
            // تهيئة DataTable
            var table = $('#notification-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('notifications.index') }}",
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox',
                        render: function(data, type, full, meta) {
                            return '<input type="checkbox" class="row-checkbox" value="' + full.id +
                                '">';
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        className: 'table-ellipsis',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                    },
                ],
                order: [
                    [2, 'desc']
                ], // ترتيب الجدول حسب التاريخ تنازليًا
                dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"fB>>rt<"row"<"col-12 d-flex align-items-center justify-content-between"ip>>',
                buttons: [{
                        extend: 'collection',
                        className: 'btn btn-export btn',
                        text: 'الإجراءات',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            },
                            {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            {
                                text: 'حذف المحدد',
                                className: 'btn btn-default btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });
                                    if (selectedIds.length > 0) {
                                        confirmDeleteSelectedmss(selectedIds);
                                    } else {
                                        showToast('لا توجد إشعارات محددة للحذف.', 'warning');
                                    }
                                }
                            }
                        ]
                    },
                    {
                        text: 'علامة مقروءة للجميع',
                        className: 'btn btn-primary btn-add',
                        action: function(e, dt, node, config) {
                            markAllNotificationsAsRead();
                        }
                    }
                ],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: true,
                rowCallback: function(row, data) {
                    if (data.read_at === null) {
                        $(row).addClass(
                            'table-warning'); // تمييز الصف بلون مختلف (يمكنك تغيير الكلاس حسب التصميم)
                    } else {
                        $(row).removeClass('table-warning');
                    }
                }
            });

            // التعامل مع صندوق اختيار الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // التعامل مع تغيير حالة صندوق الاختيار في الصفوف
            $('#notification-table tbody').on('change', 'input.row-checkbox', function() {
                if (!this.checked) {
                    var el = $('#select-all').get(0);
                    if (el && el.checked && ('indeterminate' in el)) {
                        el.indeterminate = true;
                    }
                }
            });

            // دالة لإظهار التوستر باستخدام Toastr
            function showToast(message, type = 'success') {
                if (type === 'success') {
                    toastr.success(message);
                } else if (type === 'error') {
                    toastr.error(message);
                } else if (type === 'warning') {
                    toastr.warning(message);
                } else {
                    toastr.info(message);
                }
            }

            // دالة لتحديث عدادات الإشعارات
            function updateUnreadCount(count) {
                // تحديث شارة الإشعار بجانب أيقونة الجرس
                const notificationBadge = document.getElementById('notification-badge');
                if (notificationBadge) {
                    if (count > 0) {
                        notificationBadge.textContent = count > 99 ? '99+' : count;
                        notificationBadge.style.display = 'inline-block';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }

                // تحديث شارة "جديد" في رأس القائمة المنسدلة
                const notificationHeaderBadge = document.getElementById('notification-header-badge');
                if (notificationHeaderBadge) {
                    notificationHeaderBadge.textContent = `${count} جديد`;
                }
            }

            // دالة تحديد جميع الإشعارات كمقروءة
            window.markAllNotificationsAsRead = function() {
                fetch("{{ route('notifications.markAllAsRead') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // تحديث العدادات
                            updateUnreadCount(data.unread_count);
                            // إظهار التوستر
                            showToast(data.message, 'success');
                            // إعادة تحميل DataTable بدون إعادة تعيين صفحة التصفح
                            table.ajax.reload(null, false);
                        } else {
                            showToast('حدث خطأ أثناء محاولة تحديد الإشعارات كمقروءة.', 'danger');
                        }
                    })
                    .catch(() => {
                        showToast('حدث خطأ في الاتصال بالخادم.', 'danger');
                    });
            }

            // دالة تحديد إشعار واحد كمقروء
            window.markNotificationAsRead = function(notificationId) {
                fetch(`/notifications/${notificationId}/mark-as-read`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // تحديث العدادات
                            updateUnreadCount(data.unread_count);
                            // إظهار التوستر
                            showToast(data.message, 'success');
                            // إعادة تحميل DataTable بدون إعادة تعيين صفحة التصفح
                            table.ajax.reload(null, false);
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => {
                        showToast('حدث خطأ في الاتصال بالخادم.', 'danger');
                    });
            }

            // دالة حذف إشعار
            window.deleteNotification = function(notificationId) {
                fetch(`/notifications/${notificationId}`, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // تحديث العدادات
                            updateUnreadCount(data.unread_count);
                            // إظهار التوستر
                            showToast(data.message, 'success');
                            // إعادة تحميل DataTable بدون إعادة تعيين صفحة التصفح
                            table.ajax.reload(null, false);
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => {
                        showToast('حدث خطأ في الاتصال بالخادم.', 'danger');
                    });
            }

            // دالة حذف الإشعارات المحددة
            window.confirmDeleteSelectedmss = function(selectedIds) {
                if (selectedIds.length === 0) {
                    showToast('لا توجد إشعارات محددة للحذف.', 'warning');
                    return;
                }
                fetch("{{ route('notifications.destroySelected') }}", {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            ids: selectedIds
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // تحديث العدادات
                            updateUnreadCount(data.unread_count);
                            // إظهار التوستر
                            showToast(data.message, 'success');
                            // إعادة تحميل DataTable بدون إعادة تعيين صفحة التصفح
                            table.ajax.reload(null, false);
                        } else {
                            showToast(data.message, 'danger');
                        }
                    })
                    .catch(() => {
                        showToast('حدث خطأ في الاتصال بالخادم.', 'danger');
                    });
            }
        });
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            {{-- <div class="mb-3 d-flex justify-content-between align-items-center">
                <button class="btn btn-primary" onclick="markAllNotificationsAsRead()">علامة مقروءة للجميع</button>
            </div> --}}

            <table id="notification-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>الاسم</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
@endsection
