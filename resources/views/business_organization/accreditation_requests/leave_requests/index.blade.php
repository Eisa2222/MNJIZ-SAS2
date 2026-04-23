@extends('layouts.layoutMaster')

@section('title', 'إعتماد الإجازات')

@section('breadcrumb')
    <li><a href="#">طلبات الإعتماد</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إعتماد الإجازات</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعتماد الإجازات" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

{{-- Vendor Styles --}}
@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@endsection

{{-- Vendor Scripts --}}
@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
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
        var table = $('#approval-requests-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('accreditation-requests.leave-requests.index') }}",
                data: function(d) {
                    // تمرير قيمة الفلتر الخاص بالموظف
                    d.filter_employee = $('#filter-employee').val();
                    // تمرير قيمة فلتر نوع الإجازة إذا وُجد
                    d.filter_leave_type = $('#filter-leave-type').val();
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                }, // عمود id موجود في الاستعلام
                {
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'leave_type_name',
                    name: 'leave_type_name'
                },
                {
                    data: 'start_date',
                    name: 'start_date'
                },
                {
                    data: 'end_date',
                    name: 'end_date'
                },
                {
                    data: 'days_count',
                    name: 'days_count'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            order: [
                [0, 'asc'] // ترتيب بناءً على عمود id
            ],

            language: {
                url: "{{ asset('assets/json/ar.json') }}"
            },
            responsive: true
        });

        // تهيئة الـ Select2 للفلتر الخاص بالموظفين
        $('#filter-employee').select2({
            placeholder: 'اختر الموظف',
            allowClear: true,
            width: '100%',
            language: 'ar',
            dir: 'rtl'
        });

        // تهيئة الـ Select2 للفلاتر الأخرى إذا لم تكن موجودة
        $('.select2').select2({
            placeholder: function() {
                return $(this).data('placeholder');
            },
            allowClear: true,
            width: '100%',
            language: 'ar',
            dir: 'rtl'
        });

        // عند تغيير الفلاتر، إعادة تحميل الجدول
        $('#filter-employee, #filter-leave-type').change(function() {
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
<!-- الفلاتر فوق الجدول -->
<div class="card">
    <div class="card-body">
        <div class="filters row g-3">
            <!-- فلتر نوع الإجازة -->
            <div class="col-md-6">
                <label for="filter-leave-type" class="form-label">نوع الإجازة</label>
                <select id="filter-leave-type" class="form-control select2" data-placeholder="اختر نوع الإجازة">
                    <option value=""></option>
                    @foreach (\App\Models\general_setting\SettingsLeaveType::orderBy('id', 'desc')->get() as $leaveType)
                        <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- فلتر الموظف بدلاً من حالة الطلب -->
            <div class="col-md-6">
                <label for="filter-employee" class="form-label">الموظف</label>
                <select id="filter-employee" class="form-control select2" data-placeholder="اختر الموظف">
                    <option value=""></option>
                    @foreach (\App\Models\Hr\Employees\Employees::orderBy('id', 'desc')->get() as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="mt-10">

        <!-- الجدول -->
        <table id="approval-requests-table" class="table table-striped table-bordered text-center">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>الموظف</th>
                    <th>نوع الإجازة</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>عدد الأيام</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection
