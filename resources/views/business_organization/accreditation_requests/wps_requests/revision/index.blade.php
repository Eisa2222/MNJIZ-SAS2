{{-- resources/views/hr/payroll/index.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'طلبات المراجعة')

@section('breadcrumb')
    <li><a href="#">طلبات الإعتماد</a></li>
    <li><a href="{{ route('accreditation-requests.wps-requests.index') }}"> إعتمادات مسيرات الرواتب</a></li>
    <li><a href="{{ route('accreditation-requests.wps-requests.show',$wps_payroll->id) }}"> {{ $wps_payroll->reference }}</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#">طلبات المراجعة</a>
        <i class="ti ti-star favorite-icon" data-page-name="طلبات المراجعة" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

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
                    url: "{{ route('accreditation-requests.wps-requests.revision.index', $wps_payroll->id) }}",

                },
                columns: [{
                        data: "",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'employee',
                        name: 'employee'
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
                        data: 'notes_reply',
                        name: 'notes_reply',
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
                }, ],

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
                        <th>الراتب الاساسي</th>
                        <th>بدل النقل</th>
                        <th>بدل السكن</th>
                        <th>بدلات اخرى</th>
                        <th>التأمينات</th>
                        <th>الاستقطاعات</th>
                        <th>الحوافز</th>
                        <th>الصافي</th>
                        <th>الملاحظات و الرد</th>
                        <th>الاجراءات</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection
