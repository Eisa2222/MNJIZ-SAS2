@extends('layouts.layoutMaster')

@section('title', 'إعتمادات مسيرات الرواتب')

@section('breadcrumb')
    <li><a href="#">طلبات الإعتماد</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعتمادات مسيرات الرواتب</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعتمادات مسيرات الرواتب" data-page-url="{{ url()->current() }}"
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
    @vite(['resources/assets/js/form-validation.js'])
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

            // تعديل تهيئة Select2 في الموديل
            $('#generateModal .select2').select2({
                dropdownParent: $('#generateModal'),
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
                    url: "{{ route('accreditation-requests.wps-requests.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.year = $('#filter-year').val();
                        d.month = $('#filter-month').val();
                    }
                },
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
                        data: 'reference',
                        name: 'reference'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'created_by',
                        name: 'created_by'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'total_gross',
                        name: 'total_gross'
                    },
                    {
                        data: 'total_net',
                        name: 'total_net'
                    },
                    {
                        data: 'revision',
                        name: 'revision'
                    },
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

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-year, #filter-month').change(function() {
                table.draw();
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });

            // تأثير تحريك النص داخل الخلايا (ellipsis effect)
            $(document).on('mouseenter', '.table-ellipsis', function() {
                var span = $(this).find('span');
                var textWidth = span.width();
                var cellWidth = $(this).width();
                if (textWidth > cellWidth) {
                    span.css('transform', 'translateX(' + (textWidth - cellWidth) + 'px)');
                }
            }).on('mouseleave', '.table-ellipsis', function() {
                $(this).find('span').css('transform', 'translateX(0)');
            });

        });
    </script>
@endsection

@section('content')

    <!-- جدول الرواتب -->
    <div class="card">
        <div class="card-body">
            <!-- الفلاتر -->
            {{-- <div class="filters row g-3">
                <div class="col-md-4">
                    <label for="filter-status">الحالة</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر الحالة">
                        <option value=""></option>
                        @foreach ($wps_status as $key => $option)
                            <option value="{{ $option['name'] }}">
                                {{ $option['text'] }}
                            </option>
                        @endforeach

                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-year">السنة</label>
                    <select id="filter-year" name="year" class="form-control select2" data-placeholder="اختر السنة">
                        <option value=""></option>
                        @foreach ($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-month">الشهر</label>
                    <select id="filter-month" name="month" class="form-control select2" data-placeholder="اختر الشهر">
                        <option value=""></option>
                        @foreach ($months as $num => $name)
                            <option value="{{ str_pad($num, 2, '0', STR_PAD_LEFT) }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10"> --}}
            <table id="table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>المرجع</th>
                        <th>تاريخ التشغيل</th>
                        <th>تم بواسطة</th>
                        <th>الحالة</th>
                        <th>الإجمالي قبل الخصومات</th>
                        <th>الإجمالي بعد الخصومات</th>
                        <th> طلبات المراجعة</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>


@endsection
