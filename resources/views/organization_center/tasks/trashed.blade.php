@extends('layouts.layoutMaster')

@section('title', 'ارشيف المهام')

@section('breadcrumb')
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> ارشيف المهام</a>
        <i class="ti ti-star favorite-icon" data-page-name="ارشيف المهام" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/js/tasks.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js', 'resources/assets/css/tasks.css'])
@endsection

@section('page-style')
    <style>
        /* تحسين أبعاد ومظهر مفتاح الـ switch */
        .form-check.form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-left: -2.5em;
            cursor: pointer;
        }

        .form-check.form-switch .form-check-label {
            margin-left: 0.5em;
            cursor: pointer;

        }
    </style>
    @vite(['resources/css/dataTable.css'])
@endsection


@section('page-script')
    <script>
        $(document).ready(function() {
            // تهيئة Select2 لحقول الفلترة
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            // تعريف متغير للتحقق من التحميل الأول
            var isFirstLoad = true;

            var table = $('#tasks-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('organization-center.tasks.trashed') }}",
                    data: function(d) {
                        d.priority = $('#filter-priority').val();
                        d.task_field = $('#filter-task-field').val();
                        d.status = $('#filter-status').val();
                    }
                },
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                columns: [{
                        className: 'dt-control',
                        orderable: false,
                        data: 'steps_data',
                        defaultContent: '',
                        render: function(data, type, row) {
                            if (data) {
                                return '<button class="btn btn-link p-0">خطوة</button>';
                            } else {
                                return '<p class=" p-0">مهمة</p>';
                            }
                        }
                    },
                    {
                        data: 'task_name',
                        name: 'task_name',
                    },
                    {
                        data: 'priority',
                        name: 'priority'
                    },
                    {
                        data: 'task_field',
                        name: 'task_field'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'assigned_users',
                        name: 'assigned_users'
                    },
                    {
                        data: 'remaining_days',
                        name: 'remaining_days'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by',
                        className: 'nowrap',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left d-flex flex-column flex-sm-row align-items-start align-items-sm-center"' +
                    'l' +
                    '>' +
                    '<"dt-toolbar-right d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-between"' +
                    'f' +
                    'B' +
                    '>' +
                    '>' +
                    'rt' +
                    '<"row mt-3"' +
                    '<"d-flex flex-column flex-sm-row align-items-center justify-content-between w-100"' +
                    'i' +
                    '<"pagination-wrapper overflow-auto w-100"' +
                    'p' +
                    '>' +
                    '>' +
                    '>',
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

                        // {
                        //     text: 'حذف المحدد',
                        //     className: 'btn btn-default btn-delete-selected',
                        //     action: function(e, dt, node, config) {
                        //         var selectedIds = [];
                        //         $('.row-checkbox:checked').each(function() {
                        //             selectedIds.push($(this).val());
                        //         });

                        //         confirmDeleteSelectedmss(selectedIds,
                        //             "{{ route('mass.delete') }}",
                        //             'Contract'
                        //         );
                        //     }
                        // }

                    ]
                }, ],
                order: [
                    [1, 'asc']
                ],
                responsive: true,

                // إضافة معالجة الصفوف المفتوحة
                rowCallback: function(row, data) {
                    $(row).attr('data-steps', data.steps_data ? 'true' : 'false');
                }
            });


            // معالج حدث النقر على زر التوسيع/الطي
            $('#tasks-table tbody').on('click', 'td.dt-control', function(e) {
                e.stopPropagation();
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var icon = $(this).find('i');

                if (row.child.isShown()) {
                    // إغلاق الصف
                    row.child.hide();
                    tr.removeClass('shown');
                    icon.removeClass('ti-chevron-down').addClass('ti-chevron-right');
                } else {
                    // فتح الصف إذا كان يحتوي على خطوات
                    if (row.data().steps_data) {
                        row.child(row.data().steps_data).show();
                        tr.addClass('shown');
                        icon.removeClass('ti-chevron-right').addClass('ti-chevron-down');
                    }
                }
            });

            // معالجة إعادة تحميل الجدول عند تغيير الفلاتر
            $('#filter-priority, #filter-task-field, #filter-status').change(function() {
                isFirstLoad = false; // تأكد من عدم فتح الصفوف تلقائياً عند تصفية البيانات
                table.draw(false);
            });

            // معالجة إعادة تحميل الجدول
            table.on('draw', function() {
                // إعادة تطبيق حالة الإغلاق/الفتح للصفوف
                table.rows().every(function() {
                    var tr = $(this.node());
                    var icon = tr.find('td.dt-control i');

                    if (tr.hasClass('shown')) {
                        // إذا كان الصف مفتوحاً، أعد فتحه
                        var row = this;
                        if (row.data().steps_data) {
                            row.child(row.data().steps_data).show();
                            icon.removeClass('ti-chevron-right').addClass('ti-chevron-down');
                        }
                    } else {
                        // تأكد من أن الأيقونة في الحالة الصحيحة
                        icon.removeClass('ti-chevron-down').addClass('ti-chevron-right');
                    }
                });
            });

            // عند النقر على زر "عرض الخطوات"
            $('#tasks-table tbody').on('click', '.toggle-steps', function() {
                let button = $(this);
                let rowId = button.data('id'); // رقم المهمة
                let tr = button.closest('tr'); // الصف الحالي
                let row = table.row(tr); // مرجع للصف في DataTables

                // إذا كانت الخطوات معروضة بالفعل
                if (row.child.isShown()) {
                    row.child.hide(); // إخفاء الخطوات
                    tr.removeClass('shown');
                    button.text('عرض الخطوات'); // إعادة نص الزر
                } else {
                    // في حال عدم العرض سابقًا، نقوم بجلب الخطوات من السيرفر وعرضها
                    $.ajax({
                        url: '/tasks/' + rowId + '/steps', // تأكد من وجود راوت مناسب
                        method: 'GET',
                        success: function(response) {
                            // ضع محتوى الخطوات في child row
                            row.child(response).show();
                            tr.addClass('shown');
                            button.text('إخفاء الخطوات'); // تغيير نص الزر
                        },
                        error: function() {
                            toastr.error('حدث خطأ أثناء جلب خطوات المهمة.');
                        }
                    });
                }
            });

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-priority, #filter-task-field, #filter-status').change(function() {
                table.draw();
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });
        });

        function confirmRestore(id) {
            Swal.fire({
                title: 'هل أنت متأكد من استعادة هذه المهمة ',
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
                didOpen: () => {
                    document.querySelector('.swal2-popup').style.fontSize = '0.85rem';
                }
            }).then((result) => {
                if (result.value) {
                    $('#restore-form-' + id).submit();
                }
            });
        }

        function confirmForceDelete(id) {
            Swal.fire({
                title: 'هل أنت متأكد من حذف  هذه المهمة نهائياً ؟',
                text: "لا يمكن التراجع عن هذا الإجراء!",
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
                didOpen: () => {
                    document.querySelector('.swal2-popup').style.fontSize = '0.85rem';
                }
            }).then((result) => {
                if (result.value) {
                    $('#force-delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection

@section('content')

    <script>
        var currentUserId = "{{ Auth::user()->id }}";
    </script>

    <div class="row g-4 mb-4">
        <!-- إجمالي المهام -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المهام</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalTasks }}</h4>
                            </div>
                            <small class="mb-0">إجمالي المهام</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-primary">
                                <i class="ti ti-list-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- المهام قيد الانتظار -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المهام قيد الانتظار</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $pendingTasks }}</h4>
                            </div>
                            <small class="mb-0">عدد المهام قيد الانتظار</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-danger">
                                <i class="ti ti-hourglass-empty ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- المهام قيد التنفيذ -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المهام قيد التنفيذ</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $inProgressTasks }}</h4>
                            </div>
                            <small class="mb-0">عدد المهام قيد التنفيذ</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-warning">
                                <i class="ti ti-loader ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- المهام المكتملة -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المهام المكتملة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $completedTasks }}</h4>
                            </div>
                            <small class="mb-0">عدد المهام المكتملة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-success">
                                <i class="ti ti-circle-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="filters row g-3">
                <!-- فلتر الأولوية -->
                <div class="col-md-4">
                    <label for="filter-priority">الأولوية</label>
                    <select id="filter-priority" class="form-control select2" data-placeholder="اختر الأولوية">
                        <option value=""></option>
                        <option value="low">منخفضة</option>
                        <option value="medium">متوسطة</option>
                        <option value="high">مرتفعة</option>
                    </select>
                </div>
                <!-- فلتر مجال المهمة -->
                <div class="col-md-4">
                    <label for="filter-task-field">مجال المهمة</label>
                    <select id="filter-task-field" class="form-control select2" data-placeholder="اختر مجال المهمة">
                        <option value=""></option>
                        <option value="offers">عروض</option>
                        <option value="contracts">عقود</option>
                        <option value="projects">مشاريع</option>
                        <option value="lawsuits">دعاوى</option>
                        <option value="sessions">جلسات</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <!-- فلتر حالة المهمة -->
                <div class="col-md-4">
                    <label for="filter-status">حالة المهمة</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة">
                        <option value=""></option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="in_progress">قيد التنفيذ</option>
                        <option value="completed">مكتملة</option>
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            <table id="tasks-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>المهمة</th>
                        <th>الأولوية</th>
                        <th>المجال</th>
                        <th>الحالة</th>
                        <th>المكلفين</th>
                        <th>المتبقي \ التأخير</th>
                        <th>أضافها</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة البيانات بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

@endsection
