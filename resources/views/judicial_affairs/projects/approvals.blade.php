@extends('layouts.layoutMaster')

@section('title', 'إعتماد المشاريع')

@section('breadcrumb')
    <li><a href="#">طلبات الإعتماد</a></li>

    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> إعتماد المشاريع</a>
        <i class="ti ti-star favorite-icon" data-page-name="إعتماد المشاريع" data-page-url="{{ url()->current() }}"
            onclick="toggleFavorite(event, this)"></i>
    </li>
@endsection

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
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
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: function() {
                        return $(this).data('placeholder');
                    },
                    allowClear: true,
                    width: '100%',
                    language: 'ar',
                    dir: 'rtl',
                    dropdownParent: $(this).closest(
                        '#statusModal, #card-project')
                });
            });

            var table = $('#projects-table').DataTable({
                // كود تحريك النص
                "createdRow": function(row, data, dataIndex) {
                    // البحث فقط عن الخلايا التي تحتوي على فئة 'table-ellipsis' وتغليف النص داخل span
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellText = $(this).text();
                        $(this).html('<div class="cell-content"><span>' + cellText +
                            '</span></div>');
                    });
                },
                // كود تحريك النص
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('projects.approvals') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.contract_id = $('#filter-contract_id').val();
                        d.employee = $('#filter-employee').val();
                    }
                },
                columns: [{
                    data: ""
                }, {
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'custom-checkbox',
                    render: function(data, type, full, meta) {
                        return '<input type="checkbox" class="row-checkbox" value="' + full.id +
                            '">';
                    }
                }, {
                    data: 'project_name',
                    name: 'project_name',
                    className: 'text-nowrap'

                }, {
                    data: 'project_number',
                    name: 'project_number',
                    className: 'table-ellipsis',


                }, {
                    data: 'start_date',
                    name: 'start_date'
                }, {
                    data: 'new_status_id',
                    name: 'new_status_id'
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'id',
                    name: 'id',
                    visible: false
                }],
                order: [
                    [7, 'desc']
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
                        className: 'btn btn-export btn-sm',
                        text: 'الإجراءات',
                        buttons: [{
                                extend: 'copy',
                                text: 'نسخ'
                            }, {
                                extend: 'excel',
                                text: 'إكسل'
                            },
                            {
                                extend: 'print',
                                text: 'طباعة'
                            }, {
                                text: 'حذف المحدد',
                                className: 'btn btn-default btn-delete-selected',
                                action: function(e, dt, node, config) {
                                    var selectedIds = [];
                                    $('.row-checkbox:checked').each(function() {
                                        selectedIds.push($(this).val());
                                    });

                                    confirmDeleteSelectedmss(selectedIds,
                                        "{{ route('mass.delete') }}", 'Project'
                                    );
                                }
                            }

                        ]
                    },


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

            // تحديث الجدول عند تغيير الفلاتر
            $('#filter-status, #filter-contract_id, #filter-employee').change(function() {
                table.draw();
            });

            // تحديد الكل
            $('#select-all').on('click', function() {
                var rows = table.rows({
                    'search': 'applied'
                }).nodes();
                $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });
            // كود تحريك النص
            $(document).on('mouseenter', '.table-ellipsis', function() {
                var span = $(this).find('span');
                var textWidth = span.width();
                var cellWidth = $(this).width();
                if (textWidth > cellWidth) {
                    // تحريك النص من اليسار إلى اليمين بما يتناسب مع النص العربي
                    span.css('transform', 'translateX(' + (textWidth - cellWidth) + 'px)');
                }
            }).on('mouseleave', '.table-ellipsis', function() {
                $(this).find('span').css('transform', 'translateX(0)');
            });
            // كود تحريك النص

        });
    </script>

@endsection

@section('content')
    <!-- جدول المشاريع -->
    <div class="card" id="card-project">
        <div class="card-body">
            <div class="filters row g-3">
                <div class="col-md-6">
                    <label for="filter-status">حالة المشروع</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر حالة المشروع">
                        <option value=""></option>
                        <option value="ongoing">جاري</option>
                        <option value="completed">مكتمل</option>
                        <option value="postponed">مؤجل</option>
                        <option value="canceled">ملغى</option>
                        <option value="closed">مغلق</option>
                    </select>
                </div>
                <!-- العميل -->
                <div class="col-md-6">
                    <label for="filter-contract_id">العقد</label>
                    <select id="filter-contract_id" class="form-control select2" data-placeholder="اختر العقد">
                        <option value=""></option>
                        @foreach (\App\Models\judicial_affairs\Contract::select(['id', 'contract_name'])->orderBy('id', 'desc')->get() as $contract)
                            <option value="{{ $contract->id }}">{{ $contract->contract_name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
            <hr class="mt-10">
            <table id="projects-table" class="table table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th class="text-center">اسم المشروع</th>
                        <th class="text-center">رقم المشروع</th>
                        <th class="text-center">تاريخ البدء</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- سيتم تعبئة البيانات بواسطة DataTables -->
                </tbody>
            </table>
        </div>
    </div>

@endsection
