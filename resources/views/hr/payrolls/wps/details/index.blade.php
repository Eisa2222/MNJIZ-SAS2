{{-- resources/views/hr/payroll/index.blade.php
@extends('layouts.layoutMaster')



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
            // تهيئة Select2
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#table').DataTable({
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellText = $(this).text();
                        $(this).html('<div class="cell-content"><span>' + cellText +
                            '</span></div>');
                    });
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('hr.payrolls.wps.details.index', $wps_payroll->id) }}",

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
                }, {

                    action:

                }],

                language: {
                    url: "{{ asset('assets/json/ar.json') }}"
                },
                orderCellsTop: true,
                columnDefs: [{
                    className: "control",
                    orderable: false,
                    targets: 0,
                    render: function(data, type, full, meta) {
                        return "";
                    },
                }],
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function(row) {
                                var data = row.data();
                                return "التفاصيل";
                            },
                        }),
                        type: "column",
                        renderer: function(api, rowIdx, columns) {
                            var data = $.map(columns, function(col, i) {
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
        });
    </script>
@endsection

@section('content')

    <div class="card">

        <div class="card-body">
            <table id="table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th>الموظف</th>
                        <th></th>
                        <th></th>
                        <th>بدل السكن</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>الحالة</th>
                        <th>الاجراءات</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection --}}


@extends('layouts.layoutMaster')

@section('title', $wps_payroll->reference)

@section('breadcrumb')
    <li><a href="#"> الموارد البشرية</a></li>
    <li><a href="{{ route('hr.payrolls.wps.index') }}">مسيرات الرواتب</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#">{{ $wps_payroll->reference }}</a>
        <i class="ti ti-star favorite-icon" data-page-name="مسيرات الرواتب" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/js/custom/select-all.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleavejs/cleave.js', 'resources/assets/vendor/libs/cleavejs/cleave-phone.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    {!! $dataTable->scripts() !!}
@endsection

@section('page-script')
    @vite(['resources/assets/js/hr/employees/employees.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')
    {{-- <div class="row g-2 mb-4">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">الموظفون</span>
                            <div class="d-flex align-items-center my-1">

                                <h4 class="mb-0 me-2">{{ $totalEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">إجمالي الموظفين</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-users-group ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">مورد حالي</span>
                            <div class="d-flex align-items-center my-1">

                                <h4 class="mb-0 me-2">{{ $currentEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف حالي</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-briefcase ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">متاح للإستدعاء</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $availableEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف متاح للإستدعاء</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-circle-check ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">متاح جزئيا</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $partiallyAvailableEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف متاح جزئيا</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-clock-hour-3 ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading"> غير المتاحين</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $unavailableEmployees }}</h4>
                            </div>
                            <small class="mb-0 text-muted">موظف غير متاح</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-user-pause ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            {{-- <div class="filters row g-3">
                <div class="col-md-4">
                    <label for="filter-hr_status_id">حالة الموظف</label>
                    <select id="filter-hr_status_id" class="form-control select2" data-placeholder="اختر حالة الموظف">
                        <option value=""></option>
                        @foreach ($hrStatus as $status)
                            <option value="{{ $status->id }}" {{ old('status') == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-role">المسمى الوظيفي</label> <!-- تغيير المسمى الوظيفي إلى الدور -->
                    <select id="filter-role" class="form-control select2" data-placeholder="اختر المسمى الوظيفي">
                        <option value=""></option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filter-insurance_status">حالة التأمينات</label>
                    <select id="filter-insurance_status" class="form-control select2"
                        data-placeholder="اختر حالة التأمينات">
                        <option value=""></option>
                        <option value="مضاف">مضاف</option>
                        <option value="مضاف غير رسمي">مضاف غير رسمي</option>
                        <option value="مستبعد">مستبعد</option>
                        <option value="غير مسجل">غير مسجل</option>
                    </select>
                </div>
            </div> --}}
            <hr class="mt-10">
            <div class="card-header">
                <div class="alert alert-warning text-center" role="alert">
                    يُرجى التأكد من مراجعة جميع البيانات جيدًا قبل تصديق المسير
                </div>
            </div>
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100'], true) !!}
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // التعامل مع زر تصديق المسير
            $(document).on('click', '.approve-payroll-btn', function(e) {
                e.preventDefault();

                const url = $(this).data('url');

                Swal.fire({
                    title: 'تأكيد تصديق المسير',
                    text: 'عند تصديق المسير لا يمكن التعديل عليه لاحقًا.',
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        // الانتقال مباشرة إلى الرابط
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
@endsection
