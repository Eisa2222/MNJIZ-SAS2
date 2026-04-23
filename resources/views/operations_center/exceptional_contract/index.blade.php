@extends('layouts.layoutMaster')

@section('title', 'العقود الإستثنائية')

@section('breadcrumb')
    <li><a href="#"> مركز العمليات </a></li>
    <li class="breadcrumb-item d-flex align-items-center"><a href="#"> العقود الإستثنائية</a>
        <i class="ti ti-star favorite-icon" data-page-name="العقود الإستثنائية" data-page-url="{{ url()->current() }}"
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
            $('.select2').select2({
                placeholder: function() {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            var table = $('#setting-table').DataTable({
                // كود تحريك النص
                "createdRow": function(row, data, dataIndex) {
                    $(row).find('td.table-ellipsis').each(function() {
                        var cellContent = $(this)
                            .html(); // استخدام .html() للحفاظ على الهيكل الأصلي
                        // تحقق مما إذا كان يحتوي على رابط
                        if ($(this).find('a').length > 0) {
                            // إذا كان يحتوي على رابط، لف النص داخل الرابط نفسه
                            var linkElement = $(this).find('a');
                            var linkText = linkElement.text();
                            linkElement.html('<div class="cell-content"><span>' + linkText +
                                '</span></div>');
                        } else {
                            // إذا لم يكن هناك رابط، لف النص كالمعتاد
                            $(this).html('<div class="cell-content"><span>' + cellContent +
                                '</span></div>');
                        }
                    });
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route($route . '.index') }}",
                    data: function(d) {
                        d.employee = $('#filter-employee').val();
                        d.customer = $('#filter-customer').val();
                        d.status = $('#filter-status').val();
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
                    },
                    {
                        data: 'contract_name',
                        name: 'contract_name',
                        className: 'table-ellipsis',
                    },
                    {
                        render: function(data, type, row) {
                            return '<i class="ti ti-rosette-discount-check-filled text-success" title="عميل" ></i>' +
                                ' ' + data;
                        },
                        data: 'customer_id',
                        name: 'customer_id',
                        className: 'table-ellipsis text-right',

                    },
                    {
                        data: 'employee_id',
                        name: 'employee_id'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                    },
                    {
                        data: 'created_by',
                        name: 'created_by',
                        orderable: false,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: function() {
                            return {!! auth()->user()->can('تعديل عقد إستثنائي') || auth()->user()->can('حذف عقد إستثنائي') ? 'true' : 'false' !!};
                        }()
                    }, {
                        data: 'id',
                        name: 'id',
                        visible: false
                    }

                ],
                order: [
                    [8, 'desc']
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

                            @if (auth()->user()->can('حذف عقد إستثنائي'))
                                {
                                    text: 'حذف المحدد',
                                    className: 'btn btn-default btn-delete-selected',
                                    action: function(e, dt, node, config) {
                                        var selectedIds = [];
                                        $('.row-checkbox:checked').each(function() {
                                            selectedIds.push($(this).val());
                                        });

                                    }
                                }
                            @endif

                        ]
                    },

                    @can('إضافة عقد إستثنائي')
                        {
                            text: '<i class="fas fa-plus-circle me-1"></i> إضافة عقد إستثنائي ',
                            className: 'btn btn-primary btn-add',
                            action: function(e, dt, node, config) {
                                window.location.href = "{{ route($route . '.create') }}";
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
                                    "" ?
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
            $('#filter-employee, #filter-customer, #filter-status').change(function() {
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
                    span.css('transform', 'translateX(' + (textWidth - cellWidth) + 'px)');
                }
            }).on('mouseleave', '.table-ellipsis', function() {
                $(this).find('span').css('transform', 'translateX(0)');
            });
        });
    </script>

    <script>
        $(document).on('click', '.approval-link', function(e) {
            e.preventDefault();

            let contractId = $(this).data('id');
            let fieldName = $(this).data('field');
            let action = $(this).data('action');
            let link = $(this);
            let confirmTitle = '';
            let confirmButtonText = '';

            if (action === 'approve') {
                confirmTitle = 'هل أنت متأكد من اعتماد هذه الخطوة؟';
                confirmButtonText = 'اعتماد';
            } else if (action === 'revoke') {
                confirmTitle = 'هل أنت متأكد من إلغاء الاعتماد؟';
                confirmButtonText = 'إلغاء الاعتماد';
            }

            Swal.fire({
                title: confirmTitle,
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
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'إلغاء',
                reverseButtons: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    sendAjaxRequest(action, contractId, fieldName, link);
                }
            });
        });

        function sendAjaxRequest(action, contractId, fieldName, link) {
            $.ajax({
                url: '/contracts/' + action + '/' + contractId + '/' + fieldName,
                method: 'GET',
                success: function(response) {
                    if (action === 'approve') {
                        link.find('i').removeClass('text-danger').addClass('text-success');
                        link.data('action', 'revoke');
                        toastr.success('تم اعتماد الخطوة بنجاح.');
                    } else if (action === 'revoke') {
                        link.find('i').removeClass('text-success').addClass('text-danger');
                        link.data('action', 'approve');
                        toastr.success('تم إلغاء الاعتماد بنجاح.');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.error || 'حدث خطأ أثناء تنفيذ الإجراء.');
                }
            });
        }
    </script>

@endsection
@section('content')
    {{-- <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العقود</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalContracts }}</h4>
                            </div>
                            <small class="mb-0">إجمالي العقود</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-building ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العقود الجديدة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $newContracts }}</h4>
                            </div>
                            <small class="mb-0">العقود الجديدة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="ti ti-file-plus ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العقود المتأخرة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $lateContracts }}</h4>
                            </div>
                            <small class="mb-0">العقود المتأخرة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-alert-circle ti-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">العقود المغلقة</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $closedContracts }}</h4>
                            </div>
                            <small class="mb-0">العقود المغلقة</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="ti ti-lock ti-26px"></i>
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
            <div class="filters row g-3">
                <div class="col-md-4">
                    <label for="filter-employee">المكلف </label>
                    <select id="filter-employee" name="status" class="form-control select2"
                        data-placeholder="اختر المكلف ">
                        <option value=""></option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-customer">العميل</label>
                    <select id="filter-customer" class="form-control select2" data-placeholder="اختر العميل">
                        <option value=""></option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-status">حالة العقد</label>
                    <select id="filter-status" class="form-control select2" data-placeholder="اختر  حالة العقد">
                        <option value=""></option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status['name'] }}">
                                {{ $status['text'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="mt-10">
            <table id="setting-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th class="custom-checkbox">
                            <input type="checkbox" id="select-all">
                        </th>
                        <th>اسم العقد</th>
                        <th> العميل </th>
                        <th> المكلف </th>
                        <th>حالة الإعتماد </th>
                        <th>اضيف بواسطة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

@endsection
