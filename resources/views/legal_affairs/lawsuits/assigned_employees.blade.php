@extends('layouts.layoutMaster')

@section('title', 'المكلفين بالدعوى')

@section('breadcrumb')
    <li><a href="{{ route('legal-affairs.lawsuits.index') }}"> الدعاوى</a></li>
    <li><a href="{{ route('legal-affairs.lawsuits.show', $lawsuit->id) }}"> {{ $lawsuit->name }}</a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#">المكلفين بالدعوى</a>
        <i class="ti ti-star favorite-icon" data-page-name="المكلفين بالدعاوى" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
    @vite(['resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss'])
    @vite(['resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
    @vite(['resources/assets/vendor/libs/animate-css/animate.scss'])
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/moment/moment.js'])
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js'])
    @vite(['resources/assets/vendor/libs/@form-validation/bootstrap5.js'])
    @vite(['resources/assets/vendor/libs/@form-validation/auto-focus.js'])
    @vite(['resources/assets/vendor/libs/cleavejs/cleave.js'])
    @vite(['resources/assets/vendor/libs/cleavejs/cleave-phone.js'])
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('toastr')
    @vite(['resources/css/toastr.css', 'resources/js/toastr.js'])
@endsection

@section('page-style')
    @vite(['resources/css/dataTable.css'])
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            {{--  <h4 class="card-title">المكلفين للدعوى: {{ $lawsuit->name }}</h4>  --}}

            <table id="employees-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>الاسم</th>
                        <th>الحالة</th>
                        <th>تم المعالجة بواسطة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة الجدول بواسطة DataTables -->
                </tbody>
            </table>

            <div class="mt-3">
                {{--  <button type="button" id="accept-selected" class="btn btn-success">قبول المحدد</button>
                <button type="button" id="reject-selected" class="btn btn-danger">رفض المحدد</button>  --}}
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            var table = $('#employees-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('legal-affairs.lawsuits.assignedEmployees', $lawsuit->id) }}',
                columns: [{
                        data: ""
                    },
                    {
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'custom-checkbox',
                    },
                    {
                        data: 'assigned_to',
                        name: 'assigned_to',
                        className: 'text-nowrap',

                    },
                    {
                        data: 'status',
                        name: 'status',

                    },
                    {
                        data: 'user_accepted_id',
                        name: 'user_accepted_id',
                        className: 'text-nowrap',


                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [2, 'asc']
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
                    text: 'الاجراءات',
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
                        },
                        {
                            text: 'قبول المحدد',
                            {{--  className: 'btn btn-success',  --}}
                            action: function(e, dt, node, config) {
                                var selectedIds = [];
                                $('.row-checkbox:checked').each(function() {
                                    selectedIds.push($(this).val());
                                });

                                if (selectedIds.length > 0) {
                                    $.ajax({
                                        url: '{{ route('legal-affairs.lawsuits.acceptAssignedEmployees', $lawsuit->id) }}',
                                        method: 'POST',
                                        data: {
                                            ids: selectedIds,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function(response) {
                                            table.ajax.reload(null, false);
                                            toastr.success(response.success);
                                        },
                                        error: function(xhr) {
                                            toastr.error(
                                                'حدث خطأ أثناء قبول المكلفين.'
                                            );
                                        }
                                    });
                                } else {
                                    toastr.warning('يرجى تحديد المكلفين أولاً.');
                                }
                            }
                        },
                        {
                            text: 'رفض المحدد',
                            {{--  className: 'btn btn-danger',  --}}
                            action: function(e, dt, node, config) {
                                var selectedIds = [];
                                $('.row-checkbox:checked').each(function() {
                                    selectedIds.push($(this).val());
                                });

                                if (selectedIds.length > 0) {
                                    $.ajax({
                                        url: '{{ route('legal-affairs.lawsuits.rejectAssignedEmployees', $lawsuit->id) }}',
                                        method: 'POST',
                                        data: {
                                            ids: selectedIds,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function(response) {
                                            table.ajax.reload(null, false);
                                            toastr.success(response.success);
                                        },
                                        error: function(xhr) {
                                            toastr.error(
                                                'حدث خطأ أثناء رفض المكلفين.'
                                            );
                                        }
                                    });
                                } else {
                                    toastr.warning('يرجى تحديد المكلفين أولاً.');
                                }
                            }
                        },
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
                }, ],
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
                                return col.title !==
                                    "" // ? Do not show row in modal popup if title is blank (for check box)
                                    ?
                                    '<tr data-dt-row="' +
                                    col.rowIndex +
                                    '" data-dt-column="' +
                                    col.columnIndex +
                                    '">' +
                                    "<td>" +
                                    col.title +
                                    ":" +
                                    "</td> " +
                                    "<td>" +
                                    col.data +
                                    "</td>" +
                                    "</tr>" :
                                    "";
                            }).join("");

                            return data ?
                                $('<table class="table"/><tbody />').append(data) :
                                false;
                        },
                    },
                },
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // قبول المحدد
            $('#accept-selected').on('click', function() {
                var selectedIds = [];
                $('.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length > 0) {
                    $.ajax({
                        url: '{{ route('legal-affairs.lawsuits.acceptAssignedEmployees', $lawsuit->id) }}',
                        method: 'POST',
                        data: {
                            ids: selectedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            toastr.success(response.success);
                        },
                        error: function(xhr) {
                            toastr.error('حدث خطأ أثناء قبول المكلفين.');
                        }
                    });
                } else {
                    toastr.warning('يرجى تحديد المكلفين أولاً.');
                }
            });

            // رفض المحدد
            $('#reject-selected').on('click', function() {
                var selectedIds = [];
                $('.row-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length > 0) {
                    $.ajax({
                        url: '{{ route('legal-affairs.lawsuits.rejectAssignedEmployees', $lawsuit->id) }}',
                        method: 'POST',
                        data: {
                            ids: selectedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            toastr.success(response.success);
                        },
                        error: function(xhr) {
                            toastr.error('حدث خطأ أثناء رفض المكلفين.');
                        }
                    });
                } else {
                    toastr.warning('يرجى تحديد المكلفين أولاً.');
                }
            });

            // قبول مكلف فردي
            $(document).on('click', '.accept-single', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{ route('legal-affairs.lawsuits.acceptAssignedEmployees', $lawsuit->id) }}',
                    method: 'POST',
                    data: {
                        ids: [id],
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload(null, false);
                        toastr.success('تم قبول المكلف.');
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء قبول المكلف.');
                    }
                });
            });

            // رفض مكلف فردي
            $(document).on('click', '.reject-single', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{ route('legal-affairs.lawsuits.rejectAssignedEmployees', $lawsuit->id) }}',
                    method: 'POST',
                    data: {
                        ids: [id],
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload(null, false);
                        toastr.success('تم رفض المكلف.');
                    },
                    error: function(xhr) {
                        toastr.error('حدث خطأ أثناء رفض المكلف.');
                    }
                });
            });

        });
    </script>
@endsection
