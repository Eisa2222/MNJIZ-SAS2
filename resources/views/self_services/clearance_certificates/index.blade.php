@extends('layouts.layoutMaster')

@section('title', 'إخلاء طرف')

@section('breadcrumb')
    <li><a href="#"> الخدمات الذاتية</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">إخلاء طرف </a>
        <i class="ti ti-star favorite-icon" data-page-name="إخلاء طرف" data-page-url="{{ url()->current() }}"
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
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('account.self-services.clearance-certificate.index') }}",

                },
                columns: [{
                        data: "",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'reason',
                        name: 'reason',
                        width: '250px',

                    },
                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '50px',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        width: '50px',

                    },
                    {
                        data: 'action',
                        name: 'action',
                        width: '50px',
                        orderable: false,
                        searchable: false,
                        visible: function() {
                            return {!! auth()->user()->can('تعديل طلب إخلاء طرف') ||
                            auth()->user()->can('حذف طلب إخلاء طرف') 
                                ? 'true'
                                : 'false' !!};
                        }()
                    }
                ],
                order: [
                    [3, 'desc']
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
                    },
                    @can('إضافة طلب إخلاء طرف')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> طلب خلو طرف',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href =
                                    "{{ route('account.self-services.clearance-certificate.create') }}";
                            }
                        }
                    @endcan

                ],
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

    <!-- جدول الرواتب -->
    <div class="card">
        <div class="card-body">
            <table id="table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th>السبب</th>
                        <th>ملاحظات</th>
                        <th>تاريخ الطلب</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection
