@extends('layouts.layoutMaster')

@section('title', ' طلبات الإجازات')

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> طلبات الإجازات</a>
        <i class="ti ti-star favorite-icon" data-page-name=" طلبات الإجازات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

{{-- Vendor Styles --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

{{-- Toastr --}}
@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

{{-- Page Style --}}
@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

{{-- Page Script --}}
@section('page-script')
    <script>
        $(document).ready(function() {
            // تهيئة Select2 للفلاتر
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            // تهيئة DataTable لعرض  الطلبات مع اختيار الصفوف وأزرار الإجراءات
            var table = $('#all-leave-requests-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('hr.leave-requests.index') }}",
                    data: function(d) {
                        d.filter_leave_type = $('#filter-leave-type').val();
                        d.filter_employee = $('#filter-employee').val();
                        d.filter_status = $('#filter-status').val();
                    }
                },
                columns: [{
                        data: null,
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox text-center',
                        render: function(data, type, full, meta) {
                            return '<input type="checkbox" class="row-checkbox" value="' + full.id +
                                '">';
                        }
                    },
                    {
                        data: 'employee_name',
                        name: 'employee.name',
                        className: 'text-center'
                    },
                    {
                        data: 'leave_type_name',
                        name: 'leaveType.name',
                        className: 'text-center'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        className: 'text-center'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        className: 'text-center'
                    },
                    {
                        data: 'days_count',
                        name: 'days_count',
                        className: 'text-center',
                        render: function(data, type, row) {
                            var numValue = parseFloat(data);

                            if (Number.isInteger(numValue)) {
                                return Math.floor(numValue);
                            } else {
                                return numValue;
                            }
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center'
                    }
                ],
                order: [
                    [1, 'desc']
                ],
                dom: '<"dt-toolbar"' +
                    '<"dt-toolbar-left"l>' +
                    '<"dt-toolbar-right"fB>' +
                    '>' +
                    'rt' +
                    '<"row"' +
                    '<"col-12 d-flex align-items-center justify-content-between"i p>' +
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
                        {
                            text: 'حذف المحدد',
                            className: 'btn btn-outline-danger btn-delete-selected',
                            action: function(e, dt, node, config) {
                                var selectedIds = [];
                                $('.row-checkbox:checked').each(function() {
                                    selectedIds.push($(this).val());
                                });
                                if (selectedIds.length === 0) {
                                    toastr.warning('يرجى تحديد على الأقل صفًا واحدًا.');
                                    return;
                                }
                                // استدعاء دالة الحذف الجماعي (تأكد من وجود confirmDeleteSelectedmss)
                                confirmDeleteSelectedmss(
                                    selectedIds,
                                    "{{ route('mass.delete') }}",
                                    "LeaveRequest"
                                );
                            }
                        }
                    ]
                }],
                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                responsive: true
            });

            // منع تداخل حدث النقر داخل الصفوف مع الروابط: عند النقر على رابط يتم منع انتقال الحدث للصف
            $('#all-leave-requests-table').on('click', 'a', function(e) {
                e.stopPropagation();
            });

            // إعادة تحميل الجدول عند تغيير الفلاتر
            $('#filter-leave-type, #filter-status, #filter-employee').change(function() {
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
    </script>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            {{-- الفلاتر فوق الجدول --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">نوع الإجازة</label>
                    <select id="filter-leave-type" class="form-control select2" data-placeholder="اختر نوع الإجازة">
                        <option value=""></option>
                        @foreach ($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">الموظف</label>
                    <select id="filter-employee" class="form-control select2" data-placeholder="اختر الموظف">
                        <option value=""></option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">حالة الطلب</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة">
                        <option value=""></option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="approved">موافَق عليه</option>
                        <option value="rejected">مرفوض</option>
                        <option value="closed">مغلق</option>
                    </select>
                </div>
            </div>

            <hr class="mt-10">
            <table id="all-leave-requests-table" class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th class="text-center" style="width:40px;">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>الموظف</th>
                        <th>نوع الإجازة</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>عدد الأيام</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>
@endsection
