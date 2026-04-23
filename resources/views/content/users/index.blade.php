@extends('layouts.layoutMaster')

@section('title', 'قائمة المستخدمين')

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

            var table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('user.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.role = $('#filter-role').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            // جلب المسار الخاص بالموظف أو تعيين المسار الافتراضي #
                            var employeeUrl = row.employee_id ? "{{ url('employees') }}/" + row
                                .employee_id : "#";

                            // تعيين الصورة
                            var imageUrl = row.image ? "{{ asset('storage/') }}/" + row.image :
                                "{{ asset('assets/img/avatars/1.png') }}";

                            return `<div class="d-flex align-items-center">
                    <img src="${imageUrl}" alt="User Image" class="rounded-circle" width="50" height="50">
                    <div class="ms-3">
                        <a href="${employeeUrl}" style="text-decoration: none; color: inherit;">
                            <p class="mb-0">${data}</p>
                            <small class="text-muted">${row.email}</small>
                        </a>
                    </div>
                </div>`;
                        },
                        orderable: false,
                        searchable: false
                    },


                    {
                        data: 'nationality',
                        name: 'nationality',

                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'roles',
                        name: 'roles',
                        render: function(data, type, row) {
                            if (data && data.length > 0) {
                                let rolesList = data.map(role =>
                                    `<span class="badge bg-label-info me-1">${role}</span>`);
                                return rolesList.join(' ');
                            } else {
                                return '<span class="text-muted">لا يوجد دور</span>';
                            }
                        },
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            let status_label = '';
                            if (data == 'active') {
                                status_label = '<span class="badge bg-label-success">نشط</span>';
                            } else if (data == 'inactive') {
                                status_label = '<span class="badge bg-label-danger">غير نشط</span>';
                            } else {
                                status_label = '<span class="badge bg-label-warning">معلق</span>';
                            }
                            return status_label;
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: '<"dt-toolbar"<"dt-toolbar-left"l><"dt-toolbar-right"fB>>rt<"row"<"col-12 d-flex align-items-center justify-content-between"ip>>',
                buttons: [{
                        extend: 'collection',
                        className: 'btn btn-export btn-sm',
                        text: 'تصدير',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            },
                            {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            {
                                extend: 'print',
                                text: 'طباعة'
                            }
                        ]
                    },

                    @can('إضافة مستخدم')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة مستخدم جديد',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href = "{{ route('user.create') }}";
                            }
                        }
                    @endcan
                ],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: true
            });

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-role').change(function() {
                table.draw();
            });
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'هل أنت متأكد من الحذف؟',
                text: "لن تتمكن من التراجع عن هذا!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذفها!',
                cancelButtonText: 'إلغاء',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-secondary ms-2'
                },
                buttonsStyling: false
            }).then(function(result) {
                if (result.value) {
                    $('#delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row g-4 mb-4">
        <!-- بطاقة إجمالي المستخدمين -->
        <div class="col-sm-4 ">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المستخدمين</span>
                            <div class="d-flex align-items-center my-1">
                                @php
                                    $totalUsers = \App\Models\User::count();
                                @endphp
                                <h4 class="mb-0 me-2">{{ $totalUsers }}</h4>
                            </div>
                            <small class="mb-0">إجمالي المستخدمين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-users ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- بطاقة المستخدمين النشطين -->
        <div class="col-sm-4 ">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المستخدمين النشطين</span>
                            <div class="d-flex align-items-center my-1">
                                @php
                                    $activeUsers = \App\Models\User::where('status', 'active')->count();
                                @endphp
                                <h4 class="mb-0 me-2">{{ $activeUsers }}</h4>
                            </div>
                            <small class="mb-0">عدد المستخدمين النشطين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-user-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- بطاقة المستخدمين المعلقين -->
        {{--  <div class="col-sm-6 ">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المستخدمين المعلقين</span>
                            <div class="d-flex align-items-center my-1">
                                @php
                                    $pendingUsers = \App\Models\User::where('status', 'pending')->count();
                                    $percentage = $totalUsers > 0 ? ($pendingUsers / $totalUsers) * 100 : 0;
                                @endphp
                                <h4 class="mb-0 me-2">{{ $pendingUsers }}</h4>
                                <p class="text-warning mb-0">(+{{ round($percentage) }}%)</p>
                            </div>
                            <small class="mb-0">عدد المستخدمين المعلقين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-user-plus ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>  --}}
        <!-- بطاقة المستخدمين غير النشطين -->
        <div class="col-sm-4 ">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">المستخدمين غير النشطين</span>
                            <div class="d-flex align-items-center my-1">
                                @php
                                    $inactiveUsers = \App\Models\User::where('status', 'inactive')->count();
                                @endphp
                                <h4 class="mb-0 me-2">{{ $inactiveUsers }}</h4>
                            </div>
                            <small class="mb-0">عدد المستخدمين غير النشطين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-user-off ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- فلاتر و جدول المستخدمين -->
    <div class="card">
        <div class="card-body">
            <div class="filters row g-3">
                <!-- حالة المستخدم -->
                <div class="col-md-6">
                    <label for="filter-status">حالة المستخدم</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر حالة المستخدم">
                        <option value=""></option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="pending">معلق</option>
                    </select>
                </div>
                <!-- دور المستخدم -->
                <div class="col-md-6">
                    <label for="filter-role">دور المستخدم</label>
                    <select id="filter-role" class="form-control select2" data-placeholder="اختر دور المستخدم">
                        <option value=""></option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            <table id="users-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>م</th>
                        <th>المستخدم</th>
                        <th>الجنسية</th>
                        <th>الأدوار</th>
                        <th>الحالة</th>
                        <th>البريد الإلكتروني</th>
                        <th>تاريخ الإضافة</th>
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
